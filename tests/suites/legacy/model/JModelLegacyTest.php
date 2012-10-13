<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


/**
 * Stub for the testing JModelLegacy class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 * @since       12.3
 */
class TestModelLead extends JModelLegacy
{
}

/**
 * Stub for the testing JModelLegacy class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 * @since       12.3
 */
class RemodelModelRoom extends JModelLegacy
{
}

/**
 * Tests for the JModelLegacy class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 * @since       12.3
 */
class JModelLegacyTest extends TestCase
{
	/**
	 * @var    JModelLegacy
	 * @since  12.3
	 */
	protected $fixture;

	/**
	 * Setup each test.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function setUp()
	{
		parent::setUp();
		$this->fixture = JModelLegacy::getInstance('Lead', 'TestModel');
	}

	/**
	 * Method to tear down what was previously setup before each tests.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function tearDown()
	{
		$this->fixture = null;
		parent::tearDown();
	}

	/**
	 * Tests the getInstance method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::getInstance
	 * @since   12.3
	 */
	public function testGetInstance()
	{
		$this->assertTrue($this->fixture instanceof TestModelLead);

		$this->fixture = JModelLegacy::getInstance('Model', 'NonExistent');
		$this->assertTrue($this->fixture === false);
	}

	/**
	 * Tests the setState method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::setState
	 * @since   12.3
	 */
	public function testSetState()
	{
		$this->assertNull($this->fixture->setState('foo.bar', 'baz'));
		$this->assertTrue($this->fixture->setState('foo.bar', 'fuz') === 'baz');
	}

	/**
	 * Tests the getState method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::getState
	 * @since   12.3
	 */
	public function testGetState()
	{
		$state = $this->fixture->getState();
		$this->assertTrue($state instanceof JObject);

		$stateSet = TestReflection::getValue($this->fixture, '__state_set');
		$this->assertTrue($stateSet === true);

		$this->fixture->setState('foo.bar', 'baz');
		$this->assertTrue($this->fixture->getState('foo.bar') === 'baz');

		$this->assertTrue($this->fixture->getState('non.existent', 'defaultVal') === 'defaultVal');
		$this->assertNull($this->fixture->getState('non.existent'));
	}

	/**
	 * Tests the getDbo method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::getDbo
	 * @since   12.3
	 */
	public function testGetDbo()
	{
		$dbo = $this->fixture->getDbo();
		$this->assertTrue($dbo instanceof JDatabaseDriver);
	}

	/**
	 * Tests the setDbo method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::setDbo
	 * @since   12.3
	 */
	public function testSetDbo()
	{
		$this->fixture->setDbo(new stdClass);
		$this->assertTrue($this->fixture->getDbo() instanceof stdClass);
	}

	/**
	 * Tests the getName method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::getName
	 * @since   12.3
	 */
	public function testGetName()
	{
		$this->assertEquals('lead', $this->fixture->getName());
		$this->assertEquals('com_test', TestReflection::getValue($this->fixture, 'option'));

		$this->fixture = JModelLegacy::getInstance('Room', 'RemodelModel');
		$this->assertEquals('room', $this->fixture->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($this->fixture, 'option'));

		TestReflection::setValue($this->fixture, 'name', 'foo');
		$this->assertEquals('foo', $this->fixture->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($this->fixture, 'option'));
	}

	/**
	 * Tests the getTable method.
	 *
	 * @expectedException  Exception
	 * @todo               Implement actual testing for an instantiated JTable class.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::getTable
	 * @since   12.3
	 */
	public function testGetTable()
	{
		// Try to get a non-existent table
		$this->fixture->getTable();
	}

	/**
	 * Tests the addIncludePath method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::addIncludePath
	 * @since   12.3
	 */
	public function testAddIncludePath()
	{
		$paths = JModelLegacy::addIncludePath('non/existent/path', 'prefix');

		$this->assertContains('non/existent/path', $paths);
	}

	/**
	 * Tests the addTablePath method.
	 *
	 * @return  void
	 *
	 * @covers  JModelLegacy::addTablePath
	 * @since   12.3
	 */
	public function testAddTablePath()
	{
		// Just make sure this is null, since nothing is returned
		$this->assertNull(JModelLegacy::addTablePath('dummy/path'));
	}
}
