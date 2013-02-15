<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('TControllerBar', __DIR__ . '/stubs/controllers/bar.php');
JLoader::register('MyTestControllerBaz', __DIR__ . '/stubs/controllers/baz.php');
JLoader::register('MyTestControllerFoo', __DIR__ . '/stubs/controllers/foo.php');

/**
 * Test class for JApplicationWebRouter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.3
 */
class JApplicationWebRouterTest extends TestCase
{
	/**
	 * @var    JApplicationWebRouter  The object to be tested.
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::__construct
	 * @since   12.3
	 */
	public function test__construct()
	{
		$this->assertAttributeInstanceOf('JApplicationWeb', 'app', $this->_instance);
		$this->assertAttributeInstanceOf('JInput', 'input', $this->_instance);
	}

	/**
	 * Tests the setControllerPrefix method.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::setControllerPrefix
	 * @since   12.3
	 */
	public function testSetControllerPrefix()
	{
		$this->_instance->setControllerPrefix('MyApplication');
		$this->assertAttributeEquals('MyApplication', 'controllerPrefix', $this->_instance);
	}

	/**
	 * Tests the setDefaultController method.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::setDefaultController
	 * @since   12.3
	 */
	public function testSetDefaultController()
	{
		$this->_instance->setDefaultController('foobar');
		$this->assertAttributeEquals('foobar', 'default', $this->_instance);
	}

	/**
	 * Tests the fetchController method if the controller class is missing.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::fetchController
	 * @since   12.3
	 */
	public function testFetchControllerWithMissingClass()
	{
		$this->setExpectedException('RuntimeException');
		$controller = TestReflection::invoke($this->_instance, 'fetchController', 'goober');
	}

	/**
	 * Tests the fetchController method if the class not a controller.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::fetchController
	 * @since   12.3
	 */
	public function testFetchControllerWithNonController()
	{
		$this->setExpectedException('RuntimeException');
		$controller = TestReflection::invoke($this->_instance, 'fetchController', 'MyTestControllerBaz');
	}

	/**
	 * Tests the fetchController method with a prefix set.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::fetchController
	 * @since   12.3
	 */
	public function testFetchControllerWithPrefixSet()
	{
		TestReflection::setValue($this->_instance, 'controllerPrefix', 'MyTestController');
		$controller = TestReflection::invoke($this->_instance, 'fetchController', 'foo');
	}

	/**
	 * Tests the fetchController method without a prefix set even though it is necessary.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::fetchController
	 * @since   12.3
	 */
	public function testFetchControllerWithoutPrefixSetThoughNecessary()
	{
		$this->setExpectedException('RuntimeException');
		$controller = TestReflection::invoke($this->_instance, 'fetchController', 'foo');
	}

	/**
	 * Tests the fetchController method without a prefix set.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouter::fetchController
	 * @since   12.3
	 */
	public function testFetchControllerWithoutPrefixSet()
	{
		$controller = TestReflection::invoke($this->_instance, 'fetchController', 'TControllerBar');
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

		$this->_instance = $this->getMockForAbstractClass('JApplicationWebRouter', array($this->getMockWeb()));
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

		parent::tearDown();
	}
}
