<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem\Wrapper;

use Joomla\CMS\Filesystem\Path;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for Path
 *
 * @since       3.4
 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
 */
class PathWrapper
{
	/**
	 * Helper wrapper method for canChmod
	 *
	 * @param   string  $path  Path to check.
	 *
	 * @return  boolean  True if path can have mode changed.
	 *
	 * @see         Path::canChmod()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function canChmod($path)
	{
		return Path::canChmod($path);
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
	 * @see         Path::setPermissions()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		return Path::setPermissions($path, $filemode, $foldermode);
	}

	/**
	 * Helper wrapper method for getPermissions
	 *
	 * @param   string  $path  The path of a file/folder.
	 *
	 * @return  string  Filesystem permissions.
	 *
	 * @see         Path::getPermissions()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function getPermissions($path)
	{
		return Path::getPermissions($path);
	}

	/**
	 * Helper wrapper method for check
	 *
	 * @param   string  $path  A file system path to check.
	 *
	 * @return  string  A cleaned version of the path or exit on error.
	 *
	 * @see         Path::check()
	 * @since       3.4
	 * @throws      Exception
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function check($path)
	{
		return Path::check($path);
	}

	/**
	 * Helper wrapper method for clean
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 *
	 * @see         Path::clean()
	 * @since       3.4
	 * @throws      UnexpectedValueException
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		return Path::clean($path, $ds);
	}

	/**
	 * Helper wrapper method for isOwner
	 *
	 * @param   string  $path  Path to check ownership.
	 *
	 * @return  boolean  True if the php script owns the path passed.
	 *
	 * @see         Path::isOwner()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function isOwner($path)
	{
		return Path::isOwner($path);
	}

	/**
	 * Helper wrapper method for find
	 *
	 * @param   mixed   $paths  A path string or array of path strings to search in
	 * @param   string  $file   The file name to look for.
	 *
	 * @return mixed   The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 *
	 * @see         Path::find()
	 * @since       3.4
	 * @deprecated  4.0 Use \Joomla\CMS\Filesystem\Path instead
	 */
	public function find($paths, $file)
	{
		return Path::find($paths, $file);
	}
}
