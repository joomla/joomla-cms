<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTableCorecontent.
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

		$this->object = new JTableCorecontent(static::$driver);
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
		unset($this->object);
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
	 * Tests JTableCorecontent::check with an empty dataset
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckFailsWithAnEmptyDataSet()
	{
		$this->assertFalse($this->object->check());
	}

	/**
	 * Tests JTableCorecontent::check
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckSucceedsWithMinimumData()
	{
		$this->object->core_title = 'Test Title';
		$this->assertTrue($this->object->check());
	}

	/**
	 * Tests JTableCorecontent::check
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckCorrectlyCreatesTheItemAlias()
	{
		$this->object->core_title = 'Test Title';
		$this->object->check();
		$this->assertSame('test-title', $this->object->core_alias);
	}

	/**
	 * Tests JTableCorecontent::check
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckCorrectlyValidatesInjectedData()
	{
		$this->object->core_title = 'Test Title';
		$this->object->core_body = 'The intro text object.';
		$this->object->core_publish_down = '2001-01-01 00:00:00';
		$this->object->core_publish_up = JFactory::getDate();

		$this->assertTrue($this->object->check());
		$this->assertEquals(
			'2001-01-01 00:00:00',
			$this->object->core_publish_up,
			'The check function should swap the dates if a later date is injected into publish_down than that in publish_up'
		);
	}

	/**
	 * Tests JTableCorecontent::store
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStoreCorrectlyUpdatesAnExistingRecord()
	{
		// Handle updating an existing article
		$this->object->load('3');
		$originalAlias            = $this->object->core_alias;
		$this->object->core_title = 'New Title';
		$this->object->core_alias = 'article-categories-module';
		$this->assertTrue($this->object->store());
		$this->object->reset();
		$this->object->load('3');
		$this->assertEquals('New Title', $this->object->core_title);
		$this->assertEquals($originalAlias, $this->object->core_alias);
	}

	/**
	 * Tests JTableCorecontent::store
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStoreCorrectlyCreatesANewRecord()
	{
		$this->object->load('8');
		$this->object->core_content_id = null;
		$this->object->core_title = 'Beginners (Copy)';
		$this->object->core_alias = 'beginners-copy';
		$this->object->core_created_time = null;
		$this->object->core_created_user_id = null;
		$this->assertTrue($this->object->store());
	}

	/**
	 * Tests JTableCorecontent::publish
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testPublishWithMultipleKeys()
	{
		$pks = array('18', '31');
		$this->assertTrue($this->object->publish($pks, '0'));
		$this->object->load('18');
		$this->assertEquals('0', $this->object->core_state);
	}

	/**
	 * Tests JTableCorecontent::publish
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testPublishWithSingleKey()
	{
		$this->assertTrue($this->object->publish(array('32'), '1'));
		$this->object->load('32');
		$this->assertEquals('1', $this->object->core_state);
	}
}
