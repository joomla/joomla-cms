<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.deleteactionlogs
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\DeleteActionLogs\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin. For Delete Action Logs after x days
 * {@see ExecuteTaskEvent}.
 *
 * @since __DEPLOY_VERSION__
 */
final class DeleteActionLogs extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const TASKS_MAP = [
        'delete.logs' => [
            'langConstPrefix' => 'PLG_TASK_DELETEACTIONLOGS_DELETE',
            'method'          => 'deleteLogs',
            'form'            => 'deleteForm',
        ],
    ];

    /**
     * @var boolean
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

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
        ];
    }

    /**
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  __DEPLOY_VERSION__
     * @throws \Exception
     */
    private function deleteLogs(ExecuteTaskEvent $event): int
    {
        $daysToDeleteAfter = (int) $event->getArgument('params')->logDeletePeriod ?? 0;
        $this->logTask(sprintf('Delete Logs after %d days', $daysToDeleteAfter));
        $now               = Factory::getDate()->toSql();
        $db                = $this->getDatabase();
        $query             = $db->getQuery(true);

        if ($daysToDeleteAfter > 0) {
            $days = -1 * $daysToDeleteAfter;

            $query->clear()
                ->delete($db->quoteName('#__action_logs'))
                ->where($db->quoteName('log_date') . ' < ' . $query->dateAdd($db->quote($now), $days, 'DAY'));

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                // Ignore it
                return Status::KNOCKOUT;
            }
        }

        $this->logTask('Delete Logs end');

        return Status::OK;
    }
}
