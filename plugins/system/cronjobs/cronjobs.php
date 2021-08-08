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
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Cronjobs\Administrator\Model\CronjobsModel;
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
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;


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
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function executeDueJob(Event $event): void
	{
		/** @var MVCComponent $component */
		$component = $this->app->bootComponent('com_cronjobs');

		/** @var CronjobsModel $model */
		$model = $component->getMVCFactory()->createModel('Cronjobs', 'Administrator');

		$dueJob = $this->getDueJobs($model);

		if ($dueJob)
		{
			// Pass - due implementation of the Cronjob class and Plugin API (trigger)
		}

		return;
	}

	/**
	 * Fetches due jobs from CronjobsModel
	 * ! Orphan filtering + pagination issues in the Model will break this if orphaned jobs exist [TODO]
	 *
	 * @param   CronjobsModel  $model   The CronjobsModel
	 * @param   boolean 	   $single  If true, only a single job is returned
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

		return $model->getItems();
	}
}
