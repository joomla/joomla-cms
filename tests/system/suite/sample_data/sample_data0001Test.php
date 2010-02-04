<?php
/**
 * @version		$Id$
 * @package		Joomla.FunctionalTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		$this->doAdminLogin();
		print("Open up category manager" . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		print("Move Modules category up one" . "\n");
		$this->click("//div[@id='element-box']/div[2]/form/table/tbody/tr[5]/td[4]/span[1]/a/img");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("JSuccess_Item_reordered"));
		print("Move Modules category down one" . "\n");
		$this->click("//div[@id='element-box']/div[2]/form/table/tbody/tr[4]/td[4]/span[2]/a/img");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("JSuccess_Item_reordered"));
		$this->doAdminLogout();
		print("Finish testModuleOrder" . "\n");
	}

	function testMenuItems()
	{
		$this->setUp();
		$this->doAdminLogin();
		print("Go to front end" . "\n");
		$this->gotoSite();
		$this->waitForPageToLoad("30000");
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		print("Go to login" . "\n");
		$this->click("link=Login");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Username"));
		$this->assertTrue($this->isTextPresent("Password"));
		$this->assertTrue($this->isElementPresent("//button[@type='submit']"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		print("Go to Sample Data" . "\n");
		$this->click("link=Sample Data");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Sample Sites"));
		$this->click("link=Home");
		print("Load search" . "\n");
		$this->click("link=Search");
		$this->waitForPageToLoad("30000");

		$this->click("link=Home");
		$this->waitForPageToLoad("30000");

		print("Go to Site Map" . "\n");
		$this->click("link=Site Map");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Site Map"));

		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		print("Go to Using Joomla!" . "\n");
		$this->click("link=Using Joomla!");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Using Joomla!"));
		print("Go to Extensions" . "\n");
		$this->click("link=Extensions");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Extensions"));
		$this->assertTrue($this->isElementPresent("link=Components"));
		$this->assertTrue($this->isElementPresent("link=Languages"));
		$this->assertTrue($this->isElementPresent("link=Templates"));
		$this->assertTrue($this->isElementPresent("link=Modules"));
		$this->assertTrue($this->isElementPresent("link=Components"));

		print("Go to The Joomla! Community" . "\n");
		$this->click("link=The Joomla! Community");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("The Joomla! Community"));

		print("Go to The Joomla! Project" . "\n");
		$this->click("link=The Joomla! Project");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("The Joomla! Project"));
		print("Go to Extensions" . "\n");
		$this->click("link=Extensions");
		$this->waitForPageToLoad("30000");
		print("Go to Components" . "\n");
		$this->click("//div[@id='maincolumn']/table/tbody/tr/td[3]/div/ul/li[1]/a");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Contact Component"));
		$this->assertTrue($this->isElementPresent("link=Content Component"));
		$this->assertTrue($this->isElementPresent("link=Weblinks Component"));

		$this->assertTrue($this->isElementPresent("link=News Feeds Component"));
		$this->assertTrue($this->isElementPresent("link=Users Component"));
		$this->assertTrue($this->isElementPresent("link=Administrator Components"));
		$this->assertTrue($this->isElementPresent("link=Search Component"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->gotoAdmin();
		$this->doAdminLogout();
		print("Finish testMenuItems" . "\n");
	}

}
?>
