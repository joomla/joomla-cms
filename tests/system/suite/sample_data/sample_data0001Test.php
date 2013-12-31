<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class SampleData0001 extends SeleniumJoomlaTestCase
{
	function testModuleOrder()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->setDefaultTemplate('Hathor');
		$this->doAdminLogin();
		$this->jPrint("Open up category manager" . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Move Modules category up one" . "\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Modules')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->jPrint("Move Modules category down one" . "\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Modules')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->doAdminLogout();
		$this->setDefaultTemplate('isis');
		$this->jPrint("Finish testModuleOrder" . "\n");
		$this->deleteAllVisibleCookies();
	}

	function testMenuItems()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->jPrint("Go to front end" . "\n");
		$this->gotoSite();
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("User Name"));
		$this->assertTrue($this->isTextPresent("Password"));
		$this->assertTrue($this->isElementPresent("Submit"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Go to Sample Data" . "\n");
		$this->click("link=Sample Sites");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Sample Sites"));
		$this->click("link=Home");
		$this->jPrint("Load search" . "\n");
		$this->type("mod-search-searchword", "search");
		$this->keyPress("mod-search-searchword", "13");

		$this->waitForPageToLoad("30000");

		$this->click("link=Home");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Go to Site Map" . "\n");
		$this->click("link=Site Map");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Site Map"));

		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Go to Using Joomla!" . "\n");
		$this->click("link=Using Joomla!");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Using Joomla!"));
		$this->jPrint("Go to Extensions" . "\n");
		$this->click("link=Using Extensions");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Extensions"));
		$this->assertTrue($this->isElementPresent("link=Components"));
		$this->assertTrue($this->isElementPresent("link=Languages"));
		$this->assertTrue($this->isElementPresent("link=Templates"));
		$this->assertTrue($this->isElementPresent("link=Modules"));
		$this->assertTrue($this->isElementPresent("link=Components"));

		$this->jPrint("Go to The Joomla! Community" . "\n");
		$this->click("link=The Joomla! Community");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("The Joomla! Community"));

		$this->jPrint("Go to The Joomla! Project" . "\n");
		$this->click("link=The Joomla! Project");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("The Joomla! Project"));
		$this->jPrint("Go to Using Joomla!" . "\n");
		$this->click("link=Using Joomla!");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Go to Extensions" . "\n");
		$this->click("link=Using Extensions");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Go to Components" . "\n");
		$this->click("link=Components");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Contact Component"));
		$this->assertTrue($this->isElementPresent("link=Content Component"));
		$this->assertTrue($this->isElementPresent("link=Weblinks Component"));

		$this->assertTrue($this->isElementPresent("link=News Feeds Component"));
		$this->assertTrue($this->isElementPresent("link=Users Component"));
		$this->assertTrue($this->isElementPresent("link=Administrator Components"));
		$this->assertTrue($this->isElementPresent("link=Search Components"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->gotoAdmin();
		$this->doAdminLogout();
		$this->jPrint("Finish testMenuItems" . "\n");
		$this->deleteAllVisibleCookies();
	}

}
