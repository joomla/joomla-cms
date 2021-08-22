<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      System.ScheduleRunner
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Helper\ExecRuleHelper;
use Joomla\Component\Scheduler\Administrator\Model\TasksModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * The plugin class for Plg_System_Schedulerunner.
 *
 * @since __DEPLOY_VERSION__
 */
class PlgSystemSchedulerunner extends CMSPlugin implements SubscriberInterface
{

	/**
	 * Exit Code For no time to run
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const TASK_NO_TIME = 1;

	/**
	 * Exit Code For lock failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const TASK_NO_LOCK = 2;

	/**
	 * Exit Code For execution failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const TASK_NO_RUN = 3;

	/**
	 * Exit Code For execution success
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const TASK_OK_RUN = 0;

	/**
	 * Replacement exit code for task with no exit code
	 * ! Removal due
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const TASK_NO_EXIT = -1;

	/**
	 * @var CMSApplication
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * @var DatabaseInterface
	 * @since __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Stores the schedule runner status
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	protected $snapshot = [];

	private const LOG_TEXT = [
		self::TASK_OK_RUN => 'PLG_SYSTEM_SCHEDULE_RUNNER_RUN_COMPLETE',
		self::TASK_NO_LOCK => 'PLG_SYSTEM_SCHEDULE_RUNNER_LOCKED',
		self::TASK_NO_RUN => 'PLG_SYSTEM_SCHEDULE_RUNNER_UNLOCKED'
	];

	/**
	 * Override parent constructor.
	 * Prevents the plugin from attaching to the subject if conditions are not met.
	 *
	 * @param   DispatcherInterface  $subject  The object to observe
	 * @param   array                $config   An optional associative array of configuration settings.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config = [])
	{
		// Make sure com_scheduler is installed and enabled
		if (!ComponentHelper::isEnabled('com_scheduler'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRespond' => 'executeDueTask'
		];
	}

	/**
	 * @param   Event  $event  The onAfterRespond event
	 *
	 * @return void
	 * @throws Exception|RuntimeException
	 * @since __DEPLOY_VERSION__
	 */
	public function executeDueTask(Event $event): void
	{
		// We only act on site requests
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// TODO: Should use a lock for this plugin too

		$this->snapshot['startTime'] = microtime(true);

		/** @var MVCComponent $component */
		$component = $this->app->bootComponent('com_scheduler');

		/** @var TasksModel $model */
		$model = $component->getMVCFactory()->createModel('Tasks', 'Administrator');

		if (!$model)
		{
			throw new RuntimeException('JLIB_APPLICATION_ERROR_MODEL_CREATE');
		}

		$dueTask = $this->getDueTasks($model)[0] ?? null;

		if (!$dueTask)
		{
			return;
		}

		// Log events -- should we use action logger  or this or both?
		$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_scheduler.php';
		Log::addLogger($options, Log::INFO, ['scheduler']);

		$taskId = $dueTask->id;
		$taskTitle = $dueTask->title;

		// Add task ID, Title etc
		Log::add(
			Text::sprintf('PLG_SYSTEM_SCHEDULE_RUNNER_START', $taskId, $taskTitle),
			Log::INFO,
			'scheduler'
		);

		$taskRun = $this->runTask($dueTask);
		$status = $this->snapshot['status'];
		$duration = $this->snapshot['duration'];

		if (!$taskRun)
		{
			// TODO: Exit code ?
			Log::add(
				Text::sprintf(self::LOG_TEXT[$status], $taskId, 0),
				Log::INFO,
				'scheduler'
			);

			return;
		}

		Log::add(
			Text::sprintf(self::LOG_TEXT[$status], $taskId, $duration, 0),
			LOG::INFO,
			'scheduler'
		);
	}

	/**
	 * Fetches due tasks from TasksModel
	 * ! Orphan filtering + pagination issues in the Model will break this if orphaned tasks exist [TODO]
	 *
	 * @param   TasksModel  $model   The TasksModel
	 * @param   boolean     $single  If true, only a single task is returned
	 *
	 * @return object[]
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function getDueTasks(TasksModel $model, bool $single = true): array
	{
		$model->set('__state_set', true);

		$model->setState('list.select',
			'a.id, a.title, a.type, a.next_execution, a.times_executed, a.times_failed, a.params, a.cron_rules'
		);

		$model->setState('list.start', 0);

		if ($single)
		{
			$model->setState('list.limit', 1);
		}

		$model->setState('filter.state', '1');

		$model->setState('filter.due', 1);

		$model->setState('filter.show_orphaned', 0);

		$model->setState('list.ordering', 'a.next_execution');
		$model->setState('list.direction', 'ASC');

		// Get smarter objects
		$model->setState('list.customClass', true);

		return $model->getItems() ?? [];
	}

	/**
	 * @param   object   $task        The task entry
	 * @param   boolean  $scheduling  Respect scheduling settings and state
	 *                                ! Does nothing
	 *
	 * @return boolean True on success
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function runTask(object $task, bool $scheduling = true): bool
	{
		$this->snapshot['taskId'] = $task->id;
		$this->snapshot['taskTitle'] = $task->title;
		$this->snapshot['status'] = self::TASK_NO_TIME;
		$this->snapshot['startTime'] = $this->snapshot['startTime'] ?? microtime(true);
		$this->snapshot['duration'] = 0;

		if (!$setLock = $this->setLock($task))
		{
			$this->snapshot['status'] = self::TASK_NO_LOCK;

			return false;
		}

		$app = $this->app;

		/** @var ExecuteTaskEvent $event */
		$event = AbstractEvent::create(
			'onExecuteTask',
			[
				'eventClass' => 'Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent',
				'subject' => $this,
				'taskId' => $task->type,
				'langConstPrefix' => $task->taskOption->langConstPrefix,
				'params' => json_decode($task->params),
			]
		);

		// TODO: test -- can use exception handling here to prevent locked tasks
		PluginHelper::importPlugin('task');
		$app->getDispatcher()->dispatch('onExecuteTask', $event);

		if (!$this->releaseLock($task, $event->getResultSnapshot()))
		{
			$this->snapshot['status'] = self::TASK_NO_RUN;

			return false;
		}

		$this->snapshot['endTime'] = microtime(true);
		$this->snapshot['status'] = self::TASK_OK_RUN;
		$this->snapshot['duration'] = $this->snapshot['endTime'] - $this->snapshot['startTime'];

		return true;
	}

	/**
	 * @param   object  $task  The task entry
	 *
	 * @return boolean  True on success
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function setLock(object $task): bool
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		$query->update($db->qn('#__scheduler_tasks', 't'))
			->set('t.locked = 1')
			->where($db->qn('t.id') . ' = :taskId')
			->where($db->qn('t.locked') . ' = 0')
			->bind(':taskId', $task->id, ParameterType::INTEGER);
		$db->setQuery($query)->execute();

		if (!$affRow = $db->getAffectedRows())
		{
			return false;
		}

		return true;
	}

	/**
	 * @param   object   $task        The task entry
	 * @param   ?array   $snapshot    The task snapshot, optional
	 * @param   boolean  $scheduling  Respect scheduling settings and state
	 *                                ! Does nothing
	 *
	 * @return boolean  True if success, else failure
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function releaseLock(object $task, array $snapshot = null, bool $scheduling = true): bool
	{
		$db = $this->db;

		$releaseQuery = $db->getQuery(true);
		$releaseQuery->update($db->qn('#__scheduler_tasks', 'j'))
			->set('j.locked = 0')
			->where($db->qn('j.id') . ' = :taskId')
			->where($db->qn('j.locked') . ' = 1')
			->bind(':taskId', $task->id, ParameterType::INTEGER);
		$db->setQuery($releaseQuery)->execute();

		if (!$affRow = $db->getAffectedRows())
		{
			// Log?
			return false;
		}

		$updateQuery = $db->getQuery(true);

		$taskId = $task->get('id');
		$ruleType = $task->get('cron_rules');
		$nextExec = (new ExecRuleHelper($task))->nextExec();
		$exitCode = $snapshot['status'] ?? self::TASK_NO_EXIT;
		$now = Factory::getDate('now', 'GMT')->toSql();

		/*
		 * [TODO] Failed status - should go in runTask()
		 */
		$updateQuery->update($db->qn('#__scheduler_tasks', 't'))
			->set(
				[
					't.last_execution = :now',
					't.next_execution = :nextExec',
					't.last_exit_code = :exitCode',
					't.times_executed = t.times_executed + 1'
				]
			)
			->where('t.id = :taskId')
			->bind(':nextExec', $nextExec)
			->bind(':exitCode', $exitCode, ParameterType::INTEGER)
			->bind(':now', $now)
			->bind(':taskId', $taskId, ParameterType::INTEGER);
		$db->setQuery($updateQuery)->execute();

		if (!$affRow = $db->getAffectedRows())
		{
			// Log ?
			return false;
		}

		return true;
	}
}
