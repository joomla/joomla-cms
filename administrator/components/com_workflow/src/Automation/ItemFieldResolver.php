<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

use Joomla\CMS\Event\Workflow\WorkflowResolveFieldsEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Resolves check values for the items a rule is being evaluated against.
 *
 * Batched lazily: callers announce their items with preload(), and each check is resolved for
 * the whole set the first time it is needed.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ItemFieldResolver
{
    /**
     * The checks Joomla ships with. Held for the resolver's lifetime so its content type
     * lookups are cached across every check a run evaluates.
     *
     * @var BuiltinConditionFields
     * @since __DEPLOY_VERSION__
     */
    private BuiltinConditionFields $builtinFields;

    /**
     * The items a caller announced through preload(), grouped by extension, so a check can be
     * resolved for a whole set at once the first time it is asked for.
     *
     * @var    array<string, int[]>
     * @since  __DEPLOY_VERSION__
     */
    private array $batchItemIds = [];

    /**
     * Resolved values, keyed by extension, check and moment, then by item id.
     *
     * @var    array<string, array<int, mixed>>
     * @since  __DEPLOY_VERSION__
     */
    private array $resolved = [];

    /**
     * Which items have already been asked about, keyed like $resolved. Needed because a key
     * resolved earlier may not cover the items announced after it.
     *
     * @var    array<string, array<int, boolean>>
     * @since  __DEPLOY_VERSION__
     */
    private array $asked = [];

    /**
     * @param   DatabaseInterface  $database  The database driver.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->builtinFields = new BuiltinConditionFields($database);
    }

    /**
     * Announces the items about to be evaluated, so checks can be resolved for the whole set.
     *
     * @param   int[]   $itemIds    The content item ids.
     * @param   string  $extension  The workflow extension those ids belong to.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function preload(array $itemIds, string $extension): void
    {
        $this->batchItemIds[$extension] = array_values(array_unique(
            array_merge($this->batchItemIds[$extension] ?? [], array_map('intval', $itemIds))
        ));
    }

    /**
     * Builds a resolver callback bound to one item, for use by the ConditionEvaluator.
     *
     * @param   integer         $itemId          The content item id.
     * @param   string          $extension       The workflow extension, e.g. com_content.article.
     * @param   \DateTime|null  $evaluationTime  The moment clock-based checks should describe,
     *                                           or null for now.
     *
     * @return  callable  fn(string $fieldName): mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    public function forItem(int $itemId, string $extension, ?\DateTime $evaluationTime = null): callable
    {
        return function (string $fieldName) use ($itemId, $extension, $evaluationTime) {
            return $this->valueFor($fieldName, $itemId, $extension, $evaluationTime);
        };
    }

    /**
     * One item's value for one check, resolving the whole batch on first use.
     *
     * @param   string          $fieldName       The check.
     * @param   integer         $itemId          The item.
     * @param   string          $extension       The workflow extension.
     * @param   \DateTime|null  $evaluationTime  The moment to describe, or null for now.
     *
     * @return  mixed
     *
     * @throws  ConditionEvaluationException  When nothing provides the check, or whatever does
     * had no value for this item.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function valueFor(string $fieldName, int $itemId, string $extension, ?\DateTime $evaluationTime)
    {
        $cacheKey = $extension . '|' . $fieldName . '|'
            . ($evaluationTime ? $evaluationTime->getTimestamp() : 'now');

        $announced = $this->batchItemIds[$extension] ?? [];

        if (!isset($this->asked[$cacheKey][$itemId])) {
            $itemIds = \in_array($itemId, $announced, true) ? $announced : [$itemId];

            // Merged, because an earlier batch under this key may hold other items' answers.
            $this->resolved[$cacheKey] = ($this->resolved[$cacheKey] ?? [])
                + $this->resolveBatch($fieldName, $itemIds, $extension, $evaluationTime);

            foreach ($itemIds as $askedItemId) {
                $this->asked[$cacheKey][$askedItemId] = true;
            }
        }

        // A missing value, for example from a remote check that timed out, is not an empty one.
        // Comparing against it would be a guess, so the item is skipped instead.
        if (!\array_key_exists($itemId, $this->resolved[$cacheKey])) {
            throw new ConditionEvaluationException(\sprintf(
                'Nothing could resolve a value for the "%s" check on item %s.%d.',
                $fieldName,
                $extension,
                $itemId
            ));
        }

        return $this->resolved[$cacheKey][$itemId];
    }

    /**
     * Resolves one check for a set of items, from Joomla's own checks or from a plugin.
     *
     * @param string $fieldName The check.
     * @param int[] $itemIds The items.
     * @param string $extension The workflow extension.
     * @param \DateTime|null $evaluationTime The moment to describe, or null for now
     *
     * @return array<int, mixed>
     *
     * @throws ConditionEvaluationException When nothing provides the check.
     *
     * @since __DEPLOY_VERSION__
     */
    private function resolveBatch(
        string $fieldName,
        array $itemIds,
        string $extension,
        ?\DateTime $evaluationTime
    ): array {
        $builtinValues = $this->builtinFields->resolve($fieldName, $itemIds, $extension, $evaluationTime);

        // Null means the check is not a built-in; an empty array is a built-in with no values.
        if ($builtinValues !== null) {
            return $builtinValues;
        }

        return $this->askPlugins($fieldName, $itemIds, $extension, $evaluationTime);
    }

    /**
     * Asks the workflow plugins to resolve one check for a set of items.
     *
     * @param   string          $fieldName       The check.
     * @param   int[]           $itemIds         The items.
     * @param   string          $extension       The workflow extension.
     * @param   \DateTime|null  $evaluationTime  The moment to describe, or null for now.
     *
     * @return  array<int, mixed>
     *
     * @throws  ConditionEvaluationException  When no plugin claims the check.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function askPlugins(string $fieldName, array $itemIds, string $extension, ?\DateTime $evaluationTime): array
    {
        PluginHelper::importPlugin('workflow');

        $event = new WorkflowResolveFieldsEvent(
            'onWorkflowResolveConditionFields',
            [
                'field'          => $fieldName,
                'itemIds'        => $itemIds,
                'extension'      => $extension,
                'evaluationTime' => $evaluationTime,
            ]
        );

        Factory::getContainer()->get(DispatcherInterface::class)->dispatch($event->getName(), $event);

        if (!$event->isAnswered()) {
            throw new ConditionEvaluationException(\sprintf(
                'No installed extension provides the "%s" check that this rule uses on %s. '
                    . 'The extension that supplied it has probably been disabled or uninstalled.',
                $fieldName,
                $extension
            ));
        }

        return $event->getValues();
    }
}
