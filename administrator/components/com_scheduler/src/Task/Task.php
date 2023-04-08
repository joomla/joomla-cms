<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Task;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Helper\ExecRuleHelper;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Component\Scheduler\Administrator\Scheduler\Scheduler;
use Joomla\Component\Scheduler\Administrator\Table\TaskTable;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Task class defines methods for the execution, logging and
 * related properties of Tasks as supported by `com_scheduler`,
 * a Task Scheduling component.
 *
 * @since 4.1.0
 */
class Task implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Enumerated state for enabled tasks.
     *
     * @since 4.1.0
     */
    public const STATE_ENABLED = 1;

    /**
     * Enumerated state for disabled tasks.
     *
     * @since 4.1.0
     */
    public const STATE_DISABLED = 0;

    /**
     * Enumerated state for trashed tasks.
     *
     * @since 4.1.0
     */
    public const STATE_TRASHED = -2;

    /**
     * Map state enumerations to logical language adjectives.
     *
     * @since 4.1.0
     */
    public const STATE_MAP = [
        self::STATE_TRASHED  => 'trashed',
        self::STATE_DISABLED => 'disabled',
        self::STATE_ENABLED  => 'enabled',
    ];

    /**
     * The task snapshot
     *
     * @var   array
     * @since 4.1.0
     */
    protected $snapshot = [];

    /**
     * @var  Registry
     * @since 4.1.0
     */
    protected $taskRegistry;

    /**
     * @var  string
     * @since  4.1.0
     */
    public $logCategory;

    /**
     * @var  CMSApplication
     * @since  4.1.0
     */
    protected $app;

    /**
     * @var  DatabaseInterface
     * @since  4.1.0
     */
    protected $db;

    /**
     * Maps task exit codes to events which should be dispatched when the task finishes.
     * 'NA' maps to the event for general task failures.
     *
     * @var  string[]
     * @since  4.1.0
     */
    protected const EVENTS_MAP = [
        Status::OK          => 'onTaskExecuteSuccess',
        Status::NO_ROUTINE  => 'onTaskRoutineNotFound',
        Status::WILL_RESUME => 'onTaskRoutineWillResume',
        'NA'                => 'onTaskExecuteFailure',
    ];

    /**
     * Constructor for {@see Task}.
     *
     * @param   object  $record  A task from {@see TaskTable}.
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function __construct(object $record)
    {
        // Workaround because Registry dumps private properties otherwise.
        $taskOption     = $record->taskOption;
        $record->params = json_decode($record->params, true);

        $this->taskRegistry = new Registry($record);

        $this->set('taskOption', $taskOption);
        $this->app = Factory::getApplication();
        $this->db  = Factory::getContainer()->get(DatabaseDriver::class);
        $this->setLogger(Log::createDelegatedLogger());
        $this->logCategory = 'task' . $this->get('id');

        if ($this->get('params.individual_log')) {
            $logFile = $this->get('params.log_file') ?? 'task_' . $this->get('id') . '.log.php';

            $options['text_entry_format'] = '{DATE}	{TIME}	{PRIORITY}	{MESSAGE}';
            $options['text_file']         = $logFile;
            Log::addLogger($options, Log::ALL, [$this->logCategory]);
        }
    }

    /**
     * Get the task as a data object that can be stored back in the database.
     * ! This method should be removed or changed as part of a better API implementation for the driver.
     *
     * @return object
     *
     * @since 4.1.0
     */
    public function getRecord(): object
    {
        // ! Probably, an array instead
        $recObject = $this->taskRegistry->toObject();

        $recObject->cron_rules = (array) $recObject->cron_rules;

        return $recObject;
    }

    /**
     * Execute the task.
     *
     * @return boolean  True if success
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function run(): bool
    {
        /**
         * We try to acquire the lock here, only if we don't already have one.
         * We do this, so we can support two ways of running tasks:
         *   1. Directly through {@see Scheduler}, which optimises acquiring a lock while fetching from the task queue.
         *   2. Running a task without a pre-acquired lock.
         * ! This needs some more thought, for whether it should be allowed or if the single-query optimisation
         *   should be used everywhere, although it doesn't make sense in the context of fetching
         *   a task when it doesn't need to be run. This might be solved if we force a re-fetch
         *   with the lock or do it here ourselves (using acquireLock as a proxy to the model's
         *   getter).
         */
        if ($this->get('locked') === null) {
            $this->acquireLock();
        }

        // Exit early if task routine is not available
        if (!SchedulerHelper::getTaskOptions()->findOption($this->get('type'))) {
            $this->snapshot['status'] = Status::NO_ROUTINE;
            $this->skipExecution();
            $this->dispatchExitEvent();

            return $this->isSuccess();
        }

        $this->snapshot['status']      = Status::RUNNING;
        $this->snapshot['taskStart']   = $this->snapshot['taskStart'] ?? microtime(true);
        $this->snapshot['netDuration'] = 0;

        /** @var ExecuteTaskEvent $event */
        $event = AbstractEvent::create(
            'onExecuteTask',
            [
                'eventClass'      => ExecuteTaskEvent::class,
                'subject'         => $this,
                'routineId'       => $this->get('type'),
                'langConstPrefix' => $this->get('taskOption')->langConstPrefix,
                'params'          => $this->get('params'),
            ]
        );

        PluginHelper::importPlugin('task');

        try {
            $this->app->getDispatcher()->dispatch('onExecuteTask', $event);
        } catch (\Exception $e) {
            // Suppress the exception for now, we'll throw it again once it's safe
            $this->log(Text::sprintf('COM_SCHEDULER_TASK_ROUTINE_EXCEPTION', $e->getMessage()), 'error');
            $this->snapshot['exception'] = $e;
            $this->snapshot['status']    = Status::KNOCKOUT;
        }

        $resultSnapshot = $event->getResultSnapshot();

        $this->snapshot['taskEnd']     = microtime(true);
        $this->snapshot['netDuration'] = $this->snapshot['taskEnd'] - $this->snapshot['taskStart'];
        $this->snapshot                = array_merge($this->snapshot, $resultSnapshot);

        // @todo make the ExecRuleHelper usage less ugly, perhaps it should be composed into Task
        // Update object state.
        $this->set('last_execution', Factory::getDate('@' . (int) $this->snapshot['taskStart'])->toSql());
        $this->set('last_exit_code', $this->snapshot['status']);

        if ($this->snapshot['status'] !== Status::WILL_RESUME) {
            $this->set('next_execution', (new ExecRuleHelper($this->taskRegistry->toObject()))->nextExec());
            $this->set('times_executed', $this->get('times_executed') + 1);
        } else {
            /**
             * Resumable tasks need special handling.
             *
             * They are rescheduled as soon as possible to let their next step to be executed without
             * a very large temporal gap to the previous step.
             *
             * Moreover, the times executed does NOT increase for each step. It will increase once,
             * after the last step, when they return Status::OK.
             */
            $this->set('next_execution', Factory::getDate('now', 'UTC')->sub(new \DateInterval('PT1M'))->toSql());
        }

        // The only acceptable "successful" statuses are either clean exit or resuming execution.
        if (!in_array($this->snapshot['status'], [Status::WILL_RESUME, Status::OK])) {
            $this->set('times_failed', $this->get('times_failed') + 1);
        }

        if (!$this->releaseLock()) {
            $this->snapshot['status'] = Status::NO_RELEASE;
        }

        $this->dispatchExitEvent();

        if (!empty($this->snapshot['exception'])) {
            throw $this->snapshot['exception'];
        }

        return $this->isSuccess();
    }

    /**
     * Get the task execution snapshot.
     * ! Access locations will need updates once a more robust Snapshot container is implemented.
     *
     * @return array
     *
     * @since 4.1.0
     */
    public function getContent(): array
    {
        return $this->snapshot;
    }

    /**
     * Acquire a pseudo-lock on the task record.
     * ! At the moment, this method is not used anywhere as task locks are already
     *   acquired when they're fetched. As such this method is not functional and should
     *   not be reviewed until it is updated.
     *
     * @return boolean
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function acquireLock(): bool
    {
        $db    = $this->db;
        $query = $db->getQuery(true);
        $id    = $this->get('id');
        $now   = Factory::getDate('now', 'GMT');

        $timeout          = ComponentHelper::getParams('com_scheduler')->get('timeout', 300);
        $timeout          = new \DateInterval(sprintf('PT%dS', $timeout));
        $timeoutThreshold = (clone $now)->sub($timeout)->toSql();
        $now              = $now->toSql();

        // @todo update or remove this method
        $query->update($db->quoteName('#__scheduler_tasks'))
            ->set('locked = :now')
            ->where($db->quoteName('id') . ' = :taskId')
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('locked') . ' < :threshold',
                    $db->quoteName('locked') . 'IS NULL',
                ],
                'OR'
            )
            ->bind(':taskId', $id, ParameterType::INTEGER)
            ->bind(':now', $now)
            ->bind(':threshold', $timeoutThreshold);

        try {
            $db->lockTable('#__scheduler_tasks');
            $db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
            return false;
        } finally {
            $db->unlockTables();
        }

        if ($db->getAffectedRows() === 0) {
            return false;
        }

        $this->set('locked', $now);

        return true;
    }

    /**
     * Remove the pseudo-lock and optionally update the task record.
     *
     * @param   bool  $update  If true, the record is updated with the snapshot
     *
     * @return boolean
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function releaseLock(bool $update = true): bool
    {
        $db    = $this->db;
        $query = $db->getQuery(true);
        $id    = $this->get('id');

        $query->update($db->quoteName('#__scheduler_tasks', 't'))
            ->set('locked = NULL')
            ->where($db->quoteName('id') . ' = :taskId')
            ->where($db->quoteName('locked') . ' IS NOT NULL')
            ->bind(':taskId', $id, ParameterType::INTEGER);

        if ($update) {
            $exitCode      = $this->get('last_exit_code');
            $lastExec      = $this->get('last_execution');
            $nextExec      = $this->get('next_execution');
            $timesFailed   = $this->get('times_failed');
            $timesExecuted = $this->get('times_executed');

            $query->set(
                [
                    'last_exit_code = :exitCode',
                    'last_execution = :lastExec',
                    'next_execution = :nextExec',
                    'times_executed = :times_executed',
                    'times_failed = :times_failed',
                ]
            )
                ->bind(':exitCode', $exitCode, ParameterType::INTEGER)
                ->bind(':lastExec', $lastExec)
                ->bind(':nextExec', $nextExec)
                ->bind(':times_executed', $timesExecuted)
                ->bind(':times_failed', $timesFailed);
        }

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
            return false;
        }

        if (!$db->getAffectedRows()) {
            return false;
        }

        $this->set('locked', null);

        return true;
    }

    /**
     * @param   string  $message   Log message
     * @param   string  $priority  Log level, defaults to 'info'
     *
     * @return  void
     *
     * @since 4.1.0
     * @throws InvalidArgumentException
     */
    public function log(string $message, string $priority = 'info'): void
    {
        $this->logger->log($priority, $message, ['category' => $this->logCategory]);
    }

    /**
     * Advance the task entry's next calculated execution, effectively skipping the current execution.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function skipExecution(): void
    {
        $db    = $this->db;
        $query = $db->getQuery(true);

        $id       = $this->get('id');
        $nextExec = (new ExecRuleHelper($this->taskRegistry->toObject()))->nextExec(true, true);

        $query->update($db->quoteName('#__scheduler_tasks', 't'))
            ->set('t.next_execution = :nextExec')
            ->where('t.id = :id')
            ->bind(':nextExec', $nextExec)
            ->bind(':id', $id);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
        }

        $this->set('next_execution', $nextExec);
    }

    /**
     * Handles task exit (dispatch event).
     *
     * @return void
     *
     * @since 4.1.0
     *
     * @throws \UnexpectedValueException|\BadMethodCallException
     */
    protected function dispatchExitEvent(): void
    {
        $exitCode  = $this->snapshot['status'] ?? 'NA';
        $eventName = self::EVENTS_MAP[$exitCode] ?? self::EVENTS_MAP['NA'];

        $event = AbstractEvent::create(
            $eventName,
            [
                'subject' => $this,
            ]
        );

        $this->app->getDispatcher()->dispatch($eventName, $event);
    }

    /**
     * Was the task successful?
     *
     * @return boolean  True if the task was successful.
     * @since 4.1.0
     */
    public function isSuccess(): bool
    {
        return in_array(($this->snapshot['status'] ?? null), [Status::OK, Status::WILL_RESUME]);
    }

    /**
     * Set a task property. This method is a proxy to {@see Registry::set()}.
     *
     * @param   string   $path       Registry path of the task property.
     * @param   mixed    $value      The value to set to the property.
     * @param   ?string  $separator  The key separator.
     *
     * @return mixed|null
     *
     * @since 4.1.0
     */
    protected function set(string $path, $value, string $separator = null)
    {
        return $this->taskRegistry->set($path, $value, $separator);
    }

    /**
     * Get a task property. This method is a proxy to {@see Registry::get()}.
     *
     * @param   string  $path     Registry path of the task property.
     * @param   mixed   $default  Default property to return, if the actual value is null.
     *
     * @return mixed  The task property.
     *
     * @since 4.1.0
     */
    public function get(string $path, $default = null)
    {
        return $this->taskRegistry->get($path, $default);
    }

    /**
     * Static method to determine whether an enumerated task state (as a string) is valid.
     *
     * @param   string  $state  The task state (enumerated, as a string).
     *
     * @return boolean
     *
     * @since 4.1.0
     */
    public static function isValidState(string $state): bool
    {
        if (!is_numeric($state)) {
            return false;
        }

        // Takes care of interpreting as float/int
        $state = $state + 0;

        return ArrayHelper::getValue(self::STATE_MAP, $state) !== null;
    }

    /**
     * Static method to determine whether a task id is valid. Note that this does not
     * validate ids against the database, but only verifies that an id may exist.
     *
     * @param   string  $id  The task id (as a string).
     *
     * @return boolean
     *
     * @since 4.1.0
     */
    public static function isValidId(string $id): bool
    {
        $id = is_numeric($id) ? ($id + 0) : $id;

        if (!\is_int($id) || $id <= 0) {
            return false;
        }

        return true;
    }
}
