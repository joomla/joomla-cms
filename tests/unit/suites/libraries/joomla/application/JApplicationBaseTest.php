<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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
	 * @var    JApplicationBase
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

		$this->saveFactoryState();

		// Create the class object to be tested.
		$this->class = $this->getMockForAbstractClass('JApplicationBase');
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

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the JApplicationBase::loadDispatcher method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testLoadDispatcher()
	{
		$this->class->loadDispatcher($this->getMockDispatcher());

		$this->assertAttributeInstanceOf('JEventDispatcher', 'dispatcher', $this->class);

		// Inject a mock value into the JEventDispatcher singleton.
		TestReflection::setValue('JEventDispatcher', 'instance', 'foo');
		$this->class->loadDispatcher();

		$this->assertEquals('foo', TestReflection::getValue($this->class, 'dispatcher'));
	}

	/**
	 * Tests the JApplicationBase::loadIdentity and JApplicationBase::getIdentity methods.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testLoadGetIdentityCorrectClass()
	{
		$mock = $this->getMock('JUser', array(), array(), '', false);
		$this->class->loadIdentity($mock);

		$this->assertAttributeInstanceOf('JUser', 'identity', $this->class);
	}
	/**
	 * Tests the JApplicationBase::loadIdentity and JApplicationBase::getIdentity methods.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadGetIdentityGetJUser()
	{
		$mock = $this->getMock('JUser', array(), array(), '', false);
		$this->class->loadIdentity($mock);

		$this->assertInstanceOf('JUser', $this->class->getIdentity());
	}

	/**
	 * Tests the JApplicationBase::loadIdentity and JApplicationBase::getIdentity methods.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadGetIdentity99()
	{
		// Mock the session.
		JFactory::$session = $this->getMockSession(array('get.user.id' => 99));

		$this->class->loadIdentity();

		$this->assertEquals(99, TestReflection::getValue($this->class, 'identity')->get('id'));
	}

	/**
	 * Tests the JApplicationBase::registerEvent method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testRegisterEvent()
	{
		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());

		$this->assertSame($this->class, $this->class->registerEvent('onJApplicationBaseRegisterEvent', 'function'));

		$this->assertArrayHasKey('onJApplicationBaseRegisterEvent', TestMockDispatcher::$handlers);
	}

	/**
	 * Tests the JApplicationBase::triggerEvent method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testTriggerEvent()
	{
		TestReflection::setValue($this->class, 'dispatcher', null);

		$this->assertNull($this->class->triggerEvent('onJApplicationBaseTriggerEvent'));

		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());
		$this->class->registerEvent('onJApplicationBaseTriggerEvent', 'function');

		$this->assertEquals(array('function' => null), $this->class->triggerEvent('onJApplicationBaseTriggerEvent'));
	}
}
