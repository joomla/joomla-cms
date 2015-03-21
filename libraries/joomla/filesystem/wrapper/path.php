<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Filesystem
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Wrapper class for JPath
 *
 * @package     Joomla.Platform
 * @subpackage  Filesystem
 * @since       3.4
 */
class JFilesystemWrapperPath
{
	/**
	 * Helper wrapper method for canChmod
	 *
	 * @param   string  $path  Path to check.
	 *
	 * @return  boolean  True if path can have mode changed.
	 *
	 * @see     JPath::canChmod()
	 * @since   3.4
	 */
	public function canChmod($path)
	{
		return JPath::canChmod($path);
	}

	/**
	 * Helper wrapper method for setPermissions
	 *
	 * @param   string  $path        Root path to begin changing mode [without trailing slash].
	 * @param   string  $filemode    Octal representation of the value to change file mode to [null = no change].
	 * @param   string  $foldermode  Octal representation of the value to change folder mode to [null = no change].
	 *
	 * @return  boolean  True if successful [one fail means the whole operation failed].
	 *
	 * @see     JPath::setPermissions()
	 * @since   3.4
	 */
	public function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		return JPath::setPermissions($path, $filemode, $foldermode);
	}

	/**
	 * Helper wrapper method for getPermissions
	 *
	 * @param   string  $path  The path of a file/folder.
	 *
	 * @return  string  Filesystem permissions.
	 *
	 * @see     JPath::getPermissions()
	 * @since   3.4
	 */
	public function getPermissions($path)
	{
		return JPath::getPermissions($path);
	}

	/**
	 * Helper wrapper method for check
	 *
	 * @param   string  $path  A file system path to check.
	 *
	 * @return  string  A cleaned version of the path or exit on error.
	 *
	 * @see     JPath::check()
	 * @since   3.4
	 * @throws  Exception
	 */
	public function check($path)
	{
		return JPath::check($path);
	}

	/**
	 * Helper wrapper method for clean
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 *
	 * @see     JPath::clean()
	 * @since   3.4
	 * @throws  UnexpectedValueException
	 */
	public function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		return JPath::clean($path, $ds);
	}

	/**
	 * Helper wrapper method for isOwner
	 *
	 * @param   string  $path  Path to check ownership.
	 *
	 * @return  boolean  True if the php script owns the path passed.
	 *
	 * @see     JPath::isOwner()
	 * @since   3.4
	 */
	public function isOwner($path)
	{
		return JPath::isOwner($path);
	}

	/**
	 * Helper wrapper method for find
	 *
	 * @param   mixed   $paths  An path string or array of path strings to search in
	 * @param   string  $file   The file name to look for.
	 *
	 * @return mixed   The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 *
	 * @see     JPath::find()
	 * @since   3.4
	 */
	public function find($paths, $file)
	{
		return JPath::find($paths, $file);
	}
}
