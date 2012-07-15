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
class ControlPanel0001 extends SeleniumJoomlaTestCase
{

	function testMenuLinksPresent()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->gotoSite();
		$this->doFrontEndLogout();
		$this->gotoAdmin();
		echo "Check that top menu options are visible.\n";
		$this->assertTrue($this->isElementPresent("link=Site"));
		$this->assertTrue($this->isElementPresent("link=Users"));
		$this->assertTrue($this->isElementPresent("link=Menus"));
		$this->assertTrue($this->isElementPresent("link=Content"));
		$this->assertTrue($this->isElementPresent("link=Components"));
		$this->assertTrue($this->isElementPresent("link=Extensions"));
		$this->assertTrue($this->isElementPresent("link=Help"));
		echo "Check that Site menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=Control Panel"));
		$this->assertTrue($this->isElementPresent("link=Global Configuration"));
		$this->assertTrue($this->isElementPresent("link=Maintenance"));
		$this->assertTrue($this->isElementPresent("link=System Information"));
		$this->assertTrue($this->isElementPresent("link=Logout"));
		$this->assertTrue($this->isElementPresent("link=Global Check-in"));
		$this->assertTrue($this->isElementPresent("link=Clear Cache"));
		$this->assertTrue($this->isElementPresent("link=Purge Expired Cache"));
		echo "Check that User menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=User Manager"));
		$this->assertTrue($this->isElementPresent("link=Groups"));
		$this->assertTrue($this->isElementPresent("link=Access Levels"));
		$this->assertTrue($this->isElementPresent("link=Add New User"));
		$this->assertTrue($this->isElementPresent("link=Add New Group"));
		$this->assertTrue($this->isElementPresent("link=Add New Access Level"));
		$this->assertTrue($this->isElementPresent("link=Mass Mail Users"));
		echo "Check that Menu menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=Menu Manager"));
		echo "Check that Content menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=Article Manager"));
		$this->assertTrue($this->isElementPresent("link=Category Manager"));
		$this->assertTrue($this->isElementPresent("link=Featured Articles"));
		$this->assertTrue($this->isElementPresent("link=Add New Article"));
		$this->assertTrue($this->isElementPresent("link=Add New Category"));
		echo "Check that Component menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=Banners"));
		$this->assertTrue($this->isElementPresent("link=Clients"));
		$this->assertTrue($this->isElementPresent("link=Categories"));
		$this->assertTrue($this->isElementPresent("link=Contacts"));
		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-contact']/li[1]/a"));
		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-contact']/li[2]/a"));
		$this->assertTrue($this->isElementPresent("link=Newsfeeds"));
		$this->assertTrue($this->isElementPresent("link=Feeds"));
		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-newsfeeds']/li[2]/a"));
		$this->assertTrue($this->isElementPresent("link=Redirect"));
		$this->assertTrue($this->isElementPresent("link=Search"));
		$this->assertTrue($this->isElementPresent("link=Weblinks"));
		$this->assertTrue($this->isElementPresent("link=Links"));
		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-weblinks']/li[2]/a"));
		echo "Check that Extensions menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=Extension Manager"));
		$this->assertTrue($this->isElementPresent("link=Module Manager"));
		$this->assertTrue($this->isElementPresent("link=Plug-in Manager"));
		$this->assertTrue($this->isElementPresent("link=Template Manager"));
		$this->assertTrue($this->isElementPresent("link=Language Manager"));
		echo "Check that Help menu options are visible\n";
		$this->assertTrue($this->isElementPresent("link=Joomla Help"));
		$this->assertTrue($this->isElementPresent("link=Official Support Forum"));
		$this->assertTrue($this->isElementPresent("link=Documentation Wiki"));
		$this->assertTrue($this->isElementPresent("link=Joomla Extensions"));
		$this->assertTrue($this->isElementPresent("link=Joomla Resources"));
		$this->assertTrue($this->isElementPresent("link=Security Center"));
		$this->assertTrue($this->isElementPresent("link=Developer Resources"));
		$this->assertTrue($this->isElementPresent("link=Joomla Shop"));
		echo "Check that Control Panel icons are visible\n";
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[1]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[2]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[3]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[4]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[5]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[6]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[7]/div/a/img"));
		$this->assertTrue($this->isElementPresent("//div[@class='cpanel']/div[8]/div/a/img"));
		$this->doAdminLogout();
		print("Finish control_panel0001Test.php." . "\n");
		$this->deleteAllVisibleCookies();
	}
}

