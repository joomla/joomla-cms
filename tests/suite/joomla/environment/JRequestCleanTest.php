<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * Template for a basic unit test
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2011 Open Source Matters, Inc.
 * @version $Id: JRequestCleanTest.php 20196 2011-01-09 02:40:25Z ian $
 *
 */

/*
 * We now return to our regularly scheduled environment.
 */
require_once 'JRequest-helper-dataset.php';

/**
 * A unit test class for SubjectClass
 */
class JRequestTest_Clean extends PHPUnit_Framework_TestCase
{
	public static $filter;

	/**
	 * Define some sample data
	 */
	function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();
		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
	}

	public static function setUpBeforeClass() {
		require_once JUnitHelper::normalize(dirname(__FILE__)).'/JFilterInput-mock-general.php';
		$filter = &JFilterInput::getInstance();
		self::$filter = JFilterInput::getInstance();
		$filter = new JFilterInputJRequest;
	}

	public static function tearDownAfterClass() {
		$filter = &JFilterInput::getInstance();
		$filter = self::$filter;
	}

	function testRequestClean()
	{

		/*
		 * Call the method.
		 */
		$expect = count($_POST);
		JRequest::clean();
		$this -> assertEquals($expect, count($_POST), '_POST[0] was modified.');
	}

	function testRequestCleanWithBanned()
	{
		$this->markTestIncomplete('This test needs work.');
		try {
			$passed = false;
			$_POST['_post'] = 'This is banned.';
			/*
			 * Call the clean method.
			 */
			JRequest::clean();
		} catch (Exception $e) {
			$passed = true;
		}
		if (! $passed) {
			$this -> fail('JRequest::clean() didn\'t die on a banned variable.');
		}
	}

	function testRequestCleanWithNumeric()
	{
		$this->markTestIncomplete('This test needs work.');
		try {
			$passed = false;
			$_POST[0] = 'This is invalid.';
			/*
			 * Call the clean method.
			 */
			JRequest::clean();
		} catch (Exception $e) {
			$passed = true;
		}
		if (! $passed) {
			$this -> fail('JRequest::clean() didn\'t die on a banned variable.');
		}
	}

	function testRequestCleanWithNumericString()
	{
		$this->markTestIncomplete('This test needs work.');
		try {
			$passed = false;
			$_POST['0'] = 'This is invalid.';
			/*
			 * Call the clean method.
			 */
			JRequest::clean();
		} catch (Exception $e) {
			$passed = true;
		}
		if (! $passed) {
			$this -> fail('JRequest::clean() didn\'t die on a banned variable.');
		}
	}

}

