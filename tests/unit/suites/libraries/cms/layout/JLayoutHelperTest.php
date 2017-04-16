<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLayoutHelper.
 *
 * @since  3.3.7
 */
class JLayoutHelperTest extends TestCaseDatabase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since  3.3.7
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->saveFactoryState();
		JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @since  3.3.7
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since 3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * @testdox  Render a layout file using the JLayoutHelper::render() method.
	 *
	 * @since   3.3.7
	 */
	public function testRenderUsingHelperFile()
	{
		$options = new stdClass;
		$options->displayMenu = true;
		$options->list = array();
		$options->displayFilters = false;
		$options->filters = array();

		$this->assertStringEqualsFile(
			__DIR__ . '/output/submenu.txt',
			JLayoutHelper::render('submenu', $options, JPATH_TEST_STUBS . '/jlayout', array())
		);
	}
}
