<?php
/**
 * A test plugin for com_cronjobs.
 *
 * @package       Joomla.Plugins
 * @subpackage    System.testjob
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Cronjobs\Administrator\Event\CronRunEvent;
use Joomla\Component\Cronjobs\Administrator\Traits\CronjobPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\Event;

/**
 * The PlgJobTestjob class
 *
 * @since __DEPLOY__VERSION__
 */
class PlgJobTestjob extends CMSPlugin implements SubscriberInterface
{
	use CronjobPluginTrait;

	/**
	 * Autoload the language file
	 *
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * An array of supported Form contexts
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private $supportedFormContexts = [
		'com_cronjobs.cronjob'
	];

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private const JOBS_MAP = [
	'job1' => 'routine1',
	'job2' => 'routine2'
	];

	/**
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onCronOptionsList' => 'pluginCronOptions',
			'onCronRun' => 'cronSampleRoutine',
			'onContentPrepareForm' => 'manipulateForms'
		];
	}

	/**
	 * Just returns a jobId => langConstPrefix mapped array (for now)
	 *
	 * @param   Event  $event  onCronOptionsList Event
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function pluginCronOptions(Event $event): array
	{
		$jobsArray = [
			'job1' => 'PLG_JOB_TESTJOB_JOB1',
			'job2' => 'PLG_JOB_TESTJOB_JOB2'
		];

		$subject = $event['subject'];

		$subject->addOptions($jobsArray);

		return $jobsArray;
	}

	/**
	 * @param   Event  $event  onCronRun Event
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION
	 */
	public function cronSampleRoutine(CronRunEvent $event): void
	{
		if (array_key_exists($event['jobId'], self::JOBS_MAP))
		{
			$this->jobStart();

			// Plugin does whatever it wants

			$this->jobEnd($event, 0);
		}
	}

	/**
	 * @param   Event  $event  The onContentPrepareForm event.
	 *
	 * @return void
	 * @since __DEPLOY_VERSION
	 */
	public function manipulateForms(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument('0');

		$context = $form->getName();

		// Return early if form is not supported
		if (!in_array($context, $this->supportedFormContexts))
		{
			return;
		}

		FormHelper::addFormPath(__DIR__ . '/forms');
		$loaded = $form->loadFile('testJobForm');

		return;
	}
}
