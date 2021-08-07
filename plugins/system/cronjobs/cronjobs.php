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

		$dueJob = $model->getDueJobs();

		if ($dueJob)
		{
			// Pass - due implementation of the Cronjob class and Plugin API (trigger)
		}

		return;
	}
}
