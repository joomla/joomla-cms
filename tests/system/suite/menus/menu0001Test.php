<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
		echo "Starting testMenuItemAdd()\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		echo "Navigate to Menu Manager and add new menu\n";
		$this->click("link=Menu Manager");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->type("jform_title", "Functional Test Menu");
		$this->type("jform_menutype", "function-test-menu");
		$this->type("jform_menudescription", "Menu for testing");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Menu Item Manager\n";
		$this->click("link=Menu Items");
		$this->waitForPageToLoad("30000");
		echo "Add new menu item\n";
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("60000");

		echo "Select the menu item type\n";
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		echo "Select Single Article\n";
		$this->click("link=Articles");
		$this->click("link=Single Article");
		$this->waitForPageToLoad("60000");
		echo "Enter menu item info\n";
		$this->type("jform_title", "Functional Test Menu Item");
		$this->click("//label[@for='jform_published0']");
		$this->select("jform_menutype", "value=function-test-menu");
		echo "Open Select Article modal\n";
		sleep(2);
		$this->click("//a[@title='Select or Change article']");
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

		echo "Navigate to Module Manager and add new menu module\n";
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		echo "Select New Module\n";
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitforElement("//ul[@id='new-modules-list']");

		echo "Select Menu module\n";
		$this->click("//li[@data-original-title='Menu']/a");
		$this->waitForPageToLoad("30000");

		echo "Fill in menu name and info\n";

		$this->type("jform_title", "Functional Test Menu");
		$this->select("//select[@id='jform_position']", "value=position-7");
		// Wait for jSelectPosition element to disappear

		$this->click("//label[@for='jform_published0']");
		$this->click("link=Menu Assignment");
		$this->select("jform[assignment]", "value=0"); // all pages
		$this->select("jform[assignment]", "label=On all pages");
		$this->click("link=Basic Options");
		$this->select("jform_params_menutype", "value=function-test-menu");
		echo "Save menu\n";
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Front End and make sure new menu is there\n";
		$this->gotoSite();
		$this->assertTrue($this->isTextPresent("Functional Test Menu"));
		$this->assertTrue($this->isElementPresent("link=Functional Test Menu Item"));

		echo "Click on new menu choice and make sure article shows\n";
		$this->click("link=Functional Test Menu Item");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Australian Parks"));

		echo "Navigate to back end\n";
		$this->gotoAdmin();

		echo "Navigate to Module Manager and delete new menu module\n";
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "functional test");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Menu Item Manager and delete new menu item\n";
		$this->click("link=Functional Test Menu");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Menu Manager and delete new menu\n";
		$this->click("//ul[@id='submenu']/li[1]/a");
		$this->waitForPageToLoad("30000");
		$this->click("cb6");
		$this->click("//div[@id='toolbar-delete']/button");
		sleep(2);
		$this->assertTrue((bool)preg_match("/^Are you sure you want to delete/",$this->getConfirmation()));

		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Menu type successfully deleted"));
		echo "Navigate to front end and make sure menu is not shown\n";
		$this->gotoSite();
		$this->assertFalse($this->isTextPresent("Functional Test Menu"));
		$this->assertFalse($this->isElementPresent("link=Functional Test Menu Item"));

		$this->gotoAdmin();
		$this->doAdminLogout();
		echo "Finished testMenuItemAdd()\n";
		$this->deleteAllVisibleCookies();
	}

	function testUnpublishedCategoryList()
	{
		echo "Starting testUnpublishedCategoryList()\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		echo "Create a new Category List menu item \n";
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		$this->click("link=Articles");
		$this->click("link=Category List");
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
		$this->assertTrue($this->isElementPresent("//div[@id='system-message'][contains(., 'success')]"));
		$this->doAdminLogout();
		echo "Finished testUnpublishedCategoryList()\n";
		$this->deleteAllVisibleCookies();
	}
}

