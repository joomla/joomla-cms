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
 * Test class for JRequest using $_POST REQUEST_METHOD.
 *
 * Note:
 * For tests specific to $_GET, see JRequestGetMethodTest.php
 *
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @since       12.3
 */
class JRequestTestPostMethod extends TestCase
{

	/**
	 * Set up the tests
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();
		$_SERVER['REQUEST_METHOD'] = 'POST';

		parent::setUp();
	}

	/**
	 * Test JRequest::getMethod
	 *
	 * @return  void
	 */
	public function testGetMethod()
	{
		$this->assertEquals('POST', JRequest::getMethod());
	}

	/**
	 * Test JRequest::getVar
	 *
	 * @return  void
	 */
	public function testGetVar()
	{
		$this->assertEquals('from _POST', JRequest::getVar('tag', null, 'post'));
	}

	/**
	 * Test JRequest::getInt
	 *
	 * @return  void
	 */
	public function testGetInt()
	{
		$_POST['teststr'] = '1';
		$this->assertTrue(1 === JRequest::getInt('teststr', null, 'post'));
	}

	/**
	 * Test JRequest::getFloat
	 *
	 * @return  void
	 */
	public function testGetFloat()
	{
		$_POST['teststr'] = '1.337';
		$this->assertTrue(1.337 === JRequest::getFloat('teststr', null, 'post'));
	}

	/**
	 * Test JRequest::getBool
	 *
	 * @return  void
	 */
	public function testGetBool()
	{
		$_POST['teststr'] = '0';
		$this->assertFalse(JRequest::getBool('teststr', null, 'post'));
	}

	/**
	 * Test JRequest::getWord
	 *
	 * @return  void
	 */
	public function testGetWord()
	{
		$_POST['teststr'] = 'one1';
		$this->assertTrue('one' === JRequest::getWord('teststr', null, 'post'));
	}

	/**
	 * Test JRequest::getCmd
	 *
	 * @return  void
	 */
	public function testGetCmd()
	{
		$_POST['teststr'] = 'some_command';
		$this->assertTrue('some_command' === JRequest::getCmd('teststr', null, 'post'));
	}

	/**
	 * Test JRequest::getString
	 *
	 * @return  void
	 */
	public function testGetString()
	{
		$_POST['teststr'] = 'three';
		$this->assertTrue('three' === JRequest::getString('teststr', null, 'post'));
	}

	/**
	 * Test JRequest::setVar
	 *
	 * @return  void
	 */
	public function testSetVar()
	{
		JRequest::setVar('foo', 'bar', 'post');
		$this->assertTrue('bar' === JRequest::getVar('foo', null, 'post'));
	}

	/**
	 * Test JRequest::get
	 *
	 * @return  void
	 */
	public function testGet()
	{
		$expected = array('tag' => 'from _POST');
		$this->assertSame($expected, JRequest::get('post'));
	}

	/**
	 * Test JRequest::set
	 *
	 * @return  void
	 */
	public function testSet()
	{
		// Empty $_POST var before testing
		$_POST = array();
		$post = array('foo' => 'bar', 'key' => 'value');
		JRequest::set($post, 'post', true);

		$this->assertSame($post, $_POST);
	}
}
