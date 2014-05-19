<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractCliApplication;
use Joomla\Registry\Registry;
use Joomla\Test\TestConfig;
use Joomla\Test\TestHelper;

include_once __DIR__ . '/Stubs/ConcreteCli.php';

/**
 * Test class for Joomla\Application\AbstractCliApplication.
 *
 * @since  1.0
 */
class AbstractCliApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var   AbstractCliApplication
	 * @since  1.0
	 */
	protected $instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractCliApplication::__construct
	 * @since   1.0
	 */
	public function test__construct()
	{
		// @TODO Test that configuration data loaded.

		$this->assertGreaterThan(2001, $this->instance->get('execution.datetime'), 'Tests execution.datetime was set.');
		$this->assertGreaterThan(1, $this->instance->get('execution.timestamp'), 'Tests execution.timestamp was set.');

		// Test dependancy injection.

		$mockInput = $this->getMock('Joomla\Input\Cli', array('test'), array(), '', false);
		$mockInput
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$mockConfig = $this->getMock('Joomla\Registry\Registry', array('test'), array(null), '', true);

		$instance = new ConcreteCli($mockInput, $mockConfig);

		$input = TestHelper::getValue($instance, 'input');
		$this->assertEquals('ok', $input->test());
	}

	/**
	 * Tests the close method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractCliApplication::close
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
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setUp()
	{
		// Get a new ConcreteCli instance.
		$this->instance = new ConcreteCli;
	}

	/**
	 * Test the getOutput() method.
	 *
	 * @return void
	 */
	public function testGetOutput()
	{
		$this->assertInstanceOf(
			'Joomla\Application\Cli\Output\Stdout',
			$this->instance->getOutput()
		);
	}
}
