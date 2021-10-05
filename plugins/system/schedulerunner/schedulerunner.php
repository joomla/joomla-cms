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
	 * @var  Registry
	 * @since  __DEPLOY_VERSION__
	 */
	private $schedulerConfig;

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
		$this->schedulerConfig = ComponentHelper::getParams('com_scheduler');

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
	 * @param   Event  $event  The onBeforeCompileHead event.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function injectScheduleRunner(Event $event): void
	{
		// Inject JS only if scheduler is not protected
		if ($this->schedulerConfig->get('lazy_scheduler.protected', false))
		{
			return;
		}

		// Only site requests [@todo allow admin]
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$wa = $this->app->getDocument()->getWebAssetManager();
		$wa->getRegistry()->addExtensionRegistryFile('plg_system_schedulerunner');
		$wa->useScript('plg_system_schedulerunner.run-schedule');
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
