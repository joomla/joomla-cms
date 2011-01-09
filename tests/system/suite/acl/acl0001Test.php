<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
        echo "Grant allow for all actions in article manager\n";
        $actions = array('Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
        $permissions = array('Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed');
        $this->setPermissions('Article Manager', $groupName, $actions, $permissions);

		echo "Allow" . $groupName . " back end access, deny admin access\n";
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
			$this->assertFalse($this->isElementPresent("link=User Manager"),'User Manager Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Users"),'Users Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Menus"),'Menus Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Banner"),'Banner Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Contacts"),'Contacts Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Messaging"),'Messaging Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=News Feeds"),'News Feeds Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Search"),'Search Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Web Links"),'Web Links Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

		try
		{
			$this->assertFalse($this->isElementPresent("link=Redirect",'Redirect Access Test Failed'));
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Extensions"),'Extensions Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
			$this->assertFalse($this->isElementPresent("link=Menu Manager"),'Menu Manager Access Test Failed');
		}
			catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
		try
		{
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

