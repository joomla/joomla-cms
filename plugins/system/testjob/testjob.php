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
use Joomla\Event\SubscriberInterface;
use Joomla\Event\Event;

/**
 * The Testjobplug class
 *
 * @since __DEPLOY__
 */
class PlgSystemTestjob extends CMSPlugin implements SubscriberInterface
{
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
			'job1' => 'COM_TESTJOBPLG_JOB1',
			'job2' => 'COM_TESTJOBPLG_JOB2'
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
	public function cronSampleRoutine(Event $event): void
	{
		/**
		 * ! : com_cronjobs does not trigger anything as of yet. The operations below only exist as an example.
		 * ! : The $subject object has not been implemented, nor are the operations below its intended form.
		 */

		$subject = $event['subject'];
		$supportedJobs = [
			'job1' => 'routine1',
			'job2' => 'routine2'
		];

		if (array_key_exists($subject->jobId, $supportedJobs))
		{
			$subject->exec[] = ['plugin' => $this->_name, 'exit' => 0];
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
