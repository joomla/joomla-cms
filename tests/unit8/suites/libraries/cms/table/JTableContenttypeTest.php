<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	protected function setUp(): void
	{
		parent::setUp();

		$this->object = new JTableContenttype(static::$driver);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown(): void
	{
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_content_types', JPATH_TEST_DATABASE . '/jos_content_types.csv');

		return $dataSet;
	}

	/**
	 * Tests JTableContenttype::check with an empty dataset
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckFailsWithAnEmptyDataSet()
	{
		$this->expectException(\UnexpectedValueException::class);
		$this->expectExceptionMessage('The title is empty');
		$this->object->check();
	}

	/**
	 * Tests JTableContenttype::check with an empty alias
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCheckFailsWithAnEmptyAlias()
	{
		$this->expectException(\UnexpectedValueException::class);
		$this->expectExceptionMessage('The type_alias is empty');
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
