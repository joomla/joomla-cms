<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use \Codeception\Util\Locator;
use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\LoginPage;
use Page\Acceptance\Administrator\UserAclPage;
use Page\Acceptance\Administrator\UserGroupPage;
use Page\Acceptance\Administrator\UserManagerPage;

/**
 * Acceptance Step object class contains suits for User Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    3.7
 */
class User extends \AcceptanceTester
{
	/**
	 * Method to goto user management
	 *
	 * @Given There is a add user link
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function thereIsAAddUserLink()
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->clickToolbarButton('New');
	}

	/**
	 * Method to fill create new user form
	 *
	 * @param   string  $name      User's name
	 * @param   string  $username  User's username
	 * @param   string  $password  User's password
	 * @param   string  $email     User's email
	 *
	 * @When I create new user with fields Name :name, Login Name :username, Password :password and Email :email
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iCreateNewUser($name, $username, $password, $email)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$nameField, $name);
		$I->fillField(UserManagerPage::$usernameField, $username);
		$I->fillField(UserManagerPage::$passwordField, $password);
		$I->fillField(UserManagerPage::$password2Field, $password);
		$I->fillField(UserManagerPage::$emailField, $email);
	}

	/**
	 * Method to save user
	 *
	 * @When I Save the user
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSaveTheUser()
	{
		$I = $this;

		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * Method to see success message
	 *
	 * @param   string  $message  The system message for user saved.
	 *
	 * @Then I should see the :arg1 message
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSeeTheMessage($message)
	{
		$I = $this;

		$I->waitForText($message, TIMEOUT, AdminPage::$systemMessageContainer);
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * Method to search and select user with username
	 *
	 * @param   string  $username  The username of user
	 *
	 * @Given I search and select the user with user name :username
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSearchAndSelectTheUserWithUserName($username)
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * Method to assign name and usergroup
	 *
	 * @param   string  $name       The name of user
	 * @param   string  $userGroup  The usergroup of user
	 *
	 * @When I set name as an :name and User Group as :usergroup
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iAssignedNameAndUserGroup($name, $userGroup)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$nameField, $name);
		$I->click('Assigned User Groups');

		// @todo use $userGroup variable to select user group dynamically
		$I->checkOption('#1group_4');
	}

	/**
	 * Method to search user with username
	 *
	 * @param   string  $username  The username of user
	 *
	 * @Given I have a user with user name :username
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iHaveAUserWithUserName($username)
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
	}

	/**
	 * Method to block user
	 *
	 * @When I block the user
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iBlockTheUser()
	{
		$I = $this;

		$I->clickToolbarButton('unpublish');
	}

	/**
	 * Method to search blocked user.
	 *
	 * @param   string  $username  The username of blocked user
	 *
	 * @Given I have a blocked user with user name :username
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iHaveABlockedUserWithUserName($username)
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
	}

	/**
	 * Method to unblock user
	 *
	 * @When I unblock the user
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iUnblockTheUser()
	{
		$I = $this;

		$I->clickToolbarButton('unblock');
	}

	/**
	 * Method to delete user
	 *
	 * @param   string  $username  The username of user to delete
	 *
	 * @When I Delete the user :username
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iDeleteTheUser($username)
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('delete');
		$I->acceptPopup();
	}

	/**
	 * Method to goto user manager page.
	 *
	 * @Given There is an user link
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function thereIsAnUserLink()
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
	}

	/**
	 * Method to goto user edit view.
	 *
	 * @When I see the user edit view tabs
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSeeTheUsereditViewTabs()
	{
		$I = $this;

		$I->clickToolbarButton('New');
		$I->waitForPageTitle('Users');
	}

	/**
	 * Method to check available tabs
	 *
	 * @param   string  $tab1  The name of tab1
	 * @param   string  $tab2  The name of tab2
	 * @param   string  $tab3  The name of tab3
	 *
	 * @Then I check available tabs :tab1, :tab2 and :tab3
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iCheckAvailableTabs($tab1, $tab2, $tab3)
	{
		$I = $this;

		$I->verifyAvailableTabs([$tab1, $tab2, $tab3]);
	}

	/**
	 * Method to prepare form to create super admin
	 *
	 * @param   string  $name      The name of super admin
	 * @param   string  $username  The username of super admin
	 * @param   string  $password  The password of super admin
	 * @param   string  $email     The email of super admin
	 *
	 * @Given I fill a super admin with fields Name :name, Login Name :username, Password :password, and Email :email
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iCreateASuperAdmin($name, $username, $password, $email)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$nameField, $name);
		$I->fillField(UserManagerPage::$usernameField, $username);
		$I->fillField(UserManagerPage::$passwordField, $password);
		$I->fillField(UserManagerPage::$password2Field, $password);
		$I->fillField(UserManagerPage::$emailField, $email);
	}

	/**
	 * Method to set assigned user groups as an administrator
	 *
	 * @When I set assigned user group as an Administrator
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSetAssignedUserGroupAsAnAdministrator()
	{
		$I = $this;

		$I->click('Assigned User Groups');
		$I->checkOption('#1group_7');
	}

	/**
	 * Method to login into backend
	 *
	 * @param   string  $username  The username for login
	 * @param   string  $password  The password for login
	 *
	 * @Then Login in backend with username :username and password :password
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function loginInBackend($username, $password)
	{
		$I = $this;

		$I->doAdministratorLogout();
		$I->fillField(LoginPage::$usernameField, $username);
		$I->fillField(LoginPage::$passwordField, $password);
		$I->click('Log in');
	}

	/**
	 * Method to verify error in user form.
	 *
	 * @param   string  $name      The name of user.
	 * @param   string  $password  The password or user
	 * @param   string  $email     The email of user
	 *
	 * @When I don't fill Login Name but fulfill remaining mandatory fields: Name :name, Password :password and Email :email
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iDontFillLoginName($name, $password, $email)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$nameField, $name);
		$I->fillField(UserManagerPage::$passwordField, $password);
		$I->fillField(UserManagerPage::$password2Field, $password);
		$I->fillField(UserManagerPage::$emailField, $email);
	}

	/**
	 * Method to see user title
	 *
	 * @param   string  $title  The title of user
	 *
	 * @Then I see the title :title
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSeeTheTitle($title)
	{
		$I = $this;

		$I->waitForPageTitle($title);
	}

	/**
	 * Method to see alert.
	 *
	 * @param   string  $error  The error alert message
	 *
	 * @Then I see the alert error :error
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSeeTheAlertError($error)
	{
		$I = $this;

		$I->see($error, AdminPage::$systemMessageContainer);
	}

	/**
	 * Method to add new group link
	 *
	 * @Given There is a add new group link
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function thereIsAAddNewGroupLink()
	{
		$I = $this;

		$I->amOnPage(UserGroupPage::$url);
		$I->clickToolbarButton('New');
	}

	/**
	 * Method to fill group title
	 *
	 * @param   string  $groupTitle  Group title
	 *
	 * @When I fill Group Title as a :grouptitle
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iFillGroupTitleAsA($groupTitle)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$title, $groupTitle);
	}

	/**
	 * Method to save the group
	 *
	 * @When I save the Group
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSaveTheGroup()
	{
		$I = $this;

		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * Method to search using group name
	 *
	 * @param   string  $groupTitle  The group title
	 *
	 * @Given I search and select the Group with name :grouptitle
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSearchAndSelectTheGroupWithName($groupTitle)
	{
		$I = $this;

		$I->amOnPage(UserGroupPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $groupTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * Method to set group title
	 *
	 * @param   string  $groupTitle  The group title
	 *
	 * @Given I set group Title as a :grouptitle
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSetGroupTitleAsA($groupTitle)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$title, $groupTitle);
	}

	/**
	 * Method to delete group
	 *
	 * @param   string  $groupTitle  The group title
	 *
	 * @When I Delete the Group :arg1
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iDeleteTheGroup($groupTitle)
	{
		$I = $this;

		$I->amOnPage(UserGroupPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $groupTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('delete');
		$I->acceptPopup();
	}

	/**
	 * Method to goto viewing access level
	 *
	 * @Given There is a add viewing access level link
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function thereIsAAddViewingAccessLevelLink()
	{
		$I = $this;

		$I->amOnPage(UserAclPage::$url);
		$I->clickToolbarButton('New');
	}

	/**
	 * Method to fill access level detail in form.
	 *
	 * @param   string  $levelTitle  The title of access level
	 *
	 * @When I fill Level Title as a :levelTitle and set Access as a public
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iFillAccessLevelDetail($levelTitle)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$title, $levelTitle);
		$I->checkOption('#1group_1');
	}

	/**
	 * Method to save the access level
	 *
	 * @When I save the Access Level
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSaveTheAccessLevel()
	{
		$I = $this;

		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * Method to search using the access level name
	 *
	 * @param   string  $levelTitle  The title of access level
	 *
	 * @Given I search and select the Access Level with name :leveltitle
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSearchAndSelectTheAccessLevelWithName($levelTitle)
	{
		$I = $this;

		$I->amOnPage(UserAclPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $levelTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * Method to set access level title
	 *
	 * @param   string  $levelTitle  The access level title
	 *
	 * @Given I set Access Level title as a :leveltitle
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSetAccessLevelTitleAsA($levelTitle)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$title, $levelTitle);
	}

	/**
	 * Method to delete access level
	 *
	 * @param   string  $levelTitle  The access level title
	 *
	 * @When I Delete the Access level :leveltitle
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iDeleteTheAccessLeVel($levelTitle)
	{
		$I = $this;

		$I->amOnPage(UserAclPage::$url);
		$I->fillField(UserManagerPage::$filterSearch, $levelTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('delete');
		$I->acceptPopup();
	}

	/**
	 * Method to goto user manage listing page.
	 *
	 * @Given There is a User link
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function thereIsAUserLink()
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
	}

	/**
	 * Method to goto user settings
	 *
	 * @Given I goto the option setting
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iGotoTheOptionSetting()
	{
		$I = $this;

		$I->clickToolbarButton('options');
	}

	/**
	 * Method to allow user registration
	 *
	 * @When I set Allow User Registration as a yes
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSetAllowUserRegistrationAsAYes()
	{
		$I = $this;

		$I->click(Locator::contains('label', 'Yes'));
	}

	/**
	 * Method to save the user setting
	 *
	 * @When I save the setting
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iSaveTheSetting()
	{
		$I = $this;

		$I->clickToolbarButton('Save');
	}

	/**
	 * Method to see create account link in frontend
	 *
	 * @Then I should be see the link Create an account in frontend
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iShouldBeSeeTheLinkCreateAnAccountInFrontend()
	{
		$I = $this;

		$I->amOnPage('/');
	}
}
