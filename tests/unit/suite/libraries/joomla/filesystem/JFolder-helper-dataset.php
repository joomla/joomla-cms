<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc.
 *
 */

class JFolderTest_DataSet {
	/**
	 * Tests for getVar.
	 *
	 * Each element contains $name, $default, $hash, $type, $mask, $expect,
	 * array of JFilterInput expectations.
	 *
	 * Note that this is a JRequest test, not a JFilterInput test. Cases
	 * that exersize data types and string cleaning belong in a test of the
	 * filtering code.
	 *
	 * @var array
	 */
	static public $makeSafeTests = array(
		/*
		 * Normal values
		 */
		array('test1/testdirectory','test1/testdirectory')
	);

	static function initSuperGlobals() {
	}

}
?>
