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

/** boolean True if a Windows based host */
define('JPATH_ISWIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));
/** boolean True if a Mac based host */
define('JPATH_ISMAC', (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC'));

if (!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPERATOR define */
	define('DS', DIRECTORY_SEPERATOR);
}

if (!defined('JPATH_ROOT')) {
	/** string The root directory of the file system in native format */
	define('JPATH_ROOT', JPath :: clean(JPATH_SITE));
}

/**
 * A File handling class
 *
 * @package Joomla
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
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		// Prepend a base path if it exists
		if ($path) {
			$src = JPath :: clean($path.$src, false);
			$dest = JPath :: clean($path.$dest, false);
		}

		// Check that both paths are in the Joomla filesystem root
		JPath :: check($src);
		JPath :: check($dest);

		//Check src path
		if (!is_readable($src)) {
			//TODO: Handle an error JText :: _('Cannot find source file')
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

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);

			// If the parent folder doesn't exist we must create it
			if (!file_exists(dirname($dest))) {
				JFolder :: create(dirname($dest));
			}

			//Translate the destination path for the FTP account
			$dest = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			if (!$ftp->store($src, $dest)) {
				// TODO: Handle error JText :: _('Copy failed')
				return false;
			}
			$ftp->quit();

			$ret = true;
		} else {
			if (!@ copy($src, $dest)) {
				// TODO: Handle error JText :: _('Copy failed')
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

		// Connect the FTP client
		jimport('joomla.connectors.ftp');
		$ftp = & JFTP :: getInstance('localhost');
		$ftp->login($ftpUser, $ftpPass);

		$failed = 0;
		foreach ($files as $file) {
			$file = JPath :: clean($file, false);
			JPath :: check($file);

			if ($ftpFlag == true || !is_writable($file)) {
				$failed |= $ftp->delete(JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $file)));
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
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		if ($path) {
			$src = JPath :: clean($path.$src, false);
			$dest = JPath :: clean($path.$dest, false);
		}

		JPath :: check($src);
		JPath :: check($dest);

		//Check src path
		if (!is_readable($src) && !is_writable($src)) {
			return JText :: _('Cannot find source file');
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

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);

			//Translate path for the FTP account
			$src = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $src), false);
			$dest = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			// Use FTP rename to simulate move
			if (!$ftp->rename($src, $dest)) {
				return JText :: _('Rename failed');
			}

			$ftp->quit();

			$ret = true;
		} else {
			if (!@ rename($src, $dest)) {
				return JText :: _('Rename failed');
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
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		JPath :: check($file);

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

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);

			// If the destination directory doesn't exist we need to create it
			if (!file_exists(dirname($file))) {
				JFolder :: create(dirname($file));
			}

			//Translate path for the FTP account
			$file = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $file), false);

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
	function upload($srcFile, $destFile, & $msg) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');
		$ret = false;

		$srcFile = JPath :: clean($srcFile, false);
		$destFile = JPath :: clean($destFile, false);
		JPath :: check($destFile);

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

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);

			// If the destination directory doesn't exist we need to create it
			if (!file_exists($baseDir)) {
				JFolder :: create($baseDir);
			}
			//Translate path for the FTP account
			$destFile = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $destFile), false);

			if ($ftp->store($srcFile, $destFile)) {
				$ret = true;
			} else {
				$msg = JText :: _('WARNFS_ERR02');
			}
			$ftp->quit();

		} else {
			if (move_uploaded_file($srcFile, $destFile)) {
				if (JPath :: setPermissions($destFile)) {
					$ret = true;
				} else {
					$msg = JText :: _('WARNFS_ERR01');
				}
			} else {
				$msg = JText :: _('WARNFS_ERR02');
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
		$file = JPath :: clean($file, false);
		return is_file($file);
	}
}

/**
 * A Folder handling class
 *
 * @package Joomla
 * @static
 * @since 1.1
 */
class JFolder {

	/**
	 * Create a folder -- and all necessary parent folders
	 * 
	 * @param string $path A path to create from the base path
	 * @param int $mode Directory permissions to set for folders created
	 * @return boolean True if successful
	 * @since 1.1
	 */
	function create($path = '', $mode = '0755') {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		JPath :: check($path);
		$path = JPath :: clean($path, false, true);

		// Check if dir already exists
		if (JFolder :: exists($path)) {
			return true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}
		// Check for safe mode
		if ($ftpFlag == true) {
			// Do it the safe mode way
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);
			$ret = true;

			// Translate path to FTP path
			$path = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $path), false);

			if (!$ftp->mkdir($path)) {
				$ret = false;
			}
			if (!$ftp->chmod($path, $mode)) {
				$ret = false;
			}

			$ftp->quit();
		} else {
			// First set umask and mode
			$origmask = @ umask(0);
			$mode = octdec($mode);

			// We need to get and explode the open_basedir paths
			$obd = ini_get('open_basedir');
			
			// If open_basedir is et we need to get the open_basedir that the path is in
			if ($obd != null) {
				if (JPATH_ISWIN) {
					$obdSeparator = ";";
				} else {
					$obdSeparator = ":";
				}
				// Create the array of open_basedir paths
				$obdArray = explode($obdSeparator, $obd);
				$inOBD = false;
				// Iterate through open_basedir paths looking for a match
				foreach ($obdArray as $test) {
					if (!(strpos($path, $test) === false)) {
						$obdpath = $test;
						$inOBD = true;
						break;
					}
				}
				
				if ($inOBD == false) {
					// Return false for JFolder::create because the path to be created is not in open_basedir
					return false;
				}
			}

			// Just to make sure
			$inOBD = true;
			
			do {
				$dir = $path;

				while (!@ mkdir($dir, $mode)) {
					$dir = dirname($dir);

					if ($obd != null) {
						if (strpos($dir, $obdpath) === false) {
							$inOBD = false;
							break 2;
						}
					}
					if ($dir == '/' || is_dir($dir))
						break;
				}
			}
			while ($dir != $path);

			// Reset umask
			@ umask($origmask);
			
			// If there is no open_basedir restriction this should always be true
			if ($inOBD == false) {
				// Return false for JFolder::create -- could not create path without violating open_basedir restrictions
				$ret = false;
			} else {
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Delete a folder
	 * 
	 * @param string $path The path to the folder to delete
	 * @return boolean True on success
	 * @since 1.1
	 */
	function delete($path) {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		$path = JPath :: clean($path, false);
		JPath :: check($path);

		// Remove all the files in folder
		$files = JFolder :: files($path, '.', false, true);
		JFile :: delete($files);

		// Remove sub-folders of folder
		$folders = JFolder :: folders($path, '.', false, true);
		foreach ($folders as $folder) {
			JFolder :: delete($folder);
		}

		// Make sure the path to be deleted is writable or use FTP
		if (!is_writable($path)) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		if ($ftpFlag == true) {
			// Do it the FTP way
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);

			// Translate Path
			$path = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $path));
			$ret = $ftp->delete($path);
			$ftp->quit();
		} else {
			// Do it the regular way
			$ret = rmdir($path);
		}
		return $ret;
	}

	/**
	 * Moves a folder
	 * 
	 * @param string $src The path to the source folder
	 * @param string $dest The path to the destination folder
	 * @param string $path An optional base path to prefix to the file names
	 * @return mixed Error message on false or boolean True on success
	 * @since 1.1
	 */
	function move($src, $dest, $path = '') {
		global $mainframe;

		// Initialize variables
		$ftpFlag = false;
		$ftpUser = $mainframe->getCfg('ftp_user');
		$ftpPass = $mainframe->getCfg('ftp_pass');
		$ftpRoot = $mainframe->getCfg('ftp_root');

		if ($path) {
			$src = JPath :: clean($path.$src, false);
			$dest = JPath :: clean($path.$dest, false);
		}

		JPath :: check($src);
		JPath :: check($dest);

		if (!JFolder :: exists($src) && !is_writable($src)) {
			return JText :: _('Cannot find source file');
		}
		if (JFolder :: exists($dest)) {
			return JText :: _('Directory exists');
		}

		/*
		 * If the destination file exists but isn't writable OR if the file doesn't exist and the parent directory 
		 * is not writable we need to use FTP
		 */
		if ((file_exists($dest) && !is_writable($dest)) || (!file_exists($dest) && !is_writable(dirname($dest)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		if ($ftpFlag == true) {
			// Connect the FTP client
			jimport('joomla.connectors.ftp');
			$ftp = & JFTP :: getInstance('localhost');
			$ftp->login($ftpUser, $ftpPass);

			//Translate path for the FTP account
			$src = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $src), false);
			$dest = JPath :: clean(str_replace(JPATH_SITE, $ftpRoot, $dest), false);

			// Use FTP rename to simulate move
			if (!$ftp->rename($src, $dest)) {
				return JText :: _('Rename failed');
			}

			$ftp->quit();

			$ret = true;
		} else {
			if (!@ rename($src, $dest)) {
				return JText :: _('Rename failed');
			}
			$ret = true;
		}
		return $ret;
	}

	/** 
	 * Wrapper for the standard file_exists function
	 * 
	 * @param string $path Folder name relative to installation dir
	 * @return boolean True if path is a folder
	 * @since 1.1
	 */
	function exists($path) {
		$path = JPath :: clean($path, false);
		return is_dir($path);
	}

	/**
	* Utility function to read the files in a directory
	* @param string The file system path
	* @param string A filter for the names
	* @param boolean Recurse search into sub-directories
	* @param boolean True if to prepend the full path to the file name
	* @return array
	*/
	function files($path, $filter = '.', $recurse = false, $fullpath = false) {
		$arr = array ();
		$path = JPath :: clean($path, false);
		if (!is_dir($path)) {
			return $arr;
		}

		// prevent snooping of the file system
		//JPath::check( $path );

		// read the source directory
		$handle = opendir($path);
		$path .= DS;
		while ($file = readdir($handle)) {
			$dir = $path.$file;
			$isDir = is_dir($dir);
			if ($file <> '.' && $file <> '..') {
				if ($isDir) {
					if ($recurse) {
						$arr2 = JFolder :: files($dir, $filter, $recurse, $fullpath);
						$arr = array_merge($arr, $arr2);
					}
				} else {
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $path.$file;
						} else {
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir($handle);
		asort($arr);
		return $arr;
	}

	/**
	* Utility function to read the folders in a directory
	* @param string The file system path
	* @param string A filter for the names
	* @param boolean Recurse search into sub-directories
	* @param boolean True if to prepend the full path to the file name
	* @return array
	*/
	function folders($path, $filter = '.', $recurse = false, $fullpath = false) {
		$arr = array ();
		$path = JPath :: clean($path, false);
		if (!is_dir($path)) {
			return $arr;
		}

		// prevent snooping of the file system
		//mosFS::check( $path );

		// read the source directory
		$handle = opendir($path);
		$path .= DS;
		while ($file = readdir($handle)) {
			$dir = $path.$file;
			$isDir = is_dir($dir);
			if (($file <> '.') && ($file <> '..') && $isDir) {
				// removes SVN directores from list
				if (preg_match("/$filter/", $file) && !(preg_match("/.SVN/", $file))) {
					if ($fullpath) {
						$arr[] = $dir;
					} else {
						$arr[] = $file;
					}
				}
				if ($recurse) {
					$arr2 = JFolder :: folders($dir, $filter, $recurse, $fullpath);
					$arr = array_merge($arr, $arr2);
				}
			}
		}
		closedir($handle);
		asort($arr);
		return $arr;
	}

	/**
	 * Lists folder in format suitable for tree display
	 */
	function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0) {
		$dirs = array ();
		if ($level == 0) {
			$GLOBALS['_JFolder_folder_tree_index'] = 0;
		}

		if ($level < $maxLevel) {
			JPath :: check($path);

			$folders = JFolder :: folders($path, $filter);

			// first path, index foldernames
			for ($i = 0, $n = count($folders); $i < $n; $i ++) {
				$id = ++ $GLOBALS['_JFolder_folder_tree_index'];
				$name = $folders[$i];
				$fullName = JPath :: clean($path.'/'.$name, false);
				$dirs[] = array ('id' => $id, 'parent' => $parent, 'name' => $name, 'fullname' => $fullName, 'relname' => str_replace(JPATH_ROOT, '', $fullName));
				$dirs2 = JFolder :: listFolderTree($fullName, $filter, $maxLevel, $level +1, $id);
				$dirs = array_merge($dirs, $dirs2);
			}
		}

		return $dirs;
	}
}

/**
 * An Archive handling class
 *
 * @package Joomla
 * @static
 * @since 1.1
 */
class JArchive {
	/**
	 * @param string The name of the archive
	 * @param mixed The name of a single file or an array of files
	 * @param string The compression for the archive
	 * @param string Path to add within the archive
	 * @param string Path to remove within the archive
	 * @param boolean Automatically append the extension for the archive
	 * @param boolean Remove for source files
	 */
	function create($archive, $files, $compress = 'tar', $addPath = '', $removePath = '', $autoExt = false, $cleanUp = false) {

		jimport('archive.Tar');

		if (is_string($files)) {
			$files = array ($files);
		}
		if ($autoExt) {
			$archive .= '.'.$compress;
		}

		$tar = new Archive_Tar($archive, $compress);
		$tar->setErrorHandling(PEAR_ERROR_PRINT);
		$tar->createModify($files, $addPath, $removePath);

		if ($cleanUp) {
			JFile :: delete($files);
		}
		return $tar;
	}
}

/**
 * A Path handling class
 * @package Joomla
 * @static
 * @since 1.1
 */
class JPath {

	/**
	 * Checks if a path's permissions can be changed
	 * 
	 * @param string $path Path to check
	 * @return boolean True if path can have mode changed
	 * @since 1.1
	 */
	function canChmod($path) {
		$perms = fileperms($path);
		if ($perms !== false)
			if (@ chmod($path, $perms ^ 0001)) {
				@ chmod($path, $perms);
				return true;
			}
		return false;
	}

	/**
	 * Chmods files and directories recursivly to given permissions
	 * 
	 * @param string $path Root path to begin changing mode [without trailing slash]
	 * @param string $filemode Octal representation of the value to change file mode to [null = no change]
	 * @param string $foldermode Octal representation of the value to change folder mode to [null = no change]
	 * @return boolean True if successful [one fail means the whole operation failed]
	 * @since 1.1
	 */
	function setPermissions($path, $filemode = '0644', $foldermode = '0755') {

		// Initialize return value
		$ret = true;

		if (is_dir($path)) {
			$dh = opendir($path);
			while ($file = readdir($dh)) {
				if ($file != '.' && $file != '..') {
					$fullpath = $path.'/'.$file;
					if (is_dir($fullpath)) {
						if (!JPath :: setPermissions($fullpath, $filemode, $foldermode)) {
							$ret = false;
						}
					} else {
						if (isset ($filemode)) {
							if (!@ chmod($fullpath, octdec($filemode))) {
								$ret = false;
							}
						}
					} // if
				} // if
			} // while
			closedir($dh);
			if (isset ($foldermode))
				if (!@ chmod($path, octdec($foldermode))) {
					$ret = false;
				}
		} else {
			if (isset ($filemode))
				$ret = @ chmod($path, octdec($filemode));
		} // if
		return $ret;
	}

	/**
	 * Get the permissions of the file/folder at a give path
	 * 
	 * @param string $path The path of a file/folder
	 * @return string Filesystem permissions
	 * @since 1.1
	 */
	function getPermissions($path) {
		$path = JPath :: clean($path, false);
		JPath :: check($path);
		$mode = @ decoct(@ fileperms($path) & 0777);

		if (strlen($mode) < 3) {
			return '---------';
		}
		$parsed_mode = '';
		for ($i = 0; $i < 3; $i ++) {
			// read
			$parsed_mode .= ($mode {
				$i }
			& 04) ? "r" : "-";
			// write
			$parsed_mode .= ($mode {
				$i }
			& 02) ? "w" : "-";
			// execute
			$parsed_mode .= ($mode {
				$i }
			& 01) ? "x" : "-";
		}
		return $parsed_mode;
	}

	/**
	 * Checks for snooping outside of the file system root
	 * 
	 * @param string $path A file system path to check
	 * @since 1.1
	 */
	function check($path) {
		if (strpos($path, '..') !== false) {
			mosBackTrace();
			die('JPath::check use of relative paths not permitted'); // don't translate
		}
		if (strpos(JPath :: clean($path), JPath :: clean(JPATH_ROOT)) !== 0) {
			mosBackTrace();
			die('JPath::check snooping out of bounds @ '.$path); // don't translate
		}
	}

	/**
	 * Function to strip additional / or \ in a path name
	 * 
	 * @param string $p_path The path to clean
	 * @param boolean $p_addtrailingslash True if the function shoul add a trailing slash
	 * @return string The cleaned path
	 * @since 1.1
	 */
	function clean($p_path, $p_addtrailingslash = true) {
		$retval = '';
		$path = trim($p_path);

		if (empty ($p_path)) {
			$retval = JPATH_ROOT;
		} else {
			if (JPATH_ISWIN) {
				$retval = str_replace('/', DS, $p_path);
				// Remove double \\
				$retval = str_replace('\\\\', DS, $retval);
			} else {
				$retval = str_replace('\\', DS, $p_path);
				// Remove double //
				$retval = str_replace('//', DS, $retval);
			}
		}
		if ($p_addtrailingslash) {
			if (substr($retval, -1) != DS) {
				$retval .= DS;
			}
		}

		return $retval;
	}

	/**
	 * Method to determine if script owns the path
	 * 
	 * @static
	 * @param string $path Path to check ownership
	 * @return boolean True if the php script owns the path passed
	 * @since 1.1
	 */
	function isOwner($path) {
		return (posix_getuid() == fileowner($path));
	}
}
?>