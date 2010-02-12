<?php
/**
 * @version		$Id$
 * @package		Joomla.FunctionalTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	$userName = 'My Test User' . $salt1;
	$login = 'TestUser' . $salt1;
	$email = $login . '@test.com';
	$this->createUser($userName, $login, 'password', $email, 'Author');

    echo("Verify existence of new user.\n");
    try {
        $this->assertTrue($this->isTextPresent("Item successfully saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "TestUser");
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("TestUser"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Log out");
    $this->waitForPageToLoad("30000");
    echo("Go to home page.\n");    
    $this->click("link=Go to site home page.");
    $this->waitForPageToLoad("30000");
    echo("Log in as TestUser.\n");    
    $this->type("modlgn_username", "TestUser" . $salt1);
    $this->type("modlgn_passwd", "password");
    $this->click("Submit");
    $this->waitForPageToLoad("30000");
    echo("Verify existence of new user.\n");    
    try {
        $this->assertTrue($this->isTextPresent($userName));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Logout");
    $this->waitForPageToLoad("30000");
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");    
    $this->click("link=Site Administrator");
    $this->waitForPageToLoad("30000");
	$this->doAdminLogin();
	
	echo "Back to User Manager.\n";
    $this->click("//img[@alt='User Manager']");
    $this->waitForPageToLoad("30000");
    
    echo "Filter on user name\n";
    $this->type("search", $userName);
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
  
    echo "Delete all users in view\n";
    $this->click("toggle");
    echo("Delete new user.\n");    
    $this->click("link=Delete");
    $this->waitForPageToLoad("30000");
    try {
    	$this->assertTrue($this->isTextPresent("1 item(s) successfully deleted."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
    	array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Log out");
    $this->waitForPageToLoad("30000");
  }
}
?>
