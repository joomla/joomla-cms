<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * Template for a basic unit test
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2010 Open Source Matters, Inc.
 * @version $Id$
 *
 */

require_once 'JRequest-helper-dataset.php';


/**
 * A unit test class for SubjectClass
 * @runTestsInSeparateProcesses enabled
 * @preserveGlobalState	disabled
 */
class JRequestTest_GetVar extends PHPUnit_Framework_TestCase
{
	public function getVarData() {
		return JRequestTest_DataSet::$getVarTests;
	}

	/**
	 * Define some sample data
	 */
	function setUp() {
		JRequestTest_DataSet::initSuperGlobals();
		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
	}

	/**
	 * @dataProvider getVarData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetVarFromDataSet(
		$name, $default, $hash, $type, $mask, $expect, $filterCalls
	) {

		require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/bootstrap.php');
		require_once dirname(__FILE__).DS.'JFilterInput-mock-general.php';

		jimport( 'joomla.environment.request' );

		$filter = JFilterInput::getInstance();
		$filter -> mockReset();
		if (count($filterCalls)) {
			foreach ($filterCalls as $info) {
				$filter -> mockSetUp(
					$info[0], $info[1], $info[2], $info[3]
				);
			}
		}
		/*
		 * Get the variable and check the value.
		 */
		$actual = JRequest::getVar($name, $default, $hash, $type, $mask);
		$this -> assertEquals($expect, $actual, 'Non-cached getVar');
		/*
		 * Repeat the process to check caching (the JFilterInput mock should not
		 * get called unless the default is being used).
		 */
		$actual = JRequest::getVar($name, $default, $hash, $type, $mask);
		$this -> assertEquals($expect, $actual, 'Cached getVar');
		if (($filterOK = $filter -> mockTearDown()) !== true) {
			$this -> fail(
				'JFilterInput not called as expected:'
				. print_r($filterOK, true)
			);
		}
	}

}

