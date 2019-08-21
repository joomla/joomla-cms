<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.scheduler
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Joomla! scheduler plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemScheduler extends CMSPlugin
{
	/**
	 * Status for the process
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $status;

	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = false;

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
		$startTime = microtime(true);

		$app = Factory::getApplication();

		// WebCron check
		if ($this->params->get('webcron', 0))
		{
			$webcronkey = $app->input->get('webcronkey', '', 'cmd');

			if ($webcronkey !== $this->params->get('webcronkey', ''))
			{
				return;
			}
		}

		// Pseudo Lock
		if (!PluginHelper::getLock($this->_name, $this->_type, $this->params))
		{
			return;
		}

		// Log events
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_web.php';

		Log::addLogger($options, Log::INFO, array('scheduler'));

		// Load language file.
		$this->loadLanguage();
		Log::add(
			Text::_('PLG_SYSTEM_SCHEDULER_START'),
			Log::INFO,
			'scheduler'
		);

		// Trigger jobs
		$this->triggerJobs();

		// Update job execution data
		$taskid = PluginHelper::unLock($this->_name, $this->_type);

		// Log the time it took to run
		$endTime    = microtime(true);
		$timeToLoad = sprintf('%0.2f', $endTime - $startTime);

		Log::add(
			Text::sprintf('PLG_SYSTEM_SCHEDULER_END', $taskid) . ' ' .
			Text::sprintf('PLG_SYSTEM_SCHEDULER_PROCESS_COMPLETE', $timeToLoad),
			Log::INFO,
			'scheduler'
		);
	}

	/**
	 * Trigger the jobs
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJobs()
	{
		// The job plugin group
		PluginHelper::importPlugin('job');

		// Trigger the ExecuteTask event
		Factory::getApplication()->triggerEvent('onExecuteScheduledTask', array());
	}
}
