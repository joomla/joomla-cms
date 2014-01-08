<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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

		$this->jPrint ("Set Weblinks access permissions for ". $groupName.".\n");
	    $this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
	    $component = 'Weblinks';
	    $actions = array('Create', 'Delete', 'Edit', 'Edit State');
	    $permissions = array ('Denied', 'Denied', 'Denied', 'Denied');
	    $this->setPermissions($component, $groupName, $actions, $permissions);
		$this->doAdminLogout();

		//Test access for Test User beloning to Restricted Manager Group
		$this->jPrint ("Testng access of ". $login.".\n");
		$this->gotoAdmin();
		$this->jPrint ("Log in as ". $login.".\n");
		$this->doAdminLogin($login, 'password');
		$this->jClick('Weblinks');
		$this->jPrint ("Check that user cannot edit, publish, unpublish, or trash weblinks.\n");
		$this->assertFalse($this->isElementPresent("//div[@id='toolbar-edit']/button"));
		$this->assertFalse($this->isElementPresent("//div[@id='toolbar-publish']/button"));
		$this->assertFalse($this->isElementPresent("//div[@id='toolbar-unpublish']/button"));
		$this->assertFalse($this->isElementPresent("//div[@id='toolbar-trash']/button"));

		$this->gotoAdmin();
		$this->doAdminLogout();

		$this->doAdminLogin();
		$this->deleteTestUsers($username);
		$this->deleteGroup($groupName);
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
		$this->jPrint ("Finished acl0003Test\n");
  }
}

