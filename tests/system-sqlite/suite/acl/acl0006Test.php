<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests manage permissions.
 *
 * Set global permissions for "ManageTestGroup" to deny all except manage permission
 * Log in as TestManageUser and test what you can do in each component
 */
require_once 'SeleniumJoomlaTestCase.php';

class Acl0006Test extends SeleniumJoomlaTestCase
{
	function testManagePermissions()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();

		//Set random salt
		$salt = mt_rand();

		//Set message to be checked
		$message='You do not have access to the administrator section of this site.';
		$groupName = 'ManageTestGroup' . $salt;
		$groupParent = 'Manager';
		$this->createGroup($groupName, $groupParent);

		//Add new user to ManageTestGroup Group
		$username = 'TestManageUser' . $salt;
		$login = 'TestManageUser' . $salt;
		$email = $login . '@test.com';
		$group = $groupName;
	    $this->createUser($username, $login, 'password', $email, $group);

    	echo "Set global permissions for ". $groupName." to allowed\n";
 		$actions = array('Site Login', 'Admin Login', 'Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State', 'Edit Own');
		$permissions = array('Inherited', 'Allowed', 'Denied', 'Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed');
		$this->setPermissions('Global Configuration', $group, $actions, $permissions);
    	$this->doAdminLogout();

    	$this->doAdminLogin($login, 'password');
   		echo "Testng access of ". $login.".\n";

    	echo "Testng Banners access of ". $login.".\n";
    	$this->click("link=Banners");
    	$this->waitForPageToLoad("30000");
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-checkin']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

		echo "Testng Contacts access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Contacts");
    	$this->waitForPageToLoad("30000");
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-checkin']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

		echo "Testng Messaging access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Messaging");
    	$this->waitForPageToLoad("30000");
     	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertTrue($this->isElementPresent("//li[contains(., 'My Settings')]"));
    	$this->assertFalse($this->isElementPresent("//li[contains(., 'Options')]"));

     	echo "Testng Newsfeeds access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Newsfeeds");
    	$this->waitForPageToLoad("30000");
   		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

    	echo "Testng Redirect access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Redirect");
    	$this->waitForPageToLoad("30000");
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

    	echo "Testng weblinks access of ". $login.".\n";
    	$this->click("link=Control Panel");
    	$this->waitForPageToLoad("30000");
    	$this->jClick('Weblinks');
   		$this->assertTrue($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-checkin']"));
    	$this->assertTrue($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

    	$this->doAdminLogout();
    	$this->doAdminLogin();

    	echo "Set global permissions for ". $groupName.".\n";
 		$actions = array('Site Login', 'Admin Login', 'Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State', 'Edit Own');
		$permissions = array('Inherited', 'Allowed', 'Denied', 'Allowed', 'Denied', 'Denied', 'Denied', 'Denied', 'Denied');
		$this->setPermissions('Global Configuration', $group, $actions, $permissions);
    	$this->doAdminLogout();
    	$this->doAdminLogin($login, 'password');

		echo "Testng access of ". $login.".\n";

    	echo "Testng Banners access of ". $login.".\n";
    	$this->click("link=Banners");
    	$this->waitForPageToLoad("30000");
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-checkin']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

		echo "Testng Contacts access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Contacts");
    	$this->waitForPageToLoad("30000");
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-checkin']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

		echo "Testng Messaging access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Messaging");
    	$this->waitForPageToLoad("30000");
     	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertTrue($this->isElementPresent("//li[contains(., 'My Settings')]"));
    	$this->assertFalse($this->isElementPresent("//li[contains(., 'Options')]"));

     	echo "Testng Newsfeeds access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Newsfeeds");
    	$this->waitForPageToLoad("30000");
   		$this->assertFalse($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

    	echo "Testng Redirect access of ". $login.".\n";
    	$this->click("link=Control Panel");
	    $this->waitForPageToLoad("30000");
    	$this->click("link=Redirect");
    	$this->waitForPageToLoad("30000");
   		$this->assertFalse($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

    	echo "Testng weblinks access of ". $login.".\n";
    	$this->click("link=Control Panel");
    	$this->waitForPageToLoad("30000");
    	$this->jClick('Weblinks');
   		$this->assertFalse($this->isElementPresent("//li[@id='toolbar-new']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-edit']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-publish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-unpublish']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-archive']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-checkin']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-trash']"));
    	$this->assertFalse($this->isElementPresent("//li[@id='toolbar-popup-options']"));

		$this->gotoAdmin();
		echo "Log back in as admin and delete user and group\n";
		$this->doAdminLogout();
		$this->doAdminLogin();

		$this->deleteTestUsers($username);
		$this->deleteGroup($groupName);
		$this->doAdminLogout();
	}
}
?>