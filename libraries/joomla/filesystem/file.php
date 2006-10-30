<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

if (!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPERATOR define */
	define('DS', DIRECTORY_SEPERATOR);
}

jimport('joomla.filesystem.path');

/**
 * A File handling class
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	FileSystem
 * @since		1.5
 */
class JFile
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param string $file The file name
	 * @return string The file extension
	 * @since 1.5
	 */
	function getExt($file) {
		$dot = strrpos($file, '.') + 1;
		return substr($file, $dot);
	}

	/**
	 * Strips the last extension off a file name
	 *
	 * @param string $file The file name
	 * @return string The file name without the extension
	 * @since 1.5
	 */
	function stripExt($file) {
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param string $file The name of the file [not full path]
	 * @return string The sanitised string
	 * @since 1.5
	 */
	function makeSafe($file) {
		$regex = '#\.\.[^A-Za-z0-9\.\_\- ]#';
		return preg_replace($regex, '', $file);
	}

	/**
	 * Copies a file
	 *
	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 * @param string $path An optional base path to prefix to the file names
	 * @return boolean True on success
	 * @since 1.5
	 */
	function copy($src, $dest, $path = null)
	{
		// Prepend a base path if it exists
		if ($path) {
			$src = JPath::clean($path.$src, false);
			$dest = JPath::clean($path.$dest, false);
		}

		// Check that both paths are in the Joomla filesystem root
		JPath::check($src);
		JPath::check($dest);

		//Check src path
		if (!is_readable($src)) {
			JError::raiseWarning(21, 'JFile::copy: '.JText::_('Cannot find or read file: '.$src));
			return false;
		}

		$config =& JFactory::getConfig();

		if ($config->getValue('config.ftp_enable'))
		{
			$ftpRoot = $config->getValue('config.ftp_root');

			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($config->getValue('config.ftp_host'), $config->getValue('config.ftp_port'));
			$ftp->login($config->getValue('config.ftp_user'), $config->getValue('config.ftp_pass'));

			// If the parent folder doesn't exist we must create it
			if (!file_exists(dirname($dest))) {
				jimport('joomla.filesystem.folder');
				JFolder::create(dirname($dest));
			}

			//Translate the destination path for the FTP account
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);
			if (!$ftp->store($src, $dest)) {
				// FTP connector throws an error
				return false;
			}
			$ftp->quit();
			$ret = true;
		} else {
			if (!@ copy($src, $dest)) {
				JError::raiseWarning(21, JText::_('Copy failed'));
				return false;
			}
			$ret = true;
		}
		return $ret;
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param mixed $file The file name or an array of file names
	 * @return boolean  True on success
	 * @since 1.5
	 */
	function delete($file)
	{
		$config =& JFactory::getConfig();

		// Initialize variables
		$ftpFlag	= true;
		$ftpRoot	= $config->getValue('config.ftp_root');

		if (is_array($file)) {
			$files = $file;
		} else {
			$files[] = $file;
		}

		// Do NOT use ftp if it is not enabled
		if ($config->getValue('config.ftp_enable') != 1) {
			$ftpFlag = false;
		} else {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($config->getValue('config.ftp_host'), $config->getValue('config.ftp_port'));
			$ftp->login($config->getValue('config.ftp_user'), $config->getValue('config.ftp_pass'));
		}

		$retval = true;
		foreach ($files as $file) {
			$file = JPath::clean($file, false);
			JPath::check($file);
			
		    // In case of restricted permissions we zap it one way or the other
		    // as long as the owner is either the webserver or the ftp
			if(@unlink($file)){
	        	$retval = true;
	      	} else {
	        	if($ftpFlag){
	          		$retval = $ftp->delete(JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $file), false));
	        	}
	      	}
		}

		// Close FTP connection if connected
		if ($ftpFlag) {
			$ftp->quit();
		}
		return $retval;
	}

	/**
	 * Moves a file
	 *
	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 * @param string $path An optional base path to prefix to the file names
	 * @return boolean True on success
	 * @since 1.5
	 */
	function move($src, $dest, $path = '')
	{
		$config =& JFactory::getConfig();

		// Initialize variables
		$ftpFlag	= true;
		$ftpRoot	= $config->getValue('config.ftp_root');

		if ($path) {
			$src = JPath::clean($path.$src, false);
			$dest = JPath::clean($path.$dest, false);
		}
		JPath::check($src);
		JPath::check($dest);

		//Check src path
		if (!is_readable($src) && !is_writable($src)) {
			return JText::_('Cannot find source file');
		}

		// Do NOT use ftp if it is not enabled
		if ($config->getValue('config.ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($config->getValue('config.ftp_host'), $config->getValue('config.ftp_port'));
			$ftp->login($config->getValue('config.ftp_user'), $config->getValue('config.ftp_pass'));

			//Translate path for the FTP account
			$src  = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $src), false);
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			// Use FTP rename to simulate move
			if (!$ftp->rename($src, $dest)) {
				JError::raiseWarning(21, JText::_('Rename failed'));
				return false;
			}
			$ftp->quit();
		} else {
			if (!@ rename($src, $dest)) {
				JError::raiseWarning(21, JText::_('Rename failed'));
				return false;
			}
		}
		return true;
	}

	/**
	 * Read the contents of a file
	 *
	 * @param string $filename The full file path
	 * @param boolean $incpath Use include path
	 * @return mixed Returns file contents or boolean False if failed
	 * @since 1.5
	 */
	function read($filename, $incpath = false)
	{
		// Initialize variables
		$data = null;

		if (false === $fh = fopen($filename, 'rb', $incpath)) {
			JError::raiseWarning(21, 'JFile::read: '.JText::_('Unable to open file ').$filename);
			return false;
		}
		clearstatcache();
		if ($fsize = @ filesize($filename)) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}
		fclose($fh);

		return $data;
	}

	/**
	 * Write contents to a file
	 *
	 * @param string $file The full file path
	 * @param string $buffer The buffer to write
	 * @return boolean True on success
	 * @since 1.5
	 */
	function write($file, $buffer)
	{
		$config =& JFactory::getConfig();

		// Initialize variables
		$ftpFlag	= true;
		$ftpRoot	= $config->getValue('config.ftp_root');
		JPath::check($file);

		// Do NOT use ftp if it is not enabled
		if ($config->getValue('config.ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($config->getValue('config.ftp_host'), $config->getValue('config.ftp_port'));
			$ftp->login($config->getValue('config.ftp_user'), $config->getValue('config.ftp_pass'));

			// If the destination directory doesn't exist we need to create it
			if (!file_exists(dirname($file))) {
				jimport('joomla.filesystem.folder');
				JFolder::create(dirname($file));
			}

			//Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $file), false);

			// Use FTP write buffer to file
			$ret = $ftp->write($file, $buffer);
			$ftp->quit();
		} else {
			$ret = file_put_contents($file, $buffer);
		}
		return $ret;
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param string $src The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded file to
	 * @return boolean True on success
	 * @since 1.5
	 */
	function upload($src, $dest)
	{
		$config =& JFactory::getConfig();

		// Initialize variables
		$ftpFlag	= true;
		$ftpRoot	= $config->getValue('config.ftp_root');
		$ret		= false;

		// Ensure that the path is valid and clean
		$dest = JPath::clean($dest, false);
		JPath::check($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);
		if (!file_exists($baseDir)) {
			jimport('joomla.filesystem.folder');
			JFolder::create($baseDir);
		}

		// do NOT use FTP if it is not enabled
		if ($config->getValue('config.ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($config->getValue('config.ftp_host'), $config->getValue('config.ftp_port'));
			$ftp->login($config->getValue('config.ftp_user'), $config->getValue('config.ftp_pass'));

			//Translate path for the FTP account
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			// Copy the file to the destination directory
			if ($ftp->store($src, $dest)) {
				$ftp->chmod($dest, 0777);
				$ret = true;
			} else {
				JError::raiseWarning(21, JText::_('WARNFS_ERR02'));
			}
			$ftp->quit();
		} else {
			if (move_uploaded_file($src, $dest)) {
				if (JPath::setPermissions($dest)) {
					$ret = true;
				} else {
					JError::raiseWarning(21, JText::_('WARNFS_ERR01'));
				}
			} else {
				JError::raiseWarning(21, JText::_('WARNFS_ERR02'));
			}
		}
		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param string $file File path
	 * @return boolean True if path is a file
	 * @since 1.5
	 */
	function exists($file) {
		$file = JPath::clean($file, false);
		return is_file($file);
	}
}
?>