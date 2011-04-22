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
 * @package		Joomla.Platform
 * @subpackage	FileSystem
 * @since		11.1
 */

class JStringController {

	/**
	 *
	 * @return	array
	 * @since	11.1
	 */
	function _getArray() {
		static $strings = Array();
		return $strings;
	}

	function createRef($reference, &$string) {
		$ref = &JStringController::_getArray();
		$ref[$reference] =& $string;
	}


	function getRef($reference) {
		$ref = &JStringController::_getArray();
		if(isset($ref[$reference])) {
			return $ref[$reference];
		} else {
			return false;
		}
	}
}