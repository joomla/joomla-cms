<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');

/**
 * Installer helper class
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerHelper
{
	/**
	 * Downloads a package
	 *
	 * @static
	 * @param string URL of file to download
	 * @param string Download target filename [optional]
	 * @return mixed Path to downloaded package or boolean false on failure
	 * @since 1.5
	 */
	function downloadPackage($url, $target = false)
	{
		$config = &JFactory::getConfig();

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Set user agent
		jimport('joomla.version');
		$version = new JVersion();
		ini_set('user_agent', $version->getUserAgent('Installer'));

		// Open the remote server socket for reading
		$inputHandle = @ fopen($url, "r");
		$error = strstr($php_errormsg,'failed to open stream:');
		if (!$inputHandle) {
			JError::raiseWarning(42, JText::_('SERVER_CONNECT_FAILED').', '.$error);
			return false;
		}

		$meta_data = stream_get_meta_data($inputHandle);
		foreach ($meta_data['wrapper_data'] as $wrapper_data)
		{
			if (substr($wrapper_data, 0, strlen("Content-Disposition")) == "Content-Disposition") {
				$contentfilename = explode ("\"", $wrapper_data);
				$target = $contentfilename[1];
			}
		}

		// Set the target path if not given
		if (!$target) {
			$target = $config->getValue('config.tmp_path').DS.JInstallerHelper::getFilenameFromURL($url);
		} else {
			$target = $config->getValue('config.tmp_path').DS.basename($target);
		}

		// Initialize contents buffer
		$contents = null;

		while (!feof($inputHandle))
		{
			$contents .= fread($inputHandle, 4096);
			if ($contents == false) {
				JError::raiseWarning(44, 'Failed reading network resource: '.$php_errormsg);
				return false;
			}
		}

		// Write buffer to file
		JFile::write($target, $contents);

		// Close file pointer resource
		fclose($inputHandle);

		// restore error tracking to what it was before
		ini_set('track_errors',$track_errors);

		// Return the name of the downloaded package
		return basename($target);
	}

	/**
	 * Unpacks a file and verifies it as a Joomla element package
	 * Supports .gz .tar .tar.gz and .zip
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return Array Two elements - extractdir and packagefile
	 * @since 1.5
	 */
	function unpack($p_filename)
	{
		// Path to the archive
		$archivename = $p_filename;

		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');

		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean(dirname($p_filename).DS.$tmpdir);
		$archivename = JPath::clean($archivename);

		// do the unpacking of the archive
		$result = JArchive::extract($archivename, $extractdir);

		if ($result === false) {
			return false;
		}


		/*
		 * Lets set the extraction directory and package file in the result array so we can
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
			if (JFolder::exists($extractdir.DS.$dirList[0]))
			{
				$extractdir = JPath::clean($extractdir.DS.$dirList[0]);
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
		if ($retval['type'] = JInstallerHelper::detectType($extractdir))
		{
			return $retval;
		} else
		{
			return false;
		}
	}

	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param string $p_dir Path to package directory
	 * @return mixed Extension type string or boolean false on fail
	 * @since 1.5
	 */
	function detectType($p_dir)
	{
		// Search the install dir for an xml file
		$files = JFolder::files($p_dir, '\.xml$', 1, true);

		if (count($files) > 0)
		{

			foreach ($files as $file)
			{
				$xmlDoc = & JFactory::getXMLParser('Simple');

				if (!$xmlDoc->loadFile($file))
				{
					// Free up memory
					unset ($xmlDoc);
					continue;
				}
				$root = & $xmlDoc->document;
				if (!is_object($root) || ($root->name() != "install" && $root->name() != 'extension'))
				{
					unset($xmlDoc);
					continue;
				}

				$type = $root->attributes('type');
				// Free up memory
				unset ($xmlDoc);
				return $type;
			}

			JError::raiseWarning(1, JText::_('ERRORNOTFINDJOOMLAXMLSETUPFILE'));
			// Free up memory.
			unset ($xmlDoc);
			return false;
		} else
		{
			JError::raiseWarning(1, JText::_('ERRORNOTFINDXMLSETUPFILE'));
			return false;
		}
	}

	/**
	 * Gets a file name out of a url
	 *
	 * @static
	 * @param string $url URL to get name from
	 * @return mixed String filename or boolean false if failed
	 * @since 1.5
	 */
	function getFilenameFromURL($url)
	{
		if (is_string($url)) {
			$parts = explode('/', $url);
			return $parts[count($parts) - 1];
		}
		return false;
	}

	/**
	 * Clean up temporary uploaded package and unpacked extension
	 *
	 * @static
	 * @param string $package Path to the uploaded package file
	 * @param string $resultdir Path to the unpacked extension
	 * @return boolean True on success
	 * @since 1.5
	 */
	function cleanupInstall($package, $resultdir)
	{
		$config = &JFactory::getConfig();

		// Does the unpacked extension directory exist?
		if (is_dir($resultdir)) {
			JFolder::delete($resultdir);
		}

		// Is the package file a valid file?
		if (is_file($package)) {
			JFile::delete($package);
		} elseif (is_file(JPath::clean($config->getValue('config.tmp_path').DS.$package))) {
			// It might also be just a base filename
			JFile::delete(JPath::clean($config->getValue('config.tmp_path').DS.$package));
		}
	}

	/**
	 * Splits contents of a sql file into array of discreet queries
	 * queries need to be delimited with end of statement marker ';'
	 * @param string
	 * @return array
	 */
	function splitSql($sql)
	{
		$db = &JFactory::getDbo();
		return $db->splitSql($sql);
	}
}
