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
	 * @return boolean True on success
	 * @since 1.1
	 */
	function copy($src, $dest, $path = null) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
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
			JError::raiseWarning( 21, 'JFile::copy: '. JText::_('Cannot find or read file: '.$src));
			return false;
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist
		 * and the parent directory is not writable we need to use FTP
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
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'));
			$ftp->login($mainframe->getCfg('ftp_user'),$mainframe->getCfg('ftp_pass'));

			// If the parent folder doesn't exist we must create it
			if (!file_exists(dirname($dest))) {
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
				JError::raiseWarning( 21, JText::_('Copy failed'));
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
		if ($mainframe->getCfg('ftp_enable') == 1) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'));
			$ftp->login($mainframe->getCfg('ftp_user'),$mainframe->getCfg('ftp_pass'));
			$enabled = true;
		} else {
			$enabled = false;
			$ftpFlag = false;
		}

		$retval = true;
		foreach ($files as $file) {
			$file = JPath::clean($file, false);
			JPath::check($file);

			if (($enabled == true && !is_writable($file)) || $ftpFlag == true) {
				$fail = !$ftp->delete(JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $file)));
			} else {
				$fail = !unlink($file);
			}
			
			if ($fail) {
				$retval = false;
			}
		}

		if ($enabled) {
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
	 * @since 1.1
	 */
	function move($src, $dest, $path = '') {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
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
		 * If the file exists but isn't writable OR if the file doesn't exist
		 * and the parent directory is not writable we need to use FTP
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
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'));
			$ftp->login($mainframe->getCfg('ftp_user'),$mainframe->getCfg('ftp_pass'));

			//Translate path for the FTP account
			$src = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $src), false);
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			// Use FTP rename to simulate move
			if (!$ftp->rename($src, $dest)) {
				JError::raiseWarning( 21, JText::_('Rename failed'));
				return false; 
			}
			$ftp->quit();

		} else {
			if (!@ rename($src, $dest)) {
				JError::raiseWarning( 21, JText::_('Rename failed'));
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
	 * @since 1.1
	 */
	function read($filename, $incpath = false) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
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
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'));
			$ftp->login($mainframe->getCfg('ftp_user'),$mainframe->getCfg('ftp_pass'));

			//Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $filename), false);

			// Use FTP write buffer to file
			if (!$ftp->read($file, $data)) {
				$ret = false;
			}

			$ret = true;
		} else {

			if (false === $fh = fopen($filename, 'rb', $incpath)) {
				JError::raiseWarning( 21, 'JFile::read: '. JText::_( 'Unable to open file ').$filename);
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
		$ftpRoot = $mainframe->getCfg('ftp_root');

		JPath::check($file);

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist
		 * and the parent directory is not writable we need to use FTP
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
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'));
			$ftp->login($mainframe->getCfg('ftp_user'),$mainframe->getCfg('ftp_pass'));

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
	 * Moves and uploaded file to a destination folder
	 * 
	 * @param string $src The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded
	 * file to
	 * @return boolean True on success
	 * @since 1.1
	 */
	function upload($src, $dest) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpRoot = $mainframe->getCfg('ftp_root');
		$ret = false;

		/*
		 * Prepare the source and destination paths as well as verify that the
		 * detination path is in the Joomla Root
		 */
		$src = JPath::clean($src, false);
		$dest = JPath::clean($dest, false);
		JPath::check($dest);

		/*
		 * If the destination directory does not exist, we need to create it.
		 */
		$baseDir = dirname($dest);
		if (!file_exists($baseDir)) {
			JFolder::create($baseDir);
		}

		/*
		 * If the destination file exists but isn't writable OR if the file
		 * doesn't exist and the parent directory is not writable we need to use
		 * FTP
		 */
		if ((file_exists($dest) && !is_writable($dest)) || (!file_exists($dest) && !is_writable(dirname($dest)))) {
			$ftpFlag = true;
		}

		/*
		 * Oh, and use ftp if we are in safe mode...
		 */
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		/*
		 * But, not if it is not enabled...
		 */
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'));
			$ftp->login($mainframe->getCfg('ftp_user'),$mainframe->getCfg('ftp_pass'));

			//Translate path for the FTP account
			$dest = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			/*
			 * Copy the file to the destination directory
			 */
			if ($ftp->store($src, $dest)) {
				$ret = true;
			} else {
				JError::raiseWarning( 21, JText::_('WARNFS_ERR02'));
			}
			$ftp->quit();

		} else {
			if (move_uploaded_file($src, $dest)) {
				if (JPath::setPermissions($dest)) {
					$ret = true;
				} else {
					JError::raiseWarning( 21, JText::_('WARNFS_ERR01'));
				}
			} else {
				JError::raiseWarning( 21, JText::_('WARNFS_ERR02'));
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