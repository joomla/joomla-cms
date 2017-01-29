<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/dbtestcomposite.php';

/**
 * Test class for JTable.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       12.3
 */
class JTableTest extends TestCaseDatabase
{
	/**
	 * @var    JTable
	 * @since  12.3
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		$mockApp = $this->getMockCmsApp();
		$mockApp->expects($this->any())
			->method('getDispatcher')
			->willReturn($this->getMockDispatcher());
		JFactory::$application = $mockApp;

		$this->object = new TableDbTestComposite(TestCaseDatabase::$driver);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function tearDown()
	{
		unset($this->object);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test for getFields method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetFields()
	{
		$this->assertEquals(
			array(
				'id1' => (object) array(
					'Field' => 'id1',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => 'PRI'
				),
				'id2' => (object) array(
					'Field' => 'id2',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => 'PRI'
				),
				'title' => (object) array(
					'Field' => 'title',
					'Type' => 'TEXT',
					'Null' => 'NO',
					'Default' => '\'\'',
					'Key' => ''
				),
				'asset_id' => (object) array(
					'Field' => 'asset_id',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => ''
				),
				'hits' => (object) array(
					'Field' => 'hits',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => ''
				),
				'checked_out' => (object) array(
					'Field' => 'checked_out',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => ''
				),
				'checked_out_time' => (object) array(
					'Field' => 'checked_out_time',
					'Type' => 'TEXT',
					'Null' => 'NO',
					'Default' => '\'0000-00-00 00:00:00\'',
					'Key' => ''
				),
				'published' => (object) array(
					'Field' => 'published',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => ''
				),
				'publish_up' => (object) array(
					'Field' => 'publish_up',
					'Type' => 'TEXT',
					'Null' => 'NO',
					'Default' => '\'0000-00-00 00:00:00\'',
					'Key' => ''
				),
				'publish_down' => (object) array(
					'Field' => 'publish_down',
					'Type' => 'TEXT',
					'Null' => 'NO',
					'Default' => '\'0000-00-00 00:00:00\'',
					'Key' => ''
				),
				'ordering' => (object) array(
					'Field' => 'ordering',
					'Type' => 'INTEGER',
					'Null' => 'NO',
					'Default' => '\'0\'',
					'Key' => ''
				),
				'params' => (object) array(
					'Field' => 'params',
					'Type' => 'TEXT',
					'Null' => 'NO',
					'Default' => '\'\'',
					'Key' => ''
				),
			),
			$this->object->getFields()
		);
	}

	/**
	 * Test for getInstance method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetInstance()
	{
		$object = JTable::getInstance('DbTestComposite', 'Table');

		$this->assertThat(
			$object,
			$this->isInstanceOf('TableDbTestComposite')
		);
	}

	/**
	 * Tests the JTable addIncludePath method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testAddIncludePath()
	{
		$result = JTable::addIncludePath();

		$reflection = new ReflectionClass('JTable');

		// Use of realpath to ensure test works for on all platforms
		$this->assertEquals(
			realpath(dirname($reflection->getFileName())),
			realpath($result[0]),
			'The default return from addIncludePath without additional parameters should be to the path where JTable was defined.'
		);

		// Test that adding paths that already exist don't get re-added
		$expected = array(
			'/dummy/',
			'dir/not/exist',
			realpath(JPATH_PLATFORM . '/src/Joomla/Cms/Table')
		);

		// Add dummy paths
		$paths = JTable::addIncludePath(array('dir/not/exist', '/dummy/'));

		// Re-add the returned paths - these shouldn't get added again.
		$paths = JTable::addIncludePath($paths);

		$this->assertEquals($expected, $paths);
	}

	/**
	 * Test for getTableName
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetTableName()
	{
		$this->assertEquals(
			'#__dbtest_composite',
			$this->object->getTableName()
		);
	}

	/**
	 * Test for getKeyName
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetKeyName()
	{
		$this->assertEquals(
			'id1',
			$this->object->getKeyName()
		);
	}

	/**
	 * Test for getKeyName returning an array
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetKeyNameComposite()
	{
		$this->assertEquals(
			array('id1', 'id2'),
			$this->object->getKeyName(true)
		);
	}

	/**
	 * Test for getDbo.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetDbo()
	{
		$this->assertThat(
			$this->object->getDbo(),
			$this->isInstanceOf('JDatabaseDriver')
		);
	}

	/**
	 * Test for setDbo method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetDbo()
	{
		$db = $this->getMockBuilder('JDatabaseDriver')
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->object->setDbo($db);

		$this->assertSame(
			$db,
			TestReflection::getValue($this->object, '_db')
		);
	}

	/**
	 * Test for reset method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testReset()
	{
		$this->object->title = 'My Title';
		$this->object->id1 = 25;
		$this->object->id2 = 50;

		$this->object->reset();

		// The regular fields should get reset
		$this->assertEquals(
			'\'\'',
			$this->object->title
		);

		// The primary keys should be left alone.
		$this->assertEquals(
			25,
			$this->object->id1
		);

		$this->assertEquals(
			50,
			$this->object->id2
		);
	}

	/**
	 * Test for bind method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testBind()
	{
		TestReflection::setValue($this->object, '_jsonEncode', array('params'));
		$this->object->bind(array('id1' => 25, 'id2' => 50, 'title' => 'My Title', 'params' => array('param1' => 'value1', 'param2' => 25)));

		$this->assertEquals(
			25,
			$this->object->id1
		);

		$this->assertEquals(
			50,
			$this->object->id2
		);

		$this->assertEquals(
			'My Title',
			$this->object->title
		);

		// Check the object is json encoded properly
		$this->assertEquals(
			'{"param1":"value1","param2":25}',
			$this->object->params,
			'The object should be json encoded'
		);
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   12.3
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_dbtest_composite', __DIR__ . '/stubs/jos_dbtest_composite.csv');
		$dataSet->addTable('jos_assets', __DIR__ . '/stubs/jos_assets_composite.csv');
		$dataSet->addTable('jos_session', __DIR__ . '/stubs/jos_session.csv');

		return $dataSet;
	}

	/**
	 * Test for load method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoad()
	{
		$this->object->load(array('id1' => 25, 'id2' => 50));

		$this->assertEquals(
			'My First Title',
			$this->object->title
		);
	}

	/**
	 * Tests the check method (for completeness).
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCheck()
	{
		$this->object->bind(array('id1' => null, 'id2' => 26, 'title' => 'My Title'));

		$this->assertTrue($this->object->check());
	}

	/**
	 * Tests the store method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testStoreInsert()
	{
		$this->assertEquals(2, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Pre-Condition");

		$this->object->bind(array('id1' => 38, 'id2' => 26, 'title' => 'My Title'));

		$this->object->store();

		$this->assertEquals(3, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Store failed.");
	}

	/**
	 * Tests the store method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testStoreUpdate()
	{
		$this->assertEquals(2, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Pre-Condition");

		$this->object->bind(array('id1' => 25, 'id2' => 50, 'title' => 'My testStoreInsert Title'));

		$this->object->store();

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 50));

		$this->AssertEquals('My testStoreInsert Title', $object2->title);
	}

	/**
	 * Tests the save method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSave()
	{
		$this->object = $this->getMockBuilder('TableDbTestComposite')
			->setConstructorArgs(array(TestCaseDatabase::$driver))
			->setMethods(array('bind', 'check', 'store', 'checkin', 'reorder', 'setError'))
			->getMock();

		$this->object->expects($this->once())
			->method('bind')
			->with(array('id1' => 75, 'id2' => 75, 'title' => 'My testSave Title'), '')
			->will($this->returnValue(true));

		$this->object->expects($this->once())
			->method('check')
			->with()
			->will($this->returnValue(true));

		$this->object->expects($this->once())
			->method('store')
			->with()
			->will($this->returnValue(true));

		$this->object->expects($this->never())
			->method('reorder');

		$this->object->save(array('id1' => 75, 'id2' => 75, 'title' => 'My testSave Title'));
	}

	/**
	 * Test for delete method with no primary key specified.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDelete()
	{
		$this->assertEquals(2, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Pre-Condition");

		$this->object->bind(array('id1' => 25, 'id2' => 50, 'title' => 'My Title'));

		$this->object->delete();

		$this->assertEquals(1, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Delete failed.");
	}

	/**
	 * Test for delete method with keys provided.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDeleteKeysProvided()
	{
		$this->assertEquals(2, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Pre-Condition");

		$this->object->delete(array('id1' => 25, 'id2' => 50));

		$this->assertEquals(1, $this->getConnection()->getRowCount('jos_dbtest_composite'), "Delete failed.");
	}

	/**
	 * Test for checkOut method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCheckOut()
	{
		$this->object->id1 = 25;
		$this->object->id2 = 50;

		$this->assertTrue($this->object->checkOut(5));

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 50));

		$this->AssertEquals('5', $object2->checked_out);

	}

	/**
	 * Test for checkIn method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCheckIn()
	{
		$this->object->id1 = 25;
		$this->object->id2 = 50;

		$this->assertTrue($this->object->checkOut(5));

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 50));

		$this->assertEquals(5, $object2->checked_out);

		$object2->checkIn();

		$this->object->load(array('id1' => 25, 'id2' => 50));

		$this->assertEquals(0, $this->object->checked_out);
	}

	/**
	 * Test for hasPrimaryKey method with table that has no auto increment and the result is true.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHasPrimaryKeyNoAutoincrementTrue()
	{
		$this->object->id1 = 25;
		$this->object->id2 = 50;

		$this->assertTrue($this->object->hasPrimaryKey());
	}

	/**
	 * Test for hasPrimaryKey method with table that has no auto increment and the result is false.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHasPrimaryKeyNoAutoincrementFalse()
	{
		$this->object->id1 = 75;
		$this->object->id2 = 75;

		$this->assertFalse($this->object->hasPrimaryKey());
	}

	/**
	 * Test for hasPrimaryKey method with table that has auto increment and the result is true.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHasPrimaryKeyAutoincrementTrue()
	{
		TestReflection::setValue($this->object, '_autoincrement', true);

		$this->object->id1 = 25;
		$this->object->id2 = 50;

		$this->assertTrue($this->object->hasPrimaryKey());
	}

	/**
	 * Test for hasPrimaryKey method with table that has auto increment and the result is false.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHasPrimaryKeyAutoincrementFalse()
	{
		TestReflection::setValue($this->object, '_autoincrement', true);

		$this->object->id1 = null;
		$this->object->id2 = null;

		$this->assertFalse($this->object->hasPrimaryKey());
	}

	/**
	 * Test for hit method - should always return true if there is no hits column.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHit()
	{
		$this->object->load(array('id1' => 25, 'id2' => 50));

		$this->assertTrue($this->object->hit());

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 50));

		$this->assertEquals(6, $object2->hits);
	}

	/**
	 * Test the isCheckedOut method when it is not checked out.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testIsCheckedOutFalse()
	{
		$this->object->load(array('id1' => 25, 'id2' => 50));

		$this->assertFalse($this->object->isCheckedOut());
	}

	/**
	 * Test the isCheckedOut method when it is checked out.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testIsCheckedOutTrue()
	{
		$this->object->load(array('id1' => 25, 'id2' => 50));
		$this->object->checkOut(5);

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 50));

		$this->assertTrue($object2->isCheckedOut());
	}

	/**
	 * Test the getNextOrder method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetNextOrder()
	{
		$this->assertEquals(3, $this->object->getNextOrder());
	}

	/**
	 * Test the reorder method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testReorder()
	{
		$this->object->load(array('id1' => 25, 'id2' => 51));
		$this->object->ordering = 25;
		$this->object->store();

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 51));

		$this->assertEquals(25, $object2->ordering, 'Preconditions');

		$this->object->reorder();

		$object2->load(array('id1' => 25, 'id2' => 51));

		$this->assertEquals(2, $object2->ordering, 'Elements did not get reordered');
	}

	/**
	 * Test the move method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMove()
	{
		$this->object->load(array('id1' => 25, 'id2' => 50));
		$this->object->move(1);

		$object2 = new TableDbTestComposite(TestCaseDatabase::$driver);

		$object2->load(array('id1' => 25, 'id2' => 51));

		$this->assertEquals(1, $object2->ordering);

		$object2->load(array('id1' => 25, 'id2' => 50));

		$this->assertEquals(2, $object2->ordering);
	}

	/**
	 * Test the publish method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testPublish()
	{
		$this->object->publish(
			array(array('id1' => 25, 'id2' => 50), array('id1' => 25, 'id2' => 51)),
			2
		);

		$this->object->load(array('id1' => 25, 'id2' => 50));
		$this->assertEquals(2, $this->object->published);

		$this->object->load(array('id1' => 25, 'id2' => 51));
		$this->assertEquals(2, $this->object->published);
	}
}
