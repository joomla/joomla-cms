<?php
/**
* @version		$Id:php50x.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Compatibility
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

 /**
 * PHP 5.0.x Compatibility functions
 *
 * @since		1.5
 */
if (!defined('FILE_USE_INCLUDE_PATH')) {
	define('FILE_USE_INCLUDE_PATH', 1);
}

if (!defined('FILE_APPEND')) {
	define('FILE_APPEND', 8);
}

/**
 * Replace file_put_contents()
 *
 * @link		http://php.net/function.file_put_contents
 * @author		Aidan Lister <aidan@php.net>
 * @version	 	$Revision: 47 $
 * @internal	resource_context is not supported
 * @since		PHP 5
 */
if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $content, $flags = null, $resource_context = null)
	{
		// If $content is an array, convert it to a string
		if (is_array($content)) {
			$content = implode('', $content);
		}

		// If we don't have a string, throw an error
		if (!is_scalar($content)) {
			trigger_error('file_put_contents() The 2nd parameter should be either a string or an array', E_USER_WARNING);
			return false;
		}

		// Get the length of date to write
		$length = strlen($content);

		// Check what mode we are using
		$mode = ($flags & FILE_APPEND) ?
					$mode = 'a' :
					$mode = 'w';

		// Check if we're using the include path
		$use_inc_path = ($flags & FILE_USE_INCLUDE_PATH) ?
					true :
					false;

		// Open the file for writing
		if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
			trigger_error('file_put_contents() failed to open stream: Permission denied', E_USER_WARNING);
			return false;
		}

		// Write to the file
		$bytes = 0;
		if (($bytes = @fwrite($fh, $content)) === false) {
			$errormsg = sprintf('file_put_contents() Failed to write %d bytes to %s',
							$length,
							$filename);
			trigger_error($errormsg, E_USER_WARNING);
			return false;
		}

		// Close the handle
		@fclose($fh);

		// Check all the data was written
		if ($bytes != $length) {
			$errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',
							$bytes,
							$length);
			trigger_error($errormsg, E_USER_WARNING);
			return false;
		}

		// Return length
		return $bytes;
	}
}

/**
 * Ported PHP5 function to PHP4 for forward compatibility
 */
function clone($object) {
	return $object;
}