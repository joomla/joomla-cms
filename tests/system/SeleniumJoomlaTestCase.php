<?php
/**
 * @version		$Id$
 * @package		Joomla.FunctionalTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SeleniumJoomlaTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
	public $cfg; // configuration so tests can get at the fields	

	public function setUp()
	{
		$cfg = new SeleniumConfig();
		$this->cfg = $cfg; // save current configuration
		$this->setBrowser($cfg->browser);
		$this->setBrowserUrl($cfg->host . $cfg->path);
		if (isset($cfg->selhost))
		{
			$this->setHost($cfg->selhost);
		}
		echo ".\n" . 'Starting ' . get_class($this) . ".\n";
	}
	
	function checkMessage($message)
	{
		try {
			$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., '$message')]"), 'Message not displayed or message changed, SeleniumJoomlaTestCase line 31');			
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }		
	}
	
	function changeAssignedGroup($username,$group)
	{
		$this->jClick('User Manager');
	    $this->click("link=$username");
	    $this->waitForPageToLoad("30000");		
		echo "Changing $username group assignment of $group group.\n";         
		$id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
		$this->click($id);
        $this->jClick('Save & Close');
		try {
	        $this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'User successfully saved.')]"), 'User group save message not displayed or message changed, SeleniumJoomlaTestCase line 49');	
	    }
	    catch (PHPUnit_Framework_AssertionFailedError $e){
	        array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }        
		
	}
	
	function createUser($name = 'Test User', $username = 'TestUser', $password = 'password', $email = 'testuser@test.com', $group = 'Manager')
	{
		$this->click("link=User Manager");
		$this->waitForPageToLoad("30000");
		echo("Add new user named " . $name . " to " . $group . " group.\n");
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");
		$this->type("jform_name", $name);
		$this->type("jform_username", $username);
		$this->type("jform_password", $password);
		$this->type("jform_password2", $password);
		$this->type("jform_email", $email);
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
		$this->click("link=Save & Close");
		$this->waitForPageToLoad("30000");
		try	{
			 $this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"),'Creation of Test User(s) failed.');
		}
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
	}	

	function doAdminLogin($username = null,$password = null)
	{
		echo "Logging in to back end.\n";
		$cfg = new SeleniumConfig();
		if(!isset($username))$username=$cfg->username;
		if(!isset($password))$password=$cfg->password;			
		if (!$this->isElementPresent("mod-login-username"))
		{
			$this->gotoAdmin();
			$this->click("link=Log out");
			$this->waitForPageToLoad("30000");
		}
		$this->type("mod-login-username", $username);
		$this->type("mod-login-password", $password);
		$this->click("link=Log in");
		$this->waitForPageToLoad("30000");
	}

	function doAdminLogout()
	{
		echo "Logging out of back end.\n";
		$this->click("link=Log out");
		$this->waitForPageToLoad("30000");
	}

	function gotoAdmin()
	{
		echo "Browsing to back end.\n";
		$cfg = new SeleniumConfig();
		$this->open($cfg->path . "administrator");
		$this->waitForPageToLoad("30000");
	}

	function gotoSite()
	{
		echo "Browsing to front end.\n";
		$cfg = new SeleniumConfig();
		$this->open($cfg->path);
		$this->waitForPageToLoad("30000");
	}
	
	function doFrontEndLogin($username = null,$password = null)
	{
		$cfg = new SeleniumConfig();
		if(!isset($username))$username=$cfg->username;
		if(!isset($password))$password=$cfg->password;		
		// check to see if we are already logged in
		if ($this->getValue("Submit") == "Log out")
		{
			echo "Logging out before logging in. \n";
			$this->click("Submit");
			$this->waitForPageToLoad("30000");
			$this->click("link=Home");
			$this->waitForPageToLoad("30000");
		}
		echo "Logging in to front end.\n";
		$this->type("modlgn_username", $username);
		$this->type("modlgn_passwd", $password);
		$this->click("Submit");
		$this->waitForPageToLoad("30000");
	}		

	function doFrontEndLogout()
	{
		echo "Logging out of front end.\n";
		$this->click("//input[@value='Log out']");
		$this->waitForPageToLoad("30000");
	}

	function toggleAssignedGroupCheckbox($groupName)
	{		
	    $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$groupName.'\')]/label@for');
	    $this->click($id);	    
	}

	function deleteTestUsers($partialName = 'test')
	{
		echo "Browse to User Manager.\n";
		$this->click("link=User Manager");
		$this->waitForPageToLoad("30000");

		echo "Filter on user name\n";
		$this->type("filter_search", $partialName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		echo "Delete all users in view.\n";
		$this->click("checkall-toggle");
		echo("Delete new user.\n");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		try	{
			$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"),'Deletion of Test User(s) failed.');
		}
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			echo "** ERROR in deleteTestUsers, SeleniumJoomlaTestCase, line 142 **\n";
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
	}

	function createGroup($groupName, $groupParent = 'Public')
	{
		$this->click("link=Groups");
		$this->waitForPageToLoad("30000");
		echo "Create new group " . $groupName . ".\n";
		$this->click("link=New");
		$this->waitForPageToLoad("30000");
		$this->type("jform_title", $groupName);
		$this->select("id=jform_parent_id", "label=regexp:.*".$groupParent);
		$this->jClick("Save & Close");
		try {
			$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"),'Creation of ' . $groupName . ' failed.');
			echo "Creation of " . $groupName . " succeeded.\n";
		}
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
	}

	function deleteGroup($partialName = 'test')
	{
		echo "Browse to User Manager: Groups.\n";
		$this->click("link=Groups");
		$this->waitForPageToLoad("30000");

		echo "Filter on " . $partialName . ".\n";
		$this->type("filter_search", $partialName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		echo "Delete all groups in view.\n";
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a");
		$this->waitForPageToLoad("30000");
		try	{
			$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"), 'Group deletion failed or confirm text wrong, SeleniumJoomlaTestCase line 197');
			echo "Deletion succeeded.\n";
		}
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

		try	{
			$this->assertFalse($this->isTextPresent("No Groups selected"), 'No Groups selected for deletion, SeleniumJoomlaTestCase line 207');
		}
		catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
	}

	function createLevel($levelName, $userGroup)
	{
		$this->jClick('Access Levels');
		$this->jClick('New');
		echo "Create new access level named " . $levelName . "\n";
		$this->type("jform_title", $levelName);
		$this->jInput($userGroup);
		echo "Selecting User Groups having access to " . $levelName . "\n";
		$this->jClick('Save & Close');
	}

	function deleteLevel($partialName = 'test')
	{
		$this->jClick('Access Levels');
		echo "Filter on " . $partialName . ".\n";
		$this->type("filter_search", $partialName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		echo "Delete all levels in view.\n";
		$this->click("checkall-toggle");
		$this->jClick('Delete');
	}

	function changeAccessLevel($levelName = 'Registered', $groupName = 'Public')
	{
		echo "Add group " . $groupName . " to " . $levelName . " access level.\n";
		echo "Navagating to Access Levels.\n";
		$this->jClick('Access Levels');
		$this->click("//tr/td[contains(a,'$levelName')]/preceding-sibling::*/input");
		$this->jClick('Edit');
		$this->assertTrue($this->isTextPresent(": Edit", $this->getText("//div[contains(@class,'pagetitle')]/h2")));
		$id = $this->getAttribute('//fieldset[@class=\'adminform\']/ul/li[contains(label,\''.$groupName.'\')]/label@for');
        $this->click($id);
        $this->jClick('Save & Close');
		try	{
			$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
			echo "Addding group " . $groupName . " to " . $levelName . " access level succeeded.\n";
		}
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

	}

	/**
	 * Tests for the presence of a Go button and clicks it if present.
	 * Used for the hathor accessible template when filtering on lists in back end.
	 *
	 * @since	1.6
	 */
	function clickGo()
	{
		if ($this->isElementPresent("filter-go"))
		{
			$this->click("filter-go");
		}
	}

	public function countErrors()
	{
		if ($count = count($this->verificationErrors))
		{
			echo "\n***Warning*** " . $count . " verification error(s) encountered.\n";
		}
	}

	/*
	 * Allow selection of an input based on the text contained in its correspondig label
	 */
	function jInput($labelText)
	{
		$this->click("//label[contains(.,'$labelText')]/preceding-sibling::input");
	}

	/*
	 * Unifies button and menu item selection based on corresponding IDs and Classes
	 */
	function jClick($item)
	{
		switch ($item)
		{
		case 'Access Levels':
			$screen="User Manager: Access Levels";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-levels')]");
			$this->waitForPageToLoad("30000");
		    try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Article Manager':
			$screen="Article Manager: Articles";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-article')]");
			$this->waitForPageToLoad("30000");
		    try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Contacts':
			$screen='Contact Manager: Contacts';
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-contact')]");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
		    break;
		case 'Delete':
			echo "Testng Delete capability.\n";
			$this->click("//li[@id='toolbar-delete']/a");
			$this->waitForPageToLoad("30000");
			try {
				$this->assertTrue(($this->isTextPresent("deleted") OR $this->isTextPresent("removed") OR $this->isTextPresent("trashed")), 'Deletion failed or confirm text wrong, SeleniumJoomlaTestCase line 310');
				echo "Deletion of item(s) succeeded.\n";
			}
			catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
			}
			break;
		case 'Edit':
			echo "Testng Edit capability.\n";
			$this->click("//li[@id='toolbar-edit']/a/span");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent(": Edit", $this->getText("//div[contains(@class,'pagetitle')]/h2")));
			break;
		case 'Global Configuration':
			$screen='Global Configuration';
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-config')]");
			$this->waitForPageToLoad("30000");
			try {
				$this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
			}
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Global Configuration: Permissions':
			$screen='Global Configuration';
			echo "Navigating to $screen: Permissions.\n";
			$this->click("//a[contains(@class,'icon-16-config')]");
			$this->waitForPageToLoad("30000");
	    	$this->click("permissions");			
			try {
				$this->assertTrue($this->isElementPresent("//a[contains(@id,'permissions')][contains(@class,'active')]"));
			}
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;			
		case 'Groups':
			$screen="User Manager: Groups";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-groups')]");
			$this->waitForPageToLoad("30000");
		    try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Menu Manager':
			$screen="Menu Manager: Menus";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-menumgr')]");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Menu Items':
			$screen="Menu Manager: Menu Items";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-menumgr')]");
			$this->waitForPageToLoad("30000");
			$this->click("link=Menu Items");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Module Manager':
			$screen="Module Manager: Modules";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class,'icon-16-module')]");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'New':
			echo "Clicking New toolbar button.\n";
			$this->click("//li[@id='toolbar-new']/a");
			$this->waitForPageToLoad("30000");
			break;
		case 'Options':
			echo "Opening options modal.\n";
			$this->click("//li[@id='toolbar-popup-options']/a/span");
			for ($second = 0; ; $second++) {
				if ($second >= 15) $this->fail("timeout");
				try {
					if ($this->isElementPresent("//dl[@id='config-tabs-com_content_configuration']")) break;
				}
				catch (PHPUnit_Framework_AssertionFailedError $e) {
					array_push($this->verificationErrors, $this->getTraceFiles($e));
				}
				sleep(1);
			}
			$this->assertTrue($this->isTextPresent("Options"));
			break;
		case 'Redirect Manager':
			$screen="Redirect Manager: Links";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class, 'icon-16-redirect')]");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Save & Close':
			echo "Clicking Save & Close toolbar button.\n";
			$this->click("//li[@id='toolbar-save']/a");
			$this->waitForPageToLoad("30000");
			try {
				$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"), "Save success text not present, SeleniumTestCase line 327");
				$this->assertFalse($this->isElementPresent("//dl[@id='system-message'][contains(., 'error')]"), "Error message present, SeleniumTestCase line 328");
				echo "Item successfully saved.\n";
			}
			catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
			}
			break;
		case 'Trash':
			echo "Clicking Trash toolbar button.\n";
			$this->click("//li[@id='toolbar-trash']/a/span");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"),'Error trashing item, SeleniumTestCase line 491.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Unpublish':
			echo "Clicking Unpublish toolbar button.\n";
			$this->click("//li[@id='toolbar-unpublish']/a/span");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"),'Error unpublishing item, SeleniumTestCase line 505.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			try {
		        $this->assertFalse($this->isTextPresent("Edit state is not permitted"),"Access issues with unpublishing item, SeleniumTestCase line 515.");
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'User Manager':
			$screen="User Manager: Users";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class, 'icon-16-user')]");
			$this->waitForPageToLoad("30000");
		    try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Weblinks':
			$screen="Web Links Manager: Web Links";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class, 'icon-16-weblinks')]");
			$this->waitForPageToLoad("30000");
		    try {
		        $this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		case 'Weblink Categories':
			$screen="Category Manager: Weblinks";
			echo "Navigating to ".$screen.".\n";
			$this->click("//a[contains(@class, 'icon-16-weblinks-cat')]");
			$this->waitForPageToLoad("30000");
			try {
		        $this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
			break;
		default:
			$this->click("//li[@id='toolbar-new']/a");
			echo "Clicking New toolbar button.\n";
			$this->waitForPageToLoad("30000");
			break;
		}
	}

	function filterView($filterOn ='Test')
		{
			$this->type("filter_search", $filterOn);
    		$this->click("//button[@type='submit']");
    		$this->waitForPageToLoad("30000");
		}

	function clickTab($formTab ='Permissions')
		{
			$this->click("//dt[contains(span,'$formTab')]");
		}
		
	function restoreDefaultGlobalPermissions()
	{
		$this->jClick('Global Configuration: Permissions');
		echo "Restoring default global permissions.\n";
		$this->select("//tr[1]/td[1]/select", "label=...");
		$this->select("//tr[2]/td[1]/select", "label=Allow");
		$this->select("//tr[3]/td[1]/select", "label=...");
		$this->select("//tr[4]/td[1]/select", "label=Allow");
		$this->select("//tr[5]/td[1]/select", "label=...");
		$this->select("//tr[6]/td[1]/select", "label=...");
		$this->select("//tr[7]/td[1]/select", "label=...");
		$this->select("//tr[8]/td[1]/select", "label=...");
		$this->select("//tr[9]/td[1]/select", "label=...");
		$this->select("//tr[10]/td[1]/select", "label=...");
		$this->select("//tr[1]/td[2]/select", "label=...");
		$this->select("//tr[2]/td[2]/select", "label=Allow");
		$this->select("//tr[3]/td[2]/select", "label=...");
		$this->select("//tr[4]/td[2]/select", "label=...");
		$this->select("//tr[5]/td[2]/select", "label=...");
		$this->select("//tr[6]/td[2]/select", "label=...");
		$this->select("//tr[7]/td[2]/select", "label=...");
		$this->select("//tr[8]/td[2]/select", "label=...");
		$this->select("//tr[9]/td[2]/select", "label=...");
		$this->select("//tr[10]/td[2]/select", "label=...");
		$this->select("//tr[1]/td[3]/select", "label=...");
		$this->select("//tr[2]/td[3]/select", "label=...");
		$this->select("//tr[3]/td[3]/select", "label=...");
		$this->select("//tr[4]/td[3]/select", "label=...");
		$this->select("//tr[5]/td[3]/select", "label=...");
		$this->select("//tr[6]/td[3]/select", "label=...");
		$this->select("//tr[7]/td[3]/select", "label=...");
		$this->select("//tr[8]/td[3]/select", "label=...");
		$this->select("//tr[9]/td[3]/select", "label=...");
		$this->select("//tr[10]/td[3]/select", "label=Allow");
		$this->select("//tr[1]/td[4]/select", "label=...");
		$this->select("//tr[2]/td[4]/select", "label=...");
		$this->select("//tr[3]/td[4]/select", "label=Allow");
		$this->select("//tr[4]/td[4]/select", "label=...");
		$this->select("//tr[5]/td[4]/select", "label=...");
		$this->select("//tr[6]/td[4]/select", "label=...");
		$this->select("//tr[7]/td[4]/select", "label=...");
		$this->select("//tr[8]/td[4]/select", "label=...");
		$this->select("//tr[9]/td[4]/select", "label=...");
		$this->select("//tr[10]/td[4]/select", "label=...");
		$this->select("//tr[1]/td[5]/select", "label=...");
		$this->select("//tr[2]/td[5]/select", "label=Allow");
		$this->select("//tr[3]/td[5]/select", "label=...");
		$this->select("//tr[4]/td[5]/select", "label=...");
		$this->select("//tr[5]/td[5]/select", "label=Allow");
		$this->select("//tr[6]/td[5]/select", "label=...");
		$this->select("//tr[7]/td[5]/select", "label=...");
		$this->select("//tr[8]/td[5]/select", "label=...");
		$this->select("//tr[9]/td[5]/select", "label=...");
		$this->select("//tr[10]/td[5]/select", "label=...");
		$this->select("//tr[1]/td[6]/select", "label=...");
		$this->select("//tr[2]/td[6]/select", "label=Allow");
		$this->select("//tr[3]/td[6]/select", "label=...");
		$this->select("//tr[4]/td[6]/select", "label=...");
		$this->select("//tr[5]/td[6]/select", "label=...");
		$this->select("//tr[6]/td[6]/select", "label=...");
		$this->select("//tr[7]/td[6]/select", "label=...");
		$this->select("//tr[8]/td[6]/select", "label=...");
		$this->select("//tr[9]/td[6]/select", "label=...");
		$this->select("//tr[10]/td[6]/select", "label=...");
		$this->select("//tr[1]/td[7]/select", "label=...");
		$this->select("//tr[2]/td[7]/select", "label=Allow");
		$this->select("//tr[3]/td[7]/select", "label=...");
		$this->select("//tr[4]/td[7]/select", "label=...");
		$this->select("//tr[5]/td[7]/select", "label=...");
		$this->select("//tr[6]/td[7]/select", "label=Allow");
		$this->select("//tr[7]/td[7]/select", "label=...");
		$this->select("//tr[8]/td[7]/select", "label=...");
		$this->select("//tr[9]/td[7]/select", "label=...");
		$this->select("//tr[10]/td[7]/select", "label=...");
		$this->select("//tr[1]/td[8]/select", "label=...");
		$this->select("//tr[2]/td[8]/select", "label=Allow");
		$this->select("//tr[3]/td[8]/select", "label=...");
		$this->select("//tr[4]/td[8]/select", "label=...");
		$this->select("//tr[5]/td[8]/select", "label=...");
		$this->select("//tr[6]/td[8]/select", "label=...");
		$this->select("//tr[7]/td[8]/select", "label=Allow");
		$this->select("//tr[8]/td[8]/select", "label=...");
		$this->select("//tr[9]/td[8]/select", "label=...");
		$this->select("//tr[10]/td[8]/select", "label=...");		
	  	$this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");
		try {
	        $this->assertTrue($this->isTextPresent("Configuration successfully saved."));
	    } catch (PHPUnit_Framework_AssertionFailedError $e){
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }		
	}		
		
	function setGlobalPermission($action,$group,$permission)
		{			
		$this->jClick('Global Configuration: Permissions');
		echo "Setting $action action for $group to $permission.\n";
		switch ($action)
			{
				case 'Site Login':	    	    
				$this->select("//tr[contains(th,'$group')]/td[1]/select", "label=$permission");
				break;
				case 'Admin Login':	    	    
				$this->select("//tr[contains(th,'$group')]/td[2]/select", "label=$permission");
				break;
				case 'Admin':	    	    
				$this->select("//tr[contains(th,'$group')]/td[3]/select", "label=$permission");
				break;
				case 'Manage':	    	    
				$this->select("//tr[contains(th,'$group')]/td[4]/select", "label=$permission");
				break;
				case 'Create':	    	    
				$this->select("//tr[contains(th,'$group')]/td[5]/select", "label=$permission");
				break;
				case 'Delete':	    	    
				$this->select("//tr[contains(th,'$group')]/td[6]/select", "label=$permission");
				break;
				case 'Edit':	    	    
				$this->select("//tr[contains(th,'$group')]/td[7]/select", "label=$permission");
				break;
				case 'Edit State':	    	    
				$this->select("//tr[contains(th,'$group')]/td[8]/select", "label=$permission");
				break;				
			}		
  	    $this->click("//li[@id='toolbar-save']/a/span");
	    $this->waitForPageToLoad("30000");
		try {
		        $this->assertTrue($this->isTextPresent("Configuration successfully saved."));
		    }
		    catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $this->getTraceFiles($e));
		    }
 			    			
		}		

	function setTinyText($text)
		{
			$this->selectFrame("jform_text_ifr");
			$this->type("tinymce", $text);
			$this->selectFrame("relative=top");
		}
	
	function toggleFeatured($articleTitle)
		{
			echo "Toggling Featured on/off for article " . $articleTitle . "\n";
			$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), '" . $articleTitle . "')]/../../td[4]/a/img");
			$this->waitForPageToLoad("30000");
		}

	function togglePublished($articleTitle)
	{
		echo "Toggling publishing of article " . $articleTitle . "\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), '" .	$articleTitle . "')]/../../td[3]/a");
		$this->waitForPageToLoad("30000");
	}


	function checkNotices()
	{
		try {
			$this->assertFalse($this->isTextPresent("( ! ) Notice"), "**Warning: PHP Notice found on page!");
		}
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			echo "**Warning: PHP Notice found on page\n";
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}
	}

	/**
	 * Magic method to check for PHP Notice whenever the waitForPageToLoad command is invoked
	 * To suppress the check, use waitForPageToLoad('3000', false);
	 *
	 * @param string $command
	 * @param array $arguments
	 * @return results of waitForPageToLoad method
	 */
	public function __call($command, $arguments)
	{
		$return = parent::__call($command, $arguments);
		if ($command == 'waitForPageToLoad' && (!isset($arguments[1]) || $arguments[1] !== false))
		{
			try {
				$this->assertFalse($this->isTextPresent("( ! ) Notice"), "**Warning: PHP Notice found on page!");
			}
			catch (PHPUnit_Framework_AssertionFailedError $e) {
				echo "**Warning: PHP Notice found on page\n";
				array_push($this->verificationErrors, $this->getTraceFiles($e));
			}
		}
		return $return;
	}

	/**
	 * Function to extract our test file information from the $e stack trace.
	 * Makes the error reporting more readable, since it filters out all of the PHPUnit files.
	 *
	 * @param PHPUnit_Framework_AssertionFailedError $e
	 * @return string with selected files based on path
	 */
	public function getTraceFiles($e) {
		$trace = $e->getTrace();
		$path = $this->cfg->folder . $this->cfg->path;
		$path = str_replace('\\', '/', $path);
		$message = '';
		foreach ($trace as $traceLine) {
			if (isset($traceLine['file'])){
				$file = str_replace('\\', '/', $traceLine['file']);
				if (stripos($file, $path) !== false) {
					$message .= "\n" . $traceLine['file'] . '(' . $traceLine['line'] . '): ' .
						$traceLine['class'] . $traceLine['type'] . $traceLine['function'] ;
				}
			}
		}
		return $e->toString() . $message;
	}

}
