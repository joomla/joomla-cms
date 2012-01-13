<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests creating and deleting a User
 */
require_once 'SeleniumJoomlaTestCase.php';

class User0001Test extends SeleniumJoomlaTestCase
{
	function testCreateUser()
	{
		print("Starting testCreateUser"."\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		print("Add new user"."\n");
		$this->click("link=Add New User");
		$this->waitForPageToLoad("30000");
		$this->type("jform_name", "username1");
		$this->type("jform_username", "loginname1");
		$this->type("jform_password", "password1");
		$this->type("jform_password2", "password1");
		$this->type("jform_email", "email@example.com");
		$this->click("1group_1");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("60000");
		print("Save and check that it exists"."\n");
		$this->type("filter_search", "username1");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		$this->assertEquals("username1", $this->getText("link=username1"));
		echo "Open new user for editing and check values\n";
		$this->click("link=username1");
		$this->waitForPageToLoad("30000");
		$this->assertEquals("loginname1", $this->getValue("jform_username"));
		$this->assertEquals("email@example.com", $this->getValue("jform_email"));
		$this->assertEquals("on", $this->getValue("1group_1"));
		echo "Close new user\n";
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");

		print("Delete the user"."\n");
		$this->click("cb0");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that user does not exist"."\n");
		$this->assertFalse($this->isElementPresent("link=username1"));
		echo "Clear Filter and check that Super User exists\n";
		$this->click("//button[@type='button']");
		$this->waitForPageToLoad("30000");
		$this->assertEquals("Super User", $this->getText("link=Super User"));
		print("Finished user0001Test.php"."\n");
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}
}
