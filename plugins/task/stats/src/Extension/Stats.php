<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.stats
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Stats\Extension;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin taht periodically send statistics
 * {@see ExecuteTaskEvent}.
 *
 * @since 5.0.0
 */
final class Stats extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const TASKS_MAP = [
        'send.stats' => [
            'langConstPrefix' => 'PLG_TASK_STATS_SEND',
            'form'            => 'sendForm',
            'method'          => 'sendStats',
        ],
    ];

    /**
     * @var boolean
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Unique identifier for this site
     *
     * @var    string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $uniqueId;

    /**
     * URL to send the statistics.
     *
     * @var    string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $serverUrl = 'https://developer.joomla.org/stats/submit';

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
            'onGetStatsData'       => 'onGetStats',
        ];
    }

    /**
     * Method to send the update notification.
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  __DEPLOY_VERSION__
     * @throws \Exception
     */
    private function sendStats(ExecuteTaskEvent $event): int
    {
        // Load the parameters.
        $params         = $event->getArgument('params');
        $this->uniqueId = $params->unique_id ?: $this->getUniqueId();
        $event->setArgument('unique_id', $this->uniqueId);
        $data  = $this->getStatsData($this->uniqueId);
        $error = '';

        try {
            // Don't let the request take longer than 2 seconds to avoid page timeout issues
            $response = HttpFactory::getHttp()->post($this->serverUrl, $data, [], 2);
            if (!$response) {
                $error = 'Could not send site statistics to remote server: No response';
            } elseif ($response->code !== 200) {
                $data = json_decode($response->body);

                $error = 'Could not send site statistics to remote server: ' . $data->message;
            }
        } catch (\UnexpectedValueException $e) {
            // There was an error sending stats. Should we do anything?
            $error = 'Could not send site statistics to remote server: ' . $e->getMessage();
        } catch (\RuntimeException $e) {
            // There was an error connecting to the server or in the post request
            $error = 'Could not connect to statistics server: ' . $e->getMessage();
        } catch (\Exception $e) {
            // An unexpected error in processing; don't let this failure kill the site
            $error = 'Unexpected error connecting to statistics server: ' . $e->getMessage();
        }
        $this->logTask('Stats end ' . $error);

        return Status::OK;
    }

    /**
     * Get the data that will be sent to the stats server.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getStatsData($value)
    {
        $data = [
            'unique_id'   => $value,
            'php_version' => PHP_VERSION,
            'db_type'     => $this->getDatabase()->name,
            'db_version'  => $this->getDatabase()->getVersion(),
            'cms_version' => JVERSION,
            'server_os'   => php_uname('s') . ' ' . php_uname('r'),
        ];

        // Check if we have a MariaDB version string and extract the proper version from it
        if (preg_match('/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i', $data['db_version'], $versionParts)) {
            $data['db_version'] = $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];
        }

        return $data;
    }

    /**
     * Get the data through events
     *
     * @param   Event  $event where this will be called from
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onGetStats(Event $event): void
    {
        $result[] = $this->getStatsData($event->getArgument('unique_id'));
        $event->setArgument('result', $result);
    }

    /**
     * Get the unique id. Generates one if none is set.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getUniqueId()
    {
        if (null === $this->uniqueId) {
            $this->uniqueId = $this->params->get('unique_id', hash('sha1', UserHelper::genRandomPassword(28) . time()));
        }

        return $this->uniqueId;
    }
}
