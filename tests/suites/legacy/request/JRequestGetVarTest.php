<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JRequest-helper-dataset.php';
require_once __DIR__ . '/JFilterInput-mock-general.php';

/**
 * A unit test class for SubjectClass
 */
class JRequestTest_GetVar extends PHPUnit_Framework_TestCase
{
	public static $filter;

	public function getVarData()
	{
		return JRequestTest_DataSet::$getVarTests;
	}

	/**
	 * Define some sample data
	 */
	function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();
		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
	}

	public static function setUpBeforeClass()
	{
		$filter = &JFilterInput::getInstance();
		self::$filter = JFilterInput::getInstance();
		$filter = new JFilterInputJRequest;
	}

	public static function tearDownAfterClass()
	{
		$filter = &JFilterInput::getInstance();
		$filter = self::$filter;
	}

	/**
	 * @dataProvider getVarData
	 * @covers JRequest::getVar
	 * @covers JRequest::_cleanVar
	 * @covers JRequest::_stripSlashesRecursive
	 */
	public function testGetVarFromDataSet($name, $default, $hash, $type, $mask, $expect, $filterCalls)
	{
		jimport('joomla.environment.request');

		$filter = JFilterInput::getInstance();
		$filter->mockReset();

		if (count($filterCalls))
		{
			foreach ($filterCalls as $info)
			{
				$filter->mockSetUp($info[0], $info[1], $info[2], $info[3]);
			}
		}

		/*
		 * Get the variable and check the value.
		 */
		$actual = JRequest::getVar($name, $default, $hash, $type, $mask);
		$this->assertEquals($expect, $actual, 'Non-cached getVar');

		/*
		 * Repeat the process to check caching (the JFilterInput mock should not
		 * get called unless the default is being used).
		 */
		$actual = JRequest::getVar($name, $default, $hash, $type, $mask);
		$this->assertEquals($expect, $actual, 'Cached getVar');
		if (($filterOK = $filter->mockTearDown()) !== true)
		{
			$this->fail('JFilterInput not called as expected:' . print_r($filterOK, true));
		}
	}
}
