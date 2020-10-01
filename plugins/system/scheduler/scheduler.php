<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.scheduler
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\Component\Plugins\Administrator\Jobs\JobsPlugin;

/**
 * Joomla! scheduler plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemScheduler extends JobsPlugin
{
	/**
	 * Status for the process
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $snapshot = [];

	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = false;

	/**
	 * Database object
	 *
	 * @var    \Jooomla\CMS\Application\CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The scheduler is triggered after the response is sent.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRespond()
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$startTime = microtime(true);
		$this->snapshot['startTime'] = microtime(true);

		// WebCron check
		if ($this->params->get('webcron', 0))
		{
			$webcronkey = $this->app->input->get('webcronkey', '', 'cmd');

			if (empty($webcronkey) || $webcronkey !== $this->params->get('webcronkey', ''))
			{
				return;
			}
		}

		// Log events
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_web.php';
		Log::addLogger($options, Log::INFO, array('scheduler'));

		// Load language file.
		$this->loadLanguage();

		// Pseudo Lock acquireLock
		if (!$this->acquireLock($this->_name, $this->_type, $this->params))
		{
			Log::add(
				Text::_('PLG_SYSTEM_SCHEDULER_LOCKED'),
				Log::INFO,
				'scheduler'
			);

			return;
		}

		Log::add(
			Text::_('PLG_SYSTEM_SCHEDULER_START'),
			Log::INFO,
			'scheduler'
		);

		// Trigger jobs
		$this->triggerJobs();

		// Update job execution data
		$this->releaseLock($this->_name, $this->_type);

		// Log the time it took to run
		$endTime    = microtime(true);
		$timeToLoad = sprintf('%0.2f', $endTime - $startTime);

		Log::add(
			Text::sprintf('PLG_SYSTEM_SCHEDULER_PROCESS_COMPLETE', $timeToLoad),
			Log::INFO,
			'scheduler'
		);
	}

	/**
	 * Trigger the jobs
	 *
	 * @return  array
	 *
	 * @throws Exception
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJobs()
	{
		// The job plugin group
		PluginHelper::importPlugin('job');
		PluginHelper::importPlugin('actionlog');

		// Trigger the ExecuteTask event
		$results = $this->app->triggerEvent('onExecuteScheduledTask', [false]);

		foreach ($results as $result)
		{
			$this->app->triggerEvent('onAfterScheduledTask', [$result]);
		}

		return $results;
	}
}
