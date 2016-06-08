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
		$mockInput  = $this->getMock('JInput');
		$mockConfig = $this->getMock('Joomla\Registry\Registry');
		$object     = $this->getMockForAbstractClass('JApplicationBase', array($mockInput, $mockConfig));

		$this->assertAttributeSame($mockInput, 'input', $object);
		$this->assertAttributeSame($mockConfig, 'config', $object);
	}

	/**
	 * @testdox  Tests that close() exits the application with the given code
	 *
	 * @covers  JApplicationBase::close
	 */
	public function testClose()
	{
		$object = $this->getMockForAbstractClass('JApplicationBase', array(), '', false, true, true, array('close'));
		$object->expects($this->any())
			->method('close')
			->willReturnArgument(0);

		$this->assertSame(3, $object->close(3));
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
		$mockConfig = $this->getMock('Joomla\Registry\Registry', array('get'), array(array('foo' => 'bar')), '', true, true, true, false, true);

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
	 * @testdox  Tests that a default PSR-3 LoggerInterface object is returned.
	 *
	 * @covers  JApplicationBase::getLogger
	 */
	public function testGetLogger()
	{
		$this->assertInstanceOf('Psr\Log\NullLogger', $this->class->getLogger());
	}

	/**
	 * @testdox  Tests that the global dispatcher is loaded by loadDispatcher() when no object is injected.
	 *
	 * @covers  JApplicationBase::loadDispatcher
	 * @uses    JEventDispatcher
	 */
	public function testLoadDispatcherWithNoInjection()
	{
		$this->class->loadDispatcher();

		$this->assertAttributeInstanceOf('JEventDispatcher', 'dispatcher', $this->class);

		// Reset the global state for JEventDispatcher
		TestReflection::setValue('JEventDispatcher', 'instance', null);
	}

	/**
	 * @testdox  Tests that the injected dispatcher is stored to the application.
	 *
	 * @covers  JApplicationBase::loadDispatcher
	 */
	public function testLoadDispatcherWithInjection()
	{
		$dispatcher = $this->getMockDispatcher();

		$this->class->loadDispatcher($dispatcher);

		$this->assertAttributeSame($dispatcher, 'dispatcher', $this->class);
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
		// We need to mock JSession for this test, don't use the getMockSession() method since it has an inbuilt method to mock loading the user
		$this->saveFactoryState();

		JFactory::$session = $this->getMock('JSession');

		// Before running, this should be null
		$this->assertAttributeNotInstanceOf('JUser', 'identity', $this->class);

		// Validate method chaining
		$this->assertSame($this->class, $this->class->loadIdentity());

		// A JUser object should have been loaded
		$this->assertAttributeInstanceOf('JUser', 'identity', $this->class);

		// Restore the global state
		$this->restoreFactoryState();
	}

	/**
	 * @testdox  Tests that a JUser object is injected into the application.
	 *
	 * @covers  JApplicationBase::loadIdentity
	 */
	public function testLoadIdentityWithInjectedUser()
	{
		$mockUser = $this->getMock('JUser');

		// Validate method chaining
		$this->assertSame($this->class, $this->class->loadIdentity($mockUser));

		$this->assertAttributeSame($mockUser, 'identity', $this->class);
	}

	/**
	 * @testdox  Tests that an event is registered with the application dispatcher.
	 *
	 * @covers  JApplicationBase::registerEvent
	 * @uses    JApplicationBase::loadDispatcher
	 */
	public function testRegisterEvent()
	{
		// Inject the mock dispatcher into the application
		$this->class->loadDispatcher($this->getMockDispatcher());

		// Validate method chaining
		$this->assertSame($this->class, $this->class->registerEvent('onJApplicationBaseRegisterEvent', 'function'));

		// Validate the event was registered
		$this->assertArrayHasKey('onJApplicationBaseRegisterEvent', TestMockDispatcher::$handlers);
	}

	/**
	 * @testdox  Tests that data is set to the application configuration successfully.
	 *
	 * @covers  JApplicationBase::set
	 * @uses    JApplicationBase::get
	 * @uses    JApplicationBase::setConfiguration
	 */
	public function testSet()
	{
		$mockConfig = $this->getMock('Joomla\Registry\Registry', array('get', 'set'), array(array('foo' => 'bar')), '', true, true, true, false, true);

		$this->class->setConfiguration($mockConfig);

		$this->assertEquals('bar', $this->class->set('foo', 'car'), 'Checks set returns the previous value.');
		$this->assertEquals('car', $this->class->get('foo'), 'Checks the new value has been set.');
	}

	/**
	 * @testdox  Tests that the application configuration is overwritten successfully.
	 *
	 * @covers  JApplicationBase::setConfiguration
	 */
	public function testSetConfiguration()
	{
		$mockConfig = $this->getMock('Joomla\Registry\Registry');

		$this->class->setConfiguration($mockConfig);

		$this->assertAttributeSame($mockConfig, 'config', $this->class);
	}

	/**
	 * @testdox  Tests that a PSR-3 LoggerInterface object is correctly set to the application.
	 *
	 * @covers  JApplicationBase::setLogger
	 */
	public function testSetLogger()
	{
		$mockLogger = $this->getMockForAbstractClass('Psr\Log\AbstractLogger');

		$this->class->setLogger($mockLogger);

		$this->assertAttributeSame($mockLogger, 'logger', $this->class);
	}

	/**
	 * @testdox  Tests that an event is triggered with the application dispatcher.
	 *
	 * @covers  JApplicationBase::triggerEvent
	 * @uses    JApplicationBase::loadDispatcher
	 * @uses    JApplicationBase::registerEvent
	 */
	public function testTriggerEvent()
	{
		// Inject the mock dispatcher into the application
		$this->class->loadDispatcher($this->getMockDispatcher());

		// Register our event to be triggered
		$this->class->registerEvent('onJApplicationBaseTriggerEvent', 'function');

		// Validate the event was triggered
		$this->assertSame(array('function' => null), $this->class->triggerEvent('onJApplicationBaseTriggerEvent'));
	}

	/**
	 * @testdox  Tests that no event is triggered when the application does not have a dispatcher.
	 *
	 * @covers  JApplicationBase::triggerEvent
	 */
	public function testTriggerEventWithNoDispatcher()
	{
		// Validate the event was triggered
		$this->assertNull($this->class->triggerEvent('onJApplicationBaseTriggerEvent'));
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
}
