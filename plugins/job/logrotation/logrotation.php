<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Job.logrotation
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Joomla! Log Rotation plugin
 *
 * Rotate the log files created by Joomla core
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgJobLogrotation extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * The log check and rotation code event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExecuteScheduledTask($task = array())
	{



		$taskParams = json_decode($this->params, true);
		$now        = time();

		// Sanity check
		if ((!isset($taskParams['lastrun'])) || (!isset($taskParams['cachetimeout'])) || (!isset($taskParams['unit'])))
		{
			return;
		}

		$last          = (int) $taskParams['lastrun'];
		$cache_timeout = (int) $taskParams['cachetimeout'];
		$cache_timeout = 60 * $cache_timeout;

		if ((abs($now - $last) < $cache_timeout))
		{
			return;
		}

		$startTime = microtime(true);

		try
		{
			JLog::add(
				'Running:' . $this->_name . ':', JLog::INFO, 'scheduler'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Execute the job
		$this->logRotationTask();

		// Update job execution data
		$this->updateLastRun();

		$endTime    = microtime(true);
		$timeToLoad = sprintf('%0.2f', $endTime - $startTime);

		try
		{
			JLog::add(
				'Executed:' . $this->_name . ' took ' . $timeToLoad . ' seconds',
				JLog::INFO,
				'scheduler'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}
	}

	/**
	 * Update last run.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function updateLastRun()
	{
		// Update last run status
		$this->params->set('lastrun', time());

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($this->params))
			->where($db->quoteName('element') . ' = ' . $db->quote($this->_name))
			->where($db->quoteName('folder') . ' = ' . $db->quote($this->_type));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (JDatabaseException $e)
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
		catch (JDatabaseException $exc)
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

	/**
	 * The log check and rotation code event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function logRotationTask()
	{

		// Get the number of logs to keep as configured in plugin parameters
		$logsToKeep    = (int) $this->params->get('logstokeep', 1);

		// Get the log path
		$logPath = Path::clean($this->app->get('log_path'));

		// Invalid path, stop processing further
		if (!is_dir($logPath))
		{
			return;
		}

		$logFiles = $this->getLogFiles($logPath);

		// Sort log files by version number in reserve order
		krsort($logFiles, SORT_NUMERIC);

		foreach ($logFiles as $version => $files)
		{
			if ($version >= $logsToKeep)
			{
				// Delete files which has version greater than or equals $logsToKeep
				foreach ($files as $file)
				{
					File::delete($logPath . '/' . $file);
				}
			}
			else
			{
				// For files which has version smaller than $logsToKeep, rotate (increase version number)
				foreach ($files as $file)
				{
					$this->rotate($logPath, $file, $version);
				}
			}
		}
	}

	/**
	 * Get log files from log folder
	 *
	 * @param   string  $path  The folder to get log files
	 *
	 * @return  array   The log files in the given path grouped by version number (not rotated files has number 0)
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getLogFiles($path)
	{
		$logFiles = array();
		$files    = Folder::files($path, '\.php$');

		foreach ($files as $file)
		{
			$parts    = explode('.', $file);

			/*
			 * Rotated log file has this filename format [VERSION].[FILENAME].php. So if $parts has at least 3 elements
			 * and the first element is a number, we know that it's a rotated file and can get it's current version
			 */
			if (count($parts) >= 3 && is_numeric($parts[0]))
			{
				$version = (int) $parts[0];
			}
			else
			{
				$version = 0;
			}

			if (!isset($logFiles[$version]))
			{
				$logFiles[$version] = array();
			}

			$logFiles[$version][] = $file;
		}

		return $logFiles;
	}

	/**
	 * Method to rotate (increase version) of a log file
	 *
	 * @param   string  $path            Path to file to rotate
	 * @param   string  $filename        Name of file to rotate
	 * @param   int     $currentVersion  The current version number
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function rotate($path, $filename, $currentVersion)
	{
		if ($currentVersion === 0)
		{
			$rotatedFile = $path . '/1.' . $filename;
		}
		else
		{
			/*
			 * Rotated log file has this filename format [VERSION].[FILENAME].php. To rotate it, we just need to explode
			 * the filename into an array, increase value of first element (keep version) and implode it back to get the
			 * rotated file name
			 */
			$parts    = explode('.', $filename);
			$parts[0] = $currentVersion + 1;

			$rotatedFile = $path . '/' . implode('.', $parts);
		}

		File::move($path . '/' . $filename, $rotatedFile);
	}

}
