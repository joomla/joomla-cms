<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
		$this->jPrint ("Check starting condition as Super Admin user\n");

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-users-users']"), 'User manager should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-users-groups']"), 'Groups should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-menus-menus']"), 'Menus should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-banners']"), 'Banners should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-contact']"), 'Contacts should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-messages']"), 'Messaging should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-newsfeeds']"), 'Newsfeeds should be visible');

		$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_search']"), 'Search should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-weblinks']"), 'Weblinks should be visible');

		$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_installer']"), 'Extensions should be visible');

		$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-menus-menus']"), 'Menu Manager should not be visible');

		$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_modules']"), 'Module Manager should not be visible');

		$saltGroup = mt_rand();
		$groupName = 'Test Administrator Group'.$saltGroup;
		$groupParent = 'Registered';
		$this->createGroup($groupName, $groupParent);
		$levelName = 'Special';
		$this->changeAccessLevel($levelName,$groupName);
		$this->jPrint ("Change " . $groupName . " article permissions.\n");
		$this->jPrint ("Grant allow for all actions in article manager\n");
		$actions = array('Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
		$permissions = array('Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed');
		$this->setPermissions('Article Manager', $groupName, $actions, $permissions);

		sleep(3); // Needed for google chrome
		$this->jPrint ("Allow " . $groupName . " back end access, deny admin access\n");
		$actions = array('Site Login', 'Admin Login', 'Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
		$permissions = array('Inherited', 'Allowed', 'Denied', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', $groupName, $actions, $permissions);

		$group = $groupName;
		$username = 'Test User' . $saltGroup;
		$login = 'TestUser' . $saltGroup;
		$email = $login . '@test.com';
		$this->createUser($username, $login, 'password' , $email, $group);
		$this->gotoAdmin();
		$this->doAdminLogout();
		sleep(3);

		$this->jPrint("Log in to back end as " . $username . ".\n");
		$this->doAdminLogin($login, 'password');
		$this->jPrint("Testing " .  $username . " access.\n");

		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-users-users']"), 'Users menu should not be visible');
		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-users-groups']"), 'Groups should not be visible');

		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-banners']"), 'Banners should not be visible');
		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-contact']"), 'Contacts should not be visible');
		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-messages']"), 'Messaging should not be visible');
		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-newsfeeds']"), 'Newsfeeds should not be visible');
		$this->assertFalse($this->isElementPresent("//a[@href='index.php?option=com_search']"), 'Search should not be visible');
		$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-weblinks']"), 'Weblinks should not be visible');
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'option=com_redirect')]"), 'Redirect should not be visible');

		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'option=com_installer')]"), 'Extensions should not be visible');
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'option=com_menus')]"), 'Menu Manager should not be visible');
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'option=com_modules')]"), 'Module Manager should not be visible');

		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");

		$this->assertTrue($this->isTextPresent("Article Manager: Articles"), 'Article Manager not shown when it should be, Acl0001Test line 182');


		$this->doAdminLogout();
		$this->doAdminLogin();
		$this->deleteTestUsers();
		$this->gotoAdmin();
		$this->deleteGroup();
		$this->doAdminLogout();
		$this->countErrors();

		$this->deleteAllVisibleCookies();
	}
}

