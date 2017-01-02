<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationBase.
 */
class JApplicationBaseTest extends TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var  JApplicationBase
	 */
	private $class;

	/**
	 * @testdox  Tests the constructor creates default object instances
	 *
	 * @covers  JApplicationBase::__construct
	 */
	public function test__constructDefaultBehaviour()
	{
		$object = $this->getMockForAbstractClass('JApplicationBase');

		$this->assertAttributeInstanceOf('JInput', 'input', $object);
		$this->assertAttributeInstanceOf('Joomla\Registry\Registry', 'config', $object);
	}

	/**
	 * @testdox  Tests the correct objects are stored when injected
	 *
	 * @covers  JApplicationBase::__construct
	 */
	public function test__constructDependencyInjection()
	{
		$mockInput  = $this->getMockBuilder('JInput')->getMock();
		$mockConfig = $this->getMockBuilder('Joomla\Registry\Registry')->getMock();
		$object     = $this->getMockForAbstractClass('JApplicationBase', array($mockInput, $mockConfig));

		$this->assertAttributeSame($mockInput, 'input', $object);
		$this->assertAttributeSame($mockConfig, 'config', $object);
	}

	/**
	 * @testdox  Tests that the application is executed successfully.
	 *
	 * @covers  JApplicationBase::doExecute
	 * @covers  JApplicationBase::execute
	 */
	public function testExecute()
	{
		// execute() has no return, just make sure the method runs
		$this->assertNull($this->class->execute());
	}

	/**
	 * @testdox  Tests that data is read from the application configuration successfully.
	 *
	 * @covers  JApplicationBase::get
	 * @uses    JApplicationBase::setConfiguration
	 */
	public function testGet()
	{
		// Build the mock object.
		$mockConfig  = $this->getMockBuilder('Joomla\Registry\Registry')
					->setMethods(array('get'))
					->setConstructorArgs(array(array('foo' => 'bar')))
					->setMockClassName('')
					->disableOriginalClone()
					->enableProxyingToOriginalMethods()
					->getMock();

		// Inject the mock config
		$this->class->setConfiguration($mockConfig);

		$this->assertSame('bar', $this->class->get('foo', 'car'), 'Checks a known configuration setting is returned.');
		$this->assertSame('car', $this->class->get('goo', 'car'), 'Checks an unknown configuration setting returns the default.');
	}

	/**
	 * @testdox  Tests that getIdentity() by default returns null.
	 *
	 * @covers  JApplicationBase::getIdentity
	 */
	public function testGetIdentity()
	{
		$this->assertNull($this->class->getIdentity());
	}

	/**
	 * @testdox  Tests that a JUser object is loaded into the application from the global factory.
	 *
	 * @covers  JApplicationBase::loadIdentity
	 * @uses    JFactory::getUser
	 * @uses    JUser
	 */
	public function testLoadIdentity()
	{
		// Before running, this should be null
		$this->assertAttributeNotInstanceOf('JUser', 'identity', $this->class);

		// Validate method chaining
		$this->assertSame($this->class, $this->class->loadIdentity());

		// A JUser object should have been loaded
		$this->assertAttributeInstanceOf('JUser', 'identity', $this->class);
	}

	/**
	 * @testdox  Tests that a JUser object is injected into the application.
	 *
	 * @covers  JApplicationBase::loadIdentity
	 */
	public function testLoadIdentityWithInjectedUser()
	{
		$mockUser = $this->getMockBuilder('JUser')->getMock();

		// Validate method chaining
		$this->assertSame($this->class, $this->class->loadIdentity($mockUser));

		$this->assertAttributeSame($mockUser, 'identity', $this->class);
	}

	/**
	 * @testdox  Tests that an event is registered with the application dispatcher.
	 *
	 * @covers  JApplicationBase::registerEvent
	 * @uses    JApplicationBase::setDispatcher
	 */
	public function testRegisterEvent()
	{
		// Inject the mock dispatcher into the application
		$this->class->setDispatcher($this->getMockDispatcher());

		// Validate method chaining
		$this->assertSame($this->class, $this->class->registerEvent('onJApplicationBaseRegisterEvent', [$this, 'eventCallback']));

		// Validate the event was registered
		$this->assertArrayHasKey('onJApplicationBaseRegisterEvent', TestMockDispatcher::$handlers);
	}

	/**
	 * @testdox  Tests that an event is triggered with the application dispatcher.
	 *
	 * @covers  JApplicationBase::triggerEvent
	 * @uses    JApplicationBase::setDispatcher
	 * @uses    JApplicationBase::registerEvent
	 */
	public function testTriggerEvent()
	{
		// Inject the mock dispatcher into the application
		$this->class->setDispatcher($this->getMockDispatcher());

		// Register our event to be triggered
		$this->class->registerEvent('onJApplicationBaseTriggerEvent', [$this, 'eventCallback']);

		// Validate the event was triggered
		$this->assertSame([], $this->class->triggerEvent('onJApplicationBaseTriggerEvent'));
		$this->assertTrue(in_array('onJApplicationBaseTriggerEvent', TestMockDispatcher::$triggered));
	}

	/**
	 * @testdox  Tests that no event is triggered when the application does not have a dispatcher.
	 *
	 * @covers  JApplicationBase::triggerEvent
	 */
	public function testTriggerEventWithNoDispatcher()
	{
		$this->class->setDispatcher($this->getMockDispatcher());

		// Validate the event was triggered
		$this->assertEmpty($this->class->triggerEvent('onJApplicationBaseTriggerEvent'));
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function setUp()
	{
		parent::setUp();

		// Create the class object to be tested.
		$this->class = $this->getMockForAbstractClass('JApplicationBase');
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->class);
		parent::tearDown();
	}

	/**
	 * Stub function used with testing event integrations
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function eventCallback()
	{
		// Stub for testing event integrations
	}
}
