<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlMenu.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlMenuTest extends TestCaseDatabase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
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
	 * @since   3.7.0
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

		$dataSet->addTable('jos_menu', JPATH_TEST_DATABASE . '/jos_menu.csv');
		$dataSet->addTable('jos_menu_types', JPATH_TEST_DATABASE . '/jos_menu_types.csv');

		return $dataSet;
	}

	/**
	 * Tests the JHtmlMenu::menus method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testMenus()
	{
		$this->assertContains('<option value="mainmenu">Main Menu</option>', JHtmlSelect::options(JHtmlMenu::menus(), 'value', 'text'));
	}

	/**
	 * Tests the JHtmlMenu::menuitems method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testMenuitems()
	{
		$this->assertContains(
			'<option value="mainmenu.435">- Home</option>',
			JHtmlSelect::options(JHtmlMenu::menuitems(), array('published' => '1'))
		);
	}
}
