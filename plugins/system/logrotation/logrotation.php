<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.logrotation
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
 * @since  3.9.0
 */
class PlgSystemLogrotation extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * The log check and rotation code is triggered after the page has fully rendered.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onAfterRender()
	{
		// Get the timeout as configured in plugin parameters

		/** @var \Joomla\Registry\Registry $params */
		$cache_timeout = (int) $this->params->get('cachetimeout', 30);
		$cache_timeout = 24 * 3600 * $cache_timeout;
		$logsToKeep    = (int) $this->params->get('logstokeep', 1);

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now  = time();
		$last = (int) $this->params->get('lastrun', 0);

		if ((abs($now - $last) < $cache_timeout))
		{
			return;
		}

		// Update last run status
		$this->params->set('lastrun', $now);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($this->params->toString('JSON')))
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->where($db->qn('folder') . ' = ' . $db->q('system'))
			->where($db->qn('element') . ' = ' . $db->q('logrotation'));

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
			// If we failed to execite
			$db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		// Abort on failure
		if (!$result)
		{
			return;
		}

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
	 * @since   3.9.0
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
	 * @since   3.9.0
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

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   3.9.0
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
