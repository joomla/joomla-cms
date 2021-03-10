<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

JLoader::register('BaseModel', __DIR__ . '/stubs/tbase.php');

/**
 * Tests for the JViewBase class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mapper
 * @since       3.0.0
 */
class JModelBaseTest extends TestCase
{
	/**
	 * @var    BaseModel
	 * @since  3.0.0
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function test__construct()
	{
		// @codingStandardsIgnoreStart
		// @todo check the instantiating new classes without brackets sniff
		$this->assertEquals(new Registry, $this->_instance->getState(), 'Checks default state.');
		// @codingStandardsIgnoreEnd

		$state = new Registry(array('foo' => 'bar'));
		$class = new BaseModel($state);
		$this->assertEquals($state, $class->getState(), 'Checks state injection.');
	}

	/**
	 * Tests the getState method.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
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
	 * @since   3.0.0
	 */
	public function testSetState()
	{
		$state = new Registry(array('foo' => 'bar'));
		$this->_instance->setState($state);
		$this->assertSame($state, $this->_instance->getState());
	}

	/**
	 * Tests the loadState method.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function testLoadState()
	{
		$this->assertInstanceOf('\\Joomla\\Registry\\Registry', TestReflection::invoke($this->_instance, 'loadState'));
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_instance = new BaseModel;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->_instance);
		parent::tearDown();
	}
}
