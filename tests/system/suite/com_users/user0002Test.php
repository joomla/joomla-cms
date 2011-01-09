<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Adds a user in back end and logs in as new user in front end
 */

require_once 'SeleniumJoomlaTestCase.php';

class User0002Test extends SeleniumJoomlaTestCase
{
  function testCreateVerifyDeleteUser()
  {
  	echo("Starting testMyTestCase\n");
  	$this->setUp();
	$this->gotoAdmin();
	$this->doAdminLogin();

	$salt1 = mt_rand();
	$username = 'My Test User' . $salt1;
	$login = 'TestUser' . $salt1;
	$email = $login . '@test.com';
	$this->createUser($username, $login, 'password', $email, 'Author');

    echo("Verify existence of new user.\n");
    try {
        $this->assertTrue($this->isTextPresent("User successfully saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->type("filter_search", "TestUser");
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("TestUser"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("link=Log out");
    $this->waitForPageToLoad("30000");
    echo("Go to home page.\n");
    $this->click("link=Go to site home page.");
    $this->waitForPageToLoad("30000");
    echo("Log in as TestUser.\n");
    $this->type("modlgn-username", "TestUser" . $salt1);
    $this->type("modlgn-passwd", "password");
    $this->click("Submit");
    $this->waitForPageToLoad("30000");
    echo("Verify existence of new user.\n");
    try {
        $this->assertTrue($this->isTextPresent($username));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
	$this->click("link=Login");
    $this->waitForPageToLoad("30000");
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
	$this->gotoAdmin();
	$this->doAdminLogin();

	echo "Back to User Manager.\n";
    $this->click("link=User Manager");
    $this->waitForPageToLoad("30000");

    echo "Filter on user name\n";
    $this->type("filter_search", $username);
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");

    echo "Delete all users in view\n";
    $this->click("checkall-toggle");
    echo("Delete new user.\n");
    $this->click("//li[@id='toolbar-delete']/a/span");
    $this->waitForPageToLoad("30000");
    try {
    	$this->assertTrue($this->isTextPresent("success"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
    	array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("link=Log out");
    $this->waitForPageToLoad("30000");
    $this->countErrors();
	$this->deleteAllVisibleCookies();
  }
}
