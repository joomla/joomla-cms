<?php
/**
 * @version		$Id:
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
 
	    $userName = 'ACL Test User' . $salt;
		$password = 'password' . $salt; 
		$login = 'acltestuser' . $salt;
		$email = $login . '@test.com';
		$group = 'Public';
		echo "Create $userName and add to $group group.\n";
	    $this->createUser($userName, $login, $password, $email, $group);

	    echo "Removing $userName from Registered group.\n";	    
	   	$this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $id = $this->getAttribute('//fieldset[@id="user-groups"]/ul/li[contains(label,"Registered")]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');       
	    	    
	    $this->jClick('Global Configuration');
	    $this->click("permissions");
        echo "Setting all roles to inherit for $userName.\n";
		$this->select("//tr[2]/td[1]/select", "label=...");
		$this->select("//tr[5]/td[1]/select", "label=...");
		$this->select("//tr[2]/td[2]/select", "label=...");
		//Do not deny admin access or you can't log back in
		//$this->select("//tr[4]/td[3]/select", "label=...");
		$this->select("//tr[3]/td[4]/select", "label=...");
		$this->select("//tr[2]/td[5]/select", "label=...");
		$this->select("//tr[6]/td[5]/select", "label=...");
		$this->select("//tr[2]/td[6]/select", "label=...");
		$this->select("//tr[2]/td[7]/select", "label=...");
		$this->select("//tr[7]/td[7]/select", "label=...");
		$this->select("//tr[2]/td[8]/select", "label=...");
		$this->select("//tr[8]/td[8]/select", "label=...");
			    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  		$this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");	    

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"), 'Message not displayed or message changed, SeleniumJoomlaTestCase line 31');			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();
	    
    	$this->gotoAdmin();		
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();
	    echo "Logging in to front end.\n";
		$this->type("modlgn_username", $login);
		$this->type("modlgn_passwd", $password);
		$this->click("Submit");
		$this->waitForPageToLoad("30000");
		$this->checkMessage($message);
		
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 	
		$this->checkMessage($message);	
		
    	$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");		
        $group='Manager';
		echo "Changing $userName to $group group.\n";         
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->gotoAdmin();
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");	    

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();          

    	$this->gotoAdmin();
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();
	   	$this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);
		
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password);
		$this->checkMessage($message);  	
		
    	$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Administrator';
		echo "Changing $userName to $group group.\n";         
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->gotoAdmin();
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");	    

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        	    
    	$this->gotoAdmin();	    
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();
	    $this->doFrontEndLogin($login,$password); 	
		$this->checkMessage($message);
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);					  	
		
		$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Super Users';
		echo "Changing $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();
	    echo "Logging in to front end.\n";
	    $this->doFrontEndLogin($login,$password); 
		    $this->doFrontEndLogin($login,$password); 		
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
		$this->doFrontEndLogout();	    
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password);
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();
    	
    	$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        
	    $group='Super Users';	    
	    echo "Removing $userName from $group group.\n"; 
	    $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);

	    $group='Administrator';	    
	    echo "Removing $userName from $group group.\n"; 
	    $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);

	    $group='Manager';	    
	    echo "Removing $userName from $group group.\n"; 
	    $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);

	    $group='Public';	    
	    echo "Removing $userName from $group group.\n"; 
	    $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);        
	    
	    $group='Registered';
		echo "Adding $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	   
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	

		$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Author';
		echo "Changing $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);		    
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	

		$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Editor';
		echo "Changing $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);		    
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	

		$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Publisher';
		echo "Changing $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);		    
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	

		$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Shop Suppliers';
		echo "Changing $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);		    
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	

		$this->gotoAdmin();				
		$this->jClick('User Manager');
	    $this->click("link=$userName");
	    $this->waitForPageToLoad("30000");
        $group='Customer Group';
		echo "Changing $userName to $group group.\n";        
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
        
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Allow");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 
		try {
			$this->assertTrue($this->isElementPresent("//form[@id='form-login'][contains(., '$userName')]"));			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }	    	
    	$this->doFrontEndLogout();        
        
    	$this->gotoAdmin();       	
    	$this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=Deny");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	    
		    	
	    $this->gotoAdmin();	
	    $this->jClick('Global Configuration');
	    $this->click("permissions");	    
		$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=...");
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");

	    $this->gotoSite();	    
	    $this->doFrontEndLogin($login,$password); 		
		$this->checkMessage($message);	       	
						
    	$this->gotoAdmin();	    
	    $this->deleteTestUsers();
	    $this->doAdminLogOut();	
  }
}
?>