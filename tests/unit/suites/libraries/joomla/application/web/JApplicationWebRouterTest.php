<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
 */
class JApplicationWebRouterTest extends TestCase
{
	/**
	 * @var    JApplicationWebRouter  The object to be tested.
	 */
	private $_instance;

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
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
	 */
	protected function tearDown()
	{
		$this->_instance = null;

		parent::tearDown();
	}

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 */
	public function test__construct()
	{
		$this->assertAttributeInstanceOf(
			'JApplicationWeb',
			'app',
			$this->_instance
		);
		$this->assertAttributeInstanceOf(
			'JInput',
			'input',
			$this->_instance
		);
	}

	/**
	 * Tests the setControllerPrefix method.
	 *
	 * @return  void
	 */
	public function testSetControllerPrefix()
	{
		$this->_instance->setControllerPrefix('MyApplication');

		$this->assertAttributeEquals(
			'MyApplication',
			'controllerPrefix',
			$this->_instance
		);
	}

	/**
	 * Tests the setDefaultController method.
	 *
	 * @return  void
	 */
	public function testSetDefaultController()
	{
		$this->_instance->setDefaultController('foobar');

		$this->assertAttributeEquals(
			'foobar',
			'default',
			$this->_instance
		);
	}

	/**
	 * Tests the fetchController method if the controller class is missing.
	 *
	 * @return  void
	 *
	 * @expectedException RuntimeException
	 */
	public function testFetchControllerWithMissingClass()
	{
		TestReflection::invoke($this->_instance, 'fetchController', 'goober');
	}

	/**
	 * Tests the fetchController method if the class not a controller.
	 *
	 * @return  void
	 *
	 * @expectedException RuntimeException
	 */
	public function testFetchControllerWithNonController()
	{
		TestReflection::invoke($this->_instance, 'fetchController', 'MyTestControllerBaz');
	}

	/**
	 * Tests the fetchController method with a prefix set.
	 *
	 * @return  void
	 */
	public function testFetchControllerWithPrefixSet()
	{
		TestReflection::setValue($this->_instance, 'controllerPrefix', 'MyTestController');
		$this->assertInstanceOf(
			'MyTestControllerFoo',
			TestReflection::invoke($this->_instance, 'fetchController', 'foo')
		);
	}

	/**
	 * Tests the fetchController method without a prefix set even though it is necessary.
	 *
	 * @return  void
	 *
	 * @expectedException RuntimeException
	 */
	public function testFetchControllerWithoutPrefixSetThoughNecessary()
	{
		TestReflection::invoke($this->_instance, 'fetchController', 'foo');
	}

	/**
	 * Tests the fetchController method without a prefix set.
	 *
	 * @return  void
	 */
	public function testFetchControllerWithoutPrefixSet()
	{
		$this->assertInstanceOf(
			'TControllerBar',
			TestReflection::invoke($this->_instance, 'fetchController', 'TControllerBar')
		);
	}
}
