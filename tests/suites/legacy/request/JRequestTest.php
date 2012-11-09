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
 * Test class for JRequest.
 *
 * Note: This class only tests methods from JRequest
 * that are independent of $_SERVER['REQUEST_METHOD'];
 * For tests specific to $_POST, see JRequestPostMethodTest.php
 * For tests specific to $_GET, see JRequestGetMethodTest.php
 *
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @since       12.3
 */
class JRequestTest extends TestCase
{

	/**
	 * Set up the tests
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();

		parent::setUp();
	}

	/**
	 * Test JRequest::getUri
	 *
	 * @return  void
	 */
	public function testGetURI()
	{
		$uri = JUri::getInstance();
		$uri->setPath('/foo/bar');
		$uri->setQuery(array('baz' => 'buz'));

		$this->assertEquals('/foo/bar?baz=buz', JRequest::getUri());
	}

	/**
	 * Test JRequest::getVar
	 *
	 * @return  void
	 */
	public function testGetVar()
	{
		$this->assertNull(JRequest::getVar('nonExistant'));
	}

	/**
	 * Test JRequest::getInt
	 *
	 * @return  void
	 */
	public function testGetInt()
	{
		$this->assertTrue(0 === JRequest::getInt('nonExistant'));
	}

	/**
	 * Test JRequest::getFloat
	 *
	 * @return  void
	 */
	public function testGetFloat()
	{
		$this->assertTrue(0.0 === JRequest::getFloat('nonExistant'));
	}

	/**
	 * Test JRequest::getBool
	 *
	 * @return  void
	 */
	public function testGetBool()
	{
		$this->assertFalse(JRequest::getBool('nonExistant'));
	}

	/**
	 * Test JRequest::getWord
	 *
	 * @return  void
	 */
	public function testGetWord()
	{
		$this->assertTrue('' === JRequest::getWord('nonExistant'));
	}

	/**
	 * Test JRequest::getCmd
	 *
	 * @return  void
	 */
	public function testGetCmd()
	{
		$this->assertTrue('' === JRequest::getCmd('nonExistant'));
	}

	/**
	 * Test JRequest::getString
	 *
	 * @return  void
	 */
	public function testGetString()
	{
		$this->assertTrue('' === JRequest::getString('nonExistant'));
	}

	/**
	 * Test JRequest::checkToken
	 *
	 * @todo    Implement testCheckToken().
	 *
	 * @return  void
	 */
	public function testCheckToken()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JRequest::_cleanVar
	 *
	 * @return  void
	 */
	public function testCleanVar()
	{
		$method = new ReflectionMethod('JRequest', '_cleanVar');
		$method->setAccessible(true);

		$this->assertEquals('foobar', $method->invokeArgs(null, array(' foobar   ')));
		$this->assertEquals(' foobar   ', $method->invokeArgs(null, array(' foobar   ', 1)));
		$this->assertEquals('fooxssbar', $method->invokeArgs(null, array(' foo<script>xss</script>bar   ', 4)));
	}
}
