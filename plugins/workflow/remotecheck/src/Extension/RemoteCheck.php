<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.RemoteCheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Workflow\RemoteCheck\Extension;

use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\CMS\Event\Workflow\WorkflowConditionFieldsEvent;
use Joomla\CMS\Event\Workflow\WorkflowResolveFieldsEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Http\HttpFactory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supplies a condition check whose answer lives outside Joomla.
 *
 * Declared SCOPE_ITEM, because a moment check is asked about many future times. Answers are
 * cached and fetched in one request per batch. A failed request returns nothing, so the item
 * is skipped rather than moved on a guess.
 *
 * @since  __DEPLOY_VERSION__
 */
final class RemoteCheck extends CMSPlugin implements SubscriberInterface, CacheControllerFactoryAwareInterface
{
    use CacheControllerFactoryAwareTrait;

    /**
     * The check's name. Prefixed, because this key is stored inside saved rules and must not
     * collide with a check some other extension names the same.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const FIELD = 'remotecheck.flag';

    /**
     * The only workflow extension this service knows anything about.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const EXTENSION = 'com_content.article';

    /**
     * Answers already fetched during this request, so evaluating several rules over the same
     * items does not repeat work the cache would only partly absorb.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private array $memo = [];

    /**
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onWorkflowListConditionFields'    => 'listConditionFields',
            'onWorkflowResolveConditionFields' => 'resolveConditionFields',
        ];
    }

    /**
     * Offers the check to the condition builder.
     *
     * @param   WorkflowConditionFieldsEvent  $event  The collecting event.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function listConditionFields(WorkflowConditionFieldsEvent $event): void
    {
        if ($event->getExtension() !== self::EXTENSION) {
            return;
        }

        // The builder can ask before this plugin's language file has been auto-loaded.
        $this->loadLanguage();

        $label    = trim((string) $this->params->get('checklabel', ''));
        $endpoint = trim((string) $this->params->get('endpoint', ''));

        if ($label === '' || $endpoint === '') {
            return;
        }

        $event->addField(
            self::FIELD,
            $label,
            WorkflowConditionFieldsEvent::SCOPE_ITEM,
            [
                WorkflowConditionFieldsEvent::OPERATOR_IS,
                WorkflowConditionFieldsEvent::OPERATOR_IS_NOT,
            ],
            WorkflowConditionFieldsEvent::VALUE_SELECT,
            [
                ['value' => '1', 'label' => Text::_('JYES')],
                ['value' => '0', 'label' => Text::_('JNO')],
            ]
        );
    }

    /**
     * Resolves the check for a whole batch of items.
     *
     * @param   WorkflowResolveFieldsEvent  $event  The resolving event.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function resolveConditionFields(WorkflowResolveFieldsEvent $event): void
    {
        if ($event->getField() !== self::FIELD || $event->getExtension() !== self::EXTENSION) {
            return;
        }

        // Always called, even with no values, so the check counts as claimed.
        $event->setValues($this->flagsFor(array_map('intval', $event->getItemIds())));
    }

    /**
     * The flag for each item, from memory, then the cache, then the service.
     *
     * @param   int[]  $itemIds  The items being evaluated.
     *
     * @return  array<int, string>  Item id to '1' or '0'. Items with no answer are absent.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function flagsFor(array $itemIds): array
    {
        $flags   = [];
        $unknown = [];

        foreach (array_unique($itemIds) as $itemId) {
            if (\array_key_exists($itemId, $this->memo)) {
                $flags[$itemId] = $this->memo[$itemId];

                continue;
            }

            $cached = $this->readCache($itemId);

            if ($cached !== null) {
                $this->memo[$itemId] = $cached;
                $flags[$itemId]      = $cached;

                continue;
            }

            $unknown[] = $itemId;
        }

        if ($unknown === []) {
            return $flags;
        }

        foreach ($this->askService($unknown) as $itemId => $flag) {
            $this->memo[$itemId] = $flag;
            $flags[$itemId]      = $flag;
            $this->writeCache($itemId, $flag);
        }

        return $flags;
    }

    /**
     * Asks the external service about a set of items.
     *
     * Sends {"ids": [1, 2, 3]} and expects {"flags": {"1": true, "2": false}} back. Any item
     * the service does not mention is simply left out of the result.
     *
     * @param   int[]  $itemIds  The items to ask about.
     *
     * @return  array<int, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function askService(array $itemIds): array
    {
        $endpoint = trim((string) $this->params->get('endpoint', ''));

        if ($endpoint === '') {
            $this->warn('no endpoint is configured, so nothing can be checked');

            return [];
        }

        try {
            $response = (new HttpFactory())->getHttp()->post(
                $endpoint,
                json_encode(['ids' => array_values($itemIds)]),
                ['Content-Type' => 'application/json'] + $this->authenticationHeader(),
                max(1, (int) $this->params->get('timeout', 5))
            );
        } catch (\Throwable $failure) {
            $this->warn('the service could not be reached: ' . $failure->getMessage());

            return [];
        }

        if ($response->getStatusCode() !== 200) {
            $this->warn('the service answered with HTTP ' . $response->getStatusCode());

            return [];
        }

        $decoded = json_decode((string) $response->getBody(), true);

        if (!\is_array($decoded) || !isset($decoded['flags']) || !\is_array($decoded['flags'])) {
            $this->warn('the service answered with something other than the expected JSON');

            return [];
        }

        $flags = [];

        foreach ($decoded['flags'] as $itemId => $flag) {
            // Ignore ids that were not asked about, so the service cannot answer for other items.
            if (\in_array((int) $itemId, $itemIds, true)) {
                $flags[(int) $itemId] = $flag ? '1' : '0';
            }
        }

        return $flags;
    }

    /**
     * The credential header to send, or none when the service needs no authentication.
     *
     * @return  array<string, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function authenticationHeader(): array
    {
        $header = trim((string) $this->params->get('authheader', 'Authorization'));
        $value  = (string) $this->params->get('authvalue', '');

        if ($header === '' || $value === '') {
            return [];
        }

        return [$header => $value];
    }

    /**
     * A previously cached flag, or null when there is none.
     *
     * @param   integer  $itemId  The item.
     *
     * @return  string|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function readCache(int $itemId): ?string
    {
        $cached = $this->cache()->get($this->cacheId($itemId));

        return $cached === false ? null : (string) $cached;
    }

    /**
     * Remembers one flag for as long as the configured lifetime.
     *
     * @param   integer  $itemId  The item.
     * @param   string   $flag    '1' or '0'.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function writeCache(int $itemId, string $flag): void
    {
        $this->cache()->store($flag, $this->cacheId($itemId));
    }

    /**
     * The cache controller, configured with this plugin's lifetime.
     *
     * @return  \Joomla\CMS\Cache\CacheController
     *
     * @since   __DEPLOY_VERSION__
     */
    private function cache()
    {
        return $this->getCacheControllerFactory()->createCacheController('output', [
            'defaultgroup' => 'plg_workflow_remotecheck',

            // Joomla measures this in minutes, not seconds.
            'lifetime' => max(1, (int) $this->params->get('cachelifetime', 15)),
            'caching'  => true,
        ]);
    }

    /**
     * The cache key for one item.
     *
     * The endpoint is part of the key, so pointing the plugin at a different service does not
     * hand back answers the previous one gave.
     *
     * @param   integer  $itemId  The item.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function cacheId(int $itemId): string
    {
        return md5((string) $this->params->get('endpoint', '')) . '.' . $itemId;
    }

    /**
     * Records why an answer is missing, in the same log the automation engine writes to.
     *
     * @param   string  $reason  What went wrong.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function warn(string $reason): void
    {
        Log::add('Remote check: ' . $reason . '.', Log::WARNING, 'workflow');
    }
}
