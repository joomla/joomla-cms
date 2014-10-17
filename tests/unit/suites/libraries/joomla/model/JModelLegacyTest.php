<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/lead.php';
require_once __DIR__ . '/stubs/name.php';
require_once __DIR__ . '/stubs/room.php';
require_once __DIR__ . '/stubs/constructorExceptionTest.php';

/**
 * Tests for the JModelLegacy class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       12.3
 */
class JModelLegacyTest extends TestCaseDatabase
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
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since    3.1
	 */
	public static function tearDownAfterClass()
	{
		// Reset JTable::$_includePaths
		TestReflection::setValue('JTable', '_includePaths', array());
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testConstructorSetsCorrectOption()
	{
		$this->assertEquals('com_test', TestReflection::getValue($this->fixture, 'option'));
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testConstructorSetsCorrectName()
	{
		$this->assertEquals('lead', TestReflection::getValue($this->fixture, 'name'));
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testConstructorSetsCorrectStateObject()
	{
		$state = TestReflection::getValue($this->fixture, 'state');
		$this->assertInstanceOf('JObject', $state);
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testConstructorSetsCorrectDatabaseObject()
	{
		$dbo = TestReflection::getValue($this->fixture, '_db');
		$this->assertInstanceOf('JDatabaseDriver', $dbo);
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testNewObjectHasEmptyStatesetFlag()
	{
		$this->assertNull(TestReflection::getValue($this->fixture, '__state_set'));
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testConstructorSetsDefaultCleanCacheEvent()
	{
		$this->assertEquals('onContentCleanCache', TestReflection::getValue($this->fixture, 'event_clean_cache'));
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testConstructorAppliesConfigCorrectly()
	{
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
	 * Test __constructor method
	 *
	 * @since   12.3
	 *
	 * @return  void
	 *
	 * @expectedException Exception
	 */
	public function testThatAnExecptionIsThrownWhenNoModelIsNotInTheName()
	{
		new Supercalifragilisticexpialigetisch;
	}

	/**
	 * Tests the getInstance method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testReturningAnInstanceOfAnExistingClassWorks()
	{
		$this->assertTrue($this->fixture instanceof TestModelLead);
	}

	/**
	 * Tests the getInstance method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGettingAnInstanceOfNonExistentClassReturnsFalse()
	{
		$this->fixture = JModelLegacy::getInstance('Model', 'NonExistent');
		$this->assertFalse($this->fixture);
	}

	/**
	 * Tests the getInstance method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGettingAnInstanceOfNonExistentClassFormAnExistingFile()
	{
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
	 *
	 * @testdox getState returns correct object
	 */
	public function testGetstateReturnsCorrectObject()
	{
		$state = $this->fixture->getState();
		$this->assertInstanceOf('JObject', $state);
	}

	/**
	 *Tests the getState method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 *
	 * @textdox getState sets internal __state_set flag
	 */
	public function testGetStateSetsInternalStatesetFlag()
	{
		$this->fixture->getState();
		$stateSet = TestReflection::getValue($this->fixture, '__state_set');
		$this->assertTrue($stateSet === true);
	}

	/**
	 *Tests the getState method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 *
	 * @textdox getState returns correct value
	 */
	public function testGetStateReturnsCorrectValue()
	{
		$this->fixture->setState('foo.bar', 'baz');
		$this->assertEquals('baz', $this->fixture->getState('foo.bar'));
	}

	/**
	 *Tests the getState method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 *
	 * @textdox getState returns default value
	 */
	public function testGetStateReturnsDefaultValue()
	{
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
	public function testGetThatDboReturnsCorrectObject()
	{
		$dbo = $this->fixture->getDbo();
		$this->assertInstanceOf('JDatabaseDriver', $dbo);
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
	 * Tests the getName method
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetNameReturnsCorrectName()
	{
		// Test default fixture
		$this->assertEquals('lead', $this->fixture->getName());
	}

	/**
	 * Ensure that the $name property is directly returned if set
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetNameUsesNamePropertyIfAvailable()
	{
		TestReflection::setValue($this->fixture, 'name', 'foo');
		$this->assertEquals('foo', $this->fixture->getName());
	}

	/**
	 * Test to get the name of a class with a lower 'model' in the class name
	 *
	 * This reflects an inconsistency in the current codebase of JModelLegacy and JControllerLegacy
	 * where classnames aren't treated case sensitive
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetNameOfClassWithLowercaseModelInName()
	{
		// Test creating fixture with model in class name, currently reflects an inconsistency in the codebase
		$this->fixture = JModelLegacy::getInstance('Room', 'RemodelModel');
		$this->assertEquals('modelroom', $this->fixture->getName());
		$this->assertEquals('com_remodel', TestReflection::getValue($this->fixture, 'option'));
	}

	/**
	 * Test getting the name of a class that does exist, but does not contain 'Model' (upper- or lowercase)
	 *
	 * @expectedException      Exception
	 * @expectedExceptionCode  500
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testNameOfExistingClassThatDoesNotContainModel()
	{
		$this->fixture = new NokeywordInName;
		$this->fixture->getName();
	}

	/**
	 * Tests the getTable method.
	 *
	 * @expectedException  Exception
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testExceptionIsThrownWhenGettingExplicitlyCalledNonExistentTable()
	{
		$this->fixture->getTable('Nonexistent');
	}

	/**
	 * Tests the getTable method.
	 *
	 * @expectedException  Exception
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testExceptionIsThrownWhenGettingNonExistentTable()
	{
		$this->fixture->getTable();
	}

	/**
	 * Tests the getTable method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetTableReturnsJtableObject()
	{
		$this->assertInstanceOf('JTableAsset', $this->fixture->getTable('Asset', 'JTable'));
	}

	/**
	 * Tests the addIncludePath method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testIncludePathIsAppended()
	{
		$paths = JModelLegacy::addIncludePath(__DIR__ . '/stubs');

		$this->assertContains(__DIR__ . DIRECTORY_SEPARATOR . 'stubs', $paths);
	}

	/**
	 * Tests the addTablePath method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testAddTablePathReturnsNull()
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
	public function testGeneratedFileNameForModelIsCorrect()
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
	public function testGetListPassesParamsToDatabaseDriver()
	{
		$method = new ReflectionMethod('TestModelLead', '_getList');
		$method->setAccessible(true);

		$dbMock = $this->getMockBuilder('JDatabaseDriverMysqli')
			->disableOriginalConstructor()
			->getMock();

		$dbMock->expects($this->once())
			->method('setQuery')
			->with(
				$this->equalTo('param1'),
				$this->equalTo('param2'),
				$this->equalTo('param3')
			);

		$dbMock->method('loadObjectList');

		TestReflection::setValue($this->fixture, '_db', $dbMock);

		$method->invokeArgs($this->fixture, array('param1', 'param2', 'param3'));
	}

	/**
	 * Tests the _getList method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetListReturnsDatabaseResult()
	{
		$method = new ReflectionMethod('TestModelLead', '_getList');
		$method->setAccessible(true);

		$dbMock = $this->getMockBuilder('JDatabaseDriverMysqli')
			->disableOriginalConstructor()
			->getMock();

		$dbMock->method('setQuery');

		$dbMock->method('loadObjectList')
			->willReturn('returnValue');

		TestReflection::setValue($this->fixture, '_db', $dbMock);

		$this->assertEquals('returnValue', $method->invokeArgs($this->fixture, array('')));
	}

	/**
	 * Tests the _getListCount method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetListCountPassesQueryStringToDatabase()
	{
		$method = new ReflectionMethod('TestModelLead', '_getListCount');
		$method->setAccessible(true);

		$dbMock = $this->getMockBuilder('JDatabaseDriverMysqli')
			->disableOriginalConstructor()
			->getMock();

		$dbMock->expects($this->once())
			->method('setQuery')
			->with(
				$this->equalTo('param1')
			);

		$dbMock->expects($this->once())
			->method('execute');


		$dbMock->expects($this->once())
			->method('getNumRows')
			->willReturn(1);

		TestReflection::setValue($this->fixture, '_db', $dbMock);

		$this->assertEquals(1, $method->invokeArgs($this->fixture, array('param1')));
	}

	/**
	 * Tests the _getListCount method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testGetListCountPassesQueryQueryToDatabase()
	{
		$method = new ReflectionMethod('TestModelLead', '_getListCount');
		$method->setAccessible(true);

		$queryMock = $this->getMock('JDatabaseQuery', array('select', 'clear'));
		$queryMock->method('clear')->will($this->returnSelf());

		TestReflection::setValue($queryMock, 'type', 'select');

		$dbMock = $this->getMockBuilder('JDatabaseDriverMysqli')
			->disableOriginalConstructor()
			->getMock();

		$dbMock->expects($this->once())
			->method('setQuery')
			->with(
				$this->equalTo($queryMock)
			);

		$dbMock->expects($this->once())
			->method('loadResult')
			->willReturn(1);

		TestReflection::setValue($this->fixture, '_db', $dbMock);

		$this->assertEquals(1, $method->invokeArgs($this->fixture, array($queryMock)));
	}

	/**
	 * Tests the _createTable method.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function testCreateTableReturnsCoreTableClass()
	{
		$method = new ReflectionMethod('TestModelLead', '_createTable');
		$method->setAccessible(true);

		$this->assertInstanceOf('JTableAsset', $method->invokeArgs($this->fixture, array('Asset', 'JTable')));
	}
}
