<?php
/**
 * JDate constructor tests
 *
 * @package Joomla
 * @subpackage UnitTest
 * @version $Id$
 * @author Anthony Ferrara
 */

class testCallbackHandler {

	public function instanceCallback($arg1, $arg2) {
		echo $arg1;
		return $arg2;
	}

	static function staticCallback($arg1, $arg2) {
		echo $arg1;
		return $arg2;
	}

}

function testCallbackHandlerFunc($arg1, $arg2) {
	echo $arg1;
	return $arg2;
}
