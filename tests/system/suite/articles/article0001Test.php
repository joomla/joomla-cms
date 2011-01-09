<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class Article0001 extends SeleniumJoomlaTestCase
{
	function testUnpublishArticle()
	{
		$this->setUp();
		echo "Starting testUnpublishArticle.\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Go to front end and check that Professionals is shown" . "\n";
		$this->gotoSite();
		$this->assertTrue($this->isTextPresent("Professionals"));

		echo "Go to back end and unpublish" . "\n";
		$this->gotoAdmin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "Professionals");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

    	$this->click("cb0");
    	$this->click("//li[@id='toolbar-unpublish']/a/span");
    	$this->waitForPageToLoad("30000");

		echo "Go to front end and check that Professionals is not shown" . "\n";
		$this->gotoSite();
		$this->assertFalse($this->isTextPresent("Professionals"));

		echo "Go to back end and publish Professionals" . "\n";
		$this->gotoAdmin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "Professionals");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

    	$this->click("cb0");
    	$this->click("//li[@id='toolbar-publish']/a/span");
    	$this->waitForPageToLoad("30000");

		$this->gotoAdmin();
		$this->doAdminLogout();

		$this->deleteAllVisibleCookies();
	}

	function testPublishArticle()
	{
		$this->setUp();
		echo "Starting testPublishArticle.\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Go to back end and unpublish Professionals" . "\n";
		$this->gotoAdmin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "Professionals");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");


    	$this->click("cb0");
    	$this->click("//li[@id='toolbar-unpublish']/a/span");
    	$this->waitForPageToLoad("30000");

		echo "Go to front end and check that Professionals is not shown" . "\n";
		$this->gotoSite();
		$this->assertFalse($this->isTextPresent("Professionals"));

		echo "Go to back end and publish Professionals" . "\n";
		$this->gotoAdmin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "Professionals");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

    	$this->click("cb0");
    	$this->click("//li[@id='toolbar-publish']/a/span");
    	$this->waitForPageToLoad("30000");

		echo "Go to front end and check that Professionals is shown" . "\n";
		$this->gotoSite();
		$this->assertTrue($this->isTextPresent("Professionals"));
		$this->gotoAdmin();
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}

	function testEditPermission()
	{
		echo "Starting testEditPermission" . "\n";
		echo "Go to front end and login as admin" . "\n";
		$this->gotoSite();
		$this->doFrontEndLogin();
		echo "Go to Home and check that edit icon is visible" . "\n";
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//img[@alt='Edit']"));
		echo "Drill to Sample Data article and check that edit icon is visible" . "\n";
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->click("link=Sample Sites");
		$this->waitForPageToLoad("30000");
		$this->click("link=Park Blog");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//img[@alt='Edit']"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		echo "Logout of front end." . "\n";
		$this->doFrontEndLogout();
		echo "Go to home and check that edit icon is not visible." . "\n";
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//img[@alt='Edit']"));
		echo "Drill to Sample Data article and check that edit icon is not visible." . "\n";
		$this->click("link=Sample Sites");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//img[@alt='Edit']"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->deleteAllVisibleCookies();
	}

}
