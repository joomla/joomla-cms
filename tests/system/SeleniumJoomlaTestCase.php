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
		$this->setBrowserUrl($cfg->host.$cfg->path);
		if(isset($cfg->selhost)) {
			$this->setHost($cfg->selhost);
		}
		echo ".\n".'Starting '.get_class($this).".\n";
	}

	function doAdminLogin()
	{
		//$this->setUp();
		echo "Logging in to admin.\n";
		$cfg = new SeleniumConfig();
		$this->open($cfg->path . "administrator/index.php?option=com_login");
		$this->waitForPageToLoad("30000");
		$this->type("mod-login-username", $cfg->username);
		$this->type("mod-login-password", $cfg->password);
		$this->click("link=Log in");
		$this->waitForPageToLoad("30000");
	}

	function doAdminLogout()
	{
		$this->gotoAdmin();
		echo "Logging out of back end.\n";
		$this->click("link=Logout");
	}

	function gotoAdmin()
	{
		echo "Browsing to admin.\n";
		$cfg = new SeleniumConfig();
		$this->open($cfg->path . "administrator");
	}

	function gotoSite()
	{
		echo "Browsing to site.\n";
		$cfg = new SeleniumConfig();
		$this->open($cfg->path);
	}

	function doFrontEndLogin()
	{
		$this->gotoSite();
		echo "Logging into front end of site.\n";
		$this->type("modlgn_username", "admin");
		$this->type("modlgn_passwd", "password");
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
		$this->gotoSite();
		echo "Logging out of front end of site.\n";
		$this->click("Submit");
		$this->waitForPageToLoad("30000");
	}
	
	function createUser($name, $userName, $password = 'password', $email = 'testuser@test.com', $group = 'Manager') {
		$this->gotoAdmin();
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
		
		// Set group 
		switch ($group)
		{
			case 'Manager' :
				$this->click("1group_6");
				break;

			case 'Administrator' :
				$this->click("1group_7");
				break;

			case 'Super Users' :
				$this->click("1group_8");
				break;

			case 'Park Rangers' :
				$this->click("1group_9");
				break;

			case 'Registered' :
				$this->click("1group_2");
				break;

			case 'Author' :
				$this->click("1group_3");
				break;

			case 'Editor' :
				$this->click("1group_4");
				break;
				
			case 'Publisher' :
				$this->click("1group_5");
				break;

			default:
				$this->click("1group_6");
				break;
		}
		
		$this->click("link=Save & Close");
		$this->waitForPageToLoad("30000");
		echo "New user created\n";
				
	}
	
	function deleteTestUsers($partialName = 'My Test User')
	{
		echo "Back to User Manager.\n";
	    $this->click("//img[@alt='User Manager']");
	    $this->waitForPageToLoad("30000");
	    
	    echo "Filter on user name\n";
	    $this->type("search", $partialName);
	    $this->click("//button[@type='submit']");
	    $this->waitForPageToLoad("30000");
	  
	    echo "Delete all users in view\n";
	    $this->click("toggle");
	    echo("Delete new user.\n");    
	    $this->click("link=Delete");
	    $this->waitForPageToLoad("30000");
	    try {
	    	$this->assertTrue($this->isTextPresent("item(s) successfully deleted."));
	    } catch (PHPUnit_Framework_AssertionFailedError $e) {
	    	array_push($this->verificationErrors, $e->toString());
	    }
	}

}
