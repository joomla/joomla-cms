<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Scheduler;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\User\User;
use Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent;
use Joomla\Component\Scheduler\Administrator\Model\TaskModel;
use Joomla\Component\Scheduler\Administrator\Model\TasksModel;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Scheduler class provides the core functionality of ComScheduler.
 * Currently, this includes fetching scheduled tasks from the database
 * and execution of any or the next due task.
 * It is planned that this class is extended with C[R]UD methods for
 * scheduled tasks.
 *
 * @since 4.1.0
 * @todo  A global instance?
 */
class Scheduler
{
    private const LOG_TEXT = [
        Status::OK          => 'COM_SCHEDULER_SCHEDULER_TASK_COMPLETE',
        Status::WILL_RESUME => 'COM_SCHEDULER_SCHEDULER_TASK_WILL_RESUME',
        Status::NO_LOCK     => 'COM_SCHEDULER_SCHEDULER_TASK_LOCKED',
        Status::NO_RUN      => 'COM_SCHEDULER_SCHEDULER_TASK_UNLOCKED',
        Status::NO_ROUTINE  => 'COM_SCHEDULER_SCHEDULER_TASK_ROUTINE_NA',
    ];

    /**
     * Filters for the task queue. Can be used with fetchTaskRecords().
     *
     * @since 4.1.0
     * @todo  remove?
     */
    public const TASK_QUEUE_FILTERS = [
        'due'    => 1,
        'locked' => -1,
    ];

    /**
     * List config for the task queue. Can be used with fetchTaskRecords().
     *
     * @since 4.1.0
     * @todo  remove?
     */
    public const TASK_QUEUE_LIST_CONFIG = [
        'multi_ordering' => ['a.priority DESC ', 'a.next_execution ASC'],
    ];

    /**
     * Run a scheduled task.
     * Runs a single due task from the task queue by default if $id and $title are not passed.
     *
     * @param   array  $options  Array with options to configure the method's behavior. Supports:
     *                           1. `id`: (Optional) ID of the task to run.
     *                           2. `allowDisabled`: Allow running disabled tasks.
     *                           3. `allowConcurrent`: Allow concurrent execution, i.e., running the task when another
     *                           task may be running.
     *
     * @return ?Task  The task executed or null if not exists
     *
     * @since 4.1.0
     * @throws \RuntimeException
     */
    public function runTask(array $options): ?Task
    {
        $resolver = new OptionsResolver();

        try {
            $this->configureTaskRunnerOptions($resolver);
        } catch (\Exception $e) {
        }

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            if ($e instanceof UndefinedOptionsException || $e instanceof InvalidOptionsException) {
                throw $e;
            }
        }

        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        // ? Sure about inferring scheduling bypass?
        $task = $this->getTask(
            [
                'id'                  => (int) $options['id'],
                'allowDisabled'       => $options['allowDisabled'],
                'bypassScheduling'    => (int) $options['id'] !== 0,
                'allowConcurrent'     => $options['allowConcurrent'],
                'includeCliExclusive' => ($app->isClient('cli')),
            ]
        );

        // ? Should this be logged? (probably, if an ID is passed?)
        if (empty($task)) {
            return null;
        }

        $app->getLanguage()->load('com_scheduler', JPATH_ADMINISTRATOR);

        $options['text_entry_format'] = '{DATE}	{TIME}	{PRIORITY}	{MESSAGE}';
        $options['text_file']         = 'joomla_scheduler.php';
        Log::addLogger($options, Log::ALL, $task->logCategory);

        $taskId    = $task->get('id');
        $taskTitle = $task->get('title');

        $task->log(Text::sprintf('COM_SCHEDULER_SCHEDULER_TASK_START', $taskId, $taskTitle), 'info');

        // Let's try to avoid time-outs
        if (\function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        try {
            $task->run();
        } catch (\Exception) {
            // We suppress the exception here, it's still accessible with `$task->getContent()['exception']`.
        }

        $executionSnapshot = $task->getContent();
        $exitCode          = $executionSnapshot['status'] ?? Status::NO_EXIT;
        $netDuration       = $executionSnapshot['netDuration'] ?? 0;
        $duration          = $executionSnapshot['duration'] ?? 0;

        if (\array_key_exists($exitCode, self::LOG_TEXT)) {
            $level = \in_array($exitCode, [Status::OK, Status::WILL_RESUME]) ? 'info' : 'warning';
            $task->log(Text::sprintf(self::LOG_TEXT[$exitCode], $taskId, $duration, $netDuration), $level);

            return $task;
        }

        $task->log(
            Text::sprintf('COM_SCHEDULER_SCHEDULER_TASK_UNKNOWN_EXIT', $taskId, $duration, $netDuration, $exitCode),
            'warning'
        );

        return $task;
    }

    /**
     * Set up an {@see OptionsResolver} to resolve options compatible with {@see runTask}.
     *
     * @param   OptionsResolver  $resolver  The {@see OptionsResolver} instance to set up.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws AccessException
     */
    protected function configureTaskRunnerOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'id'              => 0,
                'allowDisabled'   => false,
                'allowConcurrent' => false,
            ]
        )
            ->setAllowedTypes('id', 'numeric')
            ->setAllowedTypes('allowDisabled', 'bool')
            ->setAllowedTypes('allowConcurrent', 'bool');
    }

    /**
     * Get the next task which is due to run, limit to a specific task when ID is given
     *
     * @param   array  $options  Options for the getter, see {@see TaskModel::getTask()}.
     *                           ! should probably also support a non-locking getter.
     *
     * @return  Task $task The task to execute
     *
     * @since 4.1.0
     * @throws \RuntimeException
     */
    public function getTask(array $options = []): ?Task
    {
        $resolver = new OptionsResolver();

        try {
            TaskModel::configureTaskGetterOptions($resolver);
        } catch (\Exception $e) {
        }

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            if ($e instanceof UndefinedOptionsException || $e instanceof InvalidOptionsException) {
                throw $e;
            }
        }

        try {
            /** @var SchedulerComponent $component */
            $component = Factory::getApplication()->bootComponent('com_scheduler');

            /** @var TaskModel $model */
            $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        } catch (\Exception) {
        }

        if (!isset($model)) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        $task = $model->getTask($options);

        if (empty($task)) {
            return null;
        }

        return new Task($task);
    }

    /**
     * Fetches a single scheduled task in a Task instance.
     * If no id or title is specified, a due task is returned.
     *
     * @param   int   $id             The task ID.
     * @param   bool  $allowDisabled  Allow disabled/trashed tasks?
     *
     * @return ?object  A matching task record, if it exists
     *
     * @since 4.1.0
     * @throws \RuntimeException
     */
    public function fetchTaskRecord(int $id = 0, bool $allowDisabled = false): ?object
    {
        $filters    = [];
        $listConfig = ['limit' => 1];

        if ($id > 0) {
            $filters['id'] = $id;
        } else {
            // Filters and list config for scheduled task queue
            $filters['due']               = 1;
            $filters['locked']            = -1;
            $listConfig['multi_ordering'] = [
                'a.priority DESC',
                'a.next_execution ASC',
            ];
        }

        if ($allowDisabled) {
            $filters['state'] = '';
        }

        return $this->fetchTaskRecords($filters, $listConfig)[0] ?? null;
    }

    /**
     * @param   array  $filters     The filters to set to the model
     * @param   array  $listConfig  The list config (ordering, etc.) to set to the model
     *
     * @return array
     *
     * @since 4.1.0
     * @throws \RunTimeException
     */
    public function fetchTaskRecords(array $filters, array $listConfig): array
    {
        $model = null;

        try {
            /** @var SchedulerComponent $component */
            $component = Factory::getApplication()->bootComponent('com_scheduler');

            /** @var TasksModel $model */
            $model = $component->getMVCFactory()
                ->createModel('Tasks', 'Administrator', ['ignore_request' => true]);
        } catch (\Exception) {
        }

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        $model->setState('list.select', 'a.*');

        // Default to only enabled tasks
        if (!isset($filters['state'])) {
            $model->setState('filter.state', 1);
        }

        // Default to including orphaned tasks
        $model->setState('filter.orphaned', 0);

        // Default to ordering by ID
        $model->setState('list.ordering', 'a.id');
        $model->setState('list.direction', 'ASC');

        // List options
        foreach ($listConfig as $key => $value) {
            $model->setState('list.' . $key, $value);
        }

        // Filter options
        foreach ($filters as $type => $filter) {
            $model->setState('filter.' . $type, $filter);
        }

        return $model->getItems() ?: [];
    }

    /**
     * Determine whether a {@see User} is allowed to run a task record. Expects a task as an object from
     * {@see fetchTaskRecords}.
     *
     * @param   object  $taskRecord  The task record to check authorization against.
     * @param   User    $user        The user to check authorization for.
     *
     * @return boolean  True if the user is authorized to run the task.
     *
     * @since 5.2.4
     */
    public static function isAuthorizedToRun(object $taskRecord, User $user): bool
    {
        /**
         * We allow the user to run a task if they have the permission or if they created the task & still have the authority
         * to create tasks.
         */
        if (
            $user->authorise('core.testrun', 'com_scheduler.task.' . $taskRecord->id)
            || ($user->id == $taskRecord->created_by && $user->authorise('core.create', 'com_scheduler'))
        ) {
            return true;
        }

        return false;
    }
}
