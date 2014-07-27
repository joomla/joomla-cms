<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Module
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JModuleHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Module
 * @since       3.2
 */
class JModuleHelperTest extends TestCaseDatabase
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
		$dataSet->addTable('jos_modules', JPATH_TEST_DATABASE . '/jos_modules.csv');
		$dataSet->addTable('jos_modules_menu', JPATH_TEST_DATABASE . '/jos_modules_menu.csv');

		return $dataSet;
	}

	/**
	 * Test JModuleHelper::getModule
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetModule()
	{
		$module = JModuleHelper::getModule('mod_search');

		$this->assertEquals(
			$module->id,
			'63',
			'mod_search is module ID 63'
		);
	}

	/**
	 * Test JModuleHelper::getModules
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetModules()
	{
		$modules = JModuleHelper::getModules('position-0');

		$this->assertEquals(
			count($modules),
			1,
			'There is 1 module in position-0'
		);

		$this->assertEquals(
			$modules[0]->id,
			'63',
			'mod_search is module ID 63'
		);
	}

	/**
	 * Test JModuleHelper::isEnabled
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsEnabled()
	{
		$this->assertTrue(
			(bool) JModuleHelper::isEnabled('mod_search'),
			'mod_search should be enabled'
		);
	}

	/**
	 * Test JModuleHelper::renderModule
	 *
	 * @todo    Implement testRenderModule().
	 *
	 * @return  void
	 */
	public function testRenderModule()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JModuleHelper::getLayoutPath
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetLayoutPath()
	{
		$this->assertEquals(
			JModuleHelper::getLayoutPath('mod_search'),
			JPATH_ROOT . '/modules/mod_search/tmpl/default.php',
			'The default layout path for mod_search should be returned'
		);
	}
}
