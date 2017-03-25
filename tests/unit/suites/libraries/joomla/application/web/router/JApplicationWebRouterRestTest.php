<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationWebRouterRest.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.3
 */
class JApplicationWebRouterRestTest extends TestCase
{
	/**
	 * @var    JApplicationWebRouterRest  The object to be tested.
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * @var    string  The server REQUEST_METHOD cached to keep it clean.
	 * @since  12.3
	 */
	private $_method;

	/**
	 * Tests the setHttpMethodSuffix method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetHttpMethodSuffix()
	{
		$this->_instance->setHttpMethodSuffix('FOO', 'Bar');
		$s = TestReflection::getValue($this->_instance, 'suffixMap');
		$this->assertEquals('Bar', $s['FOO']);
	}

	/**
	 * Tests the fetchControllerSuffix method if the suffix map is missing.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testFetchControllerSuffixWithMissingSuffixMap()
	{
		$_SERVER['REQUEST_METHOD'] = 'FOOBAR';

		$this->setExpectedException('RuntimeException');
		TestReflection::invoke($this->_instance, 'fetchControllerSuffix');
	}

	/**
	 * Provides test data for testing fetch controller sufix
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedFetchControllerSuffixData()
	{
		// Input, Expected
		return array(
			// Don't allow method in POST request
			array('GET', 'Get', null, false),
			array('POST', 'Create', "get", false),
			array('POST', 'Create', null, false),
			array('POST', 'Create', "post", false),
			array('PUT', 'Update', null, false),
			array('POST', 'Create', "put", false),
			array('PATCH', 'Update', null, false),
			array('POST', 'Create', "patch", false),
			array('DELETE', 'Delete', null, false),
			array('POST', 'Create', "delete", false),
			array('HEAD', 'Head', null, false),
			array('POST', 'Create', "head", false),
			array('OPTIONS', 'Options', null, false),
			array('POST', 'Create', "options", false),
			array('POST', 'Create', "foo", false),
			array('FOO', 'Create', "foo", true),

			// Allow method in POST request
			array('GET', 'Get', null, false, true),
			array('POST', 'Get', "get", false, true),
			array('POST', 'Create', null, false, true),
			array('POST', 'Create', "post", false, true),
			array('PUT', 'Update', null, false, true),
			array('POST', 'Update', "put", false, true),
			array('PATCH', 'Update', null, false, true),
			array('POST', 'Update', "patch", false, true),
			array('DELETE', 'Delete', null, false, true),
			array('POST', 'Delete', "delete", false, true),
			array('HEAD', 'Head', null, false, true),
			array('POST', 'Head', "head", false, true),
			array('OPTIONS', 'Options', null, false, true),
			array('POST', 'Options', "options", false, true),
			array('POST', 'Create', "foo", false, true),
			array('FOO', 'Create', "foo", true, true),
		);
	}

	/**
	 * Tests the fetchControllerSuffix method.
	 *
	 * @param   string   $input        Input string to test.
	 * @param   string   $expected     Expected fetched string.
	 * @param   mixed    $method       Method to override POST request
	 * @param   boolean  $exception    True if an RuntimeException is expected based on invalid input
	 * @param   boolean  $allowMethod  Allow or not to pass method in post request as parameter
	 *
	 * @return  void
	 *
	 * @dataProvider  seedFetchControllerSuffixData
	 * @since         12.3
	 */
	public function testFetchControllerSuffix($input, $expected, $method, $exception, $allowMethod=false)
	{
		TestReflection::invoke($this->_instance, 'setMethodInPostRequest', $allowMethod);

		// Set reuqest method
		$_SERVER['REQUEST_METHOD'] = $input;

		// Set method in POST request
		$_GET['_method'] = $method;

		// If we are expecting an exception set it.
		if ($exception)
		{
			$this->setExpectedException('RuntimeException');
		}

		// Execute the code to test.
		$actual = TestReflection::invoke($this->_instance, 'fetchControllerSuffix');

		// Verify the value.
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Tests the setMethodInPostRequest and isMethodInPostRequest.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMethodInPostRequest()
	{
		// Check the defaults
		$this->assertEquals(false, TestReflection::invoke($this->_instance, 'isMethodInPostRequest'));

		// Check setting true
		TestReflection::invoke($this->_instance, 'setMethodInPostRequest', true);
		$this->assertEquals(true, TestReflection::invoke($this->_instance, 'isMethodInPostRequest'));

		// Check setting false
		TestReflection::invoke($this->_instance, 'setMethodInPostRequest', false);
		$this->assertEquals(false, TestReflection::invoke($this->_instance, 'isMethodInPostRequest'));
	}

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_instance = new JApplicationWebRouterRest($this->getMockWeb());
		$this->_method = @$_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function tearDown()
	{
		$this->_instance = null;
		$_SERVER['REQUEST_METHOD'] = $this->_method;

		parent::tearDown();
	}
}
