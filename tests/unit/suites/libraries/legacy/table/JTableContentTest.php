<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTableContent.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       12.3
 */
class JTableContentTest extends TestCaseDatabase
{
	/**
	 * Object under test
	 *
	 * @var    JTableContent
	 * @since  11.4
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function setUp()
	{
		parent::setUp();

		// Get the mocks
		$this->saveFactoryState();

		JFactory::$session = $this->getMockSession();

		$this->object = new JTableContent(self::$driver);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   11.4
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_assets', JPATH_TEST_DATABASE . '/jos_assets.csv');
		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');
		$dataSet->addTable('jos_content', JPATH_TEST_DATABASE . '/jos_content.csv');
		$dataSet->addTable('jos_tags', JPATH_TEST_DATABASE . '/jos_tags.csv');

		return $dataSet;
	}

	/**
	 * Tests JTableContent::check
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testCheck()
	{
		$table = $this->object;

		$this->assertThat(
			$table->check(),
			$this->isFalse(),
			'Line: ' . __LINE__ . ' Checking an empty table should fail.'
		);

		$table->title = 'Test Title';
		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' Checking the table with just the title should pass.'
		);

		$this->assertThat(
			$table->alias,
			$this->equalTo('test-title'),
			'Line: ' . __LINE__ . ' An empty alias should assume the value of the title.'
		);

		$table->introtext = '';
		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' Checking with an empty introtext should pass.'
		);

		$table->introtext = 'The intro text object.';
		$table->publish_down = '2001-01-01 00:00:00';
		$table->publish_up = JFactory::getDate();

		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' The check function should now complete without error.'
		);

		$this->assertThat(
			$table->publish_up,
			$this->equalTo('2001-01-01 00:00:00'),
			'Line: ' . __LINE__ . ' The check function should have reversed the previously set publish_up and down times.'
		);
	}

	/**
	 * Tests JTableContent::store
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testStore()
	{
		$table = $this->object;

		// Handle updating an existing article
		$table->load('3');
		$originalAlias = $table->alias;
		$table->title = 'New Title';
		$table->alias = 'archive-module';
		$this->assertFalse($table->store(), 'Line: ' . __LINE__ . ' Table store should fail due to a duplicated alias');
		$table->alias = 'article-categories-module';
		$this->assertTrue($table->store(), 'Line: ' . __LINE__ . ' Table store should succeed');
		$table->reset();
		$table->load('3');
		$this->assertEquals('New Title', $table->title, 'Line: ' . __LINE__ . ' Title should be updated');
		$this->assertEquals($originalAlias, $table->alias, 'Line: ' . __LINE__ . ' Alias should be the same as originally set');

		// Store a new article
		$table->load('8');
		$table->id = null;
		$table->title = 'Beginners (Copy)';
		$table->alias = 'beginners-copy';
		$table->created = null;
		$table->created_by = null;
		$this->assertTrue($table->store(), 'Line: ' . __LINE__ . ' Table store should succeed and insert a new record');
	}

	/**
	 * Tests JTableContent::publish
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testPublish()
	{
		$table = $this->object;

		// Test with pk's in an array
		$pks = array('18', '31');
		$this->assertTrue($table->publish($pks, '0'), 'Line: ' . __LINE__ . ' Publish with an array of pks should work');
		$table->load('18');
		$this->assertEquals('0', $table->state, 'Line: ' . __LINE__ . ' Id 18 should be unpublished');
		$table->reset();
		$table->load('31');
		$this->assertEquals('0', $table->state, 'Line: ' . __LINE__ . ' Id 31 should be unpublished');
		$table->reset();

		// Test with a single pk
		$this->assertTrue($table->publish('32', '1'), 'Line: ' . __LINE__ . ' Publish with a single pk should work');
		$table->load('32');
		$this->assertEquals('1', $table->state, 'Line: ' . __LINE__ . ' Id 32 should be published');
	}
}
