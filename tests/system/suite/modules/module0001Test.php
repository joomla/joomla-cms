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
class Module0001 extends SeleniumJoomlaTestCase
{
	function testUnpublishModule()
	{
		$this->setUp();
		$this->jPrint ("testUnpublishModule"."\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->gotoSite();
		$this->jPrint ("Check that login form is present"."\n");
		$this->assertTrue($this->isTextPresent("Login Form"));
		$this->gotoAdmin();
		$this->jPrint("Goto module manager and disable login module"."\n");
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "Login Form");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
    	$this->click("cb0");
    	$this->click("//div[@id='toolbar-unpublish']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Go back to front end and check that login is not shown"."\n");
		$this->gotoSite();
		$this->assertFalse($this->isTextPresent("Login Form"));

		$this->jPrint("Go back to module manager and enable login module"."\n");
		$this->gotoAdmin();
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "Login Form");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
    	$this->click("cb0");
    	$this->click("//div[@id='toolbar-publish']/button");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();

		$this->deleteAllVisibleCookies();
	}

	function testPublishModule()
	{
		$this->setUp();
		$this->jPrint("testPublishModule"."\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Go to back end and disable login module"."\n");
		$this->type("filter_search", "Login Form");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
    	$this->click("cb0");
    	$this->click("//div[@id='toolbar-unpublish']/button");
		$this->waitForPageToLoad("30000");

		$this->gotoSite();
		$this->jPrint("Go to front and check that login form is not shown"."\n");
		$this->assertFalse($this->isTextPresent("Login Form"));

		$this->jPrint("Go to module manager and enable login module"."\n");
		$this->gotoAdmin();
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "Login Form");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
    	$this->click("//div[@id='toolbar-publish']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Go to front end and check that login form is present"."\n");
		$this->gotoSite();
		$this->assertTrue($this->isTextPresent("Login Form"));

		$this->gotoAdmin();
		$this->doAdminLogout();

		$this->deleteAllVisibleCookies();
	}

}

