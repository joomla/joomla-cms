<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
/**
 * String Controller
 *
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 * @since       11.1
 */

class JStringController {

	/**
	 * Defines a variable as an array
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	function _getArray() {
		static $strings = Array();
		return $strings;
	}

	/**
	 * Create a reference
	 *
	 * @param  string  $reference  The key
	 * @param  string  &$string    The value
	 *
	 * @since   11.1
	 */
	function createRef($reference, &$string) {
		$ref = &JStringController::_getArray();
		$ref[$reference] =& $string;
	}

	/**
	 * Get reference
	 *
	 * @return  mixed  False if not set, reference if it it exists
	 *
	 * @since   11.1
	 */
	function getRef($reference) {
		$ref = &JStringController::_getArray();
		if(isset($ref[$reference])) {
			return $ref[$reference];
		} else {
			return false;
		}
	}
}