<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/legacy/html/menu.php';

/**
 * Test class for JHtmlMenu.
 *
 * @since  11.1
 */
class JHtmlMenuTest extends TestCaseDatabase
{
	/**
	 * @var    JHtmlMenu
	 * @since  11.3
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function setUp()
	{
		parent::setUp();

		jimport('joomla.html.html');
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.3
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/suites/unit/joomla/html/html/testfiles/JHtmlTest.xml');
	}

	/**
	 * Tests the JHtmlMenu::menus method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JHtmlMenu::menus
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
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JHtmlMenu::menuitems
	 */
	public function testMenuitems()
	{
		$this->assertThat(
			JHtml::_('select.options', JHtml::_('menu.menuitems'), array('published' => '1')),
			$this->StringContains('<option value="mainmenu.101">- Home</option>')
		);
	}

	/**
	 * @todo Implement testMenuitemlist().
	 */
	public function testMenuitemlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testOrdering().
	 */
	public function testOrdering()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testLinkoptions().
	 */
	public function testLinkoptions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testTreerecurse().
	 */
	public function testTreerecurse()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}
}
