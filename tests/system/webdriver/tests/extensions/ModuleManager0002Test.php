<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;

/**
 * This class tests the  Modules: Add / Edit  Front End.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class ModuleManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var ModuleManagerPage
	 */
	protected $moduleManagerPage = null;

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
	 * creating a category with two child categories
	 * creating a module of type Article Categories and verifying it in front end.
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_ArticleCategories_ModuleAdded()
	{
		$cfg = new SeleniumConfig;
		$cpPage = $this->doAdminLogin();
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$moduleManager = 'administrator/index.php?option=com_modules';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$categoryName1 = 'category_ABC1_' . $salt;
		$categoryName2 = 'category_ABC2_' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');

		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$parentCategory = '- category_ABC' . $salt;
		$desc = "child test category";
		$this->categoryManagerPage->addCategory($categoryName1, $desc, array('Parent' => $parentCategory));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->categoryManagerPage->addCategory($categoryName2, $desc, array('Parent' => $parentCategory));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->moduleManagerPage = $cpPage->clickMenu('Module Manager', 'ModuleManagerPage');

		$title = 'Module test ' . $salt;
		$client = 'Site';
		$type = 'Articles - Categories';
		$position = 'position-3';
		$suffix = 'mySuffix';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix, 'Parent Category' => $categoryName);

		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->itemExist($title, 'h3'));
		$this->assertTrue($this->siteHomePage->itemExist($categoryName1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($categoryName2, 'a'));

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->trashAndDelete($categoryName1);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName2);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $moduleManager);
		$this->moduleManagerPage->setFilter('filter_client_id', $client);
		$this->moduleManagerPage->trashAndDelete($title);
		$this->moduleManagerPage->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
	}

	/**
	 * create a category with two child categories and add articles of the child categories
	 * create module of article categories and verify from front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_ArticleCategories_ModuleAdded_2()
	{
		$cfg = new SeleniumConfig;
		$cpPage = $this->doAdminLogin();
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$moduleManager = 'administrator/index.php?option=com_modules';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$categoryName1 = 'category_ABC1_' . $salt;
		$categoryName2 = 'category_ABC2_' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');

		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$parentCategory = '- category_ABC' . $salt;
		$desc = "child test category";
		$this->categoryManagerPage->addCategory($categoryName1, $desc, array('Parent' => $parentCategory));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->categoryManagerPage->addCategory($categoryName2, $desc, array('Parent' => $parentCategory));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);

		$articleName1 = 'article_ABC_1' . $salt;
		$articleName2 = 'article_ABC_2' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$articleCategory1 = '- - ' . $categoryName1;
		$articleCategory2 = '- - ' . $categoryName2;
		$this->articleManagerPage->addArticle($articleName1, $articleCategory1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName2, $articleCategory2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$this->moduleManagerPage = $cpPage->clickMenu('Module Manager', 'ModuleManagerPage');
		$title = 'Module test ' . $salt;
		$client = 'Site';
		$type = 'Articles - Categories';
		$position = 'position-3';
		$suffix = 'mySuffix';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix, 'Parent Category' => $categoryName);
		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->itemExist($title, 'h3'));
		$this->assertTrue($this->siteHomePage->itemExist($categoryName1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($categoryName2, 'a'));
		$this->siteHomePage->itemClick($categoryName1);
		$this->assertTrue($this->siteHomePage->itemExist($articleName1, 'a'));
		$this->siteHomePage->itemClick($categoryName2);
		$this->assertTrue($this->siteHomePage->itemExist($articleName2, 'a'));
		
		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->trashAndDelete($articleName1);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName2);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test article should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->trashAndDelete($categoryName1);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName2);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $moduleManager);
		$this->moduleManagerPage->setFilter('filter_client_id', $client);
		$this->moduleManagerPage->trashAndDelete($title);
		$this->moduleManagerPage->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
	}

	/**
	 * creating a menu with two menu items in it
	 * create module of type Menu and verify from front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_ModuleTypeMenu_ModuleAdded()
	{
		$cpPage = $this->doAdminLogin();
		$moduleManager = 'administrator/index.php?option=com_modules';
		$this->menuManagerPage = $cpPage->clickMenu('Menu Manager', 'MenuManagerPage');
		$salt = rand();
		$menuName = 'Menu' . $salt;
		$type = 'menu' . $salt;
		$description = 'test menu ' . $salt;
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
		$this->menuManagerPage->addMenu($menuName, $type, $description);
		$message = $this->menuManagerPage->getAlertMessage();
		$this->assertContains('Menu successfully saved', $message, 'Menu save should return success', true);

		$cfg = new SeleniumConfig;
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);

		$menuTitle1 = 'Menu Item 1' . $salt;
		$menuTitle2 = 'Menu Item 2' . $salt;
		$menuType = 'List All Categories';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuName);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle1), 'Test menu item should not be present');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle2), 'Test menu item should not be present');

		$this->menuItemsManagerPage->addMenuItem($menuTitle1, $menuType, $menuName);
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$this->menuItemsManagerPage->addMenuItem($menuTitle2, $menuType, $menuName);
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$this->moduleManagerPage = $cpPage->clickMenu('Module Manager', 'ModuleManagerPage');

		$title = 'Module test ' . $salt;
		$client = 'Site';
		$type = 'Menu';
		$position = 'position-3';
		$suffix = 'mySuffix';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix, 'Select Menu' => $menuName);
		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->itemExist($title, 'h3'));
		$this->assertTrue($this->siteHomePage->itemExist($menuTitle1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($menuTitle2, 'a'));

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $moduleManager);
		$this->moduleManagerPage->setFilter('filter_client_id', $client);
		$this->moduleManagerPage->trashAndDelete($title);
		$this->moduleManagerPage->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuName);
		$this->menuItemsManagerPage->trashAndDelete($menuTitle1);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle1), 'Test menu should not be present');
		$this->menuItemsManagerPage->trashAndDelete($menuTitle2);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle2), 'Test menu should not be present');

		$MenuManager = 'administrator/index.php?option=com_menus&view=menus';
		$this->driver->get($cfg->host . $cfg->path . $MenuManager);
		$this->menuManagerPage->deleteMenu($menuName);
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
	}

	/**
	 * create a menu with two menu items of type single article and adding articles in the menu item
	 * create module of type Menu and verify from front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_MenuItemSingleArticle_ModuleAdded()
	{
		$cfg = new SeleniumConfig;
		$cpPage = $this->doAdminLogin();
		$articleManager = 'administrator/index.php?option=com_content';
		$moduleManager = 'administrator/index.php?option=com_modules';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$salt = rand();
		$articleName1 = 'article_ABC1' . $salt;
		$articleName2 = 'article_ABC2' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$this->articleManagerPage->addArticle($articleName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$this->menuManagerPage = $cpPage->clickMenu('Menu Manager', 'MenuManagerPage');

		$menuName = 'Menu' . $salt;
		$type = 'menu' . $salt;
		$description = 'test menu ' . $salt;
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
		$this->menuManagerPage->addMenu($menuName, $type, $description);
		$message = $this->menuManagerPage->getAlertMessage();
		$this->assertContains('Menu successfully saved', $message, 'Menu save should return success', true);
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$menuTitle1 = 'Menu Item 1' . $salt;
		$menuTitle2 = 'Menu Item 2' . $salt;
		$menuType = 'Single Article';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuName);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle1), 'Test menu item should not be present');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle2), 'Test menu item should not be present');
		$this->menuItemsManagerPage->addMenuItem($menuTitle1, $menuType, $menuName, array('article' => $articleName1));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);
		$this->menuItemsManagerPage->addMenuItem($menuTitle2, $menuType, $menuName, array('article' => $articleName2));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$this->moduleManagerPage = $cpPage->clickMenu('Module Manager', 'ModuleManagerPage');

		$title = 'Module test ' . $salt;
		$client = 'Site';
		$type = 'Menu';
		$position = 'position-3';
		$suffix = 'mySuffix';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix, 'Select Menu' => $menuName);
		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->itemExist($title, 'h3'));
		$this->assertTrue($this->siteHomePage->itemExist($menuTitle1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($menuTitle2, 'a'));
		$this->siteHomePage->itemClick($menuTitle1);
		$this->assertTrue($this->siteHomePage->itemExist($articleName1, 'h2'));
		$this->siteHomePage->itemClick($menuTitle2);
		$this->assertTrue($this->siteHomePage->itemExist($articleName2, 'h2'));

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->trashAndDelete($articleName1);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName2);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test article should not be present');

		$this->driver->get($cfg->host . $cfg->path . $moduleManager);
		$this->moduleManagerPage->setFilter('filter_client_id', $client);
		$this->moduleManagerPage->trashAndDelete($title);
		$this->moduleManagerPage->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', $menuName);
		$this->menuItemsManagerPage->trashAndDelete($menuTitle1);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle1), 'Test menu should not be present');
		$this->menuItemsManagerPage->trashAndDelete($menuTitle2);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuTitle2), 'Test menu should not be present');

		$MenuManager = 'administrator/index.php?option=com_menus&view=menus';
		$this->driver->get($cfg->host . $cfg->path . $MenuManager);
		$this->menuManagerPage->deleteMenu($menuName);
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
	}

	/**
	 * create a menu with two menu items of type category blog and adding two articles in each menu item
	 * create a module of type menu and verifying from the front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_MenuItemsCategoryBlog_ModuleAdded()
	{
		$cfg = new SeleniumConfig;
		$cpPage = $this->doAdminLogin();
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$moduleManager = 'administrator/index.php?option=com_modules';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName1 = 'category_ABC_1' . $salt;
		$categoryName2 = 'category_ABC_2' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName1);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->categoryManagerPage->addCategory($categoryName2);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);

		$articleName1 = 'article_ABC_1' . $salt;
		$articleName2 = 'article_ABC_2' . $salt;
		$articleName3 = 'article_ABC_3' . $salt;
		$articleName4 = 'article_ABC_4' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName3), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName4), 'Test Article should not be present');

		$this->articleManagerPage->addArticle($articleName1, $categoryName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName2, $categoryName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$this->articleManagerPage->addArticle($articleName3, $categoryName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$this->articleManagerPage->addArticle($articleName4, $categoryName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$this->menuManagerPage = $cpPage->clickMenu('Menu Manager', 'MenuManagerPage');
		$menuName = 'Menu' . $salt;
		$type = 'menu' . $salt;
		$description = 'test menu ' . $salt;
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
		$this->menuManagerPage->addMenu($menuName, $type, $description);
		$message = $this->menuManagerPage->getAlertMessage();
		$this->assertContains('Menu successfully saved', $message, 'Menu save should return success', true);

		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);

		$menuItem1 = 'Menu Item 1' . $salt;
		$menuItem2 = 'Menu Item 2' . $salt;
		$menuType = 'Category Blog';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuName);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuItem1), 'Test menu item should not be present');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuItem2), 'Test menu item should not be present');

		$this->menuItemsManagerPage->addMenuItem($menuItem1, $menuType, $menuName, array('Category' => $categoryName1));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$this->menuItemsManagerPage->addMenuItem($menuItem2, $menuType, $menuName, array('Category' => $categoryName2));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$this->moduleManagerPage = $cpPage->clickMenu('Module Manager', 'ModuleManagerPage');

		$title = 'Module test ' . $salt;
		$client = 'Site';
		$type = 'Menu';
		$position = 'position-3';
		$suffix = 'mySuffix';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix, 'Select Menu' => $menuName);
		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->itemExist($title, 'h3'));
		$this->assertTrue($this->siteHomePage->itemExist($menuItem1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($menuItem2, 'a'));
		$this->siteHomePage->itemClick($menuItem1);
		$this->assertTrue($this->siteHomePage->itemExist($articleName1, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($articleName2, 'a'));
		$this->siteHomePage->itemClick($menuItem2);
		$this->assertTrue($this->siteHomePage->itemExist($articleName3, 'a'));
		$this->assertTrue($this->siteHomePage->itemExist($articleName4, 'a'));

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->trashAndDelete($articleName1);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName2);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName3);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName3), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName4);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName4), 'Test article should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->trashAndDelete($categoryName1);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName2);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $moduleManager);
		$this->moduleManagerPage->setFilter('filter_client_id', $client);
		$this->moduleManagerPage->trashAndDelete($title);
		$this->moduleManagerPage->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', $menuName);
		$this->menuItemsManagerPage->trashAndDelete($menuItem1);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuItem1), 'Test menu should not be present');
		$this->menuItemsManagerPage->trashAndDelete($menuItem2);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($menuItem2), 'Test menu should not be present');

		$MenuManager = 'administrator/index.php?option=com_menus&view=menus';
		$this->driver->get($cfg->host . $cfg->path . $MenuManager);
		$this->menuManagerPage->deleteMenu($menuName);
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
	}
}
