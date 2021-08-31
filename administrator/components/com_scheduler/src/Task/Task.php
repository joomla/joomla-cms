<?php
/**
 * Implements the Task class.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Task;

// Restrict direct access
defined('_JEXEC') or die;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Helper\ExecRuleHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RunTimeException;

/**
 * The Task class.
 * This class essentially extends a task record to define methods for its execution, logging and
 * related properties.
 *
 * @since __DEPLOY_VERSION__
 */
class Task extends Registry implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * The task snapshot
	 *
	 * @var   []
	 * @since __DEPLOY_VERSION__
	 */
	public $snapshot = [];

	/**
	 * @var  string
	 * @since  __DEPLOY_VERSION__
	 */
	public $logCategory;

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
	 * Override parent Registry constructor.
	 *
	 * @param   object  $record  A `#__scheduler_tasks` record
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(object $record)
	{
		// Hack because Registry dumps private properties otherwise
		$taskOption = $record->taskOption;
		$record->params = json_decode($record->params, true);

		parent::__construct($record);

		$this->set('taskOption', $taskOption);
		$this->app = Factory::getApplication();
		$this->db = Factory::getContainer()->get(DatabaseDriver::class);
		$this->setLogger(Log::createDelegatedLogger());
		$this->logCategory = 'task' . $this->get('id');

		if ($this->get('params.individual_log'))
		{
			$logFile = $this->get('params.log_file') ?? 'task_' . $this->get('id') . '.log.php';

			$options['text_entry_format'] = '{DATE}	{TIME}	{PRIORITY}	{MESSAGE}';
			$options['text_file'] = $logFile;
			Log::addLogger($options, Log::ALL, [$this->logCategory]);
		}

	}

	/**
	 *
	 * @return object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getRecord(): object
	{
		// ! Probably, an array instead
		$recObject = $this->toObject();

		// phpcs:ignore
		$recObject->cron_rules = (array)$recObject->cron_rules;

		return $recObject;
	}

	/**
	 * Execute the task.
	 *
	 * @return boolean  True if success
	 *
	 * @throws AssertionFailedException|Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function run(): bool
	{
		if (!$this->acquireLock())
		{
			$this->snapshot['status'] = Status::NO_LOCK;

			return $this->handleExit(false);
		}

		$this->snapshot['status'] = Status::NO_TIME;
		$this->snapshot['taskStart'] = $this->snapshot['taskStart'] ?? microtime(true);
		$this->snapshot['netDuration'] = 0;

		/** @var ExecuteTaskEvent $event */
		$event = AbstractEvent::create(
			'onExecuteTask',
			[
				'eventClass'      => 'Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent',
				'subject'         => $this,
				'routineId'       => $this->get('type'),
				'langConstPrefix' => $this->get('taskOption')->langConstPrefix,
				'params'          => $this->get('params'),
			]
		);

		PluginHelper::importPlugin('task');
		$this->app->getDispatcher()->dispatch('onExecuteTask', $event);

		$resultSnapshot = $event->getResultSnapshot();
		Assertion::notNull($resultSnapshot, 'No task execution snapshot!');

		$this->snapshot['taskEnd'] = microtime(true);
		$this->snapshot['netDuration'] = $this->snapshot['taskEnd'] - $this->snapshot['taskStart'];
		$this->snapshot = array_merge($this->snapshot, $resultSnapshot);

		if (!$this->releaseLock())
		{
			$this->snapshot['status'] = Status::NO_RELEASE;

			return $this->handleExit(false);
		}

		return $this->handleExit();
	}

	/**
	 * Acquire a pseudo-lock on the task record.
	 *
	 * @return boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function acquireLock(): bool
	{
		$db = $this->db;
		$query = $db->getQuery(true);
		$id = $this->get('id');

		$query->update($db->qn('#__scheduler_tasks', 't'))
			->set('t.locked = 1')
			->where($db->qn('t.id') . ' = :taskId')
			->where($db->qn('t.locked') . ' = 0')
			->bind(':taskId', $id, ParameterType::INTEGER);

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		if (!$db->getAffectedRows())
		{
			return false;
		}

		$this->set('locked', 1);

		return true;
	}

	/**
	 * Remove the pseudo-lock and optionally update the task record.
	 *
	 * @param   bool  $update     If true, the record is updated with the snapshot
	 *                            TODO: Update object state
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function releaseLock(bool $update = true): bool
	{
		$db = $this->db;
		$query = $db->getQuery(true);
		$id = $this->get('id');

		$query->update($db->qn('#__scheduler_tasks', 't'))
			->set('t.locked = 0')
			->where($db->qn('t.id') . ' = :taskId')
			->where($db->qn('t.locked') . ' = 1')
			->bind(':taskId', $id, ParameterType::INTEGER);

		if ($update)
		{
			$id = $this->get('id');

			// @todo make this look less ugly
			$nextExec = (new ExecRuleHelper($this->toObject()))->nextExec();
			$exitCode = $this->snapshot['status'] ?? Status::NO_EXIT;
			$now = Factory::getDate('now', 'GMT')->toSql();

			$query->set(
				[
					't.last_execution = :now',
					't.next_execution = :nextExec',
					't.last_exit_code = :exitCode',
					't.times_executed = t.times_executed + 1'
				]
			)
				->bind(':nextExec', $nextExec)
				->bind(':exitCode', $exitCode, ParameterType::INTEGER)
				->bind(':now', $now);

			if ($exitCode !== Status::OK)
			{
				$query->set('t.times_failed = t.times_failed + 1');
			}
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		if (!$db->getAffectedRows())
		{
			return false;
		}

		$this->set('locked', 0);

		return true;
	}

	/**
	 * @param   string  $message   Log message
	 * @param   string  $priority  Log level, defaults to 'info'
	 *
	 * @return  void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function log(string $message, string $priority = 'info'): void
	{
		$this->logger->log($priority, $message, ['category' => $this->logCategory]);
	}

	/**
	 * Handles task exit (dispatch event, return).
	 *
	 * @param   bool  $success  If true, execution was successful
	 *
	 * @return boolean  If true, execution was successful
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function handleExit(bool $success = true): bool
	{
		$eventName = $success ? 'onTaskExecuteSuccess' : 'onTaskExecuteFailure';

		AbstractEvent::create($eventName, [
				'subject' => $this
			]
		);

		return $success;
	}
}
