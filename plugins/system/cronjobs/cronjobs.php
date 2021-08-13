<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      System.Cronjobs
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GPL v3
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
use Joomla\Component\Cronjobs\Administrator\Event\CronRunEvent;
use Joomla\Component\Cronjobs\Administrator\Helper\ExecRuleHelper;
use Joomla\Component\Cronjobs\Administrator\Model\CronjobsModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * The plugin class for Plg_System_Cronjobs.
 *
 * @since __DEPLOY_VERSION__
 */
class PlgSystemCronjobs extends CMSPlugin implements SubscriberInterface
{

	/**
	 * Exit Code For no time to run
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_NO_TIME = 1;

	/**
	 * Exit Code For lock failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_NO_LOCK = 2;

	/**
	 * Exit Code For execution failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_NO_RUN = 3;

	/**
	 * Exit Code For execution success
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_OK_RUN = 0;

	/**
	 * Replacement exit code for job with no exit code
	 * ! Removal due
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const JOB_NO_EXIT = -1;

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
	 * Stores the pseudo-cron status
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	protected $snapshot = [];

	private const LOG_TEXT = [
		self::JOB_OK_RUN => 'PLG_SYSTEM_CRONJOBS_RUN_COMPLETE',
		self::JOB_NO_LOCK => 'PLG_SYSTEM_CRONJOBS_LOCKED',
		self::JOB_NO_RUN => 'PLG_SYSTEM_CRONJOBS_UNLOCKED'
	];

	/**
	 * Override parent constructor.
	 * Prevents the plugin from attaching to the subject if conditions are not met.
	 *
	 * @param   DispatcherInterface  $subject The object to observe
	 * @param   array                $config  An optional associative array of configuration settings.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config = [])
	{
		// Make sure com_cronjobs is installed and enabled
		if (! ComponentHelper::isEnabled('com_cronjobs'))
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
			'onAfterRespond' => 'executeDueJob'
		];
	}

	/**
	 * @param   Event  $event  The onAfterRespond event
	 *
	 * @return void
	 * @throws Exception|RuntimeException
	 * @since __DEPLOY_VERSION__
	 */
	public function executeDueJob(Event $event): void
	{
		// We only act on site requests
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// TODO: Should use a lock for this plugin too

		$this->snapshot['startTime'] = microtime(true);

		/** @var MVCComponent $component */
		$component = $this->app->bootComponent('com_cronjobs');

		/** @var CronjobsModel $model */
		$model = $component->getMVCFactory()->createModel('Cronjobs', 'Administrator');

		if (!$model)
		{
			throw new RuntimeException('JLIB_APPLICATION_ERROR_MODEL_CREATE');
		}

		$dueJob = $this->getDueJobs($model)[0] ?? null;

		if (!$dueJob)
		{
			return;
		}

		// Log events -- should we use action logger  or this or both?
		$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_cronjobs.php';
		Log::addLogger($options, Log::INFO, ['cronjobs']);

		$jobId = $dueJob->id;
		$jobTitle = $dueJob->title;

		// Add job ID, Title etc
		Log::add(
			Text::sprintf('PLG_SYSTEM_CRONJOBS_START', $jobId, $jobTitle),
			Log::INFO,
			'cronjobs'
		);

		$jobRun = $this->runJob($dueJob);
		$status = $this->snapshot['status'];
		$duration = $this->snapshot['duration'];

		if (!$jobRun)
		{
			// TODO: Exit code ?
			Log::add(
				Text::sprintf(self::LOG_TEXT[$status], $jobId, 0),
				Log::INFO,
				'cronjobs'
			);

			return;
		}

		Log::add(
			Text::sprintf(self::LOG_TEXT[$status], $jobId, $duration, 0),
			LOG::INFO,
			'cronjobs'
		);
	}

	/**
	 * Fetches due jobs from CronjobsModel
	 * ! Orphan filtering + pagination issues in the Model will break this if orphaned jobs exist [TODO]
	 *
	 * @param   CronjobsModel  $model   The CronjobsModel
	 * @param   boolean        $single  If true, only a single job is returned
	 *
	 * @return object[]
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function getDueJobs(CronjobsModel $model, bool $single = true): array
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
	 * @param   object   $cronjob     The cronjob entry
	 * @param   boolean  $scheduling  Respect scheduling settings and state
	 *                                ! Does nothing
	 *
	 * @return boolean True on success
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function runJob(object $cronjob, bool $scheduling = true): bool
	{
		$this->snapshot['jobId'] = $cronjob->id;
		$this->snapshot['jobTitle'] = $cronjob->title;
		$this->snapshot['status'] = self::JOB_NO_TIME;
		$this->snapshot['startTime'] = $this->snapshot['startTime'] ?? microtime(true);
		$this->snapshot['duration'] = 0;

		if (!$setLock = $this->setLock($cronjob))
		{
			$this->snapshot['status'] = self::JOB_NO_LOCK;

			return false;
		}

		$app = $this->app;

		/** @var CronRunEvent $event */
		$event = AbstractEvent::create(
			'onCronRun',
			[
				'eventClass' => 'Joomla\Component\Cronjobs\Administrator\Event\CronRunEvent',
				'subject' => $this,
				'jobId' => $cronjob->type,
				'langConstPrefix' => $cronjob->cronOption->langConstPrefix,
				'params' => json_decode($cronjob->params),
			]
		);

		// TODO: test -- can use exception handling here to prevent locked jobs
		PluginHelper::importPlugin('job');
		$app->getDispatcher()->dispatch('onCronRun', $event);

		if (!$this->releaseLock($cronjob, $event->getResultSnapshot()))
		{
			$this->snapshot['status'] = self::JOB_NO_RUN;

			return false;
		}

		$this->snapshot['endTime'] = microtime(true);
		$this->snapshot['status'] = self::JOB_OK_RUN;
		$this->snapshot['duration'] = $this->snapshot['endTime'] - $this->snapshot['startTime'];

		return true;
	}

	/**
	 * @param   object  $cronjob  The cronjob entry
	 *
	 * @return boolean  True on success
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function setLock(object $cronjob): bool
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		$query->update($db->qn('#__cronjobs', 'j'))
			->set('j.locked = 1')
			->where($db->qn('j.id') . ' = :jobId')
			->where($db->qn('j.locked') . ' = 0')
			->bind(':jobId', $cronjob->id, ParameterType::INTEGER);
		$db->setQuery($query)->execute();

		if (!$affRow = $db->getAffectedRows())
		{
			return false;
		}

		return true;
	}

	/**
	 * @param   object   $cronjob     The cronjob entry
	 * @param   ?array   $snapshot    The job snapshot, optional
	 * @param   boolean  $scheduling  Respect scheduling settings and state
	 *                                ! Does nothing
	 *
	 * @return boolean  True if success, else failure
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function releaseLock(object $cronjob, array $snapshot = null, bool $scheduling = true): bool
	{
		$db = $this->db;

		$releaseQuery = $db->getQuery(true);
		$releaseQuery->update($db->qn('#__cronjobs', 'j'))
			->set('locked = 0')
			->where($db->qn('id') . ' = :jobId')
			->where($db->qn('locked') . ' = 1')
			->bind(':jobId', $cronjob->id, ParameterType::INTEGER);
		$db->setQuery($releaseQuery)->execute();

		if (!$affRow = $db->getAffectedRows())
		{
			// Log?
			return false;
		}

		$updateQuery = $db->getQuery(true);

		$jobId = $cronjob->get('id');
		$ruleType = $cronjob->get('cron_rules');
		$nextExec = (new ExecRuleHelper($cronjob))->nextExec();
		$exitCode = $snapshot['status'] ?? self::JOB_NO_EXIT;
		$now = Factory::getDate('now', 'GMT')->toSql();

		/*
		 * [TODO] Failed status - should go in runJob()
		 */
		$updateQuery->update($db->qn('#__cronjobs', 'j'))
			->set(
				[
					'j.last_execution = :now',
					'j.next_execution = :nextExec',
					'j.last_exit_code = :exitCode',
					'j.times_executed = j.times_executed + 1'
				]
			)
			->where('j.id = :jobId')
			->bind(':nextExec', $nextExec)
			->bind(':exitCode', $exitCode, ParameterType::INTEGER)
			->bind(':now', $now)
			->bind(':jobId', $jobId, ParameterType::INTEGER);
		$db->setQuery($updateQuery)->execute();

		if (!$affRow = $db->getAffectedRows())
		{
			// Log ?
			return false;
		}

		return true;
	}
}
