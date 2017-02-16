<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Filesystem
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Wrapper class for JFilesystemFolder
 *
 * @package     Joomla.Platform
 * @subpackage  Filesystem
 * @since       3.4
 */
class JFilesystemWrapperFolder
{
	/**
	 * Helper wrapper method for copy
	 *
	 * @param   string   $src          The path to the source folder.
	 * @param   string   $dest         The path to the destination folder.
	 * @param   string   $path         An optional base path to prefix to the file names.
	 * @param   boolean  $force        Force copy.
	 * @param   boolean  $use_streams  Optionally force folder/file overwrites.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFolder::copy()
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function copy($src, $dest, $path = '', $force = false, $use_streams = false)
	{
		return JFolder::copy($src, $dest, $path, $force, $use_streams);
	}

	/**
	 * Helper wrapper method for create
	 *
	 * @param   string   $path  A path to create from the base path.
	 * @param   integer  $mode  Directory permissions to set for folders created. 0755 by default.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @see     JFolder::create()
	 * @since   3.4
	 */
	public function create($path = '', $mode = 493)
	{
		return JFolder::create($path, $mode);
	}

	/**
	 * Helper wrapper method for delete
	 *
	 * @param   string  $path  The path to the folder to delete.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFolder::delete()
	 * @since   3.4
	 * @throws  UnexpectedValueException
	 */
	public function delete($path)
	{
		return JFolder::delete($path);
	}

	/**
	 * Helper wrapper method for move
	 *
	 * @param   string   $src          The path to the source folder.
	 * @param   string   $dest         The path to the destination folder.
	 * @param   string   $path         An optional base path to prefix to the file names.
	 * @param   boolean  $use_streams  Optionally use streams.
	 *
	 * @return  mixed  Error message on false or boolean true on success.
	 *
	 * @see     JFolder::move()
	 * @since   3.4
	 */
	public function move($src, $dest, $path = '', $use_streams = false)
	{
		return JFolder::move($src, $dest, $path, $use_streams);
	}

	/**
	 * Helper wrapper method for exists
	 *
	 * @param   string  $path  Folder name relative to installation dir.
	 *
	 * @return  boolean  True if path is a folder.
	 *
	 * @see     JFolder::exists()
	 * @since   3.4
	 */
	public function exists($path)
	{
		return JFolder::exists($path);
	}

	/**
	 * Helper wrapper method for files
	 *
	 * @param   string   $path           The path of the folder to read.
	 * @param   string   $filter         A filter for file names.
	 * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full           True to return the full path to the file.
	 * @param   array    $exclude        Array with names of files which should not be shown in the result.
	 * @param   array    $excludefilter  Array of filter to exclude.
	 * @param   boolean  $naturalSort    False for asort, true for natsort.
	 *
	 * @return  array  Files in the given folder.
	 *
	 * @see     JFolder::files()
	 * @since   3.4
	 */
	public function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*', '.*~'), $naturalSort = false)
	{
		return JFolder::files($path, $filter, $recurse, $full, $exclude, $excludefilter, $naturalSort);
	}

	/**
	 * Helper wrapper method for folders
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
	 * @see     JFolder::folders()
	 * @since   3.4
	 */
	public function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*'))
	{
		return JFolder::folders($path, $filter, $recurse, $full, $exclude, $excludefilter);
	}

	/**
	 * Helper wrapper method for listFolderTree
	 *
	 * @param   string   $path      The path of the folder to read.
	 * @param   string   $filter    A filter for folder names.
	 * @param   integer  $maxLevel  The maximum number of levels to recursively read, defaults to three.
	 * @param   integer  $level     The current level, optional.
	 * @param   integer  $parent    Unique identifier of the parent folder, if any.
	 *
	 * @return  array  Folders in the given folder.
	 *
	 * @see     JFolder::listFolderTree()
	 * @since   3.4
	 */
	public function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
	{
		return JFolder::listFolderTree($path, $filter, $maxLevel, $level, $parent);
	}

	/**
	 * Helper wrapper method for makeSafe
	 *
	 * @param   string  $path  The full path to sanitise.
	 *
	 * @return  string  The sanitised string
	 *
	 * @see     JFolder::makeSafe()
	 * @since   3.4
	 */
	public function makeSafe($path)
	{
		return JFolder::makeSafe($path);
	}
}
