<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
	 * @covers  JApplicationWebRouterRest::setHttpMethodSuffix
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
	 * @covers  JApplicationWebRouterRest::fetchControllerSuffix
	 * @since   12.3
	 */
	public function testFetchControllerSuffixWithMissingSuffixMap()
	{
		$_SERVER['REQUEST_METHOD'] = 'FOOBAR';

		$this->setExpectedException('RuntimeException');
		$suffix = TestReflection::invoke($this->_instance, 'fetchControllerSuffix');
	}

	/**
	 * Tests the fetchControllerSuffix method.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouterRest::fetchControllerSuffix
	 * @since   12.3
	 */
	public function testFetchControllerSuffix()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$suffix = TestReflection::invoke($this->_instance, 'fetchControllerSuffix');
		$this->assertEquals('Get', $suffix);
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
