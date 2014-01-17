<?php
/**
 * @package		Joomla.FunctionalTest
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SeleniumJoomlaTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
	public $cfg; // configuration so tests can get at the fields
	protected $captureScreenshotOnFailure = false;

	protected $screenshotPath = null;

	protected $screenshotUrl = null;

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
		$this->jPrint ( ".\n" . 'Starting ' . get_class($this) . ".\n");

		if (isset($cfg->captureScreenshotOnFailure) && $cfg->captureScreenshotOnFailure)
		{
			$this->captureScreenshotOnFailure = true;
			$this->screenshotPath = $cfg->folder . $cfg->path . $cfg->screenShotPath;
			$this->screenshotUrl = $cfg->host . $cfg->path . $cfg->screenShotPath;
		}
	}

	function checkMessage($message)
	{
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., '$message')]"), 'Message not displayed or message changed, SeleniumJoomlaTestCase line 31');
	}

	function changeAssignedGroup($username,$group)
	{
		$this->jClick('User Manager');
		$this->click("link=$username");
		$this->waitForPageToLoad("30000");
		$this->jPrint ( "Changing $username group assignment of $group group.\n");
		$this->click("//li/a[contains(text(), 'Assigned User Groups')]");
		$this->click("//div[@id='groups']//label[contains(., '$group')]");
		$this->jClick('Save & Close');
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'User successfully saved.')]"), 'User group save message not displayed or message changed, SeleniumJoomlaTestCase line 49');
	}

	function createUser($name = 'Test User', $username = 'TestUser', $password = 'password', $email = 'testuser@test.com', $group = 'Manager')
	{
		$this->click("link=User Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Add new user named " . $name . " to " . $group . " group.\n");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->type("jform_name", $name);
		$this->type("jform_username", $username);
		$this->type("jform_password", $password);
		$this->type("jform_password2", $password);
		$this->type("jform_email", $email);
		$this->click("//li/a[contains(text(), 'Assigned User Groups')]");
		$this->click("//div[@id='groups']//label[contains(., '$group')]");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"),'Creation of Test User(s) failed.');
	}

	function doAdminLogin($username = null,$password = null)
	{
		$this->jPrint ( "Logging in to back end.\n");
		$cfg = new SeleniumConfig();
		if(!isset($username))$username=$cfg->username;
		if(!isset($password))$password=$cfg->password;
		if (!$this->isElementPresent("mod-login-username"))
		{
			$this->gotoAdmin();
			$this->click("//li/a[contains(@href, 'option=com_login&task=logout')]");
			$this->waitForPageToLoad("30000");
		}
		$this->type("mod-login-username", $username);
		$this->type("mod-login-password", $password);
		if ($this->isElementPresent("//button[contains(., 'Log in')]"))
		{
			$this->click("//button[contains(., 'Log in')]");
		}
		elseif ($this->isElementPresent("//a[contains(., 'Log in')]"))
		{
			$this->click("//a[contains(., 'Log in')]");
		}
		$this->waitForPageToLoad("30000");
	}

	function doAdminLogout()
	{
		$this->jPrint ( "Logging out of back end.\n");
		$this->click("//li/a[contains(@href, 'option=com_login&task=logout')]");
		$this->waitForPageToLoad("30000");
	}

	function gotoAdmin()
	{
		$this->jPrint ( "Browsing to back end.\n");
		$cfg = new SeleniumConfig();
		$this->open($cfg->path . "administrator");
		$this->waitForPageToLoad("30000");
	}

	function gotoSite()
	{
		$this->jPrint ( "Browsing to front end.\n");
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
			$this->jPrint ( "Logging out before logging in. \n");
			$this->click("Submit");
			$this->waitForPageToLoad("30000");
			$this->click("link=Home");
			$this->waitForPageToLoad("30000");
		}
		$this->jPrint ( "Logging in to front end.\n");
		$this->type("modlgn-username", $username);
		$this->type("modlgn-passwd", $password);
		$this->click("Submit");
		$this->waitForPageToLoad("30000");
	}

	function doFrontEndLogout()
	{
		if ($this->getValue("Submit") == "Log out")
		{
			$this->jPrint ( "Logging out of front end.\n");
			$this->click("//input[@value='Log out']");
			$this->waitForPageToLoad("30000");
		}
	}

	function toggleAssignedGroupCheckbox($groupName)
	{
		$id = $this->getAttribute('//fieldset[@id=\'user-groups\']/ul/li[contains(label,\''.$groupName.'\')]/label@for');
		$this->click($id);
	}

	function deleteTestUsers($partialName = 'test')
	{
		$this->jPrint ( "Browse to User Manager.\n");
		$this->click("link=User Manager");
		$this->waitForPageToLoad("30000");

		$this->jPrint ( "Filter on user name\n");
		$this->type("filter_search", $partialName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		$this->jPrint ( "Delete all users in view.\n");
		$this->click("checkall-toggle");
		$this->jPrint ("Delete new user.\n");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"),'Deletion of Test User(s) failed.');
	}

	function createGroup($groupName, $groupParent = 'Public')
	{
		$this->click("link=Groups");
		$this->waitForPageToLoad("30000");
		$this->jPrint ( "Create new group " . $groupName . ".\n");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->type("jform_title", $groupName);
		$this->select("id=jform_parent_id", "label=regexp:.*".$groupParent);
		$this->jClick("Save & Close");

		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"),'Creation of ' . $groupName . ' failed.');
		$this->jPrint ( "Creation of " . $groupName . " succeeded.\n");

	}

	function deleteGroup($partialName = 'test')
	{
		$this->jPrint ( "Browse to User Manager: Groups.\n");
		$this->click("link=Groups");
		$this->waitForPageToLoad("30000");

		$this->jPrint ( "Filter on " . $partialName . ".\n");
		$this->type("filter_search", $partialName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		$this->jPrint ( "Delete all groups in view.\n");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");

		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"), 'Group deletion failed or confirm text wrong, SeleniumJoomlaTestCase line 197');
		$this->jPrint ( "Deletion succeeded.\n");

		$this->assertFalse($this->isTextPresent("No Groups selected"), 'No Groups selected for deletion, SeleniumJoomlaTestCase line 207');

	}

	function createLevel($levelName, $userGroup)
	{
		$this->jClick('Access Levels');
		$this->jClick('New');
		$this->jPrint ( "Create new access level named " . $levelName . "\n");
		$this->type("jform_title", $levelName);
		$this->jInput($userGroup);
		$this->jPrint ( "Selecting User Groups having access to " . $levelName . "\n");
		$this->jClick('Save & Close');
	}

	function deleteLevel($partialName = 'test')
	{
		$this->jClick('Access Levels');
		$this->jPrint ( "Filter on " . $partialName . ".\n");
		$this->type("filter_search", $partialName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->jPrint ( "Delete all levels in view.\n");
		$this->click("checkall-toggle");
		$this->jClick('Delete');
	}

	function changeAccessLevel($levelName = 'Registered', $groupName = 'Public')
	{
		$this->jPrint ( "Add group " . $groupName . " to " . $levelName . " access level.\n");
		$this->jPrint ( "Navagating to Access Levels.\n");
		$this->jClick('Access Levels');
		$this->click("//tr/td[contains(a,'" . $levelName . "')]/..//input[@type='checkbox']");
		$this->jClick('Edit');
		$this->assertTrue($this->isTextPresent(": Edit", $this->getText("//h1")));
		$id = $this->click("//label[contains(., '" . $groupName . "')]/input");
		$this->jClick('Save & Close');

		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"), "Line: ".__LINE__);
		$this->jPrint ( "Adding group " . $groupName . " to " . $levelName . " access level succeeded.\n");


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
			$this->jPrint ( "\n***Warning*** " . $count . " verification error(s) encountered.\n");
		}
	}

	/*
	 * Allow selection of an input based on the text contained in its correspondig label
	*/
	function jInput($labelText)
	{
		$this->click("//label[contains(., '$labelText')]");
	}

	/*
	 * Unifies button and menu item selection based on corresponding IDs and Classes
	*/
	function jClick($item)
	{
		switch ($item)
		{
			case 'Access Levels':
				$screen="User Manager: Viewing Access Levels";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("//a[contains(@href,'option=com_users&view=levels')]");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Article Manager':
				$screen="Article Manager: Articles";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Article Manager");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Contacts':
				$screen='Contact Manager: Contacts';
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Contacts");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Delete':
				$this->jPrint ( "Testng Delete capability.\n");
				$this->click("//div[@id='toolbar-delete']/button");
				$this->waitForPageToLoad("30000");
				$this->assertTrue(($this->isTextPresent("deleted") OR $this->isTextPresent("removed") OR $this->isTextPresent("trashed")), 'Deletion failed or confirm text wrong, SeleniumJoomlaTestCase line 310');
				$this->jPrint ( "Deletion of item(s) succeeded.\n");
				break;
			case 'Edit':
				$this->jPrint ( "Testng Edit capability.\n");
				$this->click("//div[@id='toolbar-edit']/button");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent(": Edit", $this->getText("//h1")));
				break;
			case 'Global Configuration':
				$screen='Global Configuration';
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Global Configuration");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen,$this->getText("//h1[@class='page-title']")),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Global Configuration: Permissions':
				$this->jClick('Global Configuration');
				$this->click("//li/a[contains(., 'Permissions')]");
				break;
			case 'Groups':
				$screen="User Manager: User Groups";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Groups");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Menu Manager':
				$screen="Menu Manager: Menus";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Menu Manager");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Menu Items':
				$screen="Menu Manager: Menu Items";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Menu Manager");
				$this->waitForPageToLoad("30000");
				$this->click("link=Menu Items");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Module Manager':
				$screen="Module Manager: Modules";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Module Manager");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'New':
				$this->jPrint ( "Clicking New toolbar button.\n");
				$this->click("//div[@id='toolbar-new']/button");
				$this->waitForPageToLoad("30000");
				break;
			case 'Options':
				$this->jPrint ( "Navigating to Options\n");
				$this->click("//div[@id='toolbar-options']/button");
				$this->waitForPageToLoad('3000');
				break;
			case 'Redirect Manager':
				$screen="Redirect Manager: Links";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Redirect");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Save & Close':
				$this->jPrint ( "Clicking Save & Close toolbar button.\n");
				$this->click("//div[@id='toolbar-save']/button");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"), "Save success text not present, SeleniumTestCase line 327");
				$this->assertFalse($this->isElementPresent("//div[@id='system-message-container'][contains(., 'error')]"), "Error message present, SeleniumTestCase line 328");
				$this->jPrint ( "Item successfully saved.\n");
				break;
			case 'Trash':
				$this->jPrint ( "Clicking Trash toolbar button.\n");
				$this->click("//li[@id='toolbar-trash']/a/span");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"),'Error trashing item, SeleniumTestCase line 491.');
				break;
			case 'Unpublish':
				$this->jPrint ( "Clicking Unpublish toolbar button.\n");
				$this->click("//li[@id='toolbar-unpublish']/a/span");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"),'Error unpublishing item, SeleniumTestCase line 505.');
				$this->assertFalse($this->isTextPresent("Edit state is not permitted"),"Access issues with unpublishing item, SeleniumTestCase line 515.");
				break;
			case 'User Manager':
				$screen="User Manager: Users";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=User Manager");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Weblinks':
				$screen="Web Links Manager: Web Links";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("link=Weblinks");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen),'Error navigating to '.$screen.' or page title changed.');
				break;
			case 'Weblink Categories':
				$screen="Category Manager: Weblinks";
				$this->jPrint ( "Navigating to ".$screen.".\n");
				$this->click("//a[contains(@href, 'index.php?option=com_categories&extension=com_weblinks')]");
				$this->waitForPageToLoad("30000");
				$this->assertTrue($this->isTextPresent($screen,$this->getText("//div[contains(@class,'pagetitle')]/h2")),'Error navigating to '.$screen.' or page title changed.');
				break;
			default:
				$this->click("//div[@id='toolbar-new']/button");
				$this->jPrint ( "Clicking New toolbar button.\n");
				$this->waitForPageToLoad("30000");
				break;
		}
	}

	public function jPrint($text)
	{
		if (isset($this->cfg->debug) && $this->cfg->debug)
		{
			echo($text);
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
		$this->click("//li/a[contains(.,'$formTab')]");
	}

	function restoreDefaultGlobalPermissions()
	{
		$actions = array('Site Login', 'Admin Login', 'Configure', 'Access Component', 'Create', 'Delete', 'Edit', 'Edit State');
		$permissions = array('Not Set', 'Not Set', 'Not Set', 'Not Set', 'Not Set', 'Not Set', 'Not Set', 'Not Set');
		$this->setPermissions('Global Configuration', 'Public', $actions, $permissions);

		$permissions = array('Allowed', 'Allowed', 'Inherited', 'Inherited', 'Allowed', 'Allowed', 'Allowed', 'Allowed');
		$this->setPermissions('Global Configuration', 'Manager', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Allowed', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', 'Administrator', $actions, $permissions);

		$permissions = array('Allowed', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', 'Registered', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Allowed', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', 'Author', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Allowed', 'Inherited');
		$this->setPermissions('Global Configuration', 'Editor', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Allowed');
		$this->setPermissions('Global Configuration', 'Publisher', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', 'Shop Suppliers', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', 'Customer Group', $actions, $permissions);

		$permissions = array('Inherited', 'Inherited', 'Allowed', 'Inherited', 'Inherited', 'Inherited', 'Inherited', 'Inherited');
		$this->setPermissions('Global Configuration', 'Super Users', $actions, $permissions);
	}

	/**
	 *
	 * Sets permissions in Global Configuration or Component for a group
	 * @param string $component	Name of Component: Global Configuration, Article Manager, Contacts, etc.
	 * @param string $group	Name of the Group
	 * @param string or array $actions Actions to set: Site Login, Admin Login, Configure, Access Component, Create, Delete, Edit, Edit State
	 * @param string or array $permissions Permissions to set for corresponding Action: Inherited, Allowed, or Locked
	 */

	function setPermissions($component, $group, $actions, $permissions)
	{
		$this->jClick($component);
		if ($component == 'Global Configuration')
		{
			$this->click("//li/a[contains(., 'Permissions')]");
		}
		else
		{
			$this->jClick('Options');
			$this->click("//li/a[contains(., 'Permissions')]");
		}
		if (!is_array($actions)) {
			$actions = array($actions);
		}
		if (!is_array($permissions)) {
			$permissions = array($permissions);
		}
		$this->jPrint ( "Open panel for group '$group'\n");
		$this->click("//div[@id='permissions-sliders']//li[contains(.,'$group')]/a");

		for ($i = 0; $i < count($actions); $i++) {
			$action = $actions[$i];
			$permission = $permissions[$i];
			$this->jPrint ( "Setting $action action for $group to $permission in $component.\n");
			switch ($action)
			{
				case 'Site Login':
					$doAction = 'login.site';
					break;
				case 'Admin Login':
					$doAction = 'login.admin';
					break;
				case 'Configure':
				case 'Super Admin':
					$doAction = 'core.admin';
					break;
				case 'Access Component':
					$doAction = 'core.manage';
					break;
				case 'Create':
					$doAction = 'create';
					break;
				case 'Delete':
					$doAction = 'delete';
					break;
				case 'Edit':
					$doAction = 'edit';
					break;
				case 'Edit State':
					$doAction = 'edit.state';
					break;
				case 'Edit Own':
					$doAction = 'edit.own';
					break;
			}

			$this->select("//select[contains(@id,'$doAction')][contains(@title,'$group')]", "label=$permission");
		}

		$this->jPrint ( "Close panel for group '$group'\n");
		$this->click("//div[@id='permissions-sliders']//li[contains(., 'Public')]/a");

		if ($component == 'Global Configuration') {
			$this->click("//div[@id='toolbar-save']/button");
			$this->waitForPageToLoad('3000');

			$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));

		}
		else {
			// Need to click the Save & Close button
			$this->click("//div[@id='toolbar-save']/button");
			$this->waitForPageToLoad('3000');
		}
	}

	function setEditor($editor)
	{
		$this->jPrint ( "Changing editor to $editor\n");
		$this->jClick('Global Configuration');
		$this->click("link=Site");
		switch (strtoupper($editor))
		{
			case 'NO EDITOR':
			case 'NONE':
				$select = 'label=Editor - None';
				break;

			case 'CODEMIRROR':
				$select = 'label=Editor - CodeMirror';

			case 'TINYMCE':
			case 'TINY':
			default:
				$select = 'label=Editor - TinyMCE';
				break;
		}


		$this->select("id=jform_editor", $select);

		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

	}

	function setTinyText($text)
	{
		$this->selectFrame("jform_articletext_ifr");
		$this->type("tinymce", $text);
		$this->selectFrame("relative=top");
	}

	function toggleFeatured($articleTitle)
	{
		$this->jPrint ( "Toggling Featured on/off for article " . $articleTitle . "\n");
		$this->click("//table[@id='articleList']/tbody//tr//td/div/a[contains(text(), '" . $articleTitle . "')]/../../../td[3]//a[contains(@onclick, 'featured')]");
		$this->waitForPageToLoad("30000");
	}

	function togglePublished($articleTitle, $type = 'Article')
	{
		if ($type == 'Article')
		{
			$this->jPrint ( "Toggling publishing of article " . $articleTitle . "\n");
			$this->click("//table[@id='articleList']/tbody/tr//td/div/a[contains(text(), '" . $articleTitle . "')]/../../../td[3]/div/a");
			$this->waitForPageToLoad("30000");
		}
		if ($type == 'Category')
		{
			$this->jPrint ( "Toggling publishing of article " . $articleTitle . "\n");
			$this->click("//table[@id='categoryList']/tbody/tr//td/a[contains(text(), '" . $articleTitle . "')]/../../td[3]/a/i");
			$this->waitForPageToLoad("30000");
		}
	}

	function toggleCheckBox($itemTitle, $type = 'Category')
	{
		switch ($type)
		{
			case 'Category' :
				$this->jPrint ( "Toggling check box selection of category " . $itemTitle . "\n");
				$this->click("//table[@id='categoryList']/tbody//tr//td/a[contains(text(), '" .	$itemTitle . "')]/../../td/input[@type='checkbox']");
				break;

			case 'Article' :
			default :
				$this->jPrint ( "Toggling check box selection of article " . $itemTitle . "\n");
				$this->click("//table[@id='articleList']/tbody//tr//td/div/a[contains(text(), '" .	$itemTitle . "')]/../../../td/input[@type='checkbox']");
				break;
		}
	}

	/**
	 *
	 * Sets state of component category or item
	 * @param string 	Title or Category or Item
	 * @param string	Name of Menu: Article Manager, Banners, Contacts, Newsfeeds, Weblinks
	 * @param string	Category or Item
	 * @param string	last part of toolbar name: publish, unpublish, archive, trash
	 */

	function changeState($title = null, $menu = 'Article Manager', $type = 'Category', $newState = 'publish')
	{
		$this->gotoAdmin();
		$this->jPrint ( "Changing state of " . $type . " " . $title . " in " . $menu . " to " . $newState .  "\n");
		$this->click("link=" . $menu);
		$this->waitForPageToLoad("30000");
		if ($type == 'Category')
		{
			$this->click("//ul[@id='submenu']/li[2]/a");
			$this->waitForPageToLoad("30000");
		}
		$filter = ($menu == 'Banners') ? 'filter_state' : 'filter_published';
		$this->select($filter, "label=All");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", $title);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->toggleCheckBox($title, $type);
		$this->click("//div[@id='toolbar-" . $newState . "']/button");
		$this->waitForPageToLoad("30000");
		$this->click("//button[@type='button'][contains(@onclick, \".value=''\")]");
		$this->waitForPageToLoad("30000");
		$this->select($filter, "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->gotoAdmin();
	}

	function changeCategory($title = null, $menu = 'Article Manager', $newCategory = 'Uncategorised')
	{
		$this->jPrint ( "Changing category for $title in $menu to $newCategory\n");
		$this->gotoAdmin();
		$this->click("link=$menu");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", $title);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("link=$title");
		$this->waitForPageToLoad("30000");
		$this->select("jform_catid", "label=*$newCategory*");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

	}
	/**
	 *
	 * Sets caching option
	 *
	 * @param string $level	Options are off, on-basic, on-full
	 */
	function setCache($level = 'off')
	{
		$this->gotoAdmin();
		$this->jClick('Global Configuration');
		$this->click("//a[@href='#page-system']");
		$this->jPrint ( "Set caching to $level\n");
		switch ($level)
		{
			case 'on-basic':
				$this->select("jform_caching", "value=1");
				break;

			case 'on-full' :
				$this->select("jform_caching", "value=2");
				break;

			case 'off'	:
			default:
				$this->select("jform_caching", "value=0");
				break;
		}

		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
	}

	function setDefaultTemplate($template)
	{
		$this->doAdminLogin();
		$this->click("link=Template Manager");
		$this->waitForPageToLoad("30000");
		try
		{
			$this->click("//table/tbody//a[contains(text(), '" . $template . "')]/../../td/a[contains(@onclick, 'setDefault')]");
			$this->waitForPageToLoad("30000");
		}
		catch (Exception $e) {
		} // ignore if already set
		$this->doAdminLogout();
	}

	function waitforElement($element, $time = 30, $present = true) {
		for ($second = 0; ; $second++) {
			if ($second >= $time) $this->fail("timeout");
			try {
			$condition = ($present) ? $this->isElementPresent($element) : !$this->isElementPresent($element);
			if ($condition) break;
			} catch (Exception $e) {}
		sleep(1);
	}
	sleep(1);
	$this->checkNotices();
}

function checkNotices()
{
	try {
		$this->assertFalse($this->isTextPresent("( ! ) Notice"), "**Warning: PHP Notice found on page!");
		$this->assertElementNotPresent("//tr[contains(., '( ! ) Notice:')]", "**Warning: PHP Notice found on page!");
		$this->assertElementNotPresent("//tr[contains(., '( ! ) Warning:')]", "**Warning: PHP Warning found on page!");
	}
	catch (PHPUnit_Framework_AssertionFailedError $e) {
		$this->jPrint ( "**Warning: PHP Notice found on page\n");
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
		$this->assertFalse($this->isTextPresent("( ! ) Notice") || $this->isTextPresent("( ! ) Warning"), "**Warning: PHP Notice found on page!");
		$this->assertElementNotPresent("//tr[contains(., '( ! ) Notice:')]", "**Warning: PHP Notice found on page!");
		$this->assertElementNotPresent("//tr[contains(., '( ! ) Warning:')]", "**Warning: PHP Warning found on page!");
	}
	catch (PHPUnit_Framework_AssertionFailedError $e) {
		$this->jPrint ( "**Warning: PHP Notice found on page\n");
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
