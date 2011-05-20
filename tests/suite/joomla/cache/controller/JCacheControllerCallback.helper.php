<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class testCallbackController {

	public function instanceCallback($arg1, $arg2) {
		echo $arg1;
		return $arg2;
	}

	static function staticCallback($arg1, $arg2) {
		echo $arg1;
		return $arg2;
	}

}

function testCallbackControllerFunc($arg1, $arg2) {
	echo $arg1;
	return $arg2;
}
