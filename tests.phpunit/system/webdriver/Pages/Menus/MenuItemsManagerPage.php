<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end menu items manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class MenuItemsManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_menus&view=items']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_menus&view=items';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Menu' => 'menutype',
			'Max Levels' => 'filter_level',
			'Status' => 'filter_published',
			'Access' => 'filter_access',
			'Language' => 'filter_language',
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Publish' => 'toolbar-publish',
			'Unpublish' => 'toolbar-unpublish',
			'Check In' => 'toolbar-checkin',
			'Empty trash' => 'toolbar-delete',
			'Trash' => 'toolbar-trash',
			'Home' => 'toolbar-star',
			'Rebuild' => 'toolbar-refresh',
			'Batch' => 'toolbar-batch',
			'Help' => 'toolbar-help',
	);

	/**
	 * Array of submenu links used for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $submenu = array (
			'option=com_menus&view=menus',
	);

	/**
	 * Add a new menu item in the Menu Manager: Menu Items screen.
	 *
	 * @param string   $title          Menu Title field
	 * @param string   $menuItemType   One of the allowed Menu Item Types (Single Article, Featured Contacts, etc.)
	 * @param string   $menuLocation   Menu Location field
	 * @param array    $otherFields    associative array of other fields in the form label => value.
	 *
	 * Note that there a special field types for the request variable (e.g., article name or category name) which is required by some menu types.
	 * This can be designated in the $otherFields with any of the following labels: 'request', 'category', 'article', 'contact', 'newsfeed', 'weblink'.
	 * For example: array('article' => 'Australian Parks').
	 *
	 * @return  MenuItemsManagerPage
	 */
	public function addMenuItem($title='Test Menu Item', $menuItemType='List All Categories', $menuLocation = 'Main Menu', array $otherFields = array())
	{
		/* @var $menuItemEditPage MenuItemEditPage */
		$this->setFilter('Menu', $menuLocation);
		$this->clickButton('toolbar-new');
		$menuItemEditPage = $this->test->getPageObject('MenuItemEditPage');
		$menuItemEditPage->setMenuItemType($menuItemType);

		$fields = array('Menu title' => $title, 'Menu Location' => $menuLocation);

		if (count($otherFields) > 0)
		{
			$fields = array_merge($fields, $otherFields);
		}

		$menuItemEditPage->setFieldValues($fields);

		$menuItemEditPage->clickButton('toolbar-save');

		return $this->test->getPageObject('MenuItemsManagerPage');
	}

	/**
	 * Edit a menu item in the Menu Manager: Menu Items screen.
	 *
	 * @param string   $title          Menu Title field
	 * @param array    $fields         associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editMenuItem($title, $fields)
	{
		$this->clickItem($title);

		/* @var $menuItemEditPage MenuItemEditPage */
		$menuItemEditPage = $this->test->getPageObject('MenuItemEditPage');
		$menuItemEditPage->setFieldValues($fields);
		$menuItemEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('MenuItemsManagerPage');
		$this->searchFor();
	}

	/**
	 * function to get current value
	 *
	 * @return String
	 */
	public function getCurrentMenu()
	{
		$el = $this->driver->findElement(By::xPath("//div[@id='menutype_chzn']/a/span"));

		return $el->getText();
	}

	/**
	 * function to delete menu Item
	 *
	 * @param   String  $name  stores the name
	 *
	 * @return void
	 */
	public function trashAndDelete($name)
	{
		$currentMenu = $this->getCurrentMenu();
		$this->searchFor($name);
		$this->checkAll();
		$this->driver->findElement(By::id('filter_search'))->click();
		$this->clickButton('toolbar-trash');
		$this->test->getPageObject('MenuItemsManagerPage');
		$this->setFilter('Status', 'Trashed');
		$this->checkAll();
		$this->driver->findElement(By::id('filter_search'))->click();
		$this->clickButton('toolbar-delete');
		$this->test->getPageObject('MenuItemsManagerPage');
		$this->setFilter('Status', 'Select Status');
		$this->test->getPageObject('MenuItemsManagerPage');
	}
}
