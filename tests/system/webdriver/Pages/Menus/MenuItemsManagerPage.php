<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class MenuItemsManagerPage extends AdminManagerPage
{
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_menus&view=items']";
	protected $url = 'administrator/index.php?option=com_menus&view=items';

	public $filters = array(
			'Menu' => 'menutype',
			'Max Levels' => 'filter_level',
			'Status' => 'filter_published',
			'Access' => 'filter_access',
			'Language' => 'filter_language',
			);

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

	public $submenu = array (
			'option=com_menus&view=menus',
	);

	/**
	 * Add a new menu item in the Menu Manager: Menu Items screen.
	 *
	 * @param string   $menuItemType   One of the allowed Menu Item Types (Single Article, Featured Contacts, etc.)
	 * @param string   $title          Menu Title field
	 * @param string   $menuLocation   Menu Location field
	 * @param array    $otherFields    associative array of other fields in the form label => value.
	 *
	 * Note that there a special field types for the request variable (e.g., article name or category name) which is required by some menu types.
	 * This can be designated in the $otherFields with any of the following labels: 'request', 'category', 'article', 'contact', 'newsfeed', 'weblink'.
	 * For example: array('article' => 'Australian Parks').
	 *
	 * @return  MenuItmesManagerPage
	 */
	public function addMenuItem($title='Test Menu Item', $menuItemType='List All Categories', $menuLocation = 'Main Menu', array $otherFields = array())
	{
		/* @var $menuItemEditPage MenuItemEditPage */
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

}