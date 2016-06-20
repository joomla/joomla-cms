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
class MenuItemsManager0004Test extends JoomlaWebdriverTestCase
{
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
	 * add a menu Item of type List all tags and verify from frontend
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_ListAllTags_MenuItemsAdded()
	{
		/*add test tags*/
		$cpPage = $this->doAdminLogin();
		$this->tagManagerPage = $cpPage->clickMenu('Tags', 'TagManagerPage');
		$cfg = new SeleniumConfig;
		$tagManager = 'administrator/index.php?option=com_tags';
		$salt = rand();
		$tagName1 = 'Tag_1' . $salt;
		$tagName2 = 'Tag_2' . $salt;
		$caption = 'Sample' . $salt;
		$alt = 'alt' . $salt;
		$float = 'Right';

		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName1), 'Test tag should not be present');
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName2), 'Test tag should not be present');
		$this->tagManagerPage->addTag($tagName1, $caption, $alt, $float);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tags successfully saved') >= 0, 'Tag save should return success');

		$this->tagManagerPage->addTag($tagName2, $caption, $alt, $float);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tags successfully saved') >= 0, 'Tag save should return success');

		/* add menu item of type single contact */

		$menuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$title = 'Menu Item' . $salt;
		$menuType = 'List of all tags';
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
		$this->assertTrue($this->siteHomePage->itemExist($tagName1, 'a'), 'Tag should be present');
		$this->assertTrue($this->siteHomePage->itemExist($tagName2, 'a'), 'Tag should be present');

		/*delete the test elements*/

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $tagManager);
		$this->tagManagerPage->trashAndDelete($tagName1);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName1), 'Test tag should not be present');
		$this->tagManagerPage->trashAndDelete($tagName2);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName2), 'Test tag should not be present');

		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * add a menu Item of type Single News Feed
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_SingleNewsFeed_MenuItemAdded()
	{
		$this->doAdminLogin();
		$cfg = new seleniumconfig;
		$salt = rand();

		$menuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$title = 'Menu Item' . $salt;
		$menuType = 'Single News Feed';
		$menuLocation = 'Main Menu';
		$feedName = "Joomla! Connect";
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('newsfeed' => $feedName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		/*verify from the front end*/

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		$this->siteHomePage->itemClick($title);
		$this->assertTrue($this->siteHomePage->itemExist($feedName, 'a'), 'News feed should be present');

		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * add menu item of type List all newsFeeds category
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_ListAllNewsFeedCategory_MenuAdded()
	{
		$this->doAdminLogin();
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_newsfeeds';
		$feedManager = 'administrator/index.php?option=com_newsfeeds';
		$menuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		/*add test category*/

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		/*add test news feed*/

		$this->driver->get($cfg->host . $cfg->path . $feedManager);
		$this->newsFeedManagerPage = $this->getPageObject('NewsFeedManagerPage');
		$salt = rand();
		$feedName1 = 'Test_Feed_1' . $salt;
		$link1 = 'www.test1.com';
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName1), 'Test Feed should not be present');
		$this->newsFeedManagerPage->addFeed($feedName1, $link1, $categoryName);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'Feed save should return success');

		/* add menu item of type List all newsFeeds category */

		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$title = 'Menu Item' . $salt;
		$menuType = 'List All News Feed Categories';
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
		$this->assertTrue($this->siteHomePage->itemExist($categoryName, 'a'), 'Item should be present');

		/*delete the test elements*/

		$this->driver->get($cfg->host . $cfg->path . $feedManager);
		$this->newsFeedManagerPage->trashAndDelete($feedName1);
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName1), 'Test feed should not be present');

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * add a menu item of type List news feeds in a category
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenuItem_ListNewsFeedCategory_MenuItemAdded()
	{
		$this->doAdminLogin();
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_newsfeeds';
		$feedManager = 'administrator/index.php?option=com_newsfeeds';
		$menuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		/*add test category*/

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		/*add test news feed*/

		$this->driver->get($cfg->host . $cfg->path . $feedManager);
		$this->newsFeedManagerPage = $this->getPageObject('NewsFeedManagerPage');
		$salt = rand();
		$feedName1 = 'Test_Feed_1' . $salt;
		$feedName2 = 'Test_Feed_2' . $salt;
		$link1 = 'www.test1.com';
		$link2 = 'www.link2.com';
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName1), 'Test Feed should not be present');
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName2), 'Test Feed should not be present');
		$this->newsFeedManagerPage->addFeed($feedName1, $link1, $categoryName);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'Feed save should return success');

		$this->newsFeedManagerPage->addFeed($feedName2, $link2, $categoryName);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'Feed save should return success');

		/*add menu Item of type List news feeds in a category*/

		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$title = 'Menu Item' . $salt;
		$menuType = 'List News Feeds in a Category';
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('Category' => $categoryName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		/*front end verification*/

		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		$this->siteHomePage->itemClick($title);
		$this->assertTrue($this->siteHomePage->itemExist($feedName1, 'a'), 'News feed should be present');
		$this->assertTrue($this->siteHomePage->itemExist($feedName1, 'a'), 'News feed should be present');

		/*delete the test elements*/

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $feedManager);
		$this->newsFeedManagerPage->trashAndDelete($feedName1);
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName1), 'Test feed should not be present');

		$this->newsFeedManagerPage->trashAndDelete($feedName2);
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName2), 'Test feed should not be present');

		$this->driver->get($cfg->host . $cfg->path . $menuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}
}
