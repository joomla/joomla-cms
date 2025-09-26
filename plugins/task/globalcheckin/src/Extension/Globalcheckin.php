<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.Globalcheckin
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Globalcheckin\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Event\SubscriberInterface;

/**
 * Task plugin with routines to check in a checked out item.
 *
 * @since  5.0.0
 */
class Globalcheckin extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since 5.0.0
     */
    protected const TASKS_MAP = [
        'plg_task_globalcheckin_task_get' => [
            'langConstPrefix' => 'PLG_TASK_GLOBALCHECKIN',
            'form'            => 'globalcheckin_params',
            'method'          => 'makeCheckin',
        ],
    ];

    /**
     * @var boolean
     * @since 5.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 5.0.0
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
     * Standard method for the checkin routine.
     *
     * @param   ExecuteTaskEvent  $event  The onExecuteTask event
     *
     * @return  integer  The exit code
     *
     * @since   5.0.0
     */
    protected function makeCheckin(ExecuteTaskEvent $event): int
    {
        $db     = $this->getDatabase();
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $delay  = (int) $event->getArgument('params')->delay ?? 1;
        $failed = false;

        foreach ($tables as $tn) {
            // Make sure we get the right tables based on prefix.
            if (stripos($tn, $prefix) !== 0) {
                continue;
            }

            $fields = $db->getTableColumns($tn, false);

            if (!(isset($fields['checked_out'], $fields['checked_out_time']))) {
                continue;
            }

            $query = $db->createQuery()
                ->update($db->quoteName($tn))
                ->set($db->quoteName('checked_out') . ' = NULL')
                ->set($db->quoteName('checked_out_time') . ' = NULL');

            if ($fields['checked_out']->Null === 'YES') {
                $query->where($db->quoteName('checked_out') . ' IS NOT NULL');
            } else {
                $query->where($db->quoteName('checked_out') . ' > 0');
            }

            if ($delay > 0) {
                $delayTime = Factory::getDate('now', 'UTC')->sub(new \DateInterval('PT' . $delay . 'H'))->toSql();
                $query->where(
                    $db->quoteName('checked_out_time') . ' < ' . $db->quote($delayTime)
                );
            }

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (ExecutionFailureException) {
                // This failure isn't critical, don't care too much
                $failed = true;
            }
        }

        return $failed ? TaskStatus::INVALID_EXIT : TaskStatus::OK;
    }
}
