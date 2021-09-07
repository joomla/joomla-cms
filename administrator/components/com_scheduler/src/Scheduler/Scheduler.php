<?php
/**
 * @package         Joomla.Administrator
 * @subpackage      com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Scheduler;

// Restrict direct access
defined('_JEXEC') or die;

use Assert\AssertionFailedException;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent;
use Joomla\Component\Scheduler\Administrator\Model\TasksModel;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/**
 * The Scheduler class provides the core functionality of ComScheduler.
 * Currently, this includes fetching scheduled tasks from the database
 * and execution of any or the next due task.
 * It is planned that this class is extended with C[R]UD methods for
 * scheduled tasks.
 *
 * @since __DEPLOY_VERSION__
 * @todo  A global instance?
 */
class Scheduler
{
	private const LOG_TEXT = [
		Status::OK      => 'COM_SCHEDULER_SCHEDULER_TASK_COMPLETE',
		Status::NO_LOCK => 'COM_SCHEDULER_SCHEDULER_TASK_LOCKED',
		Status::NO_RUN  => 'COM_SCHEDULER_SCHEDULER_TASK_UNLOCKED'
	];

	/**
	 * @var  CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * @var  DatabaseInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * @var  SchedulerComponent
	 * @since  __DEPLOY_VERSION__
	 */
	protected $component;

	/**
	 * Scheduler class constructor
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();
		$this->db = Factory::getContainer()->get(DatabaseDriver::class);
		$this->component = $this->app->bootComponent('com_scheduler');
		$this->app->getLanguage()->load('com_scheduler', JPATH_ADMINISTRATOR);
	}

	/**
	 * @param   int          $id     The task ID
	 * @param   string|null  $title  The task title
	 *
	 * @return void
	 *
	 * @throws AssertionFailedException|Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function runTask(int $id = 0, ?string $title = ''): void
	{
		$task = $this->fetchTask($id, $title);

		if (!$task)
		{
			return;
		}

		$options['text_entry_format'] = '{DATE}	{TIME}	{PRIORITY}	{MESSAGE}';
		$options['text_file'] = 'joomla_scheduler.php';
		Log::addLogger($options, Log::ALL, $task->logCategory);

		$taskId = $task->get('id');
		$taskTitle = $task->get('title');

		$task->log(Text::sprintf('COM_SCHEDULER_SCHEDULER_TASK_START', $taskId, $taskTitle), 'info');

		$task->run();
		$exitCode = $task->snapshot['status'] ?? Status::NO_EXIT;

		$netDuration = $task->snapshot['netDuration'] ?? 0;
		$duration = $task->snapshot['duration'] ?? 0;

		if (array_key_exists($exitCode, self::LOG_TEXT))
		{
			$level = $exitCode === Status::OK ? 'info' : 'warning';
			$task->log(Text::sprintf(self::LOG_TEXT[$exitCode], $taskId, $duration, $netDuration), $level);

			return;
		}

		$task->log(Text::sprintf('COM_SCHEDULER_SCHEDULER_TASK_UNKNOWN_EXIT', $taskId, $duration, $netDuration, $exitCode),
			'warning'
		);
	}

	/**
	 * Fetches a single scheduled task in a Task instance.
	 * If no id or title is specified, a due task is returned.
	 *
	 * @param   int|null     $id     The task ID
	 * @param   string|null  $title  The task title
	 *
	 * @return ?Task
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function fetchTask(int $id = 0, string $title = ''): ?Task
	{
		$record = $this->fetchTaskRecord($id, $title);

		if (!$record)
		{
			return null;
		}

		return new Task($record);
	}

	/**
	 * Fetches a single scheduled task in a Task instance.
	 * If no id or title is specified, a due task is returned.
	 *
	 * @param   int     $id     The task ID
	 * @param   string  $title  The task title
	 *
	 * @return ?object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function fetchTaskRecord(int $id = 0, string $title = ''): ?object
	{
		$filters = [];

		if ($id)
		{
			$filters['id'] = 1;
		}
		elseif ($title)
		{
			// Maybe, search?
			$filters['title'] = $title;
		}
		else
		{
			$filters['due'] = 1;
			$filters['locked'] = -1;
		}

		return $this->fetchTasks($filters, ['limit' => 1])[0] ?? null;
	}

	/**
	 * @param   array  $filters     The filters to set to the model
	 * @param   array  $listConfig  The list config (ordering, etc.) to set to the model
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function fetchTasks(array $filters, array $listConfig): array
	{
		$model = null;

		try
		{
			/** @var TasksModel $model */
			$model = $this->component->getMVCFactory()->createModel('Tasks', 'Administrator');
		}
		catch (Exception $e)
		{
		}

		if (!$model)
		{
			throw new RunTimeException('JLIB_APPLICATION_ERROR_MODEL_CREATE');
		}

		$model->set('__state_set', true);

		$model->setState('list.select', '*');

		// Default to only enabled tasks
		$model->setState('filter.state', 1);

		// Default to excluding orphaned tasks
		$model->setState('filter.orphaned', -1);

		$model->setState('list.ordering', 'a.next_execution');
		$model->setState('list.direction', 'ASC');

		$model->setState('list.multi_ordering', [
				'a.priority DESC',
				'a.next_execution ASC'
			]
		);

		// List options
		foreach ($listConfig as $key => $value)
		{
			$model->setState('list.' . $key, $value);
		}

		// Filter options
		foreach ($filters as $type => $filter)
		{
			$model->setState('filter.' . $type, $filter);
		}

		return $model->getItems() ?? [];
	}
}
