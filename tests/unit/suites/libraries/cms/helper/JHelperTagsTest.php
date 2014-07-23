<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelperTags.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Helper
 * @since       3.1
 */
class JHelperTagsTest extends TestCaseDatabase
{
	/**
	 * @var    JHelperTags
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

		$this->object = new JHelperTags;
		JFactory::$application = $this->getMockApplication();
	}
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_tags', JPATH_TEST_DATABASE . '/jos_tags.csv');
		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');
		$dataSet->addTable('jos_content', JPATH_TEST_DATABASE . '/jos_content.csv');
		$dataSet->addTable('jos_content_types', JPATH_TEST_DATABASE . '/jos_content_types.csv');
		$dataSet->addTable('jos_ucm_content', JPATH_TEST_DATABASE . '/jos_ucm_content.csv');
		$dataSet->addTable('jos_ucm_base', JPATH_TEST_DATABASE . '/jos_ucm_base.csv');

		return $dataSet;
	}

	/**
	 * Tests the tagItem method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTagItem()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the tagItems method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTagItems()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the unTagItem method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUnTagItem()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTagsId method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagsId()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getItemTags method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetItemTags()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTagItemsQuery method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagItemsQuery()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTypes method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTypes()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the tagDeleteInstances method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTagDeleteInstances()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the searchTags method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testSearchTags()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the deleteTagData method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testDeleteTagData()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTagTreeArray method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagTreeArray()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the convertPathsToNames method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testConvertPathsToNames()
	{
		$this->markTestSkipped('Test not implemented.');
	}
}
