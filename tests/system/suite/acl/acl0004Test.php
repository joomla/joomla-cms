<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests site login permissions per global configuration permission settings.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Acl0004Test extends SeleniumJoomlaTestCase
{
	function testSiteLoginPermissions()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();

		//Set random salt
		$salt = mt_rand();

		//Set message to be checked
		$message='You cannot access the private section of this site.';

	    //Define test user
		$username = 'ACL Test User' . $salt;
		$password = 'password' . $salt;
		$login = 'acltestuser' . $salt;
		$email = $login . '@test.com';
		$group = 'Public';
		echo "Create $username and add to $group group.\n";
	    $this->createUser($username, $login, $password, $email, $group);

	    echo "Removing $username from Registered group.\n";
	    $this->changeAssignedGroup($username,$group="Registered");

		echo "Setting all roles to inherit for $username.\n";
		$actions = array('Site Login', 'Admin Login', 'Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', $group, $actions, $permissions);

		$action="Site Login";
		$group = 'Public';
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"), 'Message not displayed or message changed, SeleniumJoomlaTestCase line 31');
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$permission="Not Set";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

    	$this->gotoAdmin();
        $group='Manager';
		$this->changeAssignedGroup($username,$group);

    	$this->gotoAdmin();
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	   	$this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

    	$this->gotoAdmin();
        $group='Administrator';
		$this->changeAssignedGroup($username,$group);

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

		$this->gotoAdmin();

        $group='Super Users';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    echo "Logging in to front end.\n";
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
		$this->doFrontEndLogout();

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
	    $group='Super Users';
		$this->changeAssignedGroup($username,$group);

	    $group='Administrator';
		$this->changeAssignedGroup($username,$group);

	    $group='Manager';
		$this->changeAssignedGroup($username,$group);

	    $group='Public';
		$this->changeAssignedGroup($username,$group);

	    $group='Registered';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

		$this->gotoAdmin();

        $group='Author';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

		$this->gotoAdmin();

        $group='Editor';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

		$this->gotoAdmin();
        $group='Publisher';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

		$this->gotoAdmin();
        $group='Shop Suppliers';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

		$this->gotoAdmin();
        $group='Customer Group';
		$this->changeAssignedGroup($username,$group);

		$this->jClick('Global Configuration: Permissions');
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='login-form'][contains(., '$username')]"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doFrontEndLogout();

    	$this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

	    $this->gotoAdmin();
		$this->jClick('Global Configuration: Permissions');
		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);

    	$this->gotoAdmin();
	    $this->deleteTestUsers();
		$this->restoreDefaultGlobalPermissions();
	    $this->doAdminLogOut();
		$this->deleteAllVisibleCookies();
  }
}

