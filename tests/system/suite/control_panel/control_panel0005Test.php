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
class ControlPanel0005 extends SeleniumJoomlaTestCase
{
	function testMenuTopLevelPresent()
	{
		echo "starting testMenuTopLevelPresent\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->assertTrue($this->isElementPresent("link=Site"));
		$this->assertTrue($this->isElementPresent("link=Users"));
		$this->assertTrue($this->isElementPresent("link=Menus"));
		$this->assertTrue($this->isElementPresent("link=Content"));
		$this->assertTrue($this->isElementPresent("link=Components"));
		$this->assertTrue($this->isElementPresent("link=Extensions"));
		$this->assertTrue($this->isElementPresent("link=Help"));
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}

	function testMenuDetailHelp()
	{
		echo "starting testMenuDetailHelp\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		echo "Open Using Joomla! and check that help links to Single Article help\n";
		$this->click("link=About Joomla");
		$this->waitForPageToLoad("30000");
		$this->click("link=Using Joomla!");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//li/a[contains(@onclick, 'Menus_Menu_Item_Article_Single_Article')]"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Open Categogy Blog and check that help links to Category Blog help\n";
		$this->click("link=Article Category Blog");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//li/a[contains(@onclick, 'Menus_Menu_Item_Article_Category_Blog')]"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Open Who's Online Module and check that help links to detailed help\n";
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->type("id=filter_search", "who");
		$this->click("css=button[type=\"submit\"]");
		$this->waitForPageToLoad("30000");
		$this->click("link=Who's Online");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//li/a[contains(@onclick, 'Extensions_Module_Manager_Who_Online')]"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Open Articles Category and check that help links to detailed help\n";
		$this->type("id=filter_search", "category");
		$this->click("css=button[type=\"submit\"]");
		$this->waitForPageToLoad("30000");
		$this->click("link=Articles Category");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//li/a[contains(@onclick, 'Extensions_Module_Manager_Articles_Category')]"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Clear search field\n";
		$this->click("css=button[type=\"button\"]");
		$this->waitForPageToLoad("30000");
		echo "Open Admin Logged In Module and check that help links to detailed help\n";
		$this->select("filter_client_id", "label=Administrator");
		$this->waitForPageToLoad("30000");
		$this->click("link=Logged-in Users");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//li/a[contains(@onclick, 'Extensions_Module_Manager_Admin_Logged')]"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
		echo "finished with control_panel0005Test/testMenuDetailHelp\n";
		$this->deleteAllVisibleCookies();
	}
}
