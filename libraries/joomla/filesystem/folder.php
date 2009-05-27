<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.filesystem.path');

/**
 * A Folder handling class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
class JFolder
{
	/**
	 * Copy a folder.
	 *
	 * @param	string	The path to the source folder.
	 * @param	string	The path to the destination folder.
	 * @param	string	An optional base path to prefix to the file names.
	 * @param	boolean	Optionally force folder/file overwrites.
	 * @return	mixed	JError object on failure or boolean True on success.
	 * @since	1.5
	 */
	function copy($src, $dest, $path = '', $force = false)
	{
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		if ($path) {
			$src = JPath::clean($path . DS . $src);
			$dest = JPath::clean($path . DS . $dest);
		}

		// Eliminate trailing directory separators, if any
		$src = rtrim($src, DS);
		$dest = rtrim($dest, DS);

		if (!JFolder::exists($src)) {
			return JError::raiseError(-1, JText::_('Cannot find source folder'));
		}
		if (JFolder::exists($dest) && !$force) {
			return JError::raiseError(-1, JText::_('Folder already exists'));
		}

		// Make sure the destination exists
		if (! JFolder::create($dest)) {
			return JError::raiseError(-1, JText::_('Unable to create target folder'));
		}

		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], null,
				$ftpOptions['user'], $ftpOptions['pass']
			);

			if (!($dh = @opendir($src))) {
				return JError::raiseError(-1, JText::_('Unable to open source folder'));
			}
			// Walk through the directory copying files and recursing into folders.
			while (($file = readdir($dh)) !== false) {
				$sfid = $src . DS . $file;
				$dfid = $dest . DS . $file;
				switch (filetype($sfid)) {
					case 'dir':
						if ($file != '.' && $file != '..') {
							$ret = JFolder::copy($sfid, $dfid, null, $force);
							if ($ret !== true) {
								return $ret;
							}
						}
						break;

					case 'file':
						// Translate path for the FTP account
						$dfid = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $dfid), '/');
						if (! $ftp->store($sfid, $dfid)) {
							return JError::raiseError(-1, JText::_('Copy failed'));
						}
						break;
				}
			}
		} else {
			if (!($dh = @opendir($src))) {
				return JError::raiseError(-1, JText::_('Unable to open source folder'));
			}
			// Walk through the directory copying files and recursing into folders.
			while (($file = readdir($dh)) !== false) {
				$sfid = $src . DS . $file;
				$dfid = $dest . DS . $file;
				switch (filetype($sfid)) {
					case 'dir':
						if ($file != '.' && $file != '..') {
							$ret = JFolder::copy($sfid, $dfid, null, $force);
							if ($ret !== true) {
								return $ret;
							}
						}
						break;

					case 'file':
						if (!@copy($sfid, $dfid)) {
							return JError::raiseError(-1, JText::_('Copy failed'));
						}
						break;
				}
			}
		}
		return true;
	}

	/**
	 * Create a folder -- and all necessary parent folders.
	 *
	 * @param string A path to create from the base path.
	 * @param int Directory permissions to set for folders created.
	 * @return boolean True if successful.
	 * @since 1.5
	 */
	function create($path = '', $mode = 0755)
	{
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');
		static $nested = 0;

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Check if parent dir exists
		$parent = dirname($path);
		if (!JFolder::exists($parent)) {
			// Prevent infinite loops!
			$nested++;
			if (($nested > 20) || ($parent == $path)) {
				JError::raiseWarning(
					'SOME_ERROR_CODE',
					'JFolder::create: ' . JText::_('Infinite loop detected')
				);
				$nested--;
				return false;
			}

			// Create the parent directory
			if (JFolder::create($parent, $mode) !== true) {
				// JFolder::create throws an error
				$nested--;
				return false;
			}

			// OK, parent directory has been created
			$nested--;
		}

		// Check if dir already exists
		if (JFolder::exists($path)) {
			return true;
		}

		// Check for safe mode
		if ($ftpOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], null,
				$ftpOptions['user'], $ftpOptions['pass']
			);

			// Translate path to FTP path
			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			$ret = $ftp->mkdir($path);
			$ftp->chmod($path, $mode);
		} else {
			// We need to get and explode the open_basedir paths
			$obd = ini_get('open_basedir');

			// If open_basedir is set we need to get the open_basedir that the path is in
			if ($obd != null)
			{
				if (JPATH_ISWIN) {
					$obdSeparator = ";";
				} else {
					$obdSeparator = ":";
				}
				// Create the array of open_basedir paths
				$obdArray = explode($obdSeparator, $obd);
				$inBaseDir = false;
				// Iterate through open_basedir paths looking for a match
				foreach ($obdArray as $test) {
					$test = JPath::clean($test);
					if (strpos($path, $test) === 0) {
						$obdpath = $test;
						$inBaseDir = true;
						break;
					}
				}
				if ($inBaseDir == false) {
					// Return false for JFolder::create because the path to be created is not in open_basedir
					JError::raiseWarning(
						'SOME_ERROR_CODE',
						'JFolder::create: ' . JText::_('Path not in open_basedir paths')
					);
					return false;
				}
			}

			// First set umask
			$origmask = @umask(0);

			// Create the path
			if (!$ret = @mkdir($path, $mode)) {
				@umask($origmask);
				JError::raiseWarning(
					'SOME_ERROR_CODE',
					'JFolder::create: ' . JText::_('Could not create directory'),
					'Path: ' . $path
				);
				return false;
			}

			// Reset umask
			@umask($origmask);
		}
		return $ret;
	}

	/**
	 * Delete a folder.
	 *
	 * @param string The path to the folder to delete.
	 * @return boolean True on success.
	 * @since 1.5
	 */
	function delete($path)
	{
		// Sanity check
		if (!$path) {
			// Bad programmer! Bad Bad programmer!
			JError::raiseWarning(500, 'JFolder::delete: ' . JText::_('Attempt to delete base directory'));
			return false;
		}

		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is this really a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::delete: ' . JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// Remove all the files in folder if they exist
		$files = JFolder::files($path, '.', false, true, array());
		if (!empty($files)) {
			jimport('joomla.filesystem.file');
			if (JFile::delete($files) !== true) {
				// JFile::delete throws an error
				return false;
			}
		}

		// Remove sub-folders of folder
		$folders = JFolder::folders($path, '.', false, true, array());
		foreach ($folders as $folder) {
			if (is_link($folder)) {
				// Don't descend into linked directories, just delete the link.
				jimport('joomla.filesystem.file');
				if (JFile::delete($folder) !== true) {
					// JFile::delete throws an error
					return false;
				}
			} elseif (JFolder::delete($folder) !== true) {
				// JFolder::delete throws an error
				return false;
			}
		}

		if ($ftpOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], null,
				$ftpOptions['user'], $ftpOptions['pass']
			);
		}

		// In case of restricted permissions we zap it one way or the other
		// as long as the owner is either the webserver or the ftp
		if (@rmdir($path)) {
			$ret = true;
		} elseif ($ftpOptions['enabled'] == 1) {
			// Translate path and delete
			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			// FTP connector throws an error
			$ret = $ftp->delete($path);
		} else {
			JError::raiseWarning(
				'SOME_ERROR_CODE',
				'JFolder::delete: ' . JText::_('Could not delete folder'),
				'Path: ' . $path
			);
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Moves a folder.
	 *
	 * @param string The path to the source folder.
	 * @param string The path to the destination folder.
	 * @param string An optional base path to prefix to the file names.
	 * @return mixed Error message on false or boolean true on success.
	 * @since 1.5
	 */
	function move($src, $dest, $path = '')
	{
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		if ($path) {
			$src = JPath::clean($path . DS . $src);
			$dest = JPath::clean($path . DS . $dest);
		}

		if (!JFolder::exists($src) && !is_writable($src)) {
			return JText::_('Cannot find source folder');
		}
		if (JFolder::exists($dest)) {
			return JText::_('Folder already exists');
		}

		if ($ftpOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], null,
				$ftpOptions['user'], $ftpOptions['pass']
			);

			//Translate path for the FTP account
			$src = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $src), '/');
			$dest = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $dest), '/');

			// Use FTP rename to simulate move
			if (!$ftp->rename($src, $dest)) {
				return JText::_('Rename failed');
			}
			$ret = true;
		} else {
			if (!@rename($src, $dest)) {
				return JText::_('Rename failed');
			}
			$ret = true;
		}
		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param string Folder name relative to installation dir
	 * @return boolean True if path is a folder
	 * @since 1.5
	 */
	function exists($path)
	{
		return is_dir(JPath::clean($path));
	}

	/**
	 * Utility function to read the files in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for file names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the file.
	 * @param	array	Array with names of files which should not be shown in
	 * the result.
	 * @return	array	Files in the given folder.
	 * @since 1.5
	 */
	function files($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::files: ' . JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					if ($recurse) {
						if (is_integer($recurse)) {
							$arr2 = JFolder::files($dir, $filter, $recurse - 1, $fullpath);
						} else {
							$arr2 = JFolder::files($dir, $filter, $recurse, $fullpath);
						}

						$arr = array_merge($arr, $arr2);
					}
				} else {
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $path . DS . $file;
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
	 * Utility function to read the folders in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for folder names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the folders.
	 * @param	array	Array with names of folders which should not be shown in
	 * the result.
	 * @return	array	Folders in the given folder.
	 * @since 1.5
	 */
	function folders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::folder: ' . JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					// Removes filtered directories
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $dir;
						} else {
							$arr[] = $file;
						}
					}
					if ($recurse) {
						if (is_integer($recurse)) {
							$arr2 = JFolder::folders($dir, $filter, $recurse - 1, $fullpath);
						} else {
							$arr2 = JFolder::folders($dir, $filter, $recurse, $fullpath);
						}

						$arr = array_merge($arr, $arr2);
					}
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}

	/**
	 * Lists folder in format suitable for tree display.
	 *
	 * @access	public
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for folder names.
	 * @param	integer	The maximum number of levels to recursively read,
	 * defaults to three.
	 * @param	integer	The current level, optional.
	 * @param	integer	Unique identifier of the parent folder, if any.
	 * @return	array	Folders in the given folder.
	 * @since	1.5
	 */
	function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
	{
		$dirs = array ();
		if ($level == 0) {
			$GLOBALS['_JFolder_folder_tree_index'] = 0;
		}
		if ($level < $maxLevel) {
			$folders = JFolder::folders($path, $filter);
			// first path, index foldernames
			foreach ($folders as $name) {
				$id = ++$GLOBALS['_JFolder_folder_tree_index'];
				$fullName = JPath::clean($path . DS . $name);
				$dirs[] = array(
					'id' => $id,
					'parent' => $parent,
					'name' => $name,
					'fullname' => $fullName,
					'relname' => str_replace(JPATH_ROOT, '', $fullName)
				);
				$dirs2 = JFolder::listFolderTree($fullName, $filter, $maxLevel, $level + 1, $id);
				$dirs = array_merge($dirs, $dirs2);
			}
		}
		return $dirs;
	}

	/**
	 * Makes path name safe to use.
	 *
	 * @access	public
	 * @param	string The full path to sanitise.
	 * @return	string The sanitised string.
	 * @since	1.5
	 */
	function makeSafe($path)
	{
		$ds = (DS == '\\') ? '\\' . DS : DS;
		$regex = array('#[^A-Za-z0-9:\_\-' . $ds . ' ]#');
		return preg_replace($regex, '', $path);
	}

}