<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JUcmType.
 *
 * @package     Joomla.UnitTest
 * @subpackage  UCM
 * @since       3.2
 */
class JUcmTypeTest extends TestCaseDatabase
{
	/**
	 * @var    JUcmType
	 * @since  3.2
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

		$this->object = new JUcmType('com_content.article');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
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
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_content_types', JPATH_TEST_DATABASE . '/jos_content_types.csv');
		$dataSet->addTable('jos_languages', JPATH_TEST_DATABASE . '/jos_languages.csv');
		$dataSet->addTable('jos_ucm_base', JPATH_TEST_DATABASE . '/jos_ucm_base.csv');
		$dataSet->addTable('jos_ucm_content', JPATH_TEST_DATABASE . '/jos_ucm_content.csv');

		return $dataSet;
	}

	/**
	 * Tests the __construct()
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function test__construct()
	{
		$object = new JUcmType('com_content.article');

		$this->assertEquals(
			'Article',
			$object->type->type_title,
			'Ensure the type property title is Article'
		);
	}

	/**
	 * Tests the getType() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetType()
	{
		$this->assertEquals(
			1,
			$this->object->getType()->type_id,
			'Articles are type_id = 1'
		);
	}

	/**
	 * Tests the getTypeByAlias() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTypeByAlias()
	{
		$this->assertEquals(
			1,
			$this->object->getTypeByAlias('com_content.article')->type_id,
			'Articles are type_id = 1'
		);
	}

	/**
	 * Tests the getTypeId() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTypeId()
	{
		$this->assertEquals(
			1,
			$this->object->getTypeId('com_content.article'),
			'Articles are type_id = 1'
		);
	}
}
