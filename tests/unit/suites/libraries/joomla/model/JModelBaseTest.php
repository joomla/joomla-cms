<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('BaseModel', __DIR__ . '/stubs/tbase.php');

/**
 * Tests for the JViewBase class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mapper
 * @since       12.1
 */
class JModelBaseTest extends TestCase
{
	/**
	 * @var    BaseModel
	 * @since  12.1
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JModelBase::__construct
	 * @since   12.1
	 */
	public function test__construct()
	{
		// @codingStandardsIgnoreStart
		// @todo check the instanciating new classes without brackets sniff
		$this->assertEquals(new JRegistry, $this->_instance->getState(), 'Checks default state.');
		// @codingStandardsIgnoreEnd

		$state = new JRegistry(array('foo' => 'bar'));
		$class = new BaseModel($state);
		$this->assertEquals($state, $class->getState(), 'Checks state injection.');
	}

	/**
	 * Tests the getState method.
	 *
	 * @return  void
	 *
	 * @covers  JModelBase::getState
	 * @since   12.1
	 */
	public function testGetState()
	{
		// Reset the state property to a known value.
		TestReflection::setValue($this->_instance, 'state', 'foo');

		$this->assertEquals('foo', $this->_instance->getState());
	}

	/**
	 * Tests the setState method.
	 *
	 * @return  void
	 *
	 * @covers  JModelBase::setState
	 * @since   12.1
	 */
	public function testSetState()
	{
		$state = new JRegistry(array('foo' => 'bar'));
		$this->_instance->setState($state);
		$this->assertSame($state, $this->_instance->getState());
	}

	/**
	 * Tests the loadState method.
	 *
	 * @return  void
	 *
	 * @covers  JModelBase::loadState
	 * @since   12.1
	 */
	public function testLoadState()
	{
		$this->assertInstanceOf('JRegistry', TestReflection::invoke($this->_instance, 'loadState'));
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

		$this->_instance = new BaseModel;
	}
}
