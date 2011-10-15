<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.filesystem.path');

/**
 * A Folder handling class
 *
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 * @since       11.1
 */
abstract class JFolder
{
	/**
	 * Copy a folder.
	 *
	 * @param   string   $src          The path to the source folder.
	 * @param   string   $dest         The path to the destination folder.
	 * @param   string   $path         An optional base path to prefix to the file names.
	 * @param   string   $force        Force copy.
	 * @param   boolean  $use_streams  Optionally force folder/file overwrites.
	 *
	 * @return  mixed  JError object on failure or boolean True on success.
	 *
	 * @since   11.1
	 */
	public static function copy($src, $dest, $path = '', $force = false, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// Initialise variables.
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');

		if ($path)
		{
			$src = JPath::clean($path . '/' . $src);
			$dest = JPath::clean($path . '/' . $dest);
		}

		// Eliminate trailing directory separators, if any
		$src = rtrim($src, DS);
		$dest = rtrim($dest, DS);

		if (!self::exists($src))
		{
			return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_FIND_SOURCE_FOLDER'));
		}
		if (self::exists($dest) && !$force)
		{
			return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_EXISTS'));
		}

		// Make sure the destination exists
		if (!self::create($dest))
		{
			return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'));
		}

		// If we're using ftp and don't have streams enabled
		if ($FTPOptions['enabled'] == 1 && !$use_streams)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

			if (!($dh = @opendir($src)))
			{
				return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_OPEN'));
			}
			// Walk through the directory copying files and recursing into folders.
			while (($file = readdir($dh)) !== false)
			{
				$sfid = $src . '/' . $file;
				$dfid = $dest . '/' . $file;
				switch (filetype($sfid))
				{
					case 'dir':
						if ($file != '.' && $file != '..')
						{
							$ret = self::copy($sfid, $dfid, null, $force);
							if ($ret !== true)
							{
								return $ret;
							}
						}
						break;

					case 'file':
					// Translate path for the FTP account
						$dfid = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dfid), '/');
						if (!$ftp->store($sfid, $dfid))
						{
							return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED'));
						}
						break;
				}
			}
		}
		else
		{
			if (!($dh = @opendir($src)))
			{
				return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_OPEN'));
			}
			// Walk through the directory copying files and recursing into folders.
			while (($file = readdir($dh)) !== false)
			{
				$sfid = $src . '/' . $file;
				$dfid = $dest . '/' . $file;
				switch (filetype($sfid))
				{
					case 'dir':
						if ($file != '.' && $file != '..')
						{
							$ret = self::copy($sfid, $dfid, null, $force, $use_streams);
							if ($ret !== true)
							{
								return $ret;
							}
						}
						break;

					case 'file':
						if ($use_streams)
						{
							$stream = JFactory::getStream();
							if (!$stream->copy($sfid, $dfid))
							{
								return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED') . ': ' . $stream->getError());
							}
						}
						else
						{
							if (!@copy($sfid, $dfid))
							{
								return JError::raiseError(-1, JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED'));
							}
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
	 * @param   string   $path  A path to create from the base path.
	 * @param   integer  $mode  Directory permissions to set for folders created. 0755 by default.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @since   11.1
	 */
	public static function create($path = '', $mode = 0755)
	{
		// Initialise variables.
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');
		static $nested = 0;

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Check if parent dir exists
		$parent = dirname($path);
		if (!self::exists($parent))
		{
			// Prevent infinite loops!
			$nested++;
			if (($nested > 20) || ($parent == $path))
			{
				JError::raiseWarning('SOME_ERROR_CODE', __METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_LOOP'));
				$nested--;
				return false;
			}

			// Create the parent directory
			if (self::create($parent, $mode) !== true)
			{
				// JFolder::create throws an error
				$nested--;
				return false;
			}

			// OK, parent directory has been created
			$nested--;
		}

		// Check if dir already exists
		if (self::exists($path))
		{
			return true;
		}

		// Check for safe mode
		if ($FTPOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

			// Translate path to FTP path
			$path = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $path), '/');
			$ret = $ftp->mkdir($path);
			$ftp->chmod($path, $mode);
		}
		else
		{
			// We need to get and explode the open_basedir paths
			$obd = ini_get('open_basedir');

			// If open_basedir is set we need to get the open_basedir that the path is in
			if ($obd != null)
			{
				if (JPATH_ISWIN)
				{
					$obdSeparator = ";";
				}
				else
				{
					$obdSeparator = ":";
				}
				// Create the array of open_basedir paths
				$obdArray = explode($obdSeparator, $obd);
				$inBaseDir = false;
				// Iterate through open_basedir paths looking for a match
				foreach ($obdArray as $test)
				{
					$test = JPath::clean($test);
					if (strpos($path, $test) === 0)
					{
						$obdpath = $test;
						$inBaseDir = true;
						break;
					}
				}
				if ($inBaseDir == false)
				{
					// Return false for JFolder::create because the path to be created is not in open_basedir
					JError::raiseWarning('SOME_ERROR_CODE', __METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_PATH'));
					return false;
				}
			}

			// First set umask
			$origmask = @umask(0);

			// Create the path
			if (!$ret = @mkdir($path, $mode))
			{
				@umask($origmask);
				JError::raiseWarning(
					'SOME_ERROR_CODE', __METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_COULD_NOT_CREATE_DIRECTORY'),
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
	 * @param   string  $path  The path to the folder to delete.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public static function delete($path)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// Sanity check
		if (!$path)
		{
			// Bad programmer! Bad Bad programmer!
			JError::raiseWarning(500, __METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_DELETE_BASE_DIRECTORY'));
			return false;
		}

		// Initialise variables.
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is this really a folder?
		if (!is_dir($path))
		{
			JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', $path));
			return false;
		}

		// Remove all the files in folder if they exist; disable all filtering
		$files = self::files($path, '.', false, true, array(), array());
		if (!empty($files))
		{
			jimport('joomla.filesystem.file');
			if (JFile::delete($files) !== true)
			{
				// JFile::delete throws an error
				return false;
			}
		}

		// Remove sub-folders of folder; disable all filtering
		$folders = self::folders($path, '.', false, true, array(), array());
		foreach ($folders as $folder)
		{
			if (is_link($folder))
			{
				// Don't descend into linked directories, just delete the link.
				jimport('joomla.filesystem.file');
				if (JFile::delete($folder) !== true)
				{
					// JFile::delete throws an error
					return false;
				}
			}
			elseif (self::delete($folder) !== true)
			{
				// JFolder::delete throws an error
				return false;
			}
		}

		if ($FTPOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
		}

		// In case of restricted permissions we zap it one way or the other
		// as long as the owner is either the webserver or the ftp.
		if (@rmdir($path))
		{
			$ret = true;
		}
		elseif ($FTPOptions['enabled'] == 1)
		{
			// Translate path and delete
			$path = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $path), '/');
			// FTP connector throws an error
			$ret = $ftp->delete($path);
		}
		else
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('JLIB_FILESYSTEM_ERROR_FOLDER_DELETE', $path));
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Moves a folder.
	 *
	 * @param   string   $src          The path to the source folder.
	 * @param   string   $dest         The path to the destination folder.
	 * @param   string   $path         An optional base path to prefix to the file names.
	 * @param   boolean  $use_streams  Optionally use streams.
	 *
	 * @return  mixed  Error message on false or boolean true on success.
	 *
	 * @since   11.1
	 */
	public static function move($src, $dest, $path = '', $use_streams = false)
	{
		// Initialise variables.
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');

		if ($path)
		{
			$src = JPath::clean($path . '/' . $src);
			$dest = JPath::clean($path . '/' . $dest);
		}

		if (!self::exists($src))
		{
			return JText::_('JLIB_FILESYSTEM_ERROR_FIND_SOURCE_FOLDER');
		}
		if (self::exists($dest))
		{
			return JText::_('JLIB_FILESYSTEM_ERROR_FOLDER_EXISTS');
		}
		if ($use_streams)
		{
			$stream = JFactory::getStream();
			if (!$stream->move($src, $dest))
			{
				return JText::sprintf('JLIB_FILESYSTEM_ERROR_FOLDER_RENAME', $stream->getError());
			}
			$ret = true;
		}
		else
		{
			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

				//Translate path for the FTP account
				$src = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $src), '/');
				$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Use FTP rename to simulate move
				if (!$ftp->rename($src, $dest))
				{
					return JText::_('Rename failed');
				}
				$ret = true;
			}
			else
			{
				if (!@rename($src, $dest))
				{
					return JText::_('Rename failed');
				}
				$ret = true;
			}
		}
		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param   string  $path  Folder name relative to installation dir
	 *
	 * @return  boolean  True if path is a folder
	 *
	 * @since   11.1
	 */
	public static function exists($path)
	{
		return is_dir(JPath::clean($path));
	}

	/**
	 * Utility function to read the files in a folder.
	 *
	 * @param   string   $path           The path of the folder to read.
	 * @param   string   $filter         A filter for file names.
	 * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full           True to return the full path to the file.
	 * @param   array    $exclude        Array with names of files which should not be shown in the result.
	 * @param   array    $excludefilter  Array of filter to exclude
	 *
	 * @return  array  Files in the given folder.
	 *
	 * @since   11.1
	 */
	public static function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*', '.*~'))
	{
		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		{
			JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER_FILES', $path));
			return false;
		}

		// Compute the excludefilter string
		if (count($excludefilter))
		{
			$excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
		}
		else
		{
			$excludefilter_string = '';
		}

		// Get the files
		$arr = self::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

		// Sort the files
		asort($arr);
		return array_values($arr);
	}

	/**
	 * Utility function to read the folders in a folder.
	 *
	 * @param   string   $path           The path of the folder to read.
	 * @param   string   $filter         A filter for folder names.
	 * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full           True to return the full path to the folders.
	 * @param   array    $exclude        Array with names of folders which should not be shown in the result.
	 * @param   array    $excludefilter  Array with regular expressions matching folders which should not be shown in the result.
	 *
	 * @return  array  Folders in the given folder.
	 *
	 * @since   11.1
	 */
	public static function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*'))
	{
		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		{
			JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER_FOLDER', $path));
			return false;
		}

		// Compute the excludefilter string
		if (count($excludefilter))
		{
			$excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
		}
		else
		{
			$excludefilter_string = '';
		}

		// Get the folders
		$arr = self::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);

		// Sort the folders
		asort($arr);
		return array_values($arr);
	}

	/**
	 * Function to read the files/folders in a folder.
	 *
	 * @param   string   $path                  The path of the folder to read.
	 * @param   string   $filter                A filter for file names.
	 * @param   mixed    $recurse               True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full                  True to return the full path to the file.
	 * @param   array    $exclude               Array with names of files which should not be shown in the result.
	 * @param   string   $excludefilter_string  Regexp of files to exclude
	 * @param   boolean  $findfiles             True to read the files, false to read the folders
	 *
	 * @return  array  Files.
	 *
	 * @since   11.1
	 */
	protected static function _items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// Initialise variables.
		$arr = array();

		// Read the source directory
		if (!($handle = @opendir($path)))
		{
			return $arr;
		}

		while (($file = readdir($handle)) !== false)
		{
			if ($file != '.' && $file != '..' && !in_array($file, $exclude)
				&& (empty($excludefilter_string) || !preg_match($excludefilter_string, $file))
			)
			{
				// Compute the fullpath
				$fullpath = $path . '/' . $file;

				// Compute the isDir flag
				$isDir = is_dir($fullpath);

				if (($isDir xor $findfiles) && preg_match("/$filter/", $file))
				{
					// (fullpath is dir and folders are searched or fullpath is not dir and files are searched) and file matches the filter
					if ($full)
					{
						// Full path is requested
						$arr[] = $fullpath;
					}
					else
					{
						// Filename is requested
						$arr[] = $file;
					}
				}
				if ($isDir && $recurse)
				{
					// Search recursively
					if (is_integer($recurse))
					{
						// Until depth 0 is reached
						$arr = array_merge($arr, self::_items($fullpath, $filter, $recurse - 1, $full, $exclude, $excludefilter_string, $findfiles));
					}
					else
					{
						$arr = array_merge($arr, self::_items($fullpath, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles));
					}
				}
			}
		}
		closedir($handle);
		return $arr;
	}

	/**
	 * Lists folder in format suitable for tree display.
	 *
	 * @param   string   $path      The path of the folder to read.
	 * @param   string   $filter    A filter for folder names.
	 * @param   integer  $maxLevel  The maximum number of levels to recursively read, defaults to three.
	 * @param   integer  $level     The current level, optional.
	 * @param   integer  $parent    Unique identifier of the parent folder, if any.
	 *
	 * @return  array  Folders in the given folder.
	 *
	 * @since   11.1
	 */
	public static function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
	{
		$dirs = array();
		if ($level == 0)
		{
			$GLOBALS['_JFolder_folder_tree_index'] = 0;
		}
		if ($level < $maxLevel)
		{
			$folders = self::folders($path, $filter);
			// First path, index foldernames
			foreach ($folders as $name)
			{
				$id = ++$GLOBALS['_JFolder_folder_tree_index'];
				$fullName = JPath::clean($path . '/' . $name);
				$dirs[] = array('id' => $id, 'parent' => $parent, 'name' => $name, 'fullname' => $fullName,
					'relname' => str_replace(JPATH_ROOT, '', $fullName));
				$dirs2 = self::listFolderTree($fullName, $filter, $maxLevel, $level + 1, $id);
				$dirs = array_merge($dirs, $dirs2);
			}
		}
		return $dirs;
	}

	/**
	 * Makes path name safe to use.
	 *
	 * @param   string  $path  The full path to sanitise.
	 *
	 * @return  string  The sanitised string.
	 *
	 * @since   11.1
	 */
	public static function makeSafe($path)
	{
		//$ds = (DS == '\\') ? '\\/' : DS;
		$regex = array('#[^A-Za-z0-9:_\\\/-]#');
		return preg_replace($regex, '', $path);
	}
}
