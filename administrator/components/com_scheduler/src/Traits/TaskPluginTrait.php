<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Traits;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Event\EventInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility trait for plugins that offer `com_scheduler` compatible task routines. This trait defines a lot
 * of handy methods that make it really simple to support task routines in a J4.x plugin. This trait includes standard
 * methods to broadcast routines {@see TaskPluginTrait::advertiseRoutines()}, enhance task forms
 * {@see TaskPluginTrait::enhanceTaskItemForm()} and call routines
 * {@see TaskPluginTrait::standardRoutineHandler()}. With standard cookie-cutter behaviour, a task plugin may only need
 * to include this trait, and define methods corresponding to each routine along with the `TASKS_MAP` class constant to
 * declare supported routines and related properties.
 *
 * @since  4.1.0
 */
trait TaskPluginTrait
{
    /**
     * A snapshot of the routine state.
     *
     * @var array
     * @since  4.1.0
     */
    protected $snapshot = [];

    /**
     * Set information to {@see $snapshot} when initializing a routine.
     *
     * @param   ExecuteTaskEvent  $event  The onExecuteTask event.
     *
     * @return void
     *
     * @since  4.1.0
     */
    protected function startRoutine(ExecuteTaskEvent $event): void
    {
        if (!$this instanceof CMSPlugin) {
            return;
        }

        $this->snapshot['logCategory'] = $event->getArgument('subject')->logCategory;
        $this->snapshot['plugin']      = $this->_name;
        $this->snapshot['startTime']   = microtime(true);
        $this->snapshot['status']      = Status::RUNNING;
    }

    /**
     * Set information to {@see $snapshot} when ending a routine. This information includes the routine exit code and
     * timing information.
     *
     * @param   ExecuteTaskEvent  $event     The event
     * @param   ?int              $exitCode  The task exit code
     *
     * @return void
     *
     * @since  4.1.0
     * @throws \Exception
     */
    protected function endRoutine(ExecuteTaskEvent $event, int $exitCode): void
    {
        if (!$this instanceof CMSPlugin) {
            return;
        }

        $this->snapshot['endTime']  = $endTime = microtime(true);
        $this->snapshot['duration'] = $endTime - $this->snapshot['startTime'];
        $this->snapshot['status']   = $exitCode ?? Status::OK;
        $event->setResult($this->snapshot);
    }

    /**
     * Enhance the task form with routine-specific fields from an XML file declared through the TASKS_MAP constant.
     * If a plugin only supports the task form and does not need additional logic, this method can be mapped to the
     * `onContentPrepareForm` event through {@see SubscriberInterface::getSubscribedEvents()} and will take care
     * of injecting the fields without additional logic in the plugin class.
     *
     * @param   EventInterface|Form  $context  The onContentPrepareForm event or the Form object.
     * @param   mixed                $data     The form data, required when $context is a {@see Form} instance.
     *
     * @return boolean  True if the form was successfully enhanced or the context was not relevant.
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function enhanceTaskItemForm($context, $data = null): bool
    {
        if ($context instanceof EventInterface) {
            /** @var Form $form */
            $form = $context->getArgument('0');
            $data = $context->getArgument('1');
        } elseif ($context instanceof Form) {
            $form = $context;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument 0 of %1$s must be an instance of %2$s or %3$s',
                    __METHOD__,
                    EventInterface::class,
                    Form::class
                )
            );
        }

        if ($form->getName() !== 'com_scheduler.task') {
            return true;
        }

        $routineId           = $this->getRoutineId($form, $data);
        $isSupported         = \array_key_exists($routineId, self::TASKS_MAP);
        $enhancementFormName = self::TASKS_MAP[$routineId]['form'] ?? '';

        // Return if routine is not supported by the plugin or the routine does not have a form linked in TASKS_MAP.
        if (!$isSupported || \strlen($enhancementFormName) === 0) {
            return true;
        }

        // We expect the form XML in "{PLUGIN_PATH}/forms/{FORM_NAME}.xml"
        $path                = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name;
        $enhancementFormFile = $path . '/forms/' . $enhancementFormName . '.xml';

        try {
            $enhancementFormFile = Path::check($enhancementFormFile);
        } catch (\Exception $e) {
            return false;
        }

        if (is_file($enhancementFormFile)) {
            return $form->loadFile($enhancementFormFile);
        }

        return false;
    }

    /**
     * Advertise the task routines supported by the plugin. This method should be mapped to the `onTaskOptionsList`,
     * enabling the plugin to advertise its routines without any custom logic.<br/>
     * **Note:** This method expects the `TASKS_MAP` class constant to have relevant information.
     *
     * @param   EventInterface  $event  onTaskOptionsList Event
     *
     * @return void
     *
     * @since  4.1.0
     */
    public function advertiseRoutines(EventInterface $event): void
    {
        $options = [];

        foreach (self::TASKS_MAP as $routineId => $details) {
            // Sanity check against non-compliant plugins
            if (isset($details['langConstPrefix'])) {
                $options[$routineId] = $details['langConstPrefix'];
            }
        }

        $subject = $event->getArgument('subject');
        $subject->addOptions($options);
    }

    /**
     * Get the relevant task routine ID in the context of a form event, e.g., the `onContentPrepareForm` event.
     *
     * @param   Form   $form  The form
     * @param   mixed  $data  The data
     *
     * @return  string
     *
     * @since  4.1.0
     * @throws  \Exception
     */
    protected function getRoutineId(Form $form, $data): string
    {
        /*
         * Depending on when the form is loaded, the ID may either be in $data or the data already bound to the form.
         * $data can also either be an object or an array.
         */
        $routineId = $data->taskOption->id ?? $data->type ?? $data['type'] ?? $form->getValue('type') ?? $data['taskOption']->id ?? '';

        // If we're unable to find a routineId, it might be in the form input.
        if (empty($routineId)) {
            $app       = $this->getApplication() ?? ($this->app ?? Factory::getApplication());
            $form      = $app->getInput()->get('jform', []);
            $routineId = ArrayHelper::getValue($form, 'type', '', 'STRING');
        }

        return $routineId;
    }

    /**
     * Add a log message to the task log.
     *
     * @param   string  $message   The log message
     * @param   string  $priority  The log message priority
     *
     * @return void
     *
     * @since  4.1.0
     * @throws \Exception
     * @todo   : use dependency injection here (starting from the Task & Scheduler classes).
     */
    protected function logTask(string $message, string $priority = 'info'): void
    {
        static $langLoaded;
        static $priorityMap = [
            'debug'   => Log::DEBUG,
            'error'   => Log::ERROR,
            'info'    => Log::INFO,
            'notice'  => Log::NOTICE,
            'warning' => Log::WARNING,
        ];

        if (!$langLoaded) {
            $app = $this->getApplication() ?? ($this->app ?? Factory::getApplication());
            $app->getLanguage()->load('com_scheduler', JPATH_ADMINISTRATOR);
            $langLoaded = true;
        }

        $category = $this->snapshot['logCategory'];

        Log::add(Text::_('COM_SCHEDULER_ROUTINE_LOG_PREFIX') . $message, $priorityMap[$priority] ?? Log::INFO, $category);
    }

    /**
     * Handler for *standard* task routines. Standard routines are mapped to valid class methods 'method' through
     * `static::TASKS_MAP`. These methods are expected to take a single argument (the Event) and return an integer
     * return status (see {@see Status}). For a plugin that maps each of its task routines to valid methods and does
     * not need non-standard handling, this method can be mapped to the `onExecuteTask` event through
     * {@see SubscriberInterface::getSubscribedEvents()}, which would allow it to then check if the event wants to
     * execute a routine offered by the parent plugin, call the routine and do some other housework without any code
     * in the parent classes.<br/>
     * **Compatible routine method signature:**&nbsp;&nbsp; ({@see ExecuteTaskEvent::class}, ...): int
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function standardRoutineHandler(ExecuteTaskEvent $event): void
    {
        if (!\array_key_exists($event->getRoutineId(), self::TASKS_MAP)) {
            return;
        }

        $this->startRoutine($event);
        $routineId  = $event->getRoutineId();
        $methodName = (string) self::TASKS_MAP[$routineId]['method'] ?? '';
        $exitCode   = Status::NO_EXIT;

        // We call the mapped method if it exists and confirms to the ($event) -> int signature.
        if (!empty($methodName) && ($staticReflection = new \ReflectionClass($this))->hasMethod($methodName)) {
            $method = $staticReflection->getMethod($methodName);

            // Might need adjustments here for PHP8 named parameters.
            if (
                !($method->getNumberOfRequiredParameters() === 1)
                || !$method->getParameters()[0]->hasType()
                || $method->getParameters()[0]->getType()->getName() !== ExecuteTaskEvent::class
                || !$method->hasReturnType()
                || $method->getReturnType()->getName() !== 'int'
            ) {
                $this->logTask(
                    sprintf(
                        'Incorrect routine method signature for %1$s(). See checks in %2$s()',
                        $method->getName(),
                        __METHOD__
                    ),
                    'error'
                );

                return;
            }

            try {
                // Enable invocation of private/protected methods.
                $method->setAccessible(true);
                $exitCode = $method->invoke($this, $event);
            } catch (\ReflectionException $e) {
                // @todo replace with language string (?)
                $this->logTask('Exception when calling routine: ' . $e->getMessage(), 'error');
                $exitCode = Status::NO_RUN;
            }
        } else {
            $this->logTask(
                sprintf(
                    'Incorrectly configured TASKS_MAP in class %s. Missing valid method for `routine_id` %s',
                    static::class,
                    $routineId
                ),
                'error'
            );
        }

        /**
         * Closure to validate a status against {@see Status}
         *
         * @since 4.1.0
         */
        $validateStatus = static function (int $statusCode): bool {
            return \in_array(
                $statusCode,
                (new \ReflectionClass(Status::class))->getConstants()
            );
        };

        // Validate the exit code.
        if (!\is_int($exitCode) || !$validateStatus($exitCode)) {
            $exitCode = Status::INVALID_EXIT;
        }

        $this->endRoutine($event, $exitCode);
    }
}
