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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\Event;
use Joomla\Component\Cronjobs\Administrator\Cronjobs\CronOption;

/**
 * The Testjobplug class
 *
 * @since __DEPLOY__
 */
class PlgSystemTestjob extends CMSPlugin implements SubscriberInterface
{

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
			'onCronRun' => 'cronSampleRoutine'
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

		if (\array_key_exists($subject->jobId, $supportedJobs))
		{
			$subject->exec[] = ['plugin' => $this->_name, 'exit' => 0];
		}
	}
}
