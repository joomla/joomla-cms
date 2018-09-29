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
	 * Status.
	 *
	 * @var    The status 
	 * @since  __DEPLOY_VERSION__
	 */
	protected $status;

	/**
	 * The log check and rotation code event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExecuteScheduledTask($task = array())
	{

		$startTime = microtime(true);

		// Pseudo Lock
		if (!JPluginHelper::lock($this->_name, $this->_type, $this->status))
		{
			return;
		}

		// Get the timeout for Joomla! job LogRotation task
		$now  = time();

		$last = $this->status;
		$cache_timeout = (int) $this->params->get('cachetimeout', 1);
		$unit          = (int) $this->params->get('unit', 86400);
		$cache_timeout = ($unit * $cache_timeout);

		if ((abs($now - $last) < $cache_timeout))
		{
			// Release the lock
			JPluginHelper::unLock($this->_name, $this->_type, false);
			return;
		}

		// Execute the job
		$this->logRotationTask();

		// Update job execution data
		$taskid = JPluginHelper::unLock($this->_name, $this->_type);

		try
		{
			JLog::add(
				JText::sprintf('PLG_JOB_LOGROTATION_END', $this->_name)  . 
				JText::sprintf('PLG_JOB_LOGROTATION_TASK', $taskid) . 
				JText::sprintf('PLG_JOB_LOGROTATION_PROCESS_COMPLETE', $timeToLoad),
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
