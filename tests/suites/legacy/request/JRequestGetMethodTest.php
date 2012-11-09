<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JRequest-helper-dataset.php';

/**
 * Test class for JRequest using $_GET REQUEST_METHOD
 *
 * Note:
 * For tests specific to $_POST, see JRequestPostMethodTest.php
 *
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @since       12.3
 */
class JRequestTestGetMethod extends TestCase
{

	/**
	 * Set up the tests
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();
		$_SERVER['REQUEST_METHOD'] = 'GET';

		parent::setUp();
	}

	/**
	 * Test JRequest::getMethod
	 *
	 * @return  void
	 */
	public function testGetMethod()
	{
		$this->assertEquals('GET', JRequest::getMethod());
	}

	/**
	 * Test JRequest::getVar
	 *
	 * @return  void
	 */
	public function testGetVar()
	{
		$this->assertEquals('from _GET', JRequest::getVar('tag', null, 'get'));
	}

	/**
	 * Test JRequest::getInt
	 *
	 * @return  void
	 */
	public function testGetInt()
	{
		$_GET['teststr'] = '2.0';
		$this->assertTrue(2 === JRequest::getInt('teststr', null, 'get'));
	}

	/**
	 * Test JRequest::getFloat
	 *
	 * @return  void
	 */
	public function testGetFloat()
	{
		$_GET['teststr'] = '1.337';
		$this->assertTrue(1.337 === JRequest::getFloat('teststr', null, 'get'));
	}

	/**
	 * Test JRequest::getBool
	 *
	 * @return  void
	 */
	public function testGetBool()
	{
		$_GET['teststr'] = 'true';
		$this->assertTrue(JRequest::getBool('teststr', null, 'get'));
	}

	/**
	 * Test JRequest::getWord
	 *
	 * @return  void
	 */
	public function testGetWord()
	{
		$_GET['teststr'] = 'two2';
		$this->assertTrue('two' === JRequest::getWord('teststr', null, 'get'));
	}

	/**
	 * Test JRequest::getCmd
	 *
	 * @return  void
	 */
	public function testGetCmd()
	{
		$_GET['teststr'] = 'some_command';
		$this->assertTrue('some_command' === JRequest::getCmd('teststr', null, 'get'));
	}

	/**
	 * Test JRequest::getString
	 *
	 * @return  void
	 */
	public function testGetString()
	{
		$_GET['teststr'] = 'four';
		$this->assertTrue('four' === JRequest::getString('teststr', null, 'get'));
	}

	/**
	 * Test JRequest::setVar
	 *
	 * @return  void
	 */
	public function testSetVar()
	{
		JRequest::setVar('foo', 'bar', 'get');
		$this->assertTrue('bar' === JRequest::getVar('foo', null, 'get'));
	}

	/**
	 * Test JRequest::get
	 *
	 * @return  void
	 */
	public function testGet()
	{
		$expected = array('tag' => 'from _GET');
		$this->assertSame($expected, JRequest::get('get'));
	}

	/**
	 * Test JRequest::set
	 *
	 * @return  void
	 */
	public function testSet()
	{
		// Empty $_GET var before testing
		$_GET = array();
		$get = array('foo' => 'bar', 'key' => 'value');
		JRequest::set($get, 'get', true);

		$this->assertSame($get, $_GET);
	}
}
