<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractApplication;
use Joomla\Test\TestHelper;
use Joomla\Registry\Registry;

require_once __DIR__ . '/Stubs/ConcreteBase.php';

/**
 * Test class for Joomla\Application\AbstractApplication.
 *
 * @since  1.0
 */
class AbstractApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var    AbstractApplication
	 * @since  1.0
	 */
	protected $instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::__construct
	 * @since   1.0
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'Joomla\\Input\\Input',
			$this->instance->input,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'Joomla\Registry\Registry',
			TestHelper::getValue($this->instance, 'config'),
			'Config property wrong type'
		);

		// Test dependancy injection.

		$mockInput = $this->getMock('Joomla\Input\Input', array('test'), array(), '', false);
		$mockInput
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$mockConfig = $this->getMock('Joomla\Registry\Registry', array('test'), array(null), '', true);
		$mockConfig
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$instance = new ConcreteBase($mockInput, $mockConfig);

		$input = TestHelper::getValue($instance, 'input');
		$this->assertEquals('ok', $input->test());

		$config = TestHelper::getValue($instance, 'config');
		$this->assertEquals('ok', $config->test());
	}

	/**
	 * Test the close method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::close
	 * @since   1.0
	 */
	public function testClose()
	{
		// Make sure the application is not already closed.
		$this->assertSame(
			$this->instance->closed,
			null,
			'Checks the application doesn\'t start closed.'
		);

		$this->instance->close(3);

		// Make sure the application is closed with code 3.
		$this->assertSame(
			$this->instance->closed,
			3,
			'Checks the application was closed with exit code 3.'
		);
	}

	/**
	 * Test the execute method
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::execute
	 * @since   1.0
	 */
	public function testExecute()
	{
		$this->instance->doExecute = false;

		$this->instance->execute();

		$this->assertTrue($this->instance->doExecute);
	}

	/**
	 * Tests the get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$mockInput = $this->getMock('Joomla\Input\Input', array('test'), array(), '', false);
		$config = new Registry(array('foo' => 'bar'));

		$instance = new ConcreteBase($mockInput, $config);

		$this->assertEquals('bar', $instance->get('foo', 'car'), 'Checks a known configuration setting is returned.');
		$this->assertEquals('car', $instance->get('goo', 'car'), 'Checks an unknown configuration setting returns the default.');
	}

	/**
	 * Tests the Joomla\Application\AbstractApplication::getLogger for a NullLogger.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetNullLogger()
	{
		$logger = $this->instance->getLogger();

		$this->assertInstanceOf(
			'Psr\\Log\\NullLogger',
			$logger,
			'When a logger has not been set, an instance of NullLogger should be returned.'
		);
	}

	/**
	 * Tests the set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::set
	 * @since   1.0
	 */
	public function testSet()
	{
		$mockInput = $this->getMock('Joomla\Input\Input', array('test'), array(), '', false);
		$config = new Registry(array('foo' => 'bar'));

		$instance = new ConcreteBase($mockInput, $config);

		$this->assertEquals('bar', $instance->set('foo', 'car'), 'Checks set returns the previous value.');

		$this->assertEquals('car', $instance->get('foo'), 'Checks the new value has been set.');
	}

	/**
	 * Tests the set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::setConfiguration
	 * @since   1.0
	 */
	public function testSetConfiguration()
	{
		$config = new Registry(array('foo' => 'bar'));

		$this->assertSame($this->instance, $this->instance->setConfiguration($config), 'Checks chainging.');
		$this->assertEquals('bar', $this->instance->get('foo'), 'Checks the configuration was set.');
	}

	/**
	 * Tests the Joomla\Application\AbstractApplication::setLogger and getLogger methods.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractApplication::setLogger
	 * @covers  Joomla\Application\AbstractApplication::getLogger
	 * @since   1.0
	 */
	public function testSetLogger()
	{
		$mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);

		$this->assertSame($this->instance, $this->instance->setLogger($mockLogger), 'Checks chainging.');
		$this->assertSame($mockLogger, $this->instance->getLogger(), 'Checks the get method.');
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		// Create the class object to be tested.
		$this->instance = new ConcreteBase;
	}
}
