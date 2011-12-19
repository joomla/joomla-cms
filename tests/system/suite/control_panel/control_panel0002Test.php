<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * loads each menu choice in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class ControlPanel0002 extends SeleniumJoomlaTestCase
{

	function testMenuCheck()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		echo "Navigate to Control Panel.\n";
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Global Config.\n";
		$this->click("link=Global Configuration");
		$this->waitForPageToLoad("30000");
		$this->click("site");
		$this->assertTrue($this->isTextPresent("Site Settings"));
		$this->click("system");
		$this->assertTrue($this->isTextPresent("System Settings"));
		$this->click("server");
		$this->assertTrue($this->isTextPresent("Server Settings"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Global Check-in.\n";
		$this->click("link=Global Check-in");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Global Check-in"));
		echo "Navigate to Clear Cache.\n";
		$this->click("link=Clear Cache");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Clear Cache"));
		echo "Navigate to Purge Expired Cache.\n";
		$this->click("link=Purge Expired Cache");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Purge Expired Cache"));
		$this->click("link=System Information");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("System Information"));
		$this->click("phpsettings");
		$this->assertTrue($this->isTextPresent("Relevant PHP Settings"));
		$this->click("config");
		$this->assertTrue($this->isTextPresent("Configuration File"));
		$this->click("directory");
		$this->assertTrue($this->isTextPresent("Directory Permissions"));
		$this->click("phpinfo");
		$this->assertTrue($this->isTextPresent("PHP Information"));
		echo "Navigate to User Manager.\n";
		$this->click("link=User Manager");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("User Manager: Users"));
		$this->click("//ul[@id='submenu']/li[2]/a");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Groups"));
		$this->click("link=Access Levels");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Levels"));
		$this->click("link=Groups");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Groups"));
		$this->click("link=Access Levels");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Levels"));
		echo "Navigate to Add New User.\n";
		$this->click("link=Add New User");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("User Manager: Add New User"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Add New Group.\n";
		$this->click("link=Add New Group");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("User Manager: Add New"));
		$this->assertTrue($this->isTextPresent("Group"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Add New Access Level.\n";
		$this->click("link=Add New Access Level");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("User Manager: Add New"));
		$this->assertTrue($this->isTextPresent("Level"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Mass Mail.\n";
		$this->click("link=Mass Mail Users");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Mass Mail"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Read Private Messages.\n";
		$this->click("link=0");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Private Messages"));
		echo "Navigate to New Private Message.\n";
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Open Options modal \n";
		$this->click("//li[@id='toolbar-popup-options']/a[contains(., 'Options')]");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//div[@class='configuration'][contains(., 'Messages Configuration')]")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		$this->click("//button[@type='button' and contains(@onclick, 'window.parent.SqueezeBox.close();')]");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if (!$this->isElementPresent("//div[@class='configuration'][contains(., 'Messages Configuration')]")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		echo "Navigate to Menu Manager.\n";
		$this->click("link=Menu Manager");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Menu Manager: Menus"));
		echo "Navigate to Article Manager.\n";
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Article Manager: Articles"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Category Manager.\n";
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category Manager"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-refresh']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Featured Articles.\n";
		$this->click("link=Featured Articles");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Article Manager: Featured Articles"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-remove']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Add New Article.\n";
		$this->click("link=Add New Article");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Article Manager: Add New Article"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-save']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-apply']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-save-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-cancel']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Navigate to Add New Category.\n";
		$this->click("link=Add New Category");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category Manager: Add"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-save']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-apply']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-save-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-cancel']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Control Panel.\n";
		$this->gotoAdmin();
		echo "Navigate to Banner Manager.\n";
		$this->click("link=Banners");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Banner Manager: Banners"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Banner Clients.\n";
		$this->click("link=Clients");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Banner Manager: Clients"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));

		echo "Navigate to Banner Tracks.\n";
		$this->click("link=Tracks");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Banner Manager: Tracks"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-COM_BANNERS_DELETE_MSG']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));

		echo "Navigate to Banner Categories.\n";
		$this->click("link=Categories");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category Manager: Banners"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-refresh']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));

		echo "Navigate to Contact Manager.\n";
		$this->click("//ul[@id='menu-com-contact']/li[1]/a");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Contact Manager"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Contact Category.\n";
		$this->click("//ul[@id='menu-com-contact']/li[2]/a");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category Manager: Contacts"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-refresh']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to News Feed Manager.\n";
		$this->click("link=Feeds");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("News Feed Manager"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to News Feed Categories.\n";
		$this->click("//ul[@id='menu-com-newsfeeds']/li[2]/a");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category Manager"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-refresh']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Redirect.\n";
		$this->jClick('Redirect Manager');
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Search Statistics.\n";
		$this->click("link=Search");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Search Manager: Search Term Analysis"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-refresh']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Weblinks Manager.\n";
		$this->click("link=Links");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-popup-options']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		echo "Navigate to Web Links Categories.\n";
		$this->click("//ul[@id='menu-com-weblinks']/li[2]/a");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category Manager"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-refresh']/a/span"));
		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-help']/a/span"));
		$this->gotoAdmin();
		$this->doAdminLogout();
		print("Finish control_panel0002Test.php." . "\n");
		$this->deleteAllVisibleCookies();
	}
}

