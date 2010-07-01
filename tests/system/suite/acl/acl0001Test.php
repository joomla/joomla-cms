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
		$levelName = 'Special';
        $this->changeAccessLevel($levelName,$groupName);		
        echo "Change " . $groupName . " article permissions.\n";
		$this->jClick('Article Manager');
		$this->jClick('Options');
		$this->click("//dt[contains(span,'Permissions')]");		
		$i=1;
		while($i<=6)
  		{		
			$this->select("//tr[contains(th,'$groupName')]/td[$i]/select", "label=Allow");
  			$i++;
  		}		
		echo "Saving Article Administrator article permissions\n";		
		$this->click("//button[contains(text(),'Save')]");
		
		//
		//	---- No confirmation message exists ----
		//
		
		echo "Allow" . $groupName . " back end access, deny admin access\n";		
		$this->jClick('Global Configuration');		
		$this->click("permissions");
		$this->select("//tr[contains(th,'$groupName')]/td[1]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[2]/select", "label=Allow");
		$this->select("//tr[contains(th,'$groupName')]/td[3]/select", "label=Deny");
		$this->select("//tr[contains(th,'$groupName')]/td[4]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[5]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[6]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[7]/select", "label=...");
		$this->select("//tr[contains(th,'$groupName')]/td[8]/select", "label=...");		
		sleep(2);		
		$this->jClick('Save & Close');
				
		$group = $groupName;
		$userName = 'Test User' . $saltGroup;
		$login = 'TestUser' . $saltGroup;
		$email = $login . '@test.com';
		$this->createUser($userName, $login, 'password' , $email, $group);
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
			$this->assertFalse($this->isElementPresent("link=User Manager"),'User Manager Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Users"),'Users Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Menus"),'Menus Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Banner"),'Banner Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Contacts"),'Contacts Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Messaging"),'Messaging Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=News Feeds"),'News Feeds Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Search"),'Search Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Web Links"),'Web Links Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Redirect",'Redirect Access Test Failed'));
			$this->assertFalse($this->isElementPresent("link=Extensions"),'Extensions Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Menu Manager"),'Menu Manager Access Test Failed');
			$this->assertFalse($this->isElementPresent("link=Module Manager"),'Module Manager Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
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
		$this->doAdminLogout();
		$this->countErrors();
	}
}
?>