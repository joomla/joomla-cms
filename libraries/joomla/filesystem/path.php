<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/** boolean True if a Windows based host */
define('JPATH_ISWIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));

/** boolean True if a Mac based host */
define('JPATH_ISMAC', (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC'));

if (!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPARATOR define */
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JPATH_ROOT')) {
	/** string The root directory of the file system in native format */
	define('JPATH_ROOT', JPath::clean(JPATH_SITE));
}

/**
 * A Path handling class
 *
 * @package		Joomla.Platform
 * @subpackage	FileSystem
 * @since		11.1
 */
class JPath
{
	/**
	 * Checks if a path's permissions can be changed
	 *
	 * @param	string	Path to check
	 * @return	boolean	True if path can have mode changed
	 * @since	11.1
	 */
	public static function canChmod($path)
	{
		$perms = fileperms($path);
		if ($perms !== false) {
			if (@chmod($path, $perms ^ 0001)) {
				@chmod($path, $perms);
				return true;
			}
		}

		return false;
	}

	/**
	 * Chmods files and directories recursivly to given permissions
	 *
	 * @param	string	Root path to begin changing mode [without trailing slash]
	 * @param	string	Octal representation of the value to change file mode to [null = no change]
	 * @param	string	Octal representation of the value to change folder mode to [null = no change]
	 * @return	boolean	True if successful [one fail means the whole operation failed]
	 * @since	11.1
	 */
	public static function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		// Initialise return value
		$ret = true;

		if (is_dir($path)) {
			$dh = opendir($path);

			while ($file = readdir($dh)) {
				if ($file != '.' && $file != '..') {
					$fullpath = $path.'/'.$file;
					if (is_dir($fullpath)) {
						if (!JPath::setPermissions($fullpath, $filemode, $foldermode)) {
							$ret = false;
						}
					} else {
						if (isset ($filemode)) {
							if (!@ chmod($fullpath, octdec($filemode))) {
								$ret = false;
							}
						}
					}
				}
			}
			closedir($dh);
			if (isset ($foldermode)) {
				if (!@ chmod($path, octdec($foldermode))) {
					$ret = false;
				}
			}
		} else {
			if (isset ($filemode)) {
				$ret = @ chmod($path, octdec($filemode));
			}
		}

		return $ret;
	}

	/**
	 * Get the permissions of the file/folder at a give path
	 *
	 * @param	string	The path of a file/folder
	 * @return	string	Filesystem permissions
	 * @since	11.1
	 */
	public static function getPermissions($path)
	{
		$path = JPath::clean($path);
		$mode = @ decoct(@ fileperms($path) & 0777);

		if (strlen($mode) < 3) {
			return '---------';
		}

		$parsed_mode = '';
		for ($i = 0; $i < 3; $i ++) {
			// read
			$parsed_mode .= ($mode { $i } & 04) ? "r" : "-";
			// write
			$parsed_mode .= ($mode { $i } & 02) ? "w" : "-";
			// execute
			$parsed_mode .= ($mode { $i } & 01) ? "x" : "-";
		}

		return $parsed_mode;
	}

	/**
	 * Checks for snooping outside of the file system root
	 *
	 * @param	string	A file system path to check
	 * @param	string	Directory separator (optional)
	 * @return	string	A cleaned version of the path
	 * @since	11.1
	 */
	public static function check($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (strpos($path, '..') !== false) {
			JError::raiseError(20, 'JPath::check Use of relative paths not permitted'); // don't translate
			jexit();
		}

		$path = JPath::clean($path);
		if (strpos($path, JPath::clean(JPATH_ROOT)) !== 0) {
			JError::raiseError(20, 'JPath::check Snooping out of bounds @ '.$path); // don't translate
			jexit();
		}

		return $path;
	}

	/**
	 * Function to strip additional / or \ in a path name
	 *
	 * @param	string	The path to clean
	 * @param	string	Directory separator (optional)
	 * @return	string	The cleaned path
	 * @since	11.1
	 */
	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		$path = trim($path);

		if (empty($path)) {
			$path = JPATH_ROOT;
		} else {
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Method to determine if script owns the path
	 *
	 * @param	string	Path to check ownership
	 * @return	boolean	True if the php script owns the path passed
	 * @since	11.1
	 */
	public static function isOwner($path)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.user.helper');

		$tmp = md5(JUserHelper::genRandomPassword(16));
		$ssp = ini_get('session.save_path');
		$jtp = JPATH_SITE.DS.'tmp';

		// Try to find a writable directory
		$dir = is_writable('/tmp') ? '/tmp' : false;
		$dir = (!$dir && is_writable($ssp)) ? $ssp : false;
		$dir = (!$dir && is_writable($jtp)) ? $jtp : false;

		if ($dir) {
			$test = $dir.DS.$tmp;

			// Create the test file
			$blank = '';
			JFile::write($test, $blank, false);

			// Test ownership
			$return = (fileowner($test) == fileowner($path));

			// Delete the test file
			JFile::delete($test);

			return $return;
		}

		return false;
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param	array|string	An path or array of path to search in
	 * @param	string			The file name to look for.
	 * @return	mixed			The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 * @since	11.1
	 */
	public static function find($paths, $file)
	{
		settype($paths, 'array'); //force to array

		// start looping through the path set
		foreach ($paths as $path) {
			// get the path to the file
			$fullname = $path.'/'.$file;

			// is the path based on a stream?
			if (strpos($path, '://') === false) {
				// not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// the substr() check added to make sure that the realpath()
			// results in a directory registered so that
			// non-registered directores are not accessible via directory
			// traversal attempts.
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path) {
				return $fullname;
			}
		}

		// could not find the file in the set of paths
		return false;
	}
}