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
	 * Start time for the process
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $time = null;

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

		// Get the timeout for Joomla! system scheduler
		/** @var \Joomla\Registry\Registry $params */
		$cache_timeout = (int) $this->params->get('cachetimeout', 1);
		$cache_timeout = 60 * $cache_timeout;

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now  = time();
		$last = (int) $this->params->get('lastrun', 0);

		if ((abs($now - $last) < $cache_timeout))
		{
			return;
		}

		// Log events
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_web.php';

		JLog::addLogger($options, JLog::INFO, array('scheduler'));

		try
		{
			JLog::add(
				'Starting Scheduler', JLog::INFO, 'scheduler'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		};

		// Update last run status
		$this->params->set('lastrun', $now);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($this->params->toString('JSON')))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('scheduler'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return;
		}

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();

			$this->clearCacheGroups(array('com_plugins'), array(0, 1));
		}
		catch (Exception $exc)
		{
			// If we failed to execute
			$db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (JDatabaseException $e)
		{
			// If we can't unlock the tables assume we have somehow failed
			$result = false;
		}

		// Abort on failure
		if (!$result)
		{
			return;
		}

		// Trigger all job plugin events
		$this->Trigger();

		// Log the time it took to run
		$endTime    = microtime(true);
		$timeToLoad = sprintf('%0.2f', $endTime - $startTime);

		try
		{
			JLog::add(
				'Ending Scheduler:' . JText::sprintf('SCHEDULER_CLI_PROCESS_COMPLETE', round(microtime(true) - $this->time, 3)), JLog::INFO, 'scheduler'
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
	private function Trigger()
	{
		// Unleash hell
		JPluginHelper::importPlugin('job');
		$dispatcher = \JEventDispatcher::getInstance();

		// Trigger the ExecuteTask event
		$dispatcher->trigger('onExecuteScheduledTask', array());	
	}	

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		$conf = JFactory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'    => $client_id ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache')
					);

					$cache = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}
