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

use Assert\AssertionFailedException;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Scheduler\Scheduler;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

/**
 * The plugin class for Plg_System_Schedulerunner.
 *
 * @since __DEPLOY_VERSION__
 */
class PlgSystemSchedulerunner extends CMSPlugin implements SubscriberInterface
{
	/**
	 * @var  CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;


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
		// Make sure com_scheduler is installed and enabled
		if (!ComponentHelper::isEnabled('com_scheduler'))
		{
			return [];
		}

		// Make sure lazy scheduling is enabled
		if (!ComponentHelper::getParams('com_scheduler')->get('lazy_scheduler.enabled', true))
		{
			return [];
		}

		return [
			'onBeforeRender' => ['registerRunner', Priority::MAX]
		];
	}

	/**
	 * @param   Event  $event  The onBeforeRender event
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function registerRunner(Event $event): void
	{
		$config = ComponentHelper::getParams('com_scheduler');
		$protected = (bool) $config->get('lazy_scheduler.protected', 0);
		$hash = $config->get('lazy_scheduler.hash', '');

		$requestHash = $this->app->getInput()->get('scheduler_hash');

		// We only act on site requests [@todo allow admin]
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// If scheduler is protected, we only proceed if the hash is right.
		if ($protected && $hash != $requestHash)
		{
			return;
		}

		register_shutdown_function([$this, 'runScheduler']);
	}

	/**
	 * Runs the scheduler, allowing execution of a single due task
	 *
	 * @return void
	 *
	 * @throws AssertionFailedException
	 * @since __DEPLOY_VERSION__
	 */
	public function runScheduler(): void
	{
		(new Scheduler)->runTask();
	}
}
