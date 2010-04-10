<?php
/**
 * JDate constructor tests
 *
 * @package Joomla
 * @subpackage UnitTest
 * @version $Id$
 * @author Anthony Ferrara
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
