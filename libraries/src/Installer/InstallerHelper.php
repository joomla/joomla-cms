<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

defined('JPATH_PLATFORM') or die;

use Joomla\Archive\Archive;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;

\JLoader::import('joomla.filesystem.file');
\JLoader::import('joomla.filesystem.folder');
\JLoader::import('joomla.filesystem.path');

/**
 * Installer helper class
 *
 * @since  3.1
 */
abstract class InstallerHelper
{
	/**
	 * Hash not validated identifier.
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	const HASH_NOT_VALIDATED = 0;

	/**
	 * Hash validated identifier.
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	const HASH_VALIDATED = 1;

	/**
	 * Hash not provided identifier.
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	const HASH_NOT_PROVIDED = 2;

	/**
	 * Downloads a package
	 *
	 * @param   string  $url     URL of file to download
	 * @param   mixed   $target  Download target filename or false to get the filename from the URL
	 *
	 * @return  string|boolean  Path to downloaded package or boolean false on failure
	 *
	 * @since   3.1
	 */
	public static function downloadPackage($url, $target = false)
	{
		// Capture PHP errors
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Set user agent
		$version = new Version;
		ini_set('user_agent', $version->getUserAgent('Installer'));

		// Load installer plugins, and allow URL and headers modification
		$headers = array();
		PluginHelper::importPlugin('installer');
		$dispatcher = \JEventDispatcher::getInstance();
		$dispatcher->trigger('onInstallerBeforePackageDownload', array(&$url, &$headers));

		try
		{
			$response = \JHttpFactory::getHttp()->get($url, $headers);
		}
		catch (\RuntimeException $exception)
		{
			\JLog::add(\JText::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $exception->getMessage()), \JLog::WARNING, 'jerror');

			return false;
		}

		// Convert keys of headers to lowercase, to accommodate for case variations
		$headers = array_change_key_case($response->headers);

		if (302 == $response->code && !empty($headers['location']))
		{
			return self::downloadPackage($headers['location']);
		}
		elseif (200 != $response->code)
		{
			\JLog::add(\JText::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $response->code), \JLog::WARNING, 'jerror');

			return false;
		}

		// Parse the Content-Disposition header to get the file name
		if (!empty($headers['content-disposition'])
			&& preg_match("/\s*filename\s?=\s?(.*)/", $headers['content-disposition'], $parts))
		{
			$flds = explode(';', $parts[1]);
			$target = trim($flds[0], '"');
		}

		$tmpPath = \JFactory::getConfig()->get('tmp_path');

		// Set the target path if not given
		if (!$target)
		{
			$target = $tmpPath . '/' . self::getFilenameFromUrl($url);
		}
		else
		{
			$target = $tmpPath . '/' . basename($target);
		}

		// Write buffer to file
		\JFile::write($target, $response->body);

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
	 * @param   string   $packageFilename    The uploaded package filename or install directory
	 * @param   boolean  $alwaysReturnArray  If should return false (and leave garbage behind) or return $retval['type']=false
	 *
	 * @return  array|boolean  Array on success or boolean false on failure
	 *
	 * @since   3.1
	 */
	public static function unpack($packageFilename, $alwaysReturnArray = false)
	{
		// Path to the archive
		$archivename = $packageFilename;

		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');

		// Clean the paths to use for archive extraction
		$extractdir = \JPath::clean(dirname($packageFilename) . '/' . $tmpdir);
		$archivename = \JPath::clean($archivename);

		// Do the unpacking of the archive
		try
		{
			$archive = new Archive(array('tmp_path' => \JFactory::getConfig()->get('tmp_path')));
			$extract = $archive->extract($archivename, $extractdir);
		}
		catch (\Exception $e)
		{
			if ($alwaysReturnArray)
			{
				return array(
					'extractdir'  => null,
					'packagefile' => $archivename,
					'type'        => false,
				);
			}

			return false;
		}

		if (!$extract)
		{
			if ($alwaysReturnArray)
			{
				return array(
					'extractdir'  => null,
					'packagefile' => $archivename,
					'type'        => false,
				);
			}

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
		$dirList = array_merge((array) \JFolder::files($extractdir, ''), (array) \JFolder::folders($extractdir, ''));

		if (count($dirList) === 1)
		{
			if (\JFolder::exists($extractdir . '/' . $dirList[0]))
			{
				$extractdir = \JPath::clean($extractdir . '/' . $dirList[0]);
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

		if ($alwaysReturnArray || $retval['type'])
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
	 * @param   string  $packageDirectory  Path to package directory
	 *
	 * @return  mixed  Extension type string or boolean false on fail
	 *
	 * @since   3.1
	 */
	public static function detectType($packageDirectory)
	{
		// Search the install dir for an XML file
		$files = \JFolder::files($packageDirectory, '\.xml$', 1, true);

		if (!$files || !count($files))
		{
			\JLog::add(\JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'), \JLog::WARNING, 'jerror');

			return false;
		}

		foreach ($files as $file)
		{
			$xml = simplexml_load_file($file);

			if (!$xml)
			{
				continue;
			}

			if ($xml->getName() !== 'extension')
			{
				unset($xml);
				continue;
			}

			$type = (string) $xml->attributes()->type;

			// Free up memory
			unset($xml);

			return $type;
		}

		\JLog::add(\JText::_('JLIB_INSTALLER_ERROR_NOTFINDJOOMLAXMLSETUPFILE'), \JLog::WARNING, 'jerror');

		// Free up memory.
		unset($xml);

		return false;
	}

	/**
	 * Gets a file name out of a url
	 *
	 * @param   string  $url  URL to get name from
	 *
	 * @return  string  Clean version of the filename or a unique id
	 *
	 * @since   3.1
	 */
	public static function getFilenameFromUrl($url)
	{
		$default = uniqid();

		if (!is_string($url) || strpos($url, '/') === false)
		{
			return $default;
		}

		// Get last part of the url (after the last slash).
		$parts    = explode('/', $url);
		$filename = array_pop($parts);

		// Replace special characters with underscores.
		$filename = preg_replace('/[^a-z0-9\_\-\.]/i', '_', $filename);

		// Replace multiple underscores with just one.
		$filename = preg_replace('/__+/', '_', trim($filename, '_'));

		// Return the cleaned filename or, if it is empty, a unique id.
		return $filename ?: $default;
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
		$config = \JFactory::getConfig();

		// Does the unpacked extension directory exist?
		if ($resultdir && is_dir($resultdir))
		{
			\JFolder::delete($resultdir);
		}

		// Is the package file a valid file?
		if (is_file($package))
		{
			\JFile::delete($package);
		}
		elseif (is_file(\JPath::clean($config->get('tmp_path') . '/' . $package)))
		{
			// It might also be just a base filename
			\JFile::delete(\JPath::clean($config->get('tmp_path') . '/' . $package));
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
	 * @deprecated  4.0  Use \JDatabaseDriver::splitSql() directly
	 * @codeCoverageIgnore
	 */
	public static function splitSql($query)
	{
		\JLog::add('JInstallerHelper::splitSql() is deprecated. Use JDatabaseDriver::splitSql() instead.', \JLog::WARNING, 'deprecated');
		$db = \JFactory::getDbo();

		return $db->splitSql($query);
	}

	/**
	 * Return the result of the checksum of a package with the SHA256/SHA384/SHA512 tags in the update server manifest
	 *
	 * @param   string   $packagefile   Location of the package to be installed
	 * @param   JUpdate  $updateObject  The Update Object
	 *
	 * @return  integer  one if the hashes match, zero if hashes doesn't match, two if hashes not found
	 *
	 * @since   3.9.0
	 */
	public static function isChecksumValid($packagefile, $updateObject)
	{
		$hashes     = array('sha256', 'sha384', 'sha512');
		$hashOnFile = false;

		foreach ($hashes as $hash)
		{
			if ($updateObject->get($hash, false))
			{
				$hashPackage = hash_file($hash, $packagefile);
				$hashRemote  = $updateObject->$hash->_data;
				$hashOnFile  = true;

				if ($hashPackage !== strtolower($hashRemote))	
				{
					return self::HASH_NOT_VALIDATED;
				}
			}
		}

		if ($hashOnFile)
		{
			return self::HASH_VALIDATED;
		}

		return self::HASH_NOT_PROVIDED;
	}
}
