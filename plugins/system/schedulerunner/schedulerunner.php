<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.ScheduleRunner
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Scheduler\Administrator\Scheduler\Scheduler;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This plugin implements listeners to support a visitor-triggered lazy-scheduling pattern.
 * If `com_scheduler` is installed/enabled and its configuration allows unprotected lazy scheduling, this plugin
 * injects into each response with an HTML context a JS file {@see PlgSystemSchedulerunner::injectScheduleRunner()} that
 * sets up an AJAX callback to trigger the scheduler {@see PlgSystemSchedulerunner::runScheduler()}. This is achieved
 * through a call to the `com_ajax` component.
 * Also supports the scheduler component configuration form through auto-generation of the webcron key and injection
 * of JS of usability enhancement.
 *
 * @since 4.1.0
 */
class PlgSystemSchedulerunner extends CMSPlugin implements SubscriberInterface
{
    /**
     * Length of auto-generated webcron key.
     *
     * @var integer
     * @since 4.1.0
     */
    private const WEBCRON_KEY_LENGTH = 20;

    /**
     * @var  CMSApplication
     * @since  4.1.0
     */
    protected $app;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     *
     * @throws Exception
     */
    public static function getSubscribedEvents(): array
    {
        $config = ComponentHelper::getParams('com_scheduler');
        $app    = Factory::getApplication();

        $mapping  = [];

        if ($app->isClient('site') || $app->isClient('administrator')) {
            $mapping['onBeforeCompileHead']    = 'injectLazyJS';
            $mapping['onAjaxRunSchedulerLazy'] = 'runLazyCron';

            // Only allowed in the frontend
            if ($app->isClient('site')) {
                if ($config->get('webcron.enabled')) {
                    $mapping['onAjaxRunSchedulerWebcron'] = 'runWebCron';
                }
            } elseif ($app->isClient('administrator')) {
                $mapping['onContentPrepareForm']  = 'enhanceSchedulerConfig';
                $mapping['onExtensionBeforeSave'] = 'generateWebcronKey';

                $mapping['onAjaxRunSchedulerTest'] = 'runTestCron';
            }
        }

        return $mapping;
    }

    /**
     * Inject JavaScript to trigger the scheduler in HTML contexts.
     *
     * @param   EventInterface  $event  The onBeforeCompileHead event.
     *
     * @return void
     *
     * @since 4.1.0
     */
    public function injectLazyJS(EventInterface $event): void
    {
        // Only inject in HTML documents
        if ($this->app->getDocument()->getType() !== 'html') {
            return;
        }

        $config = ComponentHelper::getParams('com_scheduler');

        if (!$config->get('lazy_scheduler.enabled', true)) {
            return;
        }

        // Check if any task is due to decrease the load
        $model = $this->app->bootComponent('com_scheduler')
            ->getMVCFactory()->createModel('Tasks', 'Administrator', ['ignore_request' => true]);

        $model->setState('filter.state', 1);
        $model->setState('filter.due', 1);

        $items = $model->getItems();

        // See if we are running currently
        $model->setState('filter.locked', 1);
        $model->setState('filter.due', 0);

        $items2 = $model->getItems();

        if (empty($items) || !empty($items2)) {
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
     * Acts on the LazyCron trigger from the frontend when Lazy Cron is enabled in the Scheduler component
     * configuration. The lazy cron trigger is implemented in client-side JavaScript which is injected on every page
     * load with an HTML context when the component configuration allows it. This method then triggers the Scheduler,
     * which effectively runs the next Task in the Scheduler's task queue.
     *
     * @param   EventInterface  $e  The onAjaxRunSchedulerLazy event.
     *
     * @return void
     *
     * @since 4.1.0
     *
     * @throws Exception
     */
    public function runLazyCron(EventInterface $e)
    {
        $config = ComponentHelper::getParams('com_scheduler');

        if (!$config->get('lazy_scheduler.enabled', true)) {
            return;
        }

        // Since `navigator.sendBeacon()` may time out, allow execution after disconnect if possible.
        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }

        // Prevent PHP from trying to output to the user pipe. PHP may kill the script otherwise if the pipe is not accessible.
        ob_start();

        // Suppress all errors to avoid any output
        try {
            $this->runScheduler();
        } catch (Exception $e) {
        }

        ob_end_clean();
    }

    /**
     * This method is responsible for the WebCron functionality of the Scheduler component.<br/>
     * Acting on a `com_ajax` call, this method can work in two ways:
     * 1. If no Task ID is specified, it triggers the Scheduler to run the next task in
     *   the task queue.
     * 2. If a Task ID is specified, it fetches the task (if it exists) from the Scheduler API and executes it.<br/>
     *
     * URL query parameters:
     * - `hash` string (required)   Webcron hash (from the Scheduler component configuration).
     * - `id`   int (optional)      ID of the task to trigger.
     *
     * @param   Event  $event  The onAjaxRunSchedulerWebcron event.
     *
     * @return void
     *
     * @since 4.1.0
     *
     * @throws Exception
     */
    public function runWebCron(Event $event)
    {
        $config = ComponentHelper::getParams('com_scheduler');
        $hash   = $config->get('webcron.key', '');

        if (!$config->get('webcron.enabled', false)) {
            Log::add(Text::_('PLG_SYSTEM_SCHEDULE_RUNNER_WEBCRON_DISABLED'));
            throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if (!strlen($hash) || $hash !== $this->app->getInput()->get('hash')) {
            throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $id = (int) $this->app->getInput()->getInt('id', 0);

        $task = $this->runScheduler($id);

        if (!empty($task) && !empty($task->getContent()['exception'])) {
            throw $task->getContent()['exception'];
        }
    }

    /**
     * This method is responsible for the "test run" functionality in the Scheduler administrator backend interface.
     * Acting on a `com_ajax` call, this method requires the URL to have a `id` query parameter (corresponding to an
     * existing Task ID).
     *
     * @param   Event  $event  The onAjaxRunScheduler event.
     *
     * @return void
     *
     * @since 4.1.0
     *
     * @throws Exception
     */
    public function runTestCron(Event $event)
    {
        if (!Session::checkToken('GET')) {
            return;
        }

        $id              = (int) $this->app->getInput()->getInt('id');
        $allowConcurrent = $this->app->getInput()->getBool('allowConcurrent', false);

        $user = Factory::getApplication()->getIdentity();

        if (empty($id) || !$user->authorise('core.testrun', 'com_scheduler.task.' . $id)) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /**
         * ?: About allow simultaneous, how do we detect if it failed because of pre-existing lock?
         *
         * We will allow CLI exclusive tasks to be fetched and executed, it's left to routines to do a runtime check
         * if they want to refuse normal operation.
         */
        $task = (new Scheduler())->getTask(
            [
                'id'               => $id,
                'allowDisabled'    => true,
                'bypassScheduling' => true,
                'allowConcurrent'  => $allowConcurrent,
            ]
        );

        if (!is_null($task)) {
            $task->run();
            $event->addArgument('result', $task->getContent());
        } else {
            /**
             * Placeholder result, but the idea is if we failed to fetch the task, it's likely because another task was
             * already running. This is a fair assumption if this test run was triggered through the administrator backend,
             * so we know the task probably exists and is either enabled/disabled (not trashed).
             */
            // @todo language constant + review if this is done right.
            $event->addArgument('result', ['message' => 'could not acquire lock on task. retry or allow concurrency.']);
        }
    }

    /**
     * Run the scheduler, allowing execution of a single due task.
     * Does not bypass task scheduling, meaning that even if an ID is passed the task is only
     * triggered if it is due.
     *
     * @param   integer  $id  The optional ID of the task to run
     *
     * @return ?Task
     *
     * @since 4.1.0
     * @throws RuntimeException
     */
    protected function runScheduler(int $id = 0): ?Task
    {
        return (new Scheduler())->runTask(['id' => $id]);
    }

    /**
     * Enhance the scheduler config form by dynamically populating or removing display fields.
     *
     * @param   EventInterface  $event  The onContentPrepareForm event.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws UnexpectedValueException|RuntimeException
     *
     * @todo  Move to another plugin?
     */
    public function enhanceSchedulerConfig(EventInterface $event): void
    {
        /** @var Form $form */
        $form = $event->getArgument('0');
        $data = $event->getArgument('1');

        if (
            $form->getName() !== 'com_config.component'
            || $this->app->getInput()->get('component') !== 'com_scheduler'
        ) {
            return;
        }

        if (!empty($data['webcron']['key'])) {
            $form->removeField('generate_key_on_save', 'webcron');

            $relative = 'index.php?option=com_ajax&plugin=RunSchedulerWebcron&group=system&format=json&hash=' . $data['webcron']['key'];
            $link     = Route::link('site', $relative, false, Route::TLS_IGNORE, true);
            $form->setValue('base_link', 'webcron', $link);
        } else {
            $form->removeField('base_link', 'webcron');
            $form->removeField('reset_key', 'webcron');
        }
    }

    /**
     * Auto-generate a key/hash for the webcron functionality.
     * This method acts on table save, when a hash doesn't already exist or a reset is required.
     * @todo Move to another plugin?
     *
     * @param   EventInterface  $event The onExtensionBeforeSave event.
     *
     * @return void
     *
     * @since 4.1.0
     */
    public function generateWebcronKey(EventInterface $event): void
    {
        /** @var Extension $table */
        [$context, $table] = $event->getArguments();

        if ($context !== 'com_config.component' || $table->name !== 'com_scheduler') {
            return;
        }

        $params = new Registry($table->params ?? '');

        if (
            empty($params->get('webcron.key'))
            || $params->get('webcron.reset_key') === 1
        ) {
            $params->set('webcron.key', UserHelper::genRandomPassword(self::WEBCRON_KEY_LENGTH));
        }

        $params->remove('webcron.base_link');
        $params->remove('webcron.reset_key');
        $table->params = $params->toString();
    }
}
