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
	define('JPATH_ROOT', JPath::clean(JPATH_SITE));
}

/**
 * A Path handling class
 * 
 * @static
 * @package 	Joomla.Framework
 * @subpackage 	FileSystem
 * @since		1.5
 */
class JPath {

	/**
	 * Checks if a path's permissions can be changed
	 *
	 * @param string $path Path to check
	 * @return boolean True if path can have mode changed
	 * @since 1.5
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
	 * @since 1.5
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
						if (!JPath::setPermissions($fullpath, $filemode, $foldermode)) {
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
	 * @since 1.5
	 */
	function getPermissions($path) {
		$path = JPath::clean($path, false);
		JPath::check($path);
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
	 * @since 1.5
	 */
	function check($path) {
		if (strpos($path, '..') != false) {
			mosBackTrace();
			JError::raiseError( 20, 'JPath::check use of relative paths not permitted'); // don't translate
		}
		if (strpos(JPath::clean($path), JPath::clean(JPATH_ROOT)) != 0) {
			mosBackTrace();
			JError::raiseError( 20, 'JPath::check snooping out of bounds @ '.$path); // don't translate
		}
	}

	/**
	 * Function to strip additional / or \ in a path name
	 *
	 * @param string $p_path The path to clean
	 * @param boolean $p_addtrailingslash True if the function shoul add a trailing slash
	 * @return string The cleaned path
	 * @since 1.5
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
	 * @since 1.5
	 */
	function isOwner($path) {
		return (posix_getuid() == fileowner($path));
	}
}
?>