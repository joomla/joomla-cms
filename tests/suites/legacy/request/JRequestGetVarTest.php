<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JRequest-helper-dataset.php';
require_once __DIR__ . '/JFilterInput-mock-general.php';

/**
 * Test class for JRequest.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @since       12.3
 */
class JRequestTest_GetVar extends TestCase
{
	public static $filter;

	/**
	 * Define some sample data
	 *
	 * @return  array  Sample data for testing
	 */
	public function getVarData()
	{
		return JRequestTest_DataSet::$getVarTests;
	}

	/**
	 * Define some sample data
	 *
	 * @return  void
	 */
	public function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();

		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
		parent::setUp();
	}

	/**
	 * Set up the environment
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		$filter = &JFilterInput::getInstance();
		self::$filter = JFilterInput::getInstance();
		$filter = new JFilterInputJRequest;
	}

	/**
	 * Tear down the environment
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		$filter = &JFilterInput::getInstance();
		$filter = self::$filter;
	}

	/**
	 * Test the getVar and _cleanVar methods
	 *
	 * @param   string   $name         Variable name.
	 * @param   string   $default      Default value if the variable does not exist.
	 * @param   string   $hash         Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 * @param   string   $type         Return type for the variable, for valid values see {@link JFilterInput::clean()}.
	 * @param   integer  $mask         Filter mask for the variable.
	 * @param   mixed    $expect       Expected result to test against
	 * @param   array    $filterCalls  Filter Calls
	 *
	 * @dataProvider getVarData
	 * @covers JRequest::getVar
	 * @covers JRequest::_cleanVar
	 *
	 * @return  void
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
