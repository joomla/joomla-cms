<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('BaseController', __DIR__ . '/stubs/tbase.php');

/**
 * Tests for the JController class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 * @since       12.1
 */
class JControllerBaseTest extends TestCase
{
	/**
	 * @var    JControllerBase
	 * @since  12.1
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__construct()
	{
		// New controller with no dependencies.
		$this->assertAttributeSame(JFactory::$application, 'app', $this->_instance, 'Checks the mock application came from the factory.');

		// New controller with dependencies
		$app   = $this->getMockWeb();
		$input = new JInput;

		$class = new BaseController($input, $app);
		$this->assertAttributeSame($input, 'input', $class, 'Checks the injected input.');
		$this->assertAttributeSame($app, 'app', $class, 'Checks the injected application.');
	}

	/**
	 * Tests the loadApplication method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testLoadApplication()
	{
		JFactory::$application = $this->getMockCmsApp();

		TestReflection::invoke($this->_instance, 'loadApplication');

		$this->assertAttributeSame(JFactory::$application, 'app', $this->_instance);
	}

	/**
	 * Tests the loadInput method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testLoadInput()
	{
		JFactory::$application->input = $this->getMockBuilder('JInput')->disableOriginalConstructor()->getMock();

		TestReflection::invoke($this->_instance, 'loadInput');

		$this->assertAttributeSame(JFactory::$application->input, 'input', $this->_instance);
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		$app = $this->getMockWeb();

		JFactory::$application = $app;

		$this->_instance = new BaseController;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		unset($this->_instance);
		parent::tearDown();
	}
}
