<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.ScheduleRunner
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * This plugin implements listeners to support a visitor-triggered lazy-scheduling pattern.
 * If `com_scheduler` is installed/enabled and its configuration allows unprotected lazy scheduling, this plugin
 * injects into each response with an HTML context a JS file {@see PlgSystemSchedulerunner::injectScheduleRunner()} that
 * sets up an AJAX callback to trigger the scheduler {@see PlgSystemSchedulerunner::runScheduler()}. This is achieved
 * through a call to the `com_ajax` component.
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
	 * @var  Registry
	 * @since  __DEPLOY_VERSION__
	 */
	private $schedulerConfig;

	/**
	 * Override {@see CMSPlugin::__construct} to set up {@see PlgSystemSchedulerunner::$schedulerConfig}.
	 *
	 * @param   DispatcherInterface  $subject  The object to observe
	 * @param   array                $config   An optional associative array of configuration settings.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config = [])
	{
		$this->schedulerConfig = ComponentHelper::getParams('com_scheduler');

		parent::__construct($subject, $config);
	}

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		$config = ComponentHelper::getParams('com_scheduler');

		// Make sure com_scheduler is installed and enabled, lazy scheduling is enabled
		if (!(ComponentHelper::isEnabled('com_scheduler')
			&& $config->get('lazy_scheduler.enabled', true)))
		{
			return [];
		}

		return [
			'onAjaxRunScheduler'  => 'runScheduler',
			'onBeforeCompileHead' => 'injectScheduleRunner'
		];
	}

	/**
	 * Inject JavaScript to trigger the scheduler in HTML contexts.
	 *
	 * @param   Event  $event  The onBeforeCompileHead event.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function injectScheduleRunner(Event $event): void
	{
		// Inject JS only if scheduler is not protected.
		if ($this->schedulerConfig->get('lazy_scheduler.protected', false))
		{
			return;
		}

		// Only site requests [@todo allow admin]
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// Add configuration options
		$triggerInterval = $this->schedulerConfig->get('lazy_scheduler.interval', 300);
		$this->app->getDocument()->addScriptOptions('plg_system_schedulerunner', ['interval' => $triggerInterval]);

		// Load and injection directive
		$wa = $this->app->getDocument()->getWebAssetManager();
		$wa->getRegistry()->addExtensionRegistryFile('plg_system_schedulerunner');
		$wa->useScript('plg_system_schedulerunner.run-schedule');
	}

	/**
	 * Run the scheduler, allowing execution of a single due task.
	 *
	 * @return void
	 *
	 * @throws AssertionFailedException
	 * @since __DEPLOY_VERSION__
	 */
	public function runScheduler(): void
	{
		$protected   = (bool) $this->schedulerConfig->get('lazy_scheduler.protected', 0);
		$hash        = $this->schedulerConfig->get('lazy_scheduler.hash', '');
		$requestHash = $this->app->getInput()->get('scheduler_hash');

		if ($protected && $hash !== $requestHash)
		{
			return;
		}

		// Since `navigator.sendBeacon()` may time out, allow execution after disconnect if possible.
		if (function_exists('ignore_user_abort'))
		{
			ignore_user_abort(true);
		}

		(new Scheduler)->runTask();
	}
}
