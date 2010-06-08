<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Creates test group and assigns priviledges with the ACL.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Acl0001Test extends SeleniumJoomlaTestCase
{
	function testAclGroupCreation()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();		
		
		$saltGroup = mt_rand();
		$groupName = 'Test Administrator Group'.$saltGroup;
		$groupParent = 'Registered';
		
		$this->createGroup($groupName, $groupParent);		
//		$this->click("link=Groups");
//		$this->waitForPageToLoad("30000");
//
//		$this->click("link=New");
//		$this->waitForPageToLoad("30000");
//		$this->type("jform_title", "Article Administrator" . $saltGroup);
//		$this->select("jform_parent_id", "label=- Registered");
//		$this->click("link=Save & Close");
//		$this->waitForPageToLoad("30000");
//		try
//		{
//			$this->assertTrue($this->isTextPresent("successfully saved"), 'Save message not shown');
//		}
//		catch (PHPUnit_Framework_AssertionFailedError $e)
//		{
//			array_push($this->verificationErrors, $this->getTraceFiles($e));
//		}

		
        $levelName = 'Special';
        $this->changeAccessLevel($levelName,$groupName);		
//		echo "Add group Article Administrator" . $saltGroup . " to Special level\n";
//		$this->click("link=Access Levels");
//		$this->waitForPageToLoad("30000");
//		$this->click("cb3");
//		$this->click("link=Edit");
//		$this->waitForPageToLoad("30000");
//		$this->click("//form[@id='level-form']/div[2]/fieldset/ul/li[6]/input");
//		$this->click("link=Save & Close");
//		$this->waitForPageToLoad("30000");
//		try
//		{
//			$this->assertTrue($this->isTextPresent("successfully saved"), 'Save message not shown');
//		}
//		catch (PHPUnit_Framework_AssertionFailedError $e)
//		{
//			array_push($this->verificationErrors, $this->getTraceFiles($e));
//		}
		
        echo "Change " . $groupName . " article permissions.\n";
		$this->jClick('Article Manager');
		$this->jClick('Options');
		$this->click("//dt[contains(span,'Permissions')]");		
//		$this->click("link=Article Manager");
//		$this->waitForPageToLoad("30000");
//		$this->click("//li[@id='toolbar-popup-Popup']/a/span");
//		sleep(2);
//		$this->click("//dl[@id='config-tabs-com_content_configuration']/dt[7]/span");

		$i=1;
		while($i<=6)
  		{		
			$this->select("//tr[contains(th,'$groupName')]/td[$i]/select", "label=Allow");
  			$i++;
  		}		
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[1]/select", "label=Allow");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[2]/select", "label=Allow");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[3]/select", "label=Allow");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[4]/select", "label=Allow");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[5]/select", "label=Allow");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[6]/select", "label=Allow");
		echo "Saving Article Administrator article permissions\n";
		
		
		
		$this->click("//button[contains(text(),'Save')]");
		//
		//	---- No confirmation message exists ----
		//
		echo "Allow" . $groupName . " back end access, deny admin access\n";		
		$this->jClick('Global Configuration');		
//		$this->click("link=Global Configuration");
//		$this->waitForPageToLoad("30000");
		
		
		$this->click("permissions");
		$this->select("//tr[contains(th,'$groupName')]/td[1]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[2]/select", "label=Allow");
		$this->select("//tr[contains(th,'$groupName')]/td[3]/select", "label=Deny");
		$this->select("//tr[contains(th,'$groupName')]/td[4]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[5]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[6]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[7]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[8]/select", "label=...");		
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[1]/select", "label=...");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[2]/select", "label=Allow");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[3]/select", "label=Deny");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[4]/select", "label=...");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[5]/select", "label=...");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[6]/select", "label=...");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[7]/select", "label=...");
//		$this->select("//table[@id='acl-config']/tbody/tr[6]/td[8]/select", "label=...");
		sleep(2);
		
		$this->jClick('Save & Close');		
//		$this->click("link=Save & Close");
//		$this->waitForPageToLoad("30000");
//		try
//		{
//			$this->assertTrue($this->isTextPresent("Configuration successfully saved."), 'Save message not shown');
//		}
//		catch (PHPUnit_Framework_AssertionFailedError $e)
//		{
//			array_push($this->verificationErrors, $this->getTraceFiles($e));
//		}
		
		$group = $groupName;
		$userName = 'Test User' . $saltGroup;
		$login = 'TestUser' . $saltGroup;
		$email = $login . '@test.com';
		$this->createUser($userName, $login, 'password' , $email, $group);		
//		$saltUser = mt_rand();
//		echo("Add new user named My Test User" . $saltUser . "\n");
//		$this->click("link=User Manager");
//		$this->waitForPageToLoad("30000");
//		$this->click("//li[@id='toolbar-new']/a/span");
//		$this->waitForPageToLoad("30000");
//		$this->type("jform_name", "My Test User" . $saltUser);
//		$this->type("jform_username", "TestUser" . $saltUser);
//		$this->type("jform_password", "password");
//		$this->type("jform_password2", "password");
//		$this->type("jform_email", "TestUser" . $saltUser . "@test.com");
//		echo("Put in Article Administrator group \n");
//		$this->click("1group_13");
//		$this->click("link=Save & Close");
//		$this->waitForPageToLoad("30000");
//		try
//		{
//			$this->assertTrue($this->isTextPresent("successfully saved"), 'Save message not shown');
//		}
//		catch (PHPUnit_Framework_AssertionFailedError $e)
//		{
//			array_push($this->verificationErrors, $this->getTraceFiles($e));
//		}
		$this->gotoAdmin();
		$this->doAdminLogout();
		sleep(3);
		echo("Log in to back end as " . $userName . ".\n");
		$this->type("mod-login-username", $login);
		$this->type("mod-login-password", 'password');
		$this->click("link=Log in");
		$this->waitForPageToLoad("30000");
		echo("Testing " .  $userName . " access.\n");
		try
		{
			if ($this->isElementPresent("link=User Manager")) echo "User Manager test failed!\n";
			if ($this->isElementPresent("link=Users")) echo "Users test failed!\n";
			if ($this->isElementPresent("link=Menus")) echo "Menus test failed!\n";
			if ($this->isElementPresent("link=Banner")) echo "Banner test failed!\n";
			if ($this->isElementPresent("link=Contacts")) echo "Contacts test failed!\n";
			if ($this->isElementPresent("link=Messaging")) echo "Messaging test failed!\n";
			if ($this->isElementPresent("link=News Feeds")) echo "News Feeds test failed!\n";
			if ($this->isElementPresent("link=Search")) echo "Search test failed!\n";
			if ($this->isElementPresent("link=Web Links")) echo "Web Links test failed!\n";
			if ($this->isElementPresent("link=Redirect")) echo "Redirect test failed!\n";
			if ($this->isElementPresent("link=Extensions")) echo "Extensions test failed!\n";
			if ($this->isElementPresent("link=Menu Manager")) echo "Menu Manager test failed!\n";
			if ($this->isElementPresent("link=Module Manager")) echo "Module Manager test failed!\n";
		}
		catch (Exception $e)
		{
		}
		sleep(3);
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		try
		{
			$this->assertTrue($this->isTextPresent("Article Manager: Articles"), 'Article Manager not shown');
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		$this->doAdminLogout();
		$this->doAdminLogin();
		$this->deleteTestUsers();
		$this->gotoAdmin();
		
		$this->deleteGroup();		
//		echo "Delete Article Administrator group(s).\n";
//		$this->click("link=Groups");
//		$this->waitForPageToLoad("30000");
//		$this->type("filter_search", "article administrator");
//		$this->click("//button[@type='submit']");
//		$this->waitForPageToLoad("30000");
//		$this->click("toggle");
//		$this->click("//li[@id='toolbar-delete']/a/span");
//		$this->waitForPageToLoad("30000");
//		echo "Check that group deleted correctly.\n";
//		$this->assertTrue($this->isTextPresent("success"));
//		echo "Log out of back end.\n";
		$this->doAdminLogout();
		$this->countErrors();
	}
}
?>