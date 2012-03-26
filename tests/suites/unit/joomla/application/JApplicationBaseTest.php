<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

include_once __DIR__ . '/stubs/JApplicationBaseInspector.php';

/**
 * Test class for JApplicationBase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.1
 */
class JApplicationBaseTest extends TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var    JApplicationCliInspector
	 * @since  11.3
	 */
	protected $class;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		parent::setUp();

		// Get a new JApplicationBaseInspector instance.
		$this->class = new JApplicationBaseInspector;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		// Reset the dispatcher instance.
		TestReflection::setValue('JDispatcher', 'instance', null);

		parent::tearDown();
	}

	/**
	 * Tests the JApplicationBase::loadDispatcher method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JApplicationBase::loadDispatcher
	 */
	public function testLoadDispatcher()
	{
		// Inject the mock dispatcher into the JDispatcher singleton.
		TestReflection::setValue('JDispatcher', 'instance', $this->getMockDispatcher());

		TestReflection::invoke($this->class, 'loadDispatcher');

		$this->assertAttributeInstanceOf(
			'JDispatcher',
			'dispatcher',
			$this->class,
			'Tests that the dispatcher object is the correct class.'
		);

		$this->assertEquals('ok', TestReflection::getValue($this->class, 'dispatcher')->test(), 'Tests that we got the dispatcher from the factory.');
	}

	/**
	 * Tests the JApplicationBase::registerEvent method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JApplicationBase::registerEvent
	 */
	public function testRegisterEvent()
	{
		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());

		$this->assertThat(
			$this->class->registerEvent('onJApplicationBaseRegisterEvent', 'function'),
			$this->identicalTo($this->class),
			'Check chaining.'
		);

		$this->assertArrayHasKey(
			'onJApplicationBaseRegisterEvent',
			TestMockDispatcher::$handlers,
			'Checks the events were passed to the mock dispatcher.'
		);
	}

	/**
	 * Tests the JApplicationBase::triggerEvent method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JApplicationBase::triggerEvent
	 */
	public function testTriggerEvent()
	{
		TestReflection::setValue($this->class, 'dispatcher', null);

		$this->assertNull($this->class->triggerEvent('onJApplicationBaseTriggerEvent'), 'Checks that for a non-dispatcher object, null is returned.');

		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());
		$this->class->registerEvent('onJApplicationBaseTriggerEvent', 'function');

		$this->assertEquals(
			array('function' => null),
			$this->class->triggerEvent('onJApplicationBaseTriggerEvent'),
			'Checks the correct dispatcher method is called.'
		);
	}
}
