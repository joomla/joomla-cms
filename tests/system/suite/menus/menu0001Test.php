<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class Menu0001 extends SeleniumJoomlaTestCase
{

	function testMenuItemAdd()
	{
		$this->jPrint ("Starting testMenuItemAdd()\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->jPrint ("Navigate to Menu Manager and add new menu\n");
		$this->click("link=Menu Manager");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->type("jform_title", "Functional Test Menu");
		$this->type("jform_menutype", "function-test-menu");
		$this->type("jform_menudescription", "Menu for testing");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Navigate to Menu Item Manager\n");
		$this->click("link=Menu Items");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Add new menu item\n");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("60000");

		$this->jPrint ("Select the menu item type\n");
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		$this->jPrint ("Select Single Article\n");
		$this->click("link=Articles");
		$this->click("//a[contains(., 'Single Article')]");
		$this->waitForPageToLoad("60000");
		$this->jPrint ("Enter menu item info\n");
		$this->type("jform_title", "Functional Test Menu Item");
		$this->click("//label[@for='jform_published0']");
		$this->select("jform_menutype", "value=function-test-menu");
		$this->jPrint ("Open Select Article modal\n");
		sleep(2);
		$this->click("//a[contains(@href,'jSelectArticle')]");
		$this->waitforElement("//iframe[contains(@src, 'jSelectArticle')]");
		$this->click("link=Australian Parks");
		$this->waitforElement("//input[@id='jform_request_id_name']");

		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		// Open menu item and make sure type displays
		$this->click("link=Functional Test Menu");
		$this->waitForPageToLoad("30000");
		$this->click("link=Functional Test Menu Item");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//input[@value='Single Article']"));
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Navigate to Module Manager and add new menu module\n");
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Select New Module\n");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitforElement("//ul[@id='new-modules-list']");

		$this->jPrint ("Select Menu module\n");
		$this->click("//li/a/strong[ text() = 'Menu']");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Fill in menu name and info\n");

		$this->type("jform_title", "Functional Test Menu");
		$this->select("//select[@id='jform_position']", "value=position-7");
		// Wait for jSelectPosition element to disappear

		$this->click("//label[@for='jform_published0']");
		$this->click("link=Menu Assignment");
		$this->select("jform[assignment]", "value=0"); // all pages
		$this->select("jform[assignment]", "label=On all pages");
		$this->click("link=Basic Options");
		$this->select("jform_params_menutype", "value=function-test-menu");
		$this->jPrint ("Save menu\n");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Navigate to Front End and make sure new menu is there\n");
		$this->gotoSite();
		$this->assertTrue($this->isTextPresent("Functional Test Menu"));
		$this->assertTrue($this->isElementPresent("link=Functional Test Menu Item"));

		$this->jPrint ("Click on new menu choice and make sure article shows\n");
		$this->click("link=Functional Test Menu Item");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Australian Parks"));

		$this->jPrint ("Navigate to back end\n");
		$this->gotoAdmin();

		$this->jPrint ("Navigate to Module Manager and delete new menu module\n");
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "functional test");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Navigate to Menu Item Manager and delete new menu item\n");
		$this->click("link=Functional Test Menu");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Navigate to Menu Manager and delete new menu\n");
		$this->click("//ul[@id='submenu']/li[1]/a");
		$this->waitForPageToLoad("30000");
		$this->click("cb6");
		$this->click("//div[@id='toolbar-delete']/button");
		sleep(2);
		$this->assertTrue((bool)preg_match("/^Are you sure you want to delete/",$this->getConfirmation()));

		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Menu type successfully deleted"));
		$this->jPrint ("Navigate to front end and make sure menu is not shown\n");
		$this->gotoSite();
		$this->assertFalse($this->isTextPresent("Functional Test Menu"));
		$this->assertFalse($this->isElementPresent("link=Functional Test Menu Item"));

		$this->gotoAdmin();
		$this->doAdminLogout();
		$this->jPrint ("Finished testMenuItemAdd()\n");
		$this->deleteAllVisibleCookies();
	}

	function testUnpublishedCategoryList()
	{
		$this->jPrint ("Starting testUnpublishedCategoryList()\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->jPrint ("Create a new Category List menu item \n");
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		$this->click("link=Articles");
		$this->click("//a[contains(., 'Category List')]");
		$this->waitForPageToLoad("30000");
		$this->type("jform_title", "Category List Test");
		$this->click("//label[@for='jform_published0']");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		// Change to toggle published
		$this->togglePublished("Sample Data-Articles", 'Category');
		$this->doAdminLogout();
		$this->gotoSite();
		$link = $this->cfg->path . 'index.php/category-list-test';
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category not found"));

		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "Category List Test");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		$this->togglePublished("Sample Data-Articles", 'Category');
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->doAdminLogout();
		$this->jPrint ("Finished testUnpublishedCategoryList()\n");
		$this->deleteAllVisibleCookies();
	}
}

