<?php
/**
 * @version		$Id
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests back-end acces of user belonging to a limited access group.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Acl0003Test extends SeleniumJoomlaTestCase
{
	function testRestrictedGroupAccess()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		//Create Restricted Manager Group
		$salt1 = mt_rand();
		$groupName = 'Restricted Manager' . $salt1;
		$groupParent = 'Manager';
		$this->createGroup($groupName, $groupParent);
   
	    //Add new user to Restricted Manager Group
		$username = 'Restricted User' . $salt1;
		$login = 'RestrictedUser' . $salt1;
		$email = $login . '@test.com';
		$group = $groupName;
	    $this->createUser($username, $login, 'password', $email, $group);
		
		echo "Set Weblinks access permissions for ". $groupName.".\n";
	    $this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
	    $this->click("//ul[@id='menu-weblinks']/li[2]/a");
	    $this->waitForPageToLoad("30000");
	    
	    $this->click("link=Sample Data-Weblinks");
	    $this->waitForPageToLoad("30000");
	    $this->click("//h3[@id='meta-rules']/a/span");
	    $this->click("link=Create");
	    $this->select("jform_rules_core.create_13", "label=Deny");
	    $this->click("link=Delete");
	    $this->select("jform_rules_core.delete_13", "label=Deny");
	    $this->click("link=Edit");
	    $this->select("jform_rules_core.edit_13", "label=Deny");
	    $this->click("link=Edit State");
	    $this->select("jform_rules_core.edit.state_13", "label=Deny");
	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");
		$this->doAdminLogout();
		
		//Test access for Test User beloning to Restricted Manager Group
		echo "Testng access of ". $login.".\n";		
		$this->gotoAdmin();
		echo "Log in as ". $login.".\n";
	    $this->type("mod-login-username", $login);
	    $this->type("mod-login-password", "password");
	    $this->click("//input[@value='Log in']");
	    $this->waitForPageToLoad("30000");
		$this->jClick('Weblinks');
	    $this->click("cb1");
	    $this->click("cb6");
		echo "Testng Unpublish capability.\n";	    
		$this->click("//li[@id='toolbar-unpublish']/a/span");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Edit state is not permitted"));
	    $this->click("cb1");
		echo "Testng Trash capability.\n";
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Edit state is not permitted"));
	    $this->click("cb1");
		echo "Testng Edit capability.\n";
		$this->click("//li[@id='toolbar-edit']/a/span");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Edit not permitted"));
		$this->gotoAdmin();
		$this->doAdminLogout();
		
		$this->doAdminLogin();		
		$this->deleteTestUsers($username);
		$this->deleteGroup($groupName);		
  }
}
?>