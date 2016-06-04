<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Menu: Add / Edit  Front End.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class MenuItemsManager0003Test extends JoomlaWebdriverTestCase
{
	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.0
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$this->doAdminLogin();
	}

	/**
	 * Do admin logout
	 *
	 * @return void
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * create menu item of type single Contact and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function  addMenu_SingleContact_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		/*Add category*/

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		/*add contact*/

		$contactManager = 'administrator/index.php?option=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $contactManager);

		$contactName = 'Contact' . $salt;
		$address = '10 Downing Street';
		$city = 'London';
		$country = 'England';
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName, array('Category' => $categoryName, 'Country' => $country, 'Address' => $address, 'City or Suburb' => $city));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$MenuItemManager = 'administrator/index.php?option=com_menus&view=items';

		/* add menu item of type single contact */

		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);
		$title = 'Menu Item' . $salt;
		$menuType = 'Single Contact';
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('Contact' => $contactName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		/*verify from front end*/

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		$this->siteHomePage->itemClick($title);
		$this->siteHomePage->itemExist($contactName, 'h2//span');

		/*delete the test elements*/
		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$this->contactManagerPage->trashAndDelete($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * create menu item of type Featured Contact and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function  addMenu_FeaturedContact_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$salt = rand();
		$contactManager = 'administrator/index.php?option=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $contactManager);

		/* add featured contact */

		$contactName1 = 'Contact_1' . $salt;
		$contactName2 = 'Contact_2' . $salt;
		$address = '11 Downing Street';
		$city = 'London';
		$country = 'England';
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName1), 'Test contact should not be present');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName2), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName1, array('Featured' => 'Yes', 'Country' => $country, 'Address' => $address, 'City or Suburb' => $city));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');

		$this->contactManagerPage->addContact($contactName2, array('Featured' => 'Yes', 'Country' => $country, 'Address' => $address, 'City or Suburb' => $city));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');

		$MenuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);

		/*add menu item of type featured contact*/

		$title = 'Menu Item' . $salt;
		$menuType = 'Featured Contact';
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation);
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		/*verify from the front end*/

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		$this->siteHomePage->itemClick($title);
		$arrayTitles = $this->siteHomePage->getContactTitles();
		$this->assertTrue(in_array($contactName1, $arrayTitles), 'Contact Must be present');
		$this->assertTrue(in_array($contactName2, $arrayTitles), 'Contact Must be present');

		/*delete the test elements*/
		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$this->contactManagerPage->trashAndDelete($contactName1);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName1), 'Test contact should not be present');

		$this->contactManagerPage->trashAndDelete($contactName2);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName2), 'Test contact should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * create menu item of type List All Contact Categories and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenu_ListAllContactCategories_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		/*add category */

		$salt = rand();
		$categoryName = 'category' . $salt;
		$categoryName1 = 'category_ABC1' . $salt;
		$categoryName2 = 'category_ABC2' . $salt;
		$parentCategory = '- ' . $categoryName;
		$desc = "Child Category";
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');

		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->categoryManagerPage->addCategory($categoryName1, $desc, array('Parent' => $parentCategory));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->categoryManagerPage->addCategory($categoryName2, $desc, array('Parent' => $parentCategory));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		/*add contact*/

		$contactManager = 'administrator/index.php?option=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$contactName1 = 'contact_ABC_1' . $salt;
		$contactName2 = 'contact_ABC_2' . $salt;
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName1), 'Test Contact should not be present');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName2), 'Test Contact should not be present');
		$category1 = "- - " . $categoryName1;
		$category2 = "- - " . $categoryName2;
		$this->contactManagerPage->addContact($contactName1, array('Category' => $category1));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'contact successfully saved') >= 0, 'Contact save should return success');
		$this->contactManagerPage->addContact($contactName2, array('Category' => $category2));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');

		/*add menuitem of type List All Contact Categories */

		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);

		$title = 'Menu Item' . $salt;
		$menuType = 'List All Contact Categories';
		$menuLocation = 'Main Menu';
		$metaDescription = 'Test menu item for web driver test.';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('category' => $categoryName, 'Meta Description' => $metaDescription));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		/*front end verification*/
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->siteHomePage->itemClick($title);
		$this->assertTrue($this->siteHomePage->itemExist($categoryName1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($categoryName2, 'a'));
		$this->siteHomePage->itemClick($categoryName1);
		$this->assertTrue($this->siteHomePage->itemExist($contactName1, 'a'));
		$this->siteHomePage->itemClick($title);
		$this->siteHomePage->itemClick($categoryName2);
		$this->assertTrue($this->siteHomePage->itemExist($contactName2, 'a'));

		/*delete all the test elements*/

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->contactManagerPage->trashAndDelete($contactName1);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName1), 'Test contact should not be present');
		$this->contactManagerPage->trashAndDelete($contactName2);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName2), 'Test contact should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage->trashAndDelete($categoryName1);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName2);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * create menu item of type List Contact in Category and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenu_ListContactInCategory_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		/*add test category*/

		$salt = rand();
		$categoryName1 = 'category_ABC1' . $salt;
		$categoryName2 = 'category_ABC2' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName1);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$this->categoryManagerPage->addCategory($categoryName2);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		/*add menu item of type List Contacts in a Category */

		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$menuType = 'List Contacts in a Category';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('category' => $categoryName1));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		/*create test contacts*/

		$contactManager = 'administrator/index.php?option=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$contactName1 = 'contact_ABC_1' . $salt;
		$contactName2 = 'contact_ABC_2' . $salt;
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName1), 'Test contact should not be present');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName2), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName1, array('Category' => $categoryName1));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'contact successfully saved') >= 0, 'contact save should return success');
		$this->contactManagerPage->addContact($contactName2, array('Category' => $categoryName1));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'contact successfully saved') >= 0, 'contact save should return success');

		$contactName3 = 'contact_ABC_3' . $salt;
		$contactName4 = 'contact_ABC_4' . $salt;
		$this->contactManagerPage = $this->getPageObject('contactManagerPage');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName3), 'Test contact should not be present');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName4), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName3, array('Category' => $categoryName2));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'contact successfully saved') >= 0, 'contact save should return success');
		$this->contactManagerPage->addContact($contactName4, array('Category' => $categoryName2));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'contact successfully saved') >= 0, 'contact save should return success');

		/*verify from the front end */

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		$this->siteHomePage->itemClick($title);
		$this->assertTrue($this->siteHomePage->itemExist($contactName1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($contactName2, 'a'));

		/*edit menu item and set category to category 2*/

		$this->doAdminLogin();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('category' => $categoryName2));

		/*front end verification*/

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		$this->siteHomePage->itemClick($title);
		$this->assertTrue($this->siteHomePage->itemExist($contactName3, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($contactName4, 'a'));

		/*delete the test elements*/

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$this->contactManagerPage->trashAndDelete($contactName1);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName1), 'Test contact should not be present');
		$this->contactManagerPage->trashAndDelete($contactName2);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName2), 'Test contact should not be present');
		$this->contactManagerPage->trashAndDelete($contactName3);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName3), 'Test contact should not be present');
		$this->contactManagerPage->trashAndDelete($contactName4);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName4), 'Test contact should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage->trashAndDelete($categoryName1);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName2);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}
}
