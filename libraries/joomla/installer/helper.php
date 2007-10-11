<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.*');

/**
 * Installer helper class
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
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
		$config =& JFactory::getConfig();

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		ini_set('track_errors', true);

		// Set user agent
		ini_set('user_agent', "Joomla! 1.5 Installer");

		// Open the remote server socket for reading
		$inputHandle = @ fopen($url, "r");
		if (!$inputHandle) {
			JError::raiseWarning(42, 'Remote Server connection failed: '.$php_errormsg);
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

		// Return the name of the downloaded package
		return basename($target);
	}

	/**
	 * Unpacks a file and verifies it as a Joomla element package
	 * Supports .gz .tar .tar.gz and .zip
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return boolean True on success, False on error
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
		$result = JArchive::extract( $archivename, $extractdir);

		if ( $result === false ) {
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
				$xmlDoc = & JFactory::getXMLParser();
				$xmlDoc->resolveErrors(true);

				if (!$xmlDoc->loadXML($file, false, true))
				{
					// Free up memory from DOMIT parser
					unset ($xmlDoc);
					continue;
				}
				$root = & $xmlDoc->documentElement;

				if ($root->getTagName() != "install" && $root->getTagName() != 'mosinstall')
				{
					continue;
				}

				$type = $root->getAttribute('type');
				// Free up memory from DOMIT parser
				unset ($xmlDoc);
				return $type;
			}

			JError::raiseWarning(1, JText::_('ERRORNOTFINDJOOMLAXMLSETUPFILE'));
			// Free up memory from DOMIT parser
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
	 * @param string $p_file Path to the uploaded package file
	 * @param string $resultdir Path to the unpacked extension
	 * @return boolean True on success
	 * @since 1.5
	 */
	function cleanupInstall($package, $resultdir)
	{
		$config =& JFactory::getConfig();

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
		$sql = trim($sql);
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++)
		{
			if ($sql[$i] == ";" && !$in_string) {
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
				$in_string = false;
			} elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
				$in_string = $sql[$i];
			} if (isset ($buffer[1])) {
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql)) {
			$ret[] = $sql;
		}
		return ($ret);
	}
}