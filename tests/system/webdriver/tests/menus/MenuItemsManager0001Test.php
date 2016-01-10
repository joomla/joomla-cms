<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Manager: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class MenuItemsManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     MenuItemsManagerPage
	 * @since   3.0
	 */
	protected $menuItemsManagerPage = null; /* Global configuration page*/

	/**
	 * Login to back end and navigate to menu item manager.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->menuItemsManagerPage = $cpPage->clickMenu('Main Menu', 'MenuItemsManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * check menu edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_MenuEditOpened()
	{
		$this->menuItemsManagerPage->clickButton('toolbar-new');
		$menuItemEditPage = $this->getPageObject('MenuItemEditPage');
		$tabIds = $menuItemEditPage->getTabIds();

		$menuItemEditPage->clickButton('toolbar-cancel');
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
	}

	/**
	 * check all input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->menuItemsManagerPage->clickButton('toolbar-new');
		$menuItemEditPage = $this->getPageObject('MenuItemEditPage');

		/* Keep the following line commented to make it easy to generate values for arrays as fields change.*/
		/* @var $menuItemEditPage MenuItemEditPage */
		/*$menuItemEditPage->printFieldArray($menuItemEditPage->getAllInputFields($menuItemEditPage->tabs));*/

		$testElements = $menuItemEditPage->getAllInputFields($menuItemEditPage->getTabIds());
		$actualFields = $this->getActualFieldsFromElements($testElements);

		$this->assertLessThanOrEqual($menuItemEditPage->inputFields, $actualFields);
		$menuItemEditPage->clickButton('toolbar-cancel');
		$this->menuItemsManagerPage = $this->getPageObject('menuItemsManagerPage');
	}

	/**
	 * check the available menu types
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getMenuItemTypes_ShouldMatchExpected()
	{
		$this->menuItemsManagerPage->clickButton('toolbar-new');
		$menuItemEditPage = $this->getPageObject('MenuItemEditPage');
		$actualMenuItemTypes = $menuItemEditPage->getMenuItemTypes();
		/* Keep the following lines commented. They make it easy to re-generate the array of menu types as more are added.*/
/* 		foreach ($actualMenuItemTypes as $array)
 		{
 			echo "array('group' => '" . $array['group'] . "', 'type' => '" . $array['type'] . "' ),\n";
 		}*/
		$count = count($actualMenuItemTypes);

		for ($i = 0; $i < $count; $i++)
		{
			$this->assertEquals($menuItemEditPage->menuItemTypes[$i], $actualMenuItemTypes[$i], 'Menu item type should match expected');
		}
	}

	/**
	 * add menu item with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_WithFieldDefaults_MenuItemAdded()
	{
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber('Test Menu Item'), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem();
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Menu successfully saved') >= 0, 'Menu save should return success');
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->assertTrue($this->menuItemsManagerPage->getRowNumber('Test Menu') > 0, 'Test menu should be in list');
		$this->menuItemsManagerPage->trashAndDelete('Test Menu');
		$this->menuItemsManagerPage->setFilter('Status', 'All');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber('Test Menu'), 'Test menu should not be present');
		$this->menuItemsManagerPage->searchFor();
	}

	/**
	 * add menu item of type single contact
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_SingleContact_MenuAdded()
	{
		$salt = rand();
		$title = 'Menu Item ' . $salt;
		$menuType = 'Single Contact';
		$itemName = 'Bananas';
		$menuLocation = 'About Joomla';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('contact' => $itemName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);
		$this->menuItemsManagerPage->setFilter('Menu', 'About Joomla');
		$this->menuItemsManagerPage->searchFor($title);
		$this->assertTrue($this->menuItemsManagerPage->getRowNumber($title) > 0, 'Test menu should be on the page');
		$this->menuItemsManagerPage->searchFor();
		$actualValues = $this->menuItemsManagerPage->getFieldValues('MenuItemEditPage', $title, array('Menu Title', 'Menu Item Type', 'contact'));
		$expectedValues = array ($title, $menuType, $itemName);
		$this->assertEquals($expectedValues, $actualValues, 'Actual values should match entered values');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->menuItemsManagerPage->searchFor($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->searchFor();
	}

	/**
	 * add menu item of type category blog
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_CategoryBlog_MenuAdded()
	{
		$salt = rand();
		$title = 'Menu Item ' . $salt;
		$menuType = 'Category Blog';
		$itemName = '- Joomla!';
		$menuLocation = 'Fruit Shop';
		$metaDescription = 'Test menu item for webdriver test.';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('category' => $itemName, 'Meta Description' => $metaDescription));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);
		$this->menuItemsManagerPage->setFilter('Menu', 'Fruit Shop');
		$this->menuItemsManagerPage->searchFor($title);
		$this->assertTrue($this->menuItemsManagerPage->getRowNumber($title) > 0, 'Test menu should be on the page');
		$this->menuItemsManagerPage->searchFor();
		$this->menuItemsManagerPage->setFilter('Menu', 'Fruit Shop');
		$actualValues = $this->menuItemsManagerPage->getFieldValues('MenuItemEditPage', $title, array('Menu Title', 'Menu Item Type', 'category'));
		$expectedValues = array ($title, $menuType, $itemName);
		$this->assertEquals($expectedValues, $actualValues, 'Actual values should match entered values');
		$this->menuItemsManagerPage->setFilter('Menu', 'Fruit Shop');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->menuItemsManagerPage->searchFor($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->searchFor();
	}

	/**
	 * edit values of input fields of menu items
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editMenuItem_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$title = 'Menu Item ' . $salt;
		$menuType = 'Single News Feed';
		$itemName = 'Joomla! Connect';
		$menuLocation = 'Top';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('newsfeed' => $itemName));

		$newTitle = 'New Menu Item ' . $salt;
		$newMenuType = 'List News Feeds in a Category';
		$newCategory = 'Uncategorised';
		$newMenuLocation = 'Main Menu';
		$this->menuItemsManagerPage->editMenuItem($title, array('Menu Title' => $newTitle, 'Menu Item Type' => $newMenuType, 'category' => $newCategory, 'Menu Location' => $newMenuLocation));

		$this->menuItemsManagerPage->setFilter('Menu', $newMenuLocation);
		$actualValues = $this->menuItemsManagerPage->getFieldValues('MenuItemEditPage', $newTitle, array('Menu Title', 'Menu Item Type', 'category', 'Menu Location'));
		$expectedValues = array ($newTitle, $newMenuType, $newCategory, $newMenuLocation);
		$this->assertEquals($expectedValues, $actualValues, 'Actual values should match entered values');
		$this->menuItemsManagerPage->setFilter('Menu', $newMenuLocation);
		$this->menuItemsManagerPage->trashAndDelete($newTitle);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($newTitle), 'Test menu item should not be present');
	}
}
