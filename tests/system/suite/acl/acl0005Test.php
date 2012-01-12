<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests admin login permissions per global configuration permission settings.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Acl0005Test extends SeleniumJoomlaTestCase
{
	function testAdminLoginPermissions()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();

		//Set random salt
		$salt = mt_rand();

		//Set message to be checked
		$message='You do not have access to the administrator section of this site.';

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

        echo "Setting all roles to inherit.\n";
		$actions = array('Site Login', 'Admin Login', 'Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', $group, $actions, $permissions);

		$group = 'Public';
		$action="Admin Login";
		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);

	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			 $this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();
    	$this->gotoAdmin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

		$this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();

		$permission="Not Set";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();
        $group='Manager';
		$this->changeAssignedGroup($username,$group);

    	$this->gotoAdmin();

		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	   	$this->doAdminLogin($login,$password);
		$this->checkMessage($message);

	    $this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();
        $group='Administrator';
		$this->changeAssignedGroup($username,$group);

    	$this->gotoAdmin();

		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

	    $this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();
        $group='Super Users';
		$this->changeAssignedGroup($username,$group);


		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    echo "Logging in to front end.\n";
	    $this->doAdminLogin($login,$password);
		    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
		$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();
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


		$permission="Allowed";

		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

	    $this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();
        $group='Author';
		$this->changeAssignedGroup($username,$group);



		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();
        $group='Editor';
		$this->changeAssignedGroup($username,$group);


		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();
        $group='Publisher';
		$this->changeAssignedGroup($username,$group);


		$permission="Allowed";

		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();
        $group='Shop Suppliers';
		$this->changeAssignedGroup($username,$group);


		$permission="Allowed";

		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

		$this->doAdminLogin();
        $group='Customer Group';
		$this->changeAssignedGroup($username,$group);


		$permission="Allowed";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("link=Log out"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
    	$this->doAdminLogout();

    	$this->doAdminLogin();

		$permission="Denied";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->doAdminLogin();

		$permission="Inherited";
		$this->setPermissions('Global Configuration', $group, $action, $permission);
	    $this->doAdminLogout();

	    $this->doAdminLogin($login,$password);
		$this->checkMessage($message);

    	$this->gotoAdmin();
    	$this->doAdminLogin();
	    $this->deleteTestUsers();
		$this->restoreDefaultGlobalPermissions();
	    $this->doAdminLogOut();
		$this->deleteAllVisibleCookies();
  }
}

