<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/lead.php';
require_once __DIR__ . '/stubs/name.php';
require_once __DIR__ . '/stubs/room.php';

/**
 * Tests for the JModelLegacy class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
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
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function setUp()
	{
		parent::setUp();
		$this->fixture = JModelLegacy::getInstance('Lead', 'TestModel');
	}

	/**
	 * Method to tear down what was previously setup before each tests.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function tearDown()
	{
		$this->fixture = null;
		parent::tearDown();
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function test__construct()
	{
		// Verify default fixture __construct
		$this->assertEquals('com_test', TestReflection::getValue($this->fixture, 'option'));
		$this->assertEquals('lead', TestReflection::getValue($this->fixture, 'name'));

		$state = TestReflection::getValue($this->fixture, 'state');
		$this->assertTrue($state instanceof JObject);

		$dbo = TestReflection::getValue($this->fixture, '_db');
		$this->assertTrue($dbo instanceof JDatabaseDriver);

		$this->assertNull(TestReflection::getValue($this->fixture, '__state_set'));
		$this->assertEquals('onContentCleanCache', TestReflection::getValue($this->fixture, 'event_clean_cache'));

		// Bypass JModelLegacy::getInstance to fully test __construct method with custom config
		$config = array(
			'name' => 'bash',
			'state' => 'foo',
			'dbo' => 'bar',
			'table_path' => 'baz',
			'ignore_request' => true,
			'event_clean_cache' => 'buz'
		);
		$this->fixture = new RemodelModelRoom($config);

		$this->assertEquals('com_remodel', TestReflection::getValue($this->fixture, 'option'));
		$this->assertEquals('bash', TestReflection::getValue($this->fixture, 'name'));
		$this->assertEquals('foo', TestReflection::getValue($this->fixture, 'state'));
		$this->assertEquals('bar', TestReflection::getValue($this->fixture, '_db'));
		$this->assertTrue(TestReflection::getValue($this->fixture, '__state_set'));
		$this->assertEquals('buz', TestReflection::getValue($this->fixture, 'event_clean_cache'));
	}

	/**
	 * Tests the getInstance method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetInstance()
	{
		$this->assertTrue($this->fixture instanceof TestModelLead);

		$this->fixture = JModelLegacy::getInstance('Model', 'NonExistent');
		$this->assertFalse($this->fixture);

		// Test getting an instance of a class from a file that exists, but a class that doesn't
		JModelLegacy::addIncludePath(__DIR__ . '/stubs');

		$this->fixture = JModelLegacy::getInstance('Barbaz', 'StubModel');
		$this->assertFalse($this->fixture);
	}

	/**
	 * Tests the setState method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testSetState()
	{
		$this->assertNull($this->fixture->setState('foo.bar', 'baz'));
		$this->assertEquals('baz', $this->fixture->setState('foo.bar', 'fuz'));
	}

	/**
	 * Tests the getState method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetState()
	{
		$state = $this->fixture->getState();
		$this->assertTrue($state instanceof JObject);

		$stateSet = TestReflection::getValue($this->fixture, '__state_set');
		$this->assertTrue($stateSet === true);

		$this->fixture->setState('foo.bar', 'baz');
		$this->assertEquals('baz', $this->fixture->getState('foo.bar'));

		$this->assertEquals('defaultVal', $this->fixture->getState('non.existent', 'defaultVal'));
		$this->assertNull($this->fixture->getState('non.existent'));
	}

	/**
	 * Tests the getDbo method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetDbo()
	{
		$dbo = $this->fixture->getDbo();
		$this->assertTrue($dbo instanceof JDatabaseDriver);
	}

	/**
	 * Tests the setDbo method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testSetDbo()
	{
		$this->fixture->setDbo(new stdClass);
		$this->assertTrue($this->fixture->getDbo() instanceof stdClass);
	}

	/**
	 * Tests the getName method.
	 *
	 * @expectedException      Exception
	 * @expectedExceptionCode  500
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetName()
	{
		$this->markTestSkipped('CMS has not yet received Platform updates to implement this test');

		// Test default fixture
		$this->assertEquals('lead', $this->fixture->getName());
		$this->assertEquals('com_test', TestReflection::getValue($this->fixture, 'option'));

		// Test creating fixture with model in class name
		$this->fixture = JModelLegacy::getInstance('Room', 'RemodelModel');
		$this->assertEquals('room', $this->fixture->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($this->fixture, 'option'));

		// Ensure that $name can be set properly, and doesn't change $option
		TestReflection::setValue($this->fixture, 'name', 'foo');
		$this->assertEquals('foo', $this->fixture->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($this->fixture, 'option'));

		// Test creating a non-existant class.
		$this->assertFalse(JModelLegacy::getInstance('Does', 'NotExist'));

		// Test creating class that does exist, but does not contain 'Model' (uppercase)
		$this->fixture = JModelLegacy::getInstance('NomodelInName');
		$this->fixture->getName();
	}

	/**
	 * Tests the getTable method.
	 *
	 * @expectedException  Exception
	 * @todo               Implement actual testing for an instantiated JTable class.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetTable()
	{
		// Try to get a non-existent table
		$this->fixture->getTable();
	}

	/**
	 * Tests the addIncludePath method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testAddIncludePath()
	{
		$paths = JModelLegacy::addIncludePath(__DIR__ . '/stubs');

		$this->assertContains(__DIR__ . '/stubs', $paths);

		$this->fixture = JModelLegacy::getInstance('Foobar', 'StubModel');
		$this->assertTrue($this->fixture instanceof StubModelFoobar);
	}

	/**
	 * Tests the addTablePath method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testAddTablePath()
	{
		// Just make sure this is null, since nothing is returned
		$this->assertNull(JModelLegacy::addTablePath('dummy/path'));
	}

	/**
	 * Tests the _createFileName method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function test_createFileName()
	{
		$method = new ReflectionMethod('TestModelLead', '_createFileName');
		$method->setAccessible(true);

		$this->assertEquals('foo.php', $method->invokeArgs($this->fixture, array('model', array('name' => 'foo'))));
	}

	/**
	 * Tests the _getList method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function test_getList()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the _getListCount method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function test_getListCount()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the _createTable method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function test_createTable()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the cleanCache method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testCleanCache()
	{
		$this->markTestIncomplete();
	}
}
