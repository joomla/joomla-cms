<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JPluginHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 * @since       3.2
 */
class JPluginHelperTest extends TestCaseDatabase
{
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

		parent::tearDown();
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

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * Test JPluginHelper::getLayoutPath
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetLayoutPath()
	{
		$this->assertEquals(
			JPluginHelper::getLayoutPath('content', 'pagenavigation'),
			JPATH_ROOT . '/plugins/content/pagenavigation/tmpl/default.php',
			'The default layout path for plg_content_pagenavigation should be returned'
		);
	}

	/**
	 * Test JPluginHelper::getPlugin
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetPlugin()
	{
		$this->markTestSkipped('Test fails unless run in isolation');

		$plugin = JPluginHelper::getPlugin('content', 'loadmodule');

		$this->assertEquals(
			$plugin->name,
			'loadmodule',
			'plg_content_loadmodule should return loadmodule as the name'
		);
	}

	/**
	 * Test JPluginHelper::getPlugin
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsEnabled()
	{
		$this->markTestSkipped('Test fails unless run in isolation');

		$this->assertTrue(
			(bool) JPluginHelper::isEnabled('content', 'loadmodule'),
			'plg_content_loadmodule should be enabled'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testImportPlugin().
	 *
	 * @return void
	 */
	public function testImportPlugin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
