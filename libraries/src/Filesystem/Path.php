<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Wrapper\PathWrapper;
use Joomla\CMS\Filesystem\Wrapper\FileWrapper;
use Joomla\CMS\Crypt\Crypt;

if (!defined('JPATH_ROOT'))
{
	// Define a string constant for the root directory of the file system in native format
	$pathHelper = new PathWrapper;
	define('JPATH_ROOT', $pathHelper->clean(JPATH_SITE));
}

/**
 * A Path handling class
 *
 * @since  1.7.0
 */
class Path
{
	/**
	 * Checks if a path's permissions can be changed.
	 *
	 * @param   string  $path  Path to check.
	 *
	 * @return  boolean  True if path can have mode changed.
	 *
	 * @since   1.7.0
	 */
	public static function canChmod($path)
	{
		$perms = fileperms($path);

		if ($perms !== false)
		{
			if (@chmod($path, $perms ^ 0001))
			{
				@chmod($path, $perms);

				return true;
			}
		}

		return false;
	}

	/**
	 * Chmods files and directories recursively to given permissions.
	 *
	 * @param   string  $path        Root path to begin changing mode [without trailing slash].
	 * @param   string  $filemode    Octal representation of the value to change file mode to [null = no change].
	 * @param   string  $foldermode  Octal representation of the value to change folder mode to [null = no change].
	 *
	 * @return  boolean  True if successful [one fail means the whole operation failed].
	 *
	 * @since   1.7.0
	 */
	public static function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		// Initialise return value
		$ret = true;

		if (is_dir($path))
		{
			$dh = opendir($path);

			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..')
				{
					$fullpath = $path . '/' . $file;

					if (is_dir($fullpath))
					{
						if (!self::setPermissions($fullpath, $filemode, $foldermode))
						{
							$ret = false;
						}
					}
					else
					{
						if (isset($filemode))
						{
							if (!@ chmod($fullpath, octdec($filemode)))
							{
								$ret = false;
							}
						}
					}
				}
			}

			closedir($dh);

			if (isset($foldermode))
			{
				if (!@ chmod($path, octdec($foldermode)))
				{
					$ret = false;
				}
			}
		}
		else
		{
			if (isset($filemode))
			{
				$ret = @ chmod($path, octdec($filemode));
			}
		}

		return $ret;
	}

	/**
	 * Get the permissions of the file/folder at a given path.
	 *
	 * @param   string  $path  The path of a file/folder.
	 *
	 * @return  string  Filesystem permissions.
	 *
	 * @since   1.7.0
	 */
	public static function getPermissions($path)
	{
		$path = self::clean($path);
		$mode = @ decoct(@ fileperms($path) & 0777);

		if (strlen($mode) < 3)
		{
			return '---------';
		}

		$parsed_mode = '';

		for ($i = 0; $i < 3; $i++)
		{
			// Read
			$parsed_mode .= ($mode[$i] & 04) ? 'r' : '-';

			// Write
			$parsed_mode .= ($mode[$i] & 02) ? 'w' : '-';

			// Execute
			$parsed_mode .= ($mode[$i] & 01) ? 'x' : '-';
		}

		return $parsed_mode;
	}

	/**
	 * Checks for snooping outside of the file system root.
	 *
	 * @param   string  $path  A file system path to check.
	 *
	 * @return  string  A cleaned version of the path or exit on error.
	 *
	 * @since   1.7.0
	 * @throws  Exception
	 */
	public static function check($path)
	{
		if (strpos($path, '..') !== false)
		{
			// Don't translate
			throw new \Exception(
				sprintf(
					'%s() - Use of relative paths not permitted',
					__METHOD__
				),
				20
			);
		}

		$path = self::clean($path);

		if ((JPATH_ROOT != '') && strpos($path, self::clean(JPATH_ROOT)) !== 0)
		{
			throw new \Exception(
				sprintf(
					'%1$s() - Snooping out of bounds @ %2$s',
					__METHOD__,
					$path
				),
				20
			);
		}

		return $path;
	}

	/**
	 * Function to strip additional / or \ in a path name.
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 *
	 * @since   1.7.0
	 * @throws  UnexpectedValueException
	 */
	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (!is_string($path) && !empty($path))
		{
			throw new \UnexpectedValueException(
				sprintf(
					'%s() - $path is not a string',
					__METHOD__
				),
				20
			);
		}

		$path = trim($path);

		if (empty($path))
		{
			$path = JPATH_ROOT;
		}
		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		elseif (($ds == '\\') && substr($path, 0, 2) == '\\\\')
		{
			$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else
		{
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Method to determine if script owns the path.
	 *
	 * @param   string  $path  Path to check ownership.
	 *
	 * @return  boolean  True if the php script owns the path passed.
	 *
	 * @since   1.7.0
	 */
	public static function isOwner($path)
	{
		$tmp = md5(Crypt::genRandomBytes());
		$ssp = ini_get('session.save_path');
		$jtp = JPATH_SITE . '/tmp';

		// Try to find a writable directory
		$dir = false;

		foreach (array($jtp, $ssp, '/tmp') as $currentDir)
		{
			if (is_writable($currentDir))
			{
				$dir = $currentDir;

				break;
			}
		}

		if ($dir)
		{
			$fileObject = new FileWrapper;
			$test       = $dir . '/' . $tmp;

			// Create the test file
			$blank = '';
			$fileObject->write($test, $blank, false);

			// Test ownership
			$return = (fileowner($test) == fileowner($path));

			// Delete the test file
			$fileObject->delete($test);

			return $return;
		}

		return false;
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   mixed   $paths  An path string or array of path strings to search in
	 * @param   string  $file   The file name to look for.
	 *
	 * @return  mixed   The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 *
	 * @since   1.7.0
	 */
	public static function find($paths, $file)
	{
		// Force to array
		if (!is_array($paths) && !($paths instanceof \Iterator))
		{
			settype($paths, 'array');
		}

		// Start looping through the path set
		foreach ($paths as $path)
		{
			// Get the path to the file
			$fullname = $path . '/' . $file;

			// Is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// Not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.

				// Needed for substr() later
				$path = realpath($path);
				$fullname = realpath($fullname);
			}

			/*
			 * The substr() check added to make sure that the realpath()
			 * results in a directory registered so that
			 * non-registered directories are not accessible via directory
			 * traversal attempts.
			 */
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// Could not find the file in the set of paths
		return false;
	}
}
