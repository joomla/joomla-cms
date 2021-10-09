<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Implements the TaskPluginTrait. */

namespace Joomla\Component\Scheduler\Administrator\Traits;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Event\Event;
use Joomla\Utilities\ArrayHelper;

/**
 * Utility trait for plugins that support com_scheduler compatible task routines
 *
 * @since  __DEPLOY_VERSION__
 */
trait TaskPluginTrait
{
	/**
	 * Stores the task state.
	 *
	 * @var array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $snapshot = [];

	/**
	 * Sets boilerplate to the snapshot when initializing a routine
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event.
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function taskStart(ExecuteTaskEvent $event): void
	{
		if (!$this instanceof CMSPlugin)
		{
			return;
		}

		$this->snapshot['logCategory'] = $event->getArgument('subject')->logCategory;
		$this->snapshot['plugin'] = $this->_name;
		$this->snapshot['startTime'] = microtime(true);
		$this->snapshot['status'] = TaskStatus::NO_TIME;
	}

	/**
	 * Sets exit code and duration to snapshot. Writes to log.
	 *
	 * @param   ExecuteTaskEvent  $event     The event
	 * @param   ?int              $exitCode  The task exit code
	 * @param   boolean           $log       If true, the method adds a log. Requires the plugin to
	 *                                       have the language strings.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	private function taskEnd(ExecuteTaskEvent $event, int $exitCode, bool $log = true): void
	{
		if (!$this instanceof CMSPlugin)
		{
			return;
		}

		$this->snapshot['endTime'] = $endTime = \microtime(true);
		$this->snapshot['duration'] = $endTime - $this->snapshot['startTime'];
		$this->snapshot['status'] = $exitCode ?? TaskStatus::OK;
		$event->setResult($this->snapshot);

		// @todo remove logging from this method
		if ($log)
		{
			$langConstPrefix = \strtoupper($event->getArgument('langConstPrefix'));
			$this->addTaskLog(
				Text::sprintf($langConstPrefix . '_ROUTINE_END_LOG_MESSAGE',
					$this->snapshot['status'], $this->snapshot['duration']
				)
			);
		}
	}

	/**
	 * Enhance the task form with task specific fields.
	 * Expects the TASKS_MAP class constant to have relevant information.
	 *
	 * @param   Form   $form  The form
	 * @param   mixed  $data  The data
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function enhanceTaskItemForm(Form $form, $data): bool
	{
		$routineId = $this->getRoutineId($form, $data);

		$isSupported = \array_key_exists($routineId, self::TASKS_MAP);

		if (!$isSupported || !$enhancementForm = self::TASKS_MAP[$routineId]['form'] ?? '')
		{
			return false;
		}

		$path = dirname((new \ReflectionClass(static::class))->getFileName());

		if (\is_file($fn = $path . '/forms/' . $enhancementForm . '.xml'))
		{
			$form->loadFile($fn);
		}

		return true;
	}

	/**
	 * Advertises the task routines supported by the parent plugin.
	 * Expects the TASKS_MAP class constant to have relevant information.
	 *
	 * @param   Event  $event  onTaskOptionsList Event
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function advertiseRoutines(Event $event): void
	{
		$options = [];

		foreach (self::TASKS_MAP as $routineId => $details)
		{
			// Sanity check against non-compliant plugins
			if (isset($details['langConstPrefix']))
			{
				$options[$routineId] = $details['langConstPrefix'];
			}
		}

		$subject = $event->getArgument('subject');
		$subject->addOptions($options);
	}

	/**
	 * @param   Form   $form  The form
	 * @param   mixed  $data  The data
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getRoutineId(Form $form, $data): string
	{
		/*
		 * Depending on when the form is loaded, the ID may either be in $data or the form bound data.
		 * Also, $data can be either an object instance or an array.
		 */
		$routineId = $data->taskOption->type ?? $data->type ?? $data['type'] ?? $form->getValue('type') ?? $data['taskOption']->type;

		// If we're unable to find a routineId, it might be in the form input.
		if (!$routineId)
		{
			$app = $this->app ?? Factory::getApplication();
			$form = $app->getInput()->get('jform', []);
			$routineId = ArrayHelper::getValue($form, 'type', '', 'STRING');
		}

		return $routineId;
	}

	/**
	 * Add a log message to the `scheduler` category.
	 * ! This might change
	 * ? Maybe use a PSR3 logger instead?
	 *
	 * @param   string  $message   The log message
	 * @param   string  $priority  The log message priority
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function addTaskLog(string $message, string $priority = 'info'): void
	{
		static $langLoaded;
		static $priorityMap = [
			'debug'   => Log::DEBUG,
			'error'   => Log::ERROR,
			'info'    => Log::INFO,
			'notice'  => Log::NOTICE,
			'warning' => Log::WARNING,
		];

		if (!$langLoaded)
		{
			$app = $this->app ?? Factory::getApplication();
			$app->getLanguage()->load('com_scheduler', JPATH_ADMINISTRATOR);
			$langLoaded = true;
		}

		$category = $this->snapshot['logCategory'];

		Log::add(Text::_('COM_SCHEDULER_ROUTINE_LOG_PREFIX') . $message, $priorityMap[$priority] ?? Log::INFO, $category);
	}

	/**
	 * Handler for *standard* task routines.
	 *
	 * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	protected function standardRoutineHandler(ExecuteTaskEvent $event): void
	{
		if (!\array_key_exists($event->getRoutineId(), self::TASKS_MAP))
		{
			return;
		}

		$this->initRoutine($event);
		$routineId = $event->getRoutineId();
		$callable  = self::TASKS_MAP[$routineId]['call'] ?? '';
		$exitCode = Status::NO_EXIT;

		if (!empty($callable) && is_callable($callable))
		{
			$exitCode = \call_user_func($callable);
		}
		elseif (!empty($callable) && method_exists($this, $callable))
		{
			$exitCode = \call_user_func([$this, $callable]);
		}
		else
		{
			$this->logTask(sprintf('Misconfigured TASKS_MAP in class %s. Missing callable for `routine_id` %s', static::class, $routineId), 'ERROR');
		}

		// If $exitCode is false, something went wrong. It indicates failure to call the callback or that it returned false.
		if ($exitCode === false)
		{
			$exitCode = Status::NO_RUN;
		}
		// A valid $exitCode is an integer.
		elseif (!is_integer($exitCode))
		{
			$exitCode = Status::INVALID_EXIT;
		}

		$this->endRoutine($event, $exitCode);
	}
}
