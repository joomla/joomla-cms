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
	
	function doAdminLogin()
	{
		echo "Logging in to back end.\n";
		$cfg = new SeleniumConfig();
		if (!$this->isElementPresent("mod-login-username"))
		{
			$this->gotoAdmin();
			$this->click("link=Log out");
			$this->waitForPageToLoad("30000");
		}
		$this->type("mod-login-username", $cfg->username);
		$this->type("mod-login-password", $cfg->password);
		$this->click("link=Log in");
		$this->waitForPageToLoad("30000");
	}

	function doAdminLogout()
	{
		echo "Logging out of back end.\n";
		$this->click("link=Logout");
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

	function doFrontEndLogin()
	{
		$cfg = new SeleniumConfig();
		// check to see if we are already logged in
		if ($this->getValue("Submit") == "Log out")
		{
			echo "Logging out before loggin in. \n";
			$this->click("Submit");
			$this->waitForPageToLoad("30000");
			$this->click("link=Home");
			$this->waitForPageToLoad("30000");
		}
		echo "Logging in to front end.\n";
		$this->type("modlgn_username", $cfg->username);
		$this->type("modlgn_passwd", $cfg->password);
		$this->click("Submit");
		$this->waitForPageToLoad("30000");
	}

	function setTinyText($text)
	{
		$this->selectFrame("text_ifr");
		$this->type("tinymce", $text);
		$this->selectFrame("relative=top");
	}

	function doFrontEndLogout()
	{
		echo "Logging out of front end.\n";
		$this->click("//input[@value='Log out']");
		$this->waitForPageToLoad("30000");
	}

	function createUser($name = 'Test User', $userName = 'TestUser', $password = 'password', $email = 'testuser@test.com', $group = 'Manager')
	{
		$this->click("link=User Manager");
		$this->waitForPageToLoad("30000");
		echo("Add new user named " . $name . " in Group=" . $group . "\n");
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");
		$this->type("jform_name", $name);
		$this->type("jform_username", $userName);
		$this->type("jform_password", $password);
		$this->type("jform_password2", $password);
		$this->type("jform_email", $email);
        $id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$group.'\')]/label@for');
        $this->click($id);
		$this->click("link=Save & Close");
		$this->waitForPageToLoad("30000");
		echo "New user created\n";
	}
	
	function toggleAssignedGroupCheckbox($groupName) {
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
		try
		{
			$this->assertTrue($this->isTextPresent("success"));
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			echo "** ERROR in deleteTestUsers, SeleniumJoomlaTestCase, line 181 **\n";
			array_push($this->verificationErrors, $e->toString());
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
		$this->click("link=Save & Close");
		$this->waitForPageToLoad("30000");
		try
		{
			$this->assertTrue($this->isTextPresent("Group successfully saved"));
			echo "Creation of " . $groupName . " succeeded.\n";
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $e->toString());
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

		echo "Delete all users in view.\n";
		$this->click("checkall-toggle");
		echo("Delete new user.\n");
		$this->jClick('Delete');
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
		try
		{
			$this->assertTrue($this->isTextPresent("Access level successfully saved"));
			echo "Addding group " . $groupName . " to " . $levelName . " access level succeeded.\n";
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $e->toString());
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
		case 'Save & Close':
			echo "Testng Save & Close capability.\n";
			$this->click("//li[@id='toolbar-save']/a");
			$this->waitForPageToLoad("30000");
			try
			{
				$this->assertTrue($this->isTextPresent("successfully saved"), "Save success text not present, SeleniumTestCase line 327");
				$this->assertFalse($this->isTextPresent("ERROR"), "Error message present, SeleniumTestCase line 328");
				echo "Item successfully saved.\n";
			}
			catch (PHPUnit_Framework_AssertionFailedError $e)
			{
				array_push($this->verificationErrors, $e->getTraceAsString());
			}
			break;
		case 'New':
			echo "Testng New capability.\n";
			$this->click("//li[@id='toolbar-new']/a");
			$this->waitForPageToLoad("30000");
			break;
		case 'Delete':
			echo "Testng Delete capability.\n";
			$this->click("//li[@id='toolbar-delete']/a");
			$this->waitForPageToLoad("30000");
			try
			{
				$this->assertTrue(($this->isTextPresent("deleted") OR $this->isTextPresent("removed") OR $this->isTextPresent("trashed")), 'Delete confirm text wrong, SeleniumJoomlaTestCase line 345');
				$this->assertFalse($this->isTextPresent("ERROR"), "Error message present, SeleniumTestCase line 346");
				echo "Deletion of item(s) succeeded.\n";
			}
			catch (PHPUnit_Framework_AssertionFailedError $e)
			{
				array_push($this->verificationErrors, $e->toString());
			}
			break;
		case 'Access Levels':
			echo "Navagating to Access Levels.\n";
			$this->click("//a[contains(@class,'icon-16-levels')]");
			$this->waitForPageToLoad("30000");
			break;
		case 'Menu Manager':
			echo "Navagating to Menu Manager.\n";
			$this->click("//a[contains(@class,'icon-16-menumgr')]");
			$this->waitForPageToLoad("30000");
			break;
		case 'Redirect Manager':
			echo "Navagating to Redirect Manager.\n";
			$this->click("//a[contains(@class, 'icon-16-redirect')]");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Redirect Manager: Links"));
			break;
		case 'Weblinks':
			echo "Navagating to Weblinks.\n";
			$this->click("//a[contains(@class, 'icon-16-weblinks')]");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Web Links Manager: Web Links"));
			break;
		case 'Unpublish':
			echo "Testng Unpublish capability.\n";
			$this->click("//li[@id='toolbar-unpublish']/a/span");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("successfully unpublished"));
			$this->assertFalse($this->isTextPresent("Edit state is not permitted"));
			break;
		case 'Trash':
			echo "Testng Trash capability.\n";
			$this->click("//li[@id='toolbar-trash']/a/span");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("successfully trashed"));
			break;
		case 'Edit':
			echo "Testng Edit capability.\n";
			$this->click("//li[@id='toolbar-edit']/a/span");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent(": Edit", $this->getText("//div[contains(@class,'pagetitle')]/h2")));
			break;
		case 'Options':
			echo "Opening options modal.\n";
			$this->click("//li[@id='toolbar-popup-Popup']/a/span");
			echo "wait 2 seconds\n";
			sleep(2);
			for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
					if ($this->isTextPresent("Options")) break;
				} catch (Exception $e) {}
				sleep(1);
			}
			$this->assertTrue($this->isTextPresent("Options"));
			break;			
		case 'Article Manager':
			echo "Navigating to Article Manager.\n";
			$this->click("//a[contains(@class,'icon-16-article')]");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Article Manager: Articles"));			
			break;
		case 'Global Configuration':
			echo "Navigating to Global Configuration.\n";
			$this->click("//a[contains(@class,'icon-16-config')]");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Global Configuration",$this->getText("//div[contains(@class,'pagetitle')]/h2")));
			break;
			
			
		default:
			$this->click("//li[@id='toolbar-new']/a");
			$this->waitForPageToLoad("30000");
			break;
		}
	}
	
	function clickTab($formTab ='Permissions')
		{
			$this->click("//dt[contains(span,'$formTab')]");
		}
		
	function toggleFeatured($articleTitle)
	{
		echo "Toggling Featured on/off for article " . $articleTitle . "\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), '" . 
			$articleTitle . "')]/../../td[4]/a/img");
		$this->waitForPageToLoad("30000");
	}
	
	function togglePublished($articleTitle)
	{
		echo "Toggling Featured on/off for article " . $articleTitle . "\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), '" . 
			$articleTitle . "')]/../../td[3]/a");
		$this->waitForPageToLoad("30000");
	}
	
	
	function checkNotices()
	{
		try
		{
			$this->assertFalse($this->isTextPresent("( ! ) Notice"), "**Warning: PHP Notice found on page!");
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
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
		if ($command == 'waitForPageToLoad' && isset($arguments[1]) && $arguments[1] !== false)
		{
			try
			{
				$this->assertFalse($this->isTextPresent("( ! ) Notice"), "**Warning: PHP Notice found on page!");
			}
			catch (PHPUnit_Framework_AssertionFailedError $e)
			{
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
