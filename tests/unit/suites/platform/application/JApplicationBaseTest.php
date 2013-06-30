<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @var    JApplicationBaseInspector
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
	protected function setUp()
	{
		parent::setUp();

		// Create the class object to be tested.
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
		TestReflection::setValue('JEventDispatcher', 'instance', null);

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
		$this->class->loadDispatcher($this->getMockDispatcher());

		$this->assertAttributeInstanceOf(
			'JEventDispatcher',
			'dispatcher',
			$this->class,
			'Tests that the dispatcher object is the correct class.'
		);

		// Inject a mock value into the JEventDispatcher singleton.
		TestReflection::setValue('JEventDispatcher', 'instance', 'foo');
		$this->class->loadDispatcher();

		$this->assertEquals('foo', TestReflection::getValue($this->class, 'dispatcher'), 'Tests that we got the dispatcher from the factory.');
	}

	/**
	 * Tests the JApplicationBase::loadIdentity and JApplicationBase::getIdentity methods.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @covers  JApplicationBase::loadIdentity
	 * @covers  JApplicationBase::getIdentity
	 */
	public function testLoadGetIdentityCorrectClass()
	{
		$mock = $this->getMock('JUser', array(), array(), '', false);
		$this->class->loadIdentity($mock);

		$this->assertAttributeInstanceOf(
			'JUser',
			'identity',
			$this->class,
			'Tests that the identity object is the correct class.'
		);
	}
	/**
	 * Tests the JApplicationBase::loadIdentity and JApplicationBase::getIdentity methods.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JApplicationBase::loadIdentity
	 * @covers  JApplicationBase::getIdentity
	 */
	public function testLoadGetIdentityGetJUser()
	{
		$mock = $this->getMock('JUser', array(), array(), '', false);
		$this->class->loadIdentity($mock);

		$this->assertInstanceOf(
			'JUser',
			$this->class->getIdentity()
		);
	}

	/**
	 * Tests the JApplicationBase::loadIdentity and JApplicationBase::getIdentity methods.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JApplicationBase::loadIdentity
	 * @covers  JApplicationBase::getIdentity
	 */
	public function testLoadGetIdentity99()
	{
		// Mock the session.
		JFactory::$session = $this->getMockSession(array('get.user.id' => 99));

		$this->class->loadIdentity();

		$this->assertEquals(99, TestReflection::getValue($this->class, 'identity')->get('id'), 'Tests that we got the identity from the factory.');
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
