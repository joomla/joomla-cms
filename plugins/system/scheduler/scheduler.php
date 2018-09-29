<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.scheduler
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Joomla! scheduler plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemScheduler extends JPlugin
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
	protected $autoloadLanguage = true;

	/**
	 * The scheduler is triggered after the response is sent.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRespond()
	{
		$startTime = microtime(true);

		$app = JFactory::getApplication();

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
		if (!JPluginHelper::lock($this->_name, $this->_type, $this->status))
		{
			return;
		}
		// Get the timeout for Joomla! system scheduler
		/** @var \Joomla\Registry\Registry $params */
		$cache_timeout = (int) $this->params->get('cachetimeout', 1);
		$unit          = (int) $this->params->get('unit', 60);
		$cache_timeout = ($unit * $cache_timeout);

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now    = time();
		$last = $this->status;

		// Log events
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_web.php';
		
		JLog::addLogger($options, JLog::INFO, array('scheduler'));

		if ((abs($now - $last) < $cache_timeout))
		{
			JPluginHelper::unLock($this->_name, $this->_type, false);

			return;
		}

		try
		{
			JLog::add(
				JText::_('PLG_SYSTEM_SCHEDULER_START'), 
				JLog::INFO, 
				'scheduler'
			);
			
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Trigger jobs
		$this->triggerJobs();

		// Update job execution data
		$taskid = JPluginHelper::unLock($this->_name, $this->_type);

		// Log the time it took to run
		$endTime    = microtime(true);
		$timeToLoad = sprintf('%0.2f', $endTime - $startTime);

		try
		{
			JLog::add(
				JText::sprintf('PLG_SYSTEM_SCHEDULER_END', $taskid) . '  ' . 
				JText::sprintf('PLG_SYSTEM_SCHEDULER_PROCESS_COMPLETE', $timeToLoad), JLog::INFO, 'scheduler'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}
	}

	/**
	 * Trigger the jobs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJobs()
	{
		// Unleash hell
		JPluginHelper::importPlugin('job');
		$dispatcher = \JEventDispatcher::getInstance();

		// Trigger the ExecuteTask event
		$dispatcher->trigger('onExecuteScheduledTask', array());	
	}	
}
