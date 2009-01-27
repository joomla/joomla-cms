<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.path');
jimport('joomla.filesystem.filesystem');

/**
 * A Folder handling class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
abstract class JFolder
{
	private static $filesystem = null;
	private static $treeLevel = 0;

	protected static function &getFileSystem() {
		if (!is_object(JFolder::$filesystem)) {
			JFolder::$filesystem = JFileSystem::getInstance();
		}
		return JFolder::$filesystem;
	}

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
	public static function copy($src, $dest, $path = '', $force = false)
	{
		$backend = JFolder::getFileSystem();

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
		if (!JFolder::create($dest)) {
			return JError::raiseError(-1, JText::_('Unable to create target folder'));
		}

		if (! ($dh = @opendir($src))) {
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
					if (!JFile::copy($sfid, $dfid)) {
						return JError::raiseError(-1, JText::_('Copy failed'));
					}
					break;
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
	public static function create($path = '', $mode = 0755)
	{
		$backend = JFolder::getFileSystem();
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

		$ret = $backend->mkdir($path);

		if (!$ret) {
			JError::raiseWarning('SOME_ERROR_CODE', 'JFolder::create: '.JText::_('Could not create directory'), 'Path: '.$path);
			return false;
		}
		$backend->chmod($path, $mode);
		return $ret;
	}

	/**
	 * Delete a folder.
	 *
	 * @param string The path to the folder to delete.
	 * @return boolean True on success.
	 * @since 1.5
	 */
	public static function delete($path)
	{
		// Sanity check
		if (!$path) {
			// Bad programmer! Bad Bad programmer!
			JError::raiseWarning(500, 'JFolder::delete: ' . JText::_('Attempt to delete base directory'));
			return false;
		}

		$backend = JFolder::getFileSystem();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is this really a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::delete: ' . JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// Remove all the files in folder if they exist; disable all filtering
		$files = JFolder::files($path, '.', false, true, array(), array());
		if (!empty($files)) {
			jimport('joomla.filesystem.file');
			if (JFile::delete($files) !== true) {
				// JFile::delete throws an error
				return false;
			}
		}

		// Remove sub-folders of folder; disable all filtering
		$folders = JFolder::folders($path, '.', false, true, array(), array());
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
		$ret = true;
		if (!$backend->rmdir($path)) {
			JError::raiseWarning('SOME_ERROR_CODE', 'JFolder::delete: '.JText::_('Could not delete folder').' '.$path);
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
	public static function move($src, $dest, $path = '')
	{
		$backend = JFolder::getFileSystem();

		if ($path) {
			$src = JPath::clean($path.DS.$src);
			$dest = JPath::clean($path.DS.$dest);
		}

		if (!JFolder::exists($src) && !$backend->isWritable($src)) {
			return JText::_('Cannot find source folder');
		}
		if (JFolder::exists($dest)) {
			return JText::_('Folder already exists');
		}

		if (!$backend->rename($src, $dest)) {
			return JText::_('Rename failed');
		}
		$ret = true;
		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function.
	 *
	 * @param string Folder name relative to installation dir.
	 * @return boolean True if path is a folder.
	 * @since 1.5
	 */
	public static function exists($path)
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
	public static function files($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX'), $excludefilter = array('^\..*','.*~'))
	{
		// Initialize variables
		$arr = array ();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::files: ' . JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		if (count($excludefilter)) {
			$excludefilter = '/('. implode('|', $excludefilter) .')/';
		} else {
			$excludefilter = '';
		}
		while (($file = readdir($handle)) !== false)
		{
			$dir = $path.DS.$file;
			$isDir = is_dir($dir);
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude)) && (!$excludefilter || !preg_match($excludefilter, $file))) {
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
							$arr[] = $path.DS.$file;
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
	 * @param	array	Array with regular expressions matching folders which
	 * should not be shown in the result.
	 * @return	array	Folders in the given folder.
	 * @since 1.5
	 */
	public static function folders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX'), $excludefilter = array('^\..*'))
	{
		// Initialize variables
		$arr = array ();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::folder: ' . JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		if (count($excludefilter)) {
			$excludefilter_string = '/('. implode('|', $excludefilter) .')/';
		} else {
			$excludefilter_string = '';
		}
		while (($file = readdir($handle)) !== false)
		{
			$dir = $path.DS.$file;
			$isDir = is_dir($dir);
			if (($file != '.') && ($file != '..') && $isDir && (!in_array($file, $exclude)) && (empty($excludefilter_string) || !preg_match($excludefilter_string, $file))) {
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
						$arr2 = JFolder::folders($dir, $filter, $recurse - 1, $fullpath, $exclude, $excludefilter);
					} else {
						$arr2 = JFolder::folders($dir, $filter, $recurse, $fullpath, $exclude, $excludefilter);
					}

					$arr = array_merge($arr, $arr2);
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
	public static function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
	{
		$dirs = array ();
		if ($level == 0) {
			JFolder::$treeLevel = 0;
		}
		if ($level < $maxLevel) {
			$folders = JFolder::folders($path, $filter);
			// first path, index foldernames
			foreach ($folders as $name) {
				$id = ++ JFolder::$treeLevel;
				$fullName = JPath::clean($path.DS.$name);
				$dirs[] = array(
					'id' => $id,
					'parent' => $parent,
					'name' => $name,
					'fullname' => $fullName,
					'relname' => str_replace(JPATH_ROOT, '', $fullName)
				);
				$dirs2 = JFolder::listFolderTree($fullName, $filter, $maxLevel, $level +1, $id);
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
	public static function makeSafe($path)
	{
		$ds		= (DS == '\\') ? '\\'.DS : DS;
		$regex = array('#[^A-Za-z0-9:\_\-'.$ds.' ]#');
		return preg_replace($regex, '', $path);
	}
}
