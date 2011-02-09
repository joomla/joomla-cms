<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 */

defined('JPATH_PLATFORM') or die;


class JStringController {

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