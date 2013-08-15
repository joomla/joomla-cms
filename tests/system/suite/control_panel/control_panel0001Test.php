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
		$this->jPrint ("Check that top menu options are visible.\n");
		$this->assertTrue($this->isElementPresent("link=" . $this->cfg->site_name));
		$this->assertTrue($this->isElementPresent("link=Users"));
		$this->assertTrue($this->isElementPresent("link=Menus"));
		$this->assertTrue($this->isElementPresent("link=Content"));
		$this->assertTrue($this->isElementPresent("link=Components"));
		$this->assertTrue($this->isElementPresent("link=Extensions"));
		$this->assertTrue($this->isElementPresent("link=Help"));
		$this->jPrint ("Check that Site menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=Control Panel"));
		$this->assertTrue($this->isElementPresent("link=Global Configuration"));
		$this->assertTrue($this->isElementPresent("link=System Information"));
		$this->assertTrue($this->isElementPresent("link=Logout"));
		$this->assertTrue($this->isElementPresent("link=Global Check-in"));
		$this->assertTrue($this->isElementPresent("link=Clear Cache"));
		$this->assertTrue($this->isElementPresent("link=Purge Expired Cache"));
		$this->jPrint ("Check that User menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=User Manager"));
		$this->assertTrue($this->isElementPresent("link=Groups"));
		$this->assertTrue($this->isElementPresent("link=Access Levels"));
		$this->assertTrue($this->isElementPresent("link=Mass Mail Users"));
		$this->jPrint ("Check that Menu menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=Menu Manager"));
		$this->jPrint ("Check that Content menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=Article Manager"));
		$this->assertTrue($this->isElementPresent("link=Category Manager"));
		$this->assertTrue($this->isElementPresent("link=Featured Articles"));
		$this->jPrint ("Check that Component menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=Banners"));
		$this->assertTrue($this->isElementPresent("link=Contacts"));
		$this->assertTrue($this->isElementPresent("link=Joomla! Update"));
		$this->assertTrue($this->isElementPresent("link=Messaging"));
		$this->assertTrue($this->isElementPresent("link=Newsfeeds"));
		$this->assertTrue($this->isElementPresent("link=Redirect"));
		$this->assertTrue($this->isElementPresent("link=Search"));
		$this->assertTrue($this->isElementPresent("link=Smart Search"));
		$this->assertTrue($this->isElementPresent("link=Weblinks"));

		$this->jPrint ("Check that Extensions menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=Extension Manager"));
		$this->assertTrue($this->isElementPresent("link=Module Manager"));
		$this->assertTrue($this->isElementPresent("link=Plug-in Manager"));
		$this->assertTrue($this->isElementPresent("link=Template Manager"));
		$this->assertTrue($this->isElementPresent("link=Language Manager"));
		$this->jPrint ("Check that Help menu options are visible\n");
		$this->assertTrue($this->isElementPresent("link=Joomla! Help"));
		$this->assertTrue($this->isElementPresent("link=Official Support Forum"));
		$this->assertTrue($this->isElementPresent("link=Documentation Wiki"));
		$this->assertTrue($this->isElementPresent("link=Joomla! Extensions"));
		$this->assertTrue($this->isElementPresent("link=Joomla! Translations"));
		$this->assertTrue($this->isElementPresent("link=Joomla! Resources"));
		$this->assertTrue($this->isElementPresent("link=Community Portal"));
		$this->assertTrue($this->isElementPresent("link=Security Center"));
		$this->assertTrue($this->isElementPresent("link=Developer Resources"));
		$this->assertTrue($this->isElementPresent("link=Joomla! Shop"));
		$this->jPrint ("Check that Control Panel icons are visible\n");
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Add New Article')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Article Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Category Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Media Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Menu Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'User Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Module Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Extension Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Language Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Global Configuration')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Template Manager')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'Edit Profile')]"));
		$this->assertTrue($this->isElementPresent("//a[contains(., 'All extensions are up-to-date')]"));

		$this->doAdminLogout();
		$this->jPrint("Finish control_panel0001Test.php." . "\n");
		$this->deleteAllVisibleCookies();
	}
}

