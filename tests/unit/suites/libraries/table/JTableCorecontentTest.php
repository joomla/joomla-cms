<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTableMenuType.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       3.1
 */
class JTableCorecontentTest extends TestCaseDatabase
{
	/**
	 * @var    JTableCorecontent
	 * @since  3.1
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		// Get the mocks
		$this->saveFactoryState();

		JFactory::$session = $this->getMockSession();

		$this->object = new JTableCorecontent(self::$driver);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_assets', JPATH_TEST_DATABASE . '/jos_assets.csv');
		$dataSet->addTable('jos_ucm_base', JPATH_TEST_DATABASE . '/jos_ucm_base.csv');
		$dataSet->addTable('jos_ucm_content', JPATH_TEST_DATABASE . '/jos_ucm_content.csv');

		return $dataSet;
	}

	/**
	 * Test JTableCorecontent::bind
	 *
	 * @todo   Implement testBind().
	 *
	 * @return  void
	 */
	public function testBind()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests JTableCorecontent::check
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheck()
	{
		$table = $this->object;

		$this->assertThat(
			$table->check(),
			$this->isFalse(),
			'Line: ' . __LINE__ . ' Checking an empty table should fail.'
		);

		$table->core_title = 'Test Title';
		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' Checking the table with just the title should pass.'
		);

		$this->assertThat(
			$table->core_alias,
			$this->equalTo('test-title'),
			'Line: ' . __LINE__ . ' An empty alias should assume the value of the title.'
		);

		$table->core_body = '';
		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' Checking with an empty body should pass.'
		);

		$table->core_body = 'The intro text object.';
		$table->core_publish_down = '2001-01-01 00:00:00';
		$table->core_publish_up = JFactory::getDate();

		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' The check function should now complete without error.'
		);

		$this->assertThat(
			$table->core_publish_up,
			$this->equalTo('2001-01-01 00:00:00'),
			'Line: ' . __LINE__ . ' The check function should have reversed the previously set publish_up and down times.'
		);
	}

	/**
	 * Tests JTableCorecontent::store
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStore()
	{
		$table = $this->object;

		// Handle updating an existing article
		$table->load('3');
		$originalAlias = $table->core_alias;
		$table->core_title = 'New Title';
		/* Alias check has been removed
		$table->core_alias = 'archive-module';
		$this->assertFalse($table->store(), 'Line: ' . __LINE__ . ' Table store should fail due to a duplicated alias');*/
		$table->core_alias = 'article-categories-module';
		$this->assertTrue($table->store(), 'Line: ' . __LINE__ . ' Table store should succeed');
		$table->reset();
		$table->load('3');
		$this->assertEquals('New Title', $table->core_title, 'Line: ' . __LINE__ . ' Title should be updated');
		$this->assertEquals($originalAlias, $table->core_alias, 'Line: ' . __LINE__ . ' Alias should be the same as originally set');

		// Store a new article
		$table->load('8');
		$table->core_content_id = null;
		$table->core_title = 'Beginners (Copy)';
		$table->core_alias = 'beginners-copy';
		$table->core_created_time = null;
		$table->core_created_user_id = null;
		$this->assertTrue($table->store(), 'Line: ' . __LINE__ . ' Table store should succeed and insert a new record');
	}

	/**
	 * Tests JTableCorecontent::publish
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testPublish()
	{
		$table = $this->object;

		// Test with pk's in an array
		$pks = array('18', '31');
		$this->assertTrue($table->publish($pks, '0'), 'Line: ' . __LINE__ . ' Publish with an array of pks should work');
		$table->load('18');
		$this->assertEquals('0', $table->core_state, 'Line: ' . __LINE__ . ' Id 18 should be unpublished');
		$table->reset();
		$table->load('31');
		$this->assertEquals('0', $table->core_state, 'Line: ' . __LINE__ . ' Id 31 should be unpublished');
		$table->reset();

		// Test with a single pk
		$this->assertTrue($table->publish('32', '1'), 'Line: ' . __LINE__ . ' Publish with a single pk should work');
		$table->load('32');
		$this->assertEquals('1', $table->core_state, 'Line: ' . __LINE__ . ' Id 32 should be published');
	}
}
