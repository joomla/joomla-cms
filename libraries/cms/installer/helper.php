<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

/**
 * Installer helper class
 *
 * @package     Joomla.Libraries
 * @subpackage  Installer
 * @since       3.1
 */
abstract class JInstallerHelper
{
	/**
	 * Downloads a package
	 *
	 * @param   string  $url     URL of file to download
	 * @param   string  $target  Download target filename [optional]
	 *
	 * @return  mixed  Path to downloaded package or boolean false on failure
	 *
	 * @since   3.1
	 */
	public static function downloadPackage($url, $target = false)
	{
		$config = JFactory::getConfig();

		// Capture PHP errors
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Set user agent
		$version = new JVersion;
		ini_set('user_agent', $version->getUserAgent('Installer'));

		$http = JHttpFactory::getHttp();
		$response = $http->get($url);

		if (302 == $response->code && isset($response->headers['Location']))
		{
			return self::downloadPackage($response->headers['Location']);
		}
		elseif (200 != $response->code)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT'), JLog::WARNING, 'jerror');

			return false;
		}

		if (isset($response->headers['Content-Disposition']))
		{
			$contentfilename = explode("\"", $response->headers['Content-Disposition']);
			$target = $contentfilename[1];
		}

		// Set the target path if not given
		if (!$target)
		{
			$target = $config->get('tmp_path') . '/' . self::getFilenameFromURL($url);
		}
		else
		{
			$target = $config->get('tmp_path') . '/' . basename($target);
		}

		// Write buffer to file
		JFile::write($target, $response->body);

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Bump the max execution time because not using built in php zip libs are slow
		@set_time_limit(ini_get('max_execution_time'));

		// Return the name of the downloaded package
		return basename($target);
	}

	/**
	 * Unpacks a file and verifies it as a Joomla element package
	 * Supports .gz .tar .tar.gz and .zip
	 *
	 * @param   string  $p_filename  The uploaded package filename or install directory
	 *
	 * @return  mixed  Array on success or boolean false on failure
	 *
	 * @since   3.1
	 */
	public static function unpack($p_filename)
	{
		// Path to the archive
		$archivename = $p_filename;

		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');

		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean(dirname($p_filename) . '/' . $tmpdir);
		$archivename = JPath::clean($archivename);

		// Do the unpacking of the archive
		try
		{
			JArchive::extract($archivename, $extractdir);
		}
		catch (Exception $e)
		{
			return false;
		}

		/*
		 * Let's set the extraction directory and package file in the result array so we can
		 * cleanup everything properly later on.
		 */
		$retval['extractdir'] = $extractdir;
		$retval['packagefile'] = $archivename;

		/*
		 * Try to find the correct install directory.  In case the package is inside a
		 * subdirectory detect this and set the install directory to the correct path.
		 *
		 * List all the items in the installation directory.  If there is only one, and
		 * it is a folder, then we will set that folder to be the installation folder.
		 */
		$dirList = array_merge(JFolder::files($extractdir, ''), JFolder::folders($extractdir, ''));

		if (count($dirList) == 1)
		{
			if (JFolder::exists($extractdir . '/' . $dirList[0]))
			{
				$extractdir = JPath::clean($extractdir . '/' . $dirList[0]);
			}
		}

		/*
		 * We have found the install directory so lets set it and then move on
		 * to detecting the extension type.
		 */
		$retval['dir'] = $extractdir;

		/*
		 * Get the extension type and return the directory/type array on success or
		 * false on fail.
		 */
		$retval['type'] = self::detectType($extractdir);

		if ($retval['type'])
		{
			return $retval;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @param   string  $p_dir  Path to package directory
	 *
	 * @return  mixed  Extension type string or boolean false on fail
	 *
	 * @since   3.1
	 */
	public static function detectType($p_dir)
	{
		// Search the install dir for an XML file
		$files = JFolder::files($p_dir, '\.xml$', 1, true);

		if (!count($files))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'), JLog::WARNING, 'jerror');

			return false;
		}

		foreach ($files as $file)
		{
			$xml = simplexml_load_file($file);

			if (!$xml)
			{
				continue;
			}

			if ($xml->getName() != 'extension')
			{
				unset($xml);
				continue;
			}

			$type = (string) $xml->attributes()->type;

			// Free up memory
			unset($xml);

			return $type;
		}

		JLog::add(JText::_('JLIB_INSTALLER_ERROR_NOTFINDJOOMLAXMLSETUPFILE'), JLog::WARNING, 'jerror');

		// Free up memory.
		unset($xml);

		return false;
	}

	/**
	 * Gets a file name out of a url
	 *
	 * @param   string  $url  URL to get name from
	 *
	 * @return  mixed   String filename or boolean false if failed
	 *
	 * @since   3.1
	 */
	public static function getFilenameFromURL($url)
	{
		if (is_string($url))
		{
			$parts = explode('/', $url);

			return $parts[count($parts) - 1];
		}
		return false;
	}

	/**
	 * Clean up temporary uploaded package and unpacked extension
	 *
	 * @param   string  $package    Path to the uploaded package file
	 * @param   string  $resultdir  Path to the unpacked extension
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public static function cleanupInstall($package, $resultdir)
	{
		$config = JFactory::getConfig();

		// Does the unpacked extension directory exist?
		if (is_dir($resultdir))
		{
			JFolder::delete($resultdir);
		}

		// Is the package file a valid file?
		if (is_file($package))
		{
			JFile::delete($package);
		}
		elseif (is_file(JPath::clean($config->get('tmp_path') . '/' . $package)))
		{
			// It might also be just a base filename
			JFile::delete(JPath::clean($config->get('tmp_path') . '/' . $package));
		}
	}

	/**
	 * Splits contents of a sql file into array of discreet queries.
	 * Queries need to be delimited with end of statement marker ';'
	 *
	 * @param   string  $query  The SQL statement.
	 *
	 * @return  array  Array of queries
	 *
	 * @since   3.1
	 * @deprecated  13.3  Use JDatabaseDriver::splitSql() directly
	 * @codeCoverageIgnore
	 */
	public static function splitSql($query)
	{
		JLog::add('JInstallerHelper::splitSql() is deprecated. Use JDatabaseDriver::splitSql() instead.', JLog::WARNING, 'deprecated');
		$db = JFactory::getDbo();

		return $db->splitSql($query);
	}
}
