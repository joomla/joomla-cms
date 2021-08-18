<?php
/**
 * Implements the CronOptions class used by com_cronjobs as the subject arg for the `OnCronOptionsList` event.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 *
 */

namespace Joomla\Component\Cronjobs\Administrator\Cronjobs;

// Restrict direct access
defined('_JEXEC') or die;

use function defined;

/**
 * The CronOptions class.
 * Used as the subject argument for the `OnCronOptionsList` event, plugins that support jobs must add them to the object
 * through the addOptions() method.
 *
 * @since  __DEPLOY_VERSION__
 */
class CronOptions
{
	/**
	 * An array of CronOptions
	 *
	 * @var CronOption[]
	 * @since  __DEPLOY_VERSION__
	 */
	public $options = [];


	/**
	 * A plugin can support several jobs
	 * This method is used by a plugin's OnCronOptionsList subscriber to advertise supported jobs.
	 *
	 * @param   array  $jobsArray   An associative array of {@var CronOption} constructor argument pairs:
	 *                              [ 'jobId' => 'languageConstantPrefix', ... ]
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function addOptions(array $jobsArray): void
	{
		foreach ($jobsArray as $jobId => $langConstPrefix)
		{
			$this->options[] = new CronOption($jobId, $langConstPrefix);
		}
	}

	/**
	 * @param   ?string  $jobType  A unique identifier for the job routine offered by a plugin
	 *
	 * @return  ?CronOption  A matching CronOption if available, null otherwise
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function findOption(?string $jobType): ?CronOption
	{
		if ($jobType === null)
		{
			return null;
		}

		foreach ($this->options as $job)
		{
			if ($job->type === $jobType)
			{
				return $job;
			}
		}

		return null;
	}
}
