<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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

		$this->object = new JTableContenttype(self::$driver);
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
	 * Tests JTableContenttype::check
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheck()
	{
		$table = $this->object;

		try
		{
			$table->check();
		}
		catch (UnexpectedValueException $e)
		{
			$this->assertThat(
				$e->getMessage(),
				$this->equalTo('The title is empty')
			);
		}

		$table->type_title = 'Unit Test';

		try
		{
			$table->check();
		}
		catch (UnexpectedValueException $e)
		{
			$this->assertThat(
				$e->getMessage(),
				$this->equalTo('The type_alias is empty')
			);
		}

		$table->type_alias = 'com_unit.test';

		$this->assertThat(
			$table->check(),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' The check function should complete without issue.'
		);
	}

	/**
	 * Tests JTableContenttype::store
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStore()
	{
		$table = $this->object;

		// Store a new language
		$table->type_title = 'Content';
		$table->type_alias = 'com_content.article';
		$this->assertFalse(
			$table->store(),
			'Line: ' . __LINE__ . ' Table store should fail due to a duplicated type_alias field.'
		);
		$table->type_title = 'Unit Test Item';
		$table->type_alias = 'com_test.item';
		$this->assertTrue(
			$table->store(),
		'Line: ' . __LINE__ . ' Table store should successfully insert a record for the unit test item.'
		);
	}
}
