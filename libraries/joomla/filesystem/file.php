<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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

/**
 * A File handling class
 *
 * @package 	Joomla.Framework
 * @subpackage 	FileSystem
 * @static
 * @since 1.1
 */
class JFile {

	/**
	 * Gets the extension of a file name
	 *
	 * @param string $file The file name
	 * @return string The file extension
	 * @since 1.1
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
	 * @since 1.1
	 */
	function stripExt($file) {
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param string $file The name of the file [not full path]
	 * @return string The sanitised string
	 * @since 1.1
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
	 * @return mixed Error message on false or boolean True on success
	 * @since 1.1
	 */
	function copy($src, $dest, $path = null) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpHost = $mainframe->getCfg('ftp_host');
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');


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
			//TODO: Handle an error JText::_('Cannot find source file')
			return false;
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		if ((file_exists($dest) && !is_writable($dest)) || (!file_exists($dest) && !is_writable(dirname($dest)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($ftpHost);
			$ftp->login($ftpUser, $ftpPass);

			// If the parent folder doesn't exist we must create it
			if (!file_exists(dirname($dest))) {
				JFolder::create(dirname($dest));
			}

			//Translate the destination path for the FTP account
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			if (!$ftp->store($src, $dest)) {
				// TODO: Handle error JText::_('Copy failed')
				return false;
			}
			$ftp->quit();

			$ret = true;
		} else {
			if (!@ copy($src, $dest)) {
				// TODO: Handle error JText::_('Copy failed')
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
	 * @since 1.1
	 */
	function delete($file) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpHost = $mainframe->getCfg('ftp_host');
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		if (is_array($file)) {
			$files = $file;
		} else {
			$files[] = $file;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		// Connect the FTP client
		jimport('joomla.connector.ftp');
		$ftp = & JFTP::getInstance($ftpHost);
		$ftp->login($ftpUser, $ftpPass);

		$failed = 0;
		foreach ($files as $file) {
			$file = JPath::clean($file, false);
			JPath::check($file);

			if ($ftpFlag == true || !is_writable($file)) {
				$failed |= $ftp->delete(JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $file)));
			} else {
				$failed |= !unlink($file);
			}
		}
		$ftp->quit();
		return !$failed;
	}

	/**
	 * Moves a file
	 *
	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 * @param string $path An optional base path to prefix to the file names
	 * @return mixed Error message on false or boolean True on success
	 * @since 1.1
	 */
	function move($src, $dest, $path = '') {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpHost = $mainframe->getCfg('ftp_host');
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

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

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		if ((file_exists($dest) && !is_writable($dest)) || (!file_exists($dest) && !is_writable(dirname($dest)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($ftpHost);
			$ftp->login($ftpUser, $ftpPass);

			//Translate path for the FTP account
			$src = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $src), false);
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			// Use FTP rename to simulate move
			if (!$ftp->rename($src, $dest)) {
				return JText::_('Rename failed');
			}

			$ftp->quit();

			$ret = true;
		} else {
			if (!@ rename($src, $dest)) {
				return JText::_('Rename failed');
			}
			$ret = true;
		}
		return $ret;
	}

	/**
	 * Read the contents of a file
	 *
	 * @param string $filename The full file path
	 * @param boolean $incpath Use include path
	 * @return mixed Returns file contents or boolean False if failed
	 * @since 1.1
	 */
	function read($filename, $incpath = false) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpHost = $mainframe->getCfg('ftp_host');
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');
		$data = null;

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		if (!is_readable($filename)) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Now check for http protocol
		if (substr($filename, 0, 7) == 'http://') {
			$ftpFlag = false;
		}

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($ftpHost);
			$ftp->login($ftpUser, $ftpPass);

			//Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $filename), false);

			// Use FTP write buffer to file
			if (!$ftp->read($file, $data)) {
				$ret = false;
			}

			$ret = true;
		} else {

			if (false === $fh = fopen($filename, 'rb', $incpath)) {
				//trigger_error('JFile::read failed to open stream: No such file or directory', E_USER_WARNING);
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
		}
		return $data;
	}

	/**
	 * Write contents to a file
	 *
	 * @param string $file The full file path
	 * @param string $buffer The buffer to write
	 * @return boolean True on success
	 * @since 1.1
	 */
	function write($file, $buffer) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpHost = $mainframe->getCfg('ftp_host');
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		JPath::check($file);

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		if ((file_exists($file) && !is_writable($file)) || (!file_exists($file) && !is_writable(dirname($file)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($ftpHost);
			$ftp->login($ftpUser, $ftpPass);

			// If the destination directory doesn't exist we need to create it
			if (!file_exists(dirname($file))) {
				JFolder::create(dirname($file));
			}

			//Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $file), false);

			// Use FTP write buffer to file
			if (!$ftp->write($file, $buffer)) {
				$ret = false;
			}

			$ftp->quit();
			$ret = true;
		} else {
			$ret = file_put_contents($file, $buffer);
		}

		return $ret;
	}

	/**
	 * @param string The name of the php (temporary) uploaded file
	 * @param string The name of the file to put in the temp directory
	 * @param string The message to return
	 */
	function upload($srcFile, $destFile) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpHost = $mainframe->getCfg('ftp_host');
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');
		$ret = false;

		$srcFile = JPath::clean($srcFile, false);
		$destFile = JPath::clean($destFile, false);
		JPath::check($destFile);

		$baseDir = dirname($destFile);

		/*
		 * If the destination file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		if ((file_exists($destFile) && !is_writable($destFile)) || (!file_exists($destFile) && !is_writable(dirname($destFile)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($ftpHost);
			$ftp->login($ftpUser, $ftpPass);

			// If the destination directory doesn't exist we need to create it
			if (!file_exists($baseDir)) {
				JFolder::create($baseDir);
			}
			//Translate path for the FTP account
			$destFile = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $destFile), false);

			if ($ftp->store($srcFile, $destFile)) {
				$ret = true;
			} else {
				$msg = JText::_('WARNFS_ERR02');
			}
			$ftp->quit();

		} else {
			if (move_uploaded_file($srcFile, $destFile)) {
				if (JPath::setPermissions($destFile)) {
					$ret = true;
				} else {
					$msg = JText::_('WARNFS_ERR01');
				}
			} else {
				$msg = JText::_('WARNFS_ERR02');
			}
		}
		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param string $file File path
	 * @return boolean True if path is a file
	 * @since 1.1
	 */
	function exists($file) {
		$file = JPath::clean($file, false);
		return is_file($file);
	}
}
?>