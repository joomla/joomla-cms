<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/stubs/listmodeltest.php';
require_once __DIR__ . '/stubs/listmodelexceptiontest.php';

/**
 * Test class for JModelList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       3.4
 */
class JModelListTest extends TestCaseDatabase
{
	/**
	 * @var    JModelList
	 * @since  3.4
	 */
	public $object;

	/**
	 * Setup each test.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session = $this->getMockSession();

		$this->object = new \Joomla\CMS\MVC\Model\ListModel(array("filter_fields" => array("field1", "field2")));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox Test $filter_fields is set in constructor
	 */
	public function testFilterFieldsIsSetInConstructor()
	{
		$this->assertSame(array("field1", "field2"), TestReflection::getValue($this->object, 'filter_fields'));
	}

	/**
	 * Tests the __construct method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox Test $context is applied in constructor
	 */
	public function testContextIsSetInConstructor()
	{
		$this->assertSame("com_mvc.list", TestReflection::getValue($this->object, 'context'));
	}

	/**
	 * Tests the getActiveFilters method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox Active filters with string as state are returned correctly
	 */
	public function testActiveFiltersWithStringAsStateAreReturned()
	{
		$this->object->setState('filter.field1', 'string');
		$this->assertSame(array('field1' => 'string'), $this->object->getActiveFilters());
	}

	/**
	 * Tests the getActiveFilters method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox Active filters with numeric values as state are returned correctly
	 */
	public function testActiveFiltersWithNumericValuesAsStateAreReturned()
	{
		$this->object->setState('filter.field2', 5);
		$this->assertSame(array('field2' => 5), $this->object->getActiveFilters());
	}

	/**
	 * Tests the getStoreId method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getStoreId() includes all states to create the ID
	 */
	public function testGetStoreIdIncludesAllStates()
	{
		$method = new ReflectionMethod('JModelList', 'getStoreId');
		$method->setAccessible(true);

		TestReflection::setValue($this->object, '__state_set', true);
		$this->object->setState('list.start', '0');
		$this->object->setState('list.limit', '100');
		$this->object->setState('list.ordering', 'enabled');
		$this->object->setState('list.direction', 'ASC');

		$expectedString = "com_mvc.list:1:0:100:enabled:ASC";

		$this->assertSame(md5($expectedString), $method->invokeArgs($this->object, array('1')));
	}

	/**
	 * Tests the getListQuery method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getListQuery() returns an instance of JDatabaseQuery
	 */
	public function testGetListQueryReturnsQueryObject()
	{
		$method = new ReflectionMethod('JModelList', 'getListQuery');
		$method->setAccessible(true);

		$this->assertInstanceOf('JDatabaseQuery', $method->invoke($this->object));
	}

	/**
	 * Tests the _getListQuery method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox _getListQuery() returns an instance of JDatabaseQuery
	 */
	public function testGetListQueryCachingMethodReturnsQueryObject()
	{
		TestReflection::setValue($this->object, '__state_set', true);

		$method = new ReflectionMethod('JModelList', '_getListQuery');
		$method->setAccessible(true);

		$this->assertInstanceOf('JDatabaseQuery', $method->invoke($this->object));
	}

	/**
	 * Tests the _getListQuery method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox _getListQuery() stores query in object cache
	 */
	public function testListQueryGetsCached()
	{
		TestReflection::setValue($this->object, '__state_set', true);

		$method = new ReflectionMethod('JModelList', '_getListQuery');
		$method->setAccessible(true);

		$method->invoke($this->object);

		$this->assertInstanceOf('JDatabaseQuery', TestReflection::getValue($this->object, 'query'));
	}

	/**
	 * Tests the getTotal method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getTotal() returns false if an empty query is passed to the database object
	 */
	public function testGetTotalReturnsFalseOnEmptyQuery()
	{
		TestReflection::setValue($this->object, '__state_set', true);

		$this->assertFalse($this->object->getTotal());
	}

	/**
	 * Tests the getTotal method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getTotal() returns the correct total count
	 */
	public function testGetTotalReturnsCorrectTotalCount()
	{
		$object = new ListModelTest;

		// Don't run populateState as this blows up
		TestReflection::setValue($object, '__state_set', true);

		$this->assertEquals(4, $object->getTotal());
	}

	/**
	 * Tests the getTotal method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getTotal() stores the total count in the object cache
	 */
	public function testGetTotalStoresTotalCountInCache()
	{
		$object = new ListModelTest;

		// Don't run populateState as this blows up
		TestReflection::setValue($object, '__state_set', true);

		$object->getTotal();

		$objectCache = TestReflection::getValue($object, 'cache');

		$this->assertArrayHasKey('2815428b0b5aadc23925b739c08d4c96', $objectCache);
		$this->assertEquals(4, $objectCache['2815428b0b5aadc23925b739c08d4c96']);
	}

	/**
	 * Tests the getItems method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getItems() returns false on a database exception
	 */
	public function testGetItemsReturnsFalseOnDatabaseException()
	{
		$object = new ListModelExceptionTest;

		TestReflection::setValue($object, '__state_set', true);

		$this->assertFalse($object->getItems());
	}

	/**
	 * Tests the getItems method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getItems() returns correct results from the database
	 */
	public function testGetItemsReturnsItemsFromDatabase()
	{
		$object = new ListModelTest;

		// Don't run populateState as this blows up
		TestReflection::setValue($object, '__state_set', true);

		$itemsReturned = $object->getItems();
		$itemsExpected = array(
			(object) array("id" => 1),
			(object) array("id" => 2),
			(object) array("id" => 3),
			(object) array("id" => 4),
		);

		$this->assertCount(4, $itemsReturned);
		$this->assertEquals($itemsExpected, $itemsReturned);
	}

	/**
	 * Tests the getItems method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getItems() applies list.limit state correctly
	 */
	public function testGetItemsAppliesLimit()
	{
		$object = new ListModelTest;

		// Don't run populateState as this blows up
		TestReflection::setValue($object, '__state_set', true);
		$object->setState('list.start', 0);
		$object->setState('list.limit', 2);

		$itemsReturned = $object->getItems();
		$itemsExpected = array(
			(object) array("id" => 1),
			(object) array("id" => 2)
		);

		$this->assertCount(2, $itemsReturned);
		$this->assertEquals($itemsExpected, $itemsReturned);
	}

	/**
	 * Tests the getItems method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getItems() applies list.start state correctly
	 */
	public function testGetItemsAppliesOffset()
	{
		$object = new ListModelTest;

		// Don't run populateState as this blows up
		TestReflection::setValue($object, '__state_set', true);
		$object->setState('list.start', 2);
		$object->setState('list.limit', 2);

		$itemsReturned = $object->getItems();
		$itemsExpected = array(
			(object) array("id" => 3),
			(object) array("id" => 4)
		);

		$this->assertCount(2, $itemsReturned);
		$this->assertEquals($itemsExpected, $itemsReturned);
	}

	/**
	 * Tests the getItems method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getItems() stores result in object cache
	 */
	public function testGetItemsStoresItemsInObjectCache()
	{
		$object = new ListModelTest;

		// Don't run populateState as this blows up
		TestReflection::setValue($object, '__state_set', true);
		$object->setState('list.start', 0);
		$object->setState('list.limit', 1);

		$object->getItems();

		$objectCache = TestReflection::getValue($object, 'cache');

		$this->assertArrayHasKey('ecbe76894d32ce7af659e46be7f2a9f0', $objectCache);
		$this->assertEquals(array((object) array("id" => 1)), $objectCache['ecbe76894d32ce7af659e46be7f2a9f0']);
	}

	/**
	 * Tests the loadFormData method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox loadFormData() returns correct data from currently set states
	 */
	public function testListInfoIsAppendedToFormData()
	{
		$method = new ReflectionMethod('JModelList', 'loadFormData');
		$method->setAccessible(true);

		$applicationMock = $this->getMockCmsApp();
		$applicationMock->expects($this->once())
			->method('getUserState')
			->with(
				$this->equalTo('com_mvc.list'),
				$this->equalTo(new stdClass)
			)
			->will(
				$this->returnValue((object) array("foo" => "bar"))
			);

		JFactory::$application = $applicationMock;

		$this->object->setState('list.direction', 'ASC');
		$this->object->setState('list.limit', 30);
		$this->object->setState('list.ordering', 'enabled');
		$this->object->setState('list.start', 0);

		$expected = (object) array(
			"foo" => "bar",
			"list" => array(
				'direction' => 'ASC',
				'limit' => 30,
				'ordering' => 'enabled',
				'start' => 0
			)
		);

		// We've set the state manually, populateState call will overwrite it.
		TestReflection::setValue($this->object, '__state_set', true);

		$this->assertEquals($expected, $method->invoke($this->object));
	}

	/**
	 * Tests the loadFormData method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox loadFormData() does not overwrite passed "list" data from the form
	 */
	public function testLoadFormDataDoesNotOverwriteListInfo()
	{
		$method = new ReflectionMethod('JModelList', 'loadFormData');
		$method->setAccessible(true);

		$data = (object) array("foo" => "bar", "list" => "foobar");

		$applicationMock = $this->getMockCmsApp();
		$applicationMock->expects($this->once())
			->method('getUserState')
			->with(
				$this->equalTo('com_mvc.list'),
				$this->equalTo(new stdClass)
			)
			->will($this->returnValue($data));

		JFactory::$application = $applicationMock;

		$this->assertEquals($data, $method->invoke($this->object));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() applies filters passed from getUserStateFromRequest()
	 */
	public function testPopulateStateAppliesFilters()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Simulates filter data
		$data = array(
			"filter1" => "value1",
			"filter2" => "value2"
		);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('global.list.limit'), $this->equalTo('limit'), $this->equalTo(null), $this->equalTo('uint')),
				array($this->equalTo('com_mvc.list.ordercol'), $this->equalTo('filter_order'), $this->equalTo('col'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.orderdirn'), $this->equalTo('filter_order_Dir'), $this->equalTo('ASC'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					$data,
					false,
					30,
					'col',
					'ASC',
					0
				)
			);

		// Override JFactory with our Mock
		JFactory::$application = $applicationMock;

		// Call the actual method and pass default values for ordering and order direction
		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Check if all user inputs have been applied correctly
		$this->assertEquals('value1', $this->object->getState('filter.filter1'));
		$this->assertEquals('value2', $this->object->getState('filter.filter2'));
		$this->assertEquals(30, $this->object->getState('list.limit'));
		$this->assertEquals('col', $this->object->getState('list.ordering'));
		$this->assertEquals('ASC', $this->object->getState('list.direction'));
		$this->assertEquals(0, $this->object->getState('list.start'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() makes correct use of the column whitelist for order columns
	 */
	public function testPopulateStateUsesWhitelistForOrderColumn()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('global.list.limit'), $this->equalTo('limit'), $this->equalTo(null), $this->equalTo('uint')),
				array($this->equalTo('com_mvc.list.ordercol'), $this->equalTo('filter_order'), $this->equalTo('inwhitelist'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.orderdirn'), $this->equalTo('filter_order_Dir'), $this->equalTo('ASC'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					false,
					30,
					// Returning a column name that is not on the whitelist
					'notinwhitelist',
					'ASC',
					0
				)
			);

		JFactory::$application = $applicationMock;

		// Set up the whitelist of valid order columns
		TestReflection::setValue($this->object, 'filter_fields', array('inwhitelist'));

		// Call the actual method and pass default values for ordering and order direction
		$method->invokeArgs($this->object, array('inwhitelist', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Check if all user inputs have been applied correctly
		$this->assertEquals('inwhitelist', $this->object->getState('list.ordering'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() fixes an invalid order direction passed by getUserStateFromRequest()
	 */
	public function testPopulateStateFixedInvalidOrderDirection()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('global.list.limit'), $this->equalTo('limit'), $this->equalTo(null), $this->equalTo('uint')),
				array($this->equalTo('com_mvc.list.ordercol'), $this->equalTo('filter_order'), $this->equalTo('col'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.orderdirn'), $this->equalTo('filter_order_Dir'), $this->equalTo('ASC'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					false,
					30,
					'col',
					// Returning an invalid value for order direction
					'INVALID',
					0
				)
			);

		JFactory::$application = $applicationMock;

		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Make sure that the invalid value is overriden by the value from the constructor
		$this->assertEquals('ASC', $this->object->getState('list.direction'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() supports the legacy filter_order and filter_order_Dir inputs
	 */
	public function testPopulateStateSupportsOldFilterOrder()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('global.list.limit'), $this->equalTo('limit'), $this->equalTo(null), $this->equalTo('uint')),
				array($this->equalTo('com_mvc.list.ordercol'), $this->equalTo('filter_order'), $this->equalTo('col'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.orderdirn'), $this->equalTo('filter_order_Dir'), $this->equalTo('ASC'), $this->equalTo('none')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					false,
					30,
					'col',
					'ASC',
					0
				)
			);

		// Simulate user input
		$applicationMock->input = new JInput(array());
		$applicationMock->input->set('filter_order', 'usercol');
		$applicationMock->input->set('filter_order_Dir', 'DESC');

		JFactory::$application = $applicationMock;

		// Add the usercol to the column whitelist
		TestReflection::setValue($this->object, 'filter_fields', array('usercol'));

		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Ensure that the user input is used
		$this->assertEquals('DESC', $this->object->getState('list.direction'));
		$this->assertEquals('usercol', $this->object->getState('list.ordering'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() supports the the new "list." filters passed by getUserStateFromRequest()
	 */
	public function testPopulateStateSupportsListFilters()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		$data = array(
			"ordering" => "listcol",
			"direction" => "DESC",
			"limit" => "100",
			"foo" => "bar",
			"select" => "foo"
		);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					$data,
					0
				)
			);

		JFactory::$application = $applicationMock;

		// Add the usercol to the column whitelist
		TestReflection::setValue($this->object, 'filter_fields', array('listcol'));

		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Ensure that the user input is used
		$this->assertEquals('DESC', $this->object->getState('list.direction'));
		$this->assertEquals('listcol', $this->object->getState('list.ordering'));
		$this->assertEquals('bar', $this->object->getState('list.foo'));
		$this->assertEquals('100', $this->object->getState('list.limit'));
		$this->assertNull($this->object->getState('list.select'), 'The list blacklist does not allow this variable to be set.');
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() supports the "fullordering" syntax
	 */
	public function testPopulateStateSupportsFullordering()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Pass the fullordering
		$data = array(
			"fullordering" => "listcol DESC"
		);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					$data,
					0
				)
			);

		JFactory::$application = $applicationMock;

		// Add the listcol to the column whitelist
		TestReflection::setValue($this->object, 'filter_fields', array('listcol'));

		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Ensure that the user input is used
		$this->assertEquals('DESC', $this->object->getState('list.direction'));
		$this->assertEquals('listcol', $this->object->getState('list.ordering'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() fixes an invalid "fullordering" syntax
	 */
	public function testPopulateStateFixesInvalidFullordering()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Pass the invalid fullordering
		$data = array(
			"fullordering" => "listcol;"
		);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					$data,
					0
				)
			);

		JFactory::$application = $applicationMock;

		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Ensure that the user input is used
		$this->assertEquals('ASC', $this->object->getState('list.direction'));
		$this->assertEquals('col', $this->object->getState('list.ordering'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() fixes an invalid "ordering" and "direction" values passed by "list." states
	 */
	public function testPopulateStateFixesInvalidOrderValuesFromList()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		// Pass the invalid values
		$data = array(
			"ordering" => "invalidcol",
			"direction" => "invaliddir"
		);

		// Set up a quite complex mock object that checks if the correct calls are made and simulates the user output
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserStateFromRequest')
			->withConsecutive(
				array($this->equalTo('com_mvc.list.filter'), $this->equalTo('filter'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.list'), $this->equalTo('list'), $this->equalTo(array()), $this->equalTo('array')),
				array($this->equalTo('com_mvc.list.limitstart'), $this->equalTo('limitstart'), $this->equalTo(0))
			)
			->will(
				$this->onConsecutiveCalls(
					array(),
					$data,
					0
				)
			);

		JFactory::$application = $applicationMock;

		$method->invokeArgs($this->object, array('col', 'ASC'));

		// This stops populate state from being called again
		TestReflection::setValue($this->object, '__state_set', true);

		// Ensure that the user input is used
		$this->assertEquals('ASC', $this->object->getState('list.direction'));
		$this->assertEquals('col', $this->object->getState('list.ordering'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox populateState() returns 0 without an applied context
	 */
	public function testPopulateStateReturnsZeroWithoutContext()
	{
		$method = new ReflectionMethod('JModelList', 'populateState');
		$method->setAccessible(true);

		TestReflection::setValue($this->object, 'context', false);
		TestReflection::setValue($this->object, '__state_set', true);

		$method->invoke($this->object);

		$this->assertEquals(0, $this->object->getState('list.start'));
		$this->assertEquals(0, $this->object->getState('list.limit'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getUserStates() returns default value
	 */
	public function testGetuserstateUsesDefault()
	{
		// Set up a Mock for JApplicationCms
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserState')
			->with(
				$this->equalTo('state.key')
			)
			->will(
				$this->returnValue(null)
			);

		$applicationMock->method('setUserState')
			->with(
				$this->equalTo('state.key'), $this->equalTo('defaultValue')
			);

		$applicationMock->input = new JInput(array());
		JFactory::$application = $applicationMock;

		$this->assertEquals('defaultValue', $this->object->getUserStateFromRequest('state.key', '', 'defaultValue'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getUserState() returns data from the current request if exisiting
	 */
	public function testGetuserstateUsesRequestData()
	{
		// Set up a Mock for JApplicationCms
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserState')
			->with(
				$this->equalTo('state.key')
			)
			->will(
				$this->returnValue(null)
			);

		$applicationMock->method('setUserState')
			->with(
				$this->equalTo('state.key'), $this->equalTo('requestValue')
			);

		$applicationMock->input = new JInput(array());
		$applicationMock->input->set('request.key', 'requestValue');

		JFactory::$application = $applicationMock;

		$this->assertEquals('requestValue', $this->object->getUserStateFromRequest('state.key', 'request.key'));
	}

	/**
	 * Tests the populateState method.
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox getUserState() supports the resetPage attribute
	 */
	public function testGetuserstateSupportsResetPage()
	{
		// Set up a Mock for JApplicationCms
		$applicationMock = $this->getMockCmsApp();
		$applicationMock->method('getUserState')
			->with(
				$this->equalTo('state.key')
			)
			->will(
				$this->returnValue(null)
			);

		$applicationMock->input = new JInput(array());
		$applicationMock->input->set('request.key', 'requestValue');

		JFactory::$application = $applicationMock;

		$this->object->getUserStateFromRequest('state.key', 'request.key', 'defaultValue', 'none', true);

		$this->assertEquals(0, JFactory::getApplication()->input->get('limistart'));
	}

	/**
	 * data provider for testGetStartCalculatesCorrectly
	 *
	 * @since   3.4
	 *
	 * @return array
	 */
	public function getStartDataProvider()
	{
		return array(
			array(0, 30, 87,  '30e29215b4fac06b4ea59894161c5b70F', 0),
			array(30, 30, 87, '7b2ec67b92bf302ff8c5a4ab575baf7f', 30),
			array(60, 30, 87, 'a061a820ad5a502c73bb4577849dc090', 60),
			array(67, 30, 87, '1d1114ae603b8e6ed4f536c7f1d0c827', 60),
			array(88, 30, 87, '9ea5981186b1bb5899d8bf4fc4d5e444', 60)
		);
	}
}
