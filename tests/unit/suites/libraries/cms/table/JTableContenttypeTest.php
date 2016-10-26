<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTableContenttype.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       3.1
 */
class JTableContenttypeTest extends TestCaseDatabase
{
	/**
	 * @var    JTableContenttype
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

		$this->object = new JTableContenttype(static::$driver);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
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

		$dataSet->addTable('jos_content_types', JPATH_TEST_DATABASE . '/jos_content_types.csv');

		return $dataSet;
	}

	/**
	 * Tests JTableContenttype::check with an empty dataset
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @expectedException         UnexpectedValueException
	 * @expectedExceptionMessage  The title is empty
	 */
	public function testCheckFailsWithAnEmptyDataSet()
	{
		$this->object->check();
	}

	/**
	 * Tests JTableContenttype::check with an empty alias
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @expectedException         UnexpectedValueException
	 * @expectedExceptionMessage  The type_alias is empty
	 */
	public function testCheckFailsWithAnEmptyAlias()
	{
		$this->object->type_title = 'Unit Test';
		$this->object->check();
	}

	/**
	 * Tests JTableContenttype::check
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckSucceedsWithMinimumRequiredData()
	{
		$this->object->type_title = 'Unit Test';
		$this->object->type_alias = 'com_unit.test';

		$this->assertTrue($this->object->check());
	}

	/**
	 * Tests JTableContenttype::store
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStoreFailsWithADuplicateAlias()
	{
		$this->object->type_title = 'Content';
		$this->object->type_alias = 'com_content.article';
		$this->assertFalse($this->object->store());
	}

	/**
	 * Tests JTableContenttype::store
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStoreSucceedsWithCorrectDatay()
	{
		$this->object->type_title = 'Unit Test Item';
		$this->object->type_alias = 'com_test.item';
		$this->assertTrue($this->object->store());
	}
}
