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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Cronjobs\Administrator\Event\CronRunEvent;
use Joomla\Component\Cronjobs\Administrator\Traits\CronjobPluginTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * The PlgJobTestjob class
 *
 * @since __DEPLOY__VERSION__
 */
class PlgJobTestjob extends CMSPlugin implements SubscriberInterface
{
	use CronjobPluginTrait;

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private const JOBS_MAP = [
		'job1' => [
			'langConstPrefix' => 'PLG_JOB_TESTJOB_JOB1',
			'form' => 'testJobForm'
		],
		'job2' => [
			'langConstPrefix' => 'PLG_JOB_TESTJOB_JOB2',
			'form' => 'testJobForm'
		]
	];

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
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onCronOptionsList' => 'advertiseJobs',
			'onCronRun' => 'cronSampleRoutine',
			'onContentPrepareForm' => 'manipulateForms'
		];
	}

	/**
	 * @param   CronRunEvent  $event  onCronRun Event
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION
	 */
	public function cronSampleRoutine(CronRunEvent $event): void
	{
		if (array_key_exists($event->getJobId(), self::JOBS_MAP))
		{
			$this->jobStart();

			// Access to job parameters
			$params = $event->getArgument('params');

			// Plugin does whatever it wants

			$this->jobEnd($event, 0);
		}
	}

	/**
	 * @param   Event  $event  The onContentPrepareForm event.
	 *
	 * @return void
	 * @since __DEPLOY_VERSION__
	 */
	public function manipulateForms(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		$context = $form->getName();

		if ($context === 'com_cronjobs.cronjob')
		{
			$this->enhanceCronjobItemForm($form, $data);
		}
	}
}
