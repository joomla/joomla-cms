<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
		echo "Check starting condition as Super Admin user\n";
			try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-users-users']"), 'User manager should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{

			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-users-groups']"), 'Groups should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-menus-menus']"), 'Menus should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-banners']"), 'Banners should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-contact']"), 'Contacts should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-messages']"), 'Messaging should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-newsfeeds']"), 'Newsfeeds should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_search']"), 'Search should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-weblinks']"), 'Weblinks should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

		try
		{
			$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_redirect']"), 'Redirect should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_installer']"), 'Extensions should be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//ul[@id='menu-com-menus-menus']"), 'Menu Manager should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertTrue($this->isElementPresent("//a[@href='index.php?option=com_modules']"), 'Module Manager should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

		$saltGroup = mt_rand();
		$groupName = 'Test Administrator Group'.$saltGroup;
		$groupParent = 'Registered';
		$this->createGroup($groupName, $groupParent);
		$levelName = 'Special';
        $this->changeAccessLevel($levelName,$groupName);
        echo "Change " . $groupName . " article permissions.\n";
        echo "Grant allow for all actions in article manager\n";
        $actions = array('Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
        $permissions = array('Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed');
        $this->setPermissions('Article Manager', $groupName, $actions, $permissions);

		sleep(3); // Needed for google chrome
        echo "Allow " . $groupName . " back end access, deny admin access\n";
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

		echo("Log in to back end as " . $username . ".\n");
		$this->type("mod-login-username", $login);
		$this->type("mod-login-password", 'password');
		$this->click("link=Log in");
		$this->waitForPageToLoad("30000");
		echo("Testing " .  $username . " access.\n");
		try
		{
			$this->assertFalse($this->isElementPresent("//a[@class=''][@href='#'][contains(text(), 'Users')]"), 'Users menu should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{

			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-users-groups']"), 'Groups should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-menus-menus']"), 'Menus option should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-banners']"), 'Banners should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-contact']"), 'Contacts should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-messages']"), 'Messaging should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-newsfeeds']"), 'Newsfeeds should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//a[@href='index.php?option=com_search']"), 'Search should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-weblinks']"), 'Weblinks should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

		try
		{
			$this->assertFalse($this->isElementPresent("//a[@href='index.php?option=com_redirect']"), 'Redirect should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//a[@href='index.php?option=com_installer']"), 'Extensions should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//ul[@id='menu-com-menus-menus']"), 'Menu Manager should not be visible');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("//a[@href='index.php?option=com_modules']"), 'Module Manager should not be visible');
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
			$this->assertTrue($this->isTextPresent("Article Manager: Articles"), 'Article Manager not shown when it should be, Acl0001Test line 182');
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

		$this->deleteAllVisibleCookies();
	}
}

