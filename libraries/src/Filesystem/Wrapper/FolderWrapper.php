<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem\Wrapper;

use Joomla\CMS\Filesystem\Folder;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for Folder
 *
 * @since       3.4
 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
 */
class FolderWrapper
{
	/**
	 * Helper wrapper method for copy
	 *
	 * @param   string   $src         The path to the source folder.
	 * @param   string   $dest        The path to the destination folder.
	 * @param   string   $path        An optional base path to prefix to the file names.
	 * @param   boolean  $force       Force copy.
	 * @param   boolean  $useStreams  Optionally force folder/file overwrites.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see         Folder::copy()
	 * @since       3.4
	 * @throws      RuntimeException
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function copy($src, $dest, $path = '', $force = false, $useStreams = false)
	{
		return Folder::copy($src, $dest, $path, $force, $useStreams);
	}

	/**
	 * Helper wrapper method for create
	 *
	 * @param   string   $path  A path to create from the base path.
	 * @param   integer  $mode  Directory permissions to set for folders created. 0755 by default.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @see         Folder::create()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function create($path = '', $mode = 493)
	{
		return Folder::create($path, $mode);
	}

	/**
	 * Helper wrapper method for delete
	 *
	 * @param   string  $path  The path to the folder to delete.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see         Folder::delete()
	 * @since       3.4
	 * @throws      UnexpectedValueException
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function delete($path)
	{
		return Folder::delete($path);
	}

	/**
	 * Helper wrapper method for move
	 *
	 * @param   string   $src         The path to the source folder.
	 * @param   string   $dest        The path to the destination folder.
	 * @param   string   $path        An optional base path to prefix to the file names.
	 * @param   boolean  $useStreams  Optionally use streams.
	 *
	 * @return  mixed  Error message on false or boolean true on success.
	 *
	 * @see         Folder::move()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function move($src, $dest, $path = '', $useStreams = false)
	{
		return Folder::move($src, $dest, $path, $useStreams);
	}

	/**
	 * Helper wrapper method for exists
	 *
	 * @param   string  $path  Folder name relative to installation dir.
	 *
	 * @return  boolean  True if path is a folder.
	 *
	 * @see         Folder::exists()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function exists($path)
	{
		return Folder::exists($path);
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
	 * @see         Folder::files()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*', '.*~'), $naturalSort = false)
	{
		return Folder::files($path, $filter, $recurse, $full, $exclude, $excludefilter, $naturalSort);
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
	 * @see         Folder::folders()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*'))
	{
		return Folder::folders($path, $filter, $recurse, $full, $exclude, $excludefilter);
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
	 * @see         Folder::listFolderTree()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
	{
		return Folder::listFolderTree($path, $filter, $maxLevel, $level, $parent);
	}

	/**
	 * Helper wrapper method for makeSafe
	 *
	 * @param   string  $path  The full path to sanitise.
	 *
	 * @return  string  The sanitised string
	 *
	 * @see         Folder::makeSafe()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Folder instead
	 */
	public function makeSafe($path)
	{
		return Folder::makeSafe($path);
	}
}
