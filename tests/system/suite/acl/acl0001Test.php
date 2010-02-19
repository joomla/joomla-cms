<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
    $this->click("link=Groups");
    $this->waitForPageToLoad("30000");
    $saltGroup = mt_rand();
    echo "Create new group Article Administrator".$saltGroup."\n";
    $this->click("link=New");
    $this->waitForPageToLoad("30000");
    $this->type("jform_title", "Article Administrator".$saltGroup);
    $this->select("jformparent_id", "label=- Registered");
    $this->click("link=Save & Close");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Item successfully saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    echo "Add group Article Administrator".$saltGroup." to Special level\n";    
    $this->click("link=Access Levels");
    $this->waitForPageToLoad("30000");
    $this->click("cb3");
    $this->click("link=Edit");
    $this->waitForPageToLoad("30000");
    $this->click("//form[@id='level-form']/div[2]/fieldset/ul/li[6]/input");
    $this->click("link=Save & Close");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Item successfully saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    echo "Change Article Administrator".$saltGroup." article permissions.\n";        
    $this->click("link=Article Manager");
    $this->waitForPageToLoad("30000");
    $this->click("//li[@id='toolbar-popup-Popup']/a/span");
    sleep(2);
    $this->click("//dl[@id='config-tabs-com_content_configuration']/dt[7]/span");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[1]/select", "label=Allow");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[2]/select", "label=Allow");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[3]/select", "label=Allow");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[4]/select", "label=Allow");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[5]/select", "label=Allow");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[6]/select", "label=Allow");
    echo "Saving Article Administrator article permissions\n";      
	$this->click("xpath=/html/body/form/fieldset/div/button[contains(text(),'Save')]");
//	
//	---- No confirmation message exists ----
//
    echo "Allow Article Administrator".$saltGroup." back end access, deny admin access\n";         
    $this->click("link=Global Configuration");
    $this->waitForPageToLoad("30000");
    $this->click("permissions");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[1]/select", "label=...");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[2]/select", "label=Allow");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[3]/select", "label=Deny");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[4]/select", "label=...");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[5]/select", "label=...");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[6]/select", "label=...");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[7]/select", "label=...");
    $this->select("//table[@id='acl-config']/tbody/tr[6]/td[8]/select", "label=...");
	sleep(2);    
    $this->click("link=Save & Close");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Save Success"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    } 
	$saltUser = mt_rand();
	echo("Add new user named My Test User". $saltUser . "\n");   	
	$this->click("link=User Manager");
    $this->waitForPageToLoad("30000");
    $this->click("//li[@id='toolbar-new']/a/span"); 
    $this->waitForPageToLoad("30000");
    $this->type("jform_name", "My Test User". $saltUser);
    $this->type("jform_username", "TestUser". $saltUser);
    $this->type("jform_password", "password");
    $this->type("jform_password2", "password");
    $this->type("jform_email", "TestUser". $saltUser."@test.com");
    $this->click("//form[@id='user-form']/div[2]/fieldset/ul/li[6]/input");
    $this->click("link=Save & Close");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Item successfully saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }   
    $this->doAdminLogout();
	sleep (1);   
 	echo("Log in to back end as  My Test User". $saltUser . ".\n");
 	$this->waitForPageToLoad("30000");       
    $this->type("mod-login-username", "TestUser". $saltUser);
	$this->type("mod-login-password", "password");
    $this->click("link=Log in");
    $this->waitForPageToLoad("30000");
    echo("Testing Test User". $saltUser . "access.\n");   
    try {
    	if ($this->isElementPresent("link=User Manager")) echo "User Manager test failed!\n";
    	if ($this->isElementPresent("link=Users")) echo "Users test failed!\n";
    	if ($this->isElementPresent("link=Menus")) echo "Menus test failed!\n";
    	if ($this->isElementPresent("link=Banner")) echo "Banner test failed!\n";
    	if ($this->isElementPresent("link=Contacts")) echo "Contacts test failed!\n";
    	if ($this->isElementPresent("link=Messaging")) echo "Messaging test failed!\n";
    	if ($this->isElementPresent("link=News Feeds")) echo "News Feeds test failed!\n";
    	if ($this->isElementPresent("link=Search")) echo "Search test failed!\n";    	    	    	    	
    	if ($this->isElementPresent("link=Web Links")) echo "Web Links test failed!\n";
    	if ($this->isElementPresent("link=Redirect")) echo "Redirect test failed!\n";    	    	    	
    	if ($this->isElementPresent("link=Extensions")) echo "Extensions test failed!\n";
    	if ($this->assertEquals("Menu Manager", $this->getText("//div[@id='cpanel']/div[5]/div/a"))) echo "Menu Manager test failed!\n";
		if ($this->assertEquals("User Manager", $this->getText("//div[@id='cpanel']/div[6]/div/a/span"))) echo "User Manager test failed!\n";    	    	    	
    		} catch (Exception $e) {}
		sleep(3);		
	$this->click("link=Module Manager");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("You are not authorised to view this resource."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Control Panel");
    $this->waitForPageToLoad("30000");
	$this->click("link=Article Manager");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Article Manager: Articles"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
	$this->doAdminLogout();
	$this->doAdminLogin();
	$this->deleteTestUsers('My Test User');
	$this->gotoAdmin();
    echo "Delete Article Administrator".$saltGroup." group.\n";	
    $this->click("link=Groups");
    $this->waitForPageToLoad("30000");
    $this->type("filter_search", "article administrator".$saltGroup);
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    $this->click("toggle");
    $this->click("link=Delete");
    $this->waitForPageToLoad("30000");
    try {
    	$this->assertTrue($this->isTextPresent("1 item(s) successfully deleted."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
    	array_push($this->verificationErrors, $e->toString());
    }
	$this->doAdminLogout();       	 
  }
}
?>