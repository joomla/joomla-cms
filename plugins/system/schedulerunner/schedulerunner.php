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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
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
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		$config = ComponentHelper::getParams('com_scheduler');
		$app = Factory::getApplication();

		$mapping  = [];

		if ($app->isClient('site') || $app->isClient('administrator'))
		{
			$mapping['onBeforeCompileHead'] = 'injectLazyJS';
			$mapping['onAjaxRunSchedulerLazy'] = 'runLazyCron';

			// Only allowed in the frontend
			if ($app->isClient('site'))
			{
				if ($config->get('webcron.enabled'))
				{
					$mapping['onAjaxRunSchedulerWebcron'] = 'runWebCron';
				}
			}
			elseif ($app->isClient('administrator'))
			{
				$user = Factory::getUser();

				$mapping['onAjaxRunSchedulerTest'] = 'runTestCron';
			}
		}

		return $mapping;
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
	public function injectLazyJS(Event $event): void
	{
		// Only inject in HTML documents
		if ($this->app->getDocument()->getType() !== 'html')
		{
			return;
		}

		$config = ComponentHelper::getParams('com_scheduler');

		if (!$config->get('lazy_scheduler.enabled'))
		{
			return;
		}

		// Add configuration options
		$triggerInterval = $config->get('lazy_scheduler.interval', 300);
		$this->app->getDocument()->addScriptOptions('plg_system_schedulerunner', ['interval' => $triggerInterval]);

		// Load and injection directive
		$wa = $this->app->getDocument()->getWebAssetManager();
		$wa->getRegistry()->addExtensionRegistryFile('plg_system_schedulerunner');
		$wa->useScript('plg_system_schedulerunner.run-schedule');
	}

	/**
	 * Runs the lazy cron in the frontend when activated. No ID allowed
	 *
	 * @return void
	 */
	public function runLazyCron()
	{
		$config = ComponentHelper::getParams('com_scheduler');

		if (!$config->get('lazy_scheduler.enabled'))
		{
			throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->runScheduler();
	}

	/**
	 * Runs the webcron and uses an ID if given.
	 *
	 * @return void
	 */
	public function runWebCron()
	{
		$config = ComponentHelper::getParams('com_scheduler');

		$hash = $config->get('webcron.hash');

		// @todo enforce a minimum complexity for hash?
		if (!strlen($hash) || $hash !== $this->app->input->get('hash'))
		{
			throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$id = (int) $this->app->input->getInt('id');

		$this->runScheduler($id);
	}

	/**
	 * Runs the test cron in the backend. ID is required
	 *
	 * @return void
	 */
	public function runTestCron()
	{
		$id = (int) $this->app->input->getInt('id');

		$user = Factory::getUser();

		if (empty($id) || !$user->authorise('core.testrun', 'com_scheduler.task.' . $id))
		{
			throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->runScheduler($id);
	}

	/**
	 * Run the scheduler, allowing execution of a single due task.
	 *
	 * @param   integer    $id  The optional ID of the task to run
	 *
	 * @return void
	 *
	 * @throws AssertionFailedException
	 * @since __DEPLOY_VERSION__
	 */
	protected function runScheduler(int $id = 0): void
	{
		// Since `navigator.sendBeacon()` may time out, allow execution after disconnect if possible.
		if (\function_exists('ignore_user_abort'))
		{
			ignore_user_abort(true);
		}

		(new Scheduler)->runTask($id);
	}
}
