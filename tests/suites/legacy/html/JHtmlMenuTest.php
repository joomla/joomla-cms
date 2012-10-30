<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/legacy/html/menu.php';
jimport('joomla.html.html');

/**
 * Test class for JHtmlMenu.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @since       11.3
 */
class JHtmlMenuTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @since   11.3
	 *
	 * @return  xml dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/suites/unit/joomla/html/html/testfiles/JHtmlTest.xml');
	}

	/**
	 * Tests the JHtmlMenu::menus method.
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testMenus()
	{
		$this->assertThat(
			JHtml::_('select.options', JHtml::_('menu.menus'), 'value', 'text'),
			$this->StringContains('<option value="mainmenu">Main Menu</option>')
		);
	}

	/**
	 * Tests the JHtmlMenu::menuitems method.
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testMenuitems()
	{
		$this->assertThat(
			JHtml::_('select.options', JHtml::_('menu.menuitems'), array('published' => '1')),
			$this->StringContains('<option value="mainmenu.101">- Home</option>')
		);
	}

	/**
	 * Test JHtmlMenu::menuItemList
	 *
	 * @todo Implement testMenuitemlist().
	 *
	 * @return  void
	 */
	public function testMenuitemlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JHtmlMenu::ordering
	 *
	 * @todo Implement testOrdering().
	 *
	 * @return  void
	 */
	public function testOrdering()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JHtmlMenu::linkOptions
	 *
	 * @todo Implement testLinkoptions().
	 *
	 * @return  void
	 */
	public function testLinkoptions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JHtmlMenu::treeRecurse
	 *
	 * @todo Implement testTreerecurse().
	 *
	 * @return  void
	 */
	public function testTreerecurse()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
