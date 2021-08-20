<?php
/**
 * Declares the CronjobsPluginTrait.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cronjobs\Administrator\Traits;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Cronjobs\Administrator\Event\CronRunEvent;
use Joomla\Event\Event;
use Joomla\Utilities\ArrayHelper;
use ReflectionClass;
use function array_key_exists;
use function is_file;

/**
 * Utility trait for plugins that support com_scheduler jobs
 *
 * @since  __DEPLOY_VERSION__
 */
trait CronjobPluginTrait
{
	/**
	 * Stores the job state.
	 *
	 * @var array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $snapshot = [];

	/**
	 * Predefined exit codes
	 *
	 * @var string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private static $STATUS = [
		'OK_RUN' => 0,
		'NO_TIME' => 1,
		'KO_RUN' => 3,
		'TIMEOUT' => 124
	];

	/**
	 * Sets boilerplate to snapshot when starting a job
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function jobStart(): void
	{
		if (!$this instanceof CMSPlugin)
		{
			return;
		}

		$this->snapshot['plugin'] = $this->_name;
		$this->snapshot['startTime'] = microtime(true);
		$this->snapshot['status'] = self::$STATUS['NO_TIME'];
	}

	/**
	 * Sets exit code and duration to snapshot. Writes to log.
	 *
	 * @param   CronRunEvent  $event     The event
	 * @param   ?int          $exitCode  The job exit code
	 * @param   boolean       $log       If true, the method adds a log. Requires the plugin to
	 *                                   have the language strings.
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function jobEnd(CronRunEvent $event, int $exitCode, bool $log = true): void
	{
		if (!$this instanceof CMSPlugin)
		{
			return;
		}

		$this->snapshot['endTime'] = $endTime = microtime(true);
		$this->snapshot['duration'] = $endTime - $this->snapshot['startTime'];
		$this->snapshot['status'] = $exitCode ?? self::$STATUS['OK_RUN'];
		$event->setResult($this->snapshot);

		if ($log)
		{
			$langConstPrefix = strtoupper($event->getArgument('langConstPrefix'));
			Log::add(
				Text::sprintf($langConstPrefix . '_JOB_LOG_MESSAGE',
					$this->snapshot['status'], $this->snapshot['duration']
				),
				Log::INFO,
				'cronjobs'
			);
		}
	}

	/**
	 * Enhance the cronjob form with a job specific form.
	 * Expects the JOBS_MAP class constant to have the relevant information.
	 *
	 * @param   Form   $form  The form
	 * @param   mixed  $data  The data
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function enhanceCronjobItemForm(Form $form, $data): bool
	{
		$jobId = $this->getJobId($form, $data);

		$isSupported = array_key_exists($jobId, self::JOBS_MAP);

		if (!$isSupported || !$enhancementForm = self::JOBS_MAP[$jobId]['form'] ?? '')
		{
			return false;
		}

		$path = dirname((new ReflectionClass(static::class))->getFileName());

		if (is_file($fn = $path . '/forms/' . $enhancementForm . '.xml'))
		{
			$form->loadFile($fn);
		}

		return true;
	}

	/**
	 * Advertises jobs supported by this plugin.
	 *
	 * @param   Event  $event  onCronOptionsList Event
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function advertiseJobs(Event $event): void
	{
		$options = [];

		foreach (self::JOBS_MAP as $job => $details)
		{
			$options[$job] = $details['langConstPrefix'];
		}

		$subject = $event->getArgument('subject');
		$subject->addOptions($options);
	}

	/**
	 * @param   Form   $form  The form
	 * @param   mixed  $data  The data
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getJobId(Form $form, $data): string
	{
		$jobId = $data->cronOption->type ?? $data['cronOption']->type ?? $form->getValue('type');

		if (!$jobId)
		{
			$app = $this->app ?? Factory::getApplication();
			$form = $app->getInput()->get('jform', []);
			$jobId = ArrayHelper::getValue($form, 'type', '', 'STRING');
		}

		return $jobId;
	}

	/**
	 * Add a log message to the `cronjobs` category.
	 * ! This might change
	 * ? Maybe use a PSR3 logger instead?
	 *
	 * @param   string  $message   The log message
	 * @param   string  $priority  The log message priority
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function addJobLog(string $message, string $priority = 'info'): void
	{
		static $langLoaded;
		static $priorityMap = [
			'debug' => Log::DEBUG,
			'error' => Log::ERROR,
			'info' => Log::INFO,
			'notice' => Log::NOTICE,
			'warning' => Log::WARNING,
		];

		if (!$langLoaded)
		{
			$app = $this->app ?? Factory::getApplication();
			$app->getLanguage()->load('com_scheduler', JPATH_ADMINISTRATOR);
			$langLoaded = true;
		}

		Log::add(Text::_('COM_SCHEDULER_JOB_LOG_PREFIX') . $message, $priorityMap[$priority] ?? Log::INFO, 'cronjobs');
	}
}
