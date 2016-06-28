<?php
namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\LoginPage;
use Page\Acceptance\Administrator\UserAclPage;
use Page\Acceptance\Administrator\UserGroupPage;
use  Page\Acceptance\Administrator\UserManagerPage;

class User extends \AcceptanceTester
{
	/**
	 * @Given There is a add user link
	 */
	public function thereIsAAddUserLink()
	{
		$I = $this;
		$I->amOnPage(UserManagerPage::$pageURL);
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I create new user with fields Name :name, Login Name :username, Password :password and Email :email
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
	 * @Then I Save the  user
	 */
	public function iSaveTheUser()
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * @Then I see the :arg1 message
	 */
	public function iSeeTheMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Users');
		$I->see($message,AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given I search and select the user with user name :username
	 */
	public function iSearchAndSelectTheUserWithUserName($username)
	{
		$I = $this;
		$I->amOnPage(UserManagerPage::$pageURL);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 *  @When I set name as an :name and User Group as :arg1
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
	 * @Then I should display the :arg1 message
	 */
	public function iShouldDisplayTheMessage($message)
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
		$I->waitForPageTitle('Users');
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given I have a user with user name :arg1
	 */
	public function iHaveAUserWithUserName($username)
	{
		$I = $this;
		$I->amOnPage(UserManagerPage::$pageURL);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
	}

	/**
	 * @When I block the user
	 */
	public function iBlockTheUser()
	{
		$I = $this;
		$I->clickToolbarButton('unpublish');
	}

	/**
	 * @Then I should see the user block message :arg1
	 */
	public function iShouldSeeTheUserBlockMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Users');
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given I have a blocked user with user name :arg1
	 */
	public function iHaveABlockedUserWithUserName($username)
	{
		$I = $this;
		$I->amOnPage(UserManagerPage::$pageURL);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
	}

	/**
	 * @When I unblock the user
	 */
	public function iUnblockTheUser()
	{
		$I = $this;
		$I->waitForPageTitle('Users');
		$I->clickToolbarButton('unblock');
	}

	/**
	 * @Then I should see the user unblock message :arg1
	 */
	public function iShouldSeeTheUserUnblockMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Users');
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @When I Delete the user :arg1
	 */
	public function iDeleteTheUser($username)
	{
		$I = $this;
		$I->amOnPage(UserManagerPage::$pageURL);
		$I->fillField(UserManagerPage::$filterSearch, $username);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('delete');
		$I->acceptPopup();
	}

	/**
	 * @Then I confirm the user should have been deleted by getting the message :arg1
	 */
	public function iConfirmTheUserDeleteSucessfully($message)
	{
		$I = $this;
		$I->checkForPhpNoticesOrWarnings();
		$I->see($message, AdminPage::$systemMessageContainer);;
	}

	/**
	 * @Given There is an user link
	 */
	public function thereIsAnUserLink()
	{
		$I = $this;
		$I->amOnPage(UserManagerPage::$pageURL);
	}

	/**
	 * @When I see the user edit view tabs
	 */
	public function iSeeTheUsereditViewTabs()
	{
		$I = $this;
		$I->clickToolbarButton('New');
		$I->waitForPageTitle('Users');
	}

	/**
	 * @Then I check available tabs :tab1, :tab2 and :tab3
	 */
	public function iCheckAvailableTabs($tab1, $tab2, $tab3)
	{
		$I = $this;
		$I->verifyAvailableTabs([$tab1, $tab2, $tab3]);
	}

	/**
	 * @When I create a super admin with fields Name :name, Login Name :username, Password :password, and Email :email
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
	 * @When I set assigned user group as an Administrator
	 */
	public function iSetAssignedUserGroupAsAnAdministrator()
	{
		$I = $this;
		$I->click('Assigned User Groups');
		$I->checkOption('#1group_7');
	}

	/**
	 * @Then Login in backend with username :username and password :password
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
	 * @When I don't fill Login Name but fulfill remaining mandatory fields: Name :name, Password :password and Email :email
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
	 * @Then I see the :error alert error
	 */
	public function iSeeTheAlertError($error)
	{
		$I = $this;
		$I->see($error,AdminPage::$systemMessageContainer);
	}


	/**
	 * @Given There is a add new group link
	 */
	public function thereIsAAddNewGroupLink()
	{
		$I = $this;
		$I->amOnPage(UserGroupPage::$groupPageURL);
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I fill Group Title as a :arg1
	 */
	public function iFillGroupTitleAsA($GroupTitle)
	{
		$I = $this;
		$I->fillField(UserManagerPage::$title, $GroupTitle);
	}

	/**
	 * @When I save the Group
	 */
	public function iSaveTheGroup()
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * @Then I should see the :arg1 message
	 */
	public function iShouldSeeTheMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Users: Groups');
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given I search and select the Group with name :arg1
	 */
	public function iSearchAndSelectTheGroupWithName($GroupTitle)
	{
		$I = $this;
		$I->amOnPage(UserGroupPage::$groupPageURL);
		$I->fillField(UserManagerPage::$filterSearch, $GroupTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * @Given I set group Title as a :arg1
	 */
	public function iSetGroupTitleAsA($GroupTitle)
	{
		$I = $this;
		$I->fillField(UserManagerPage::$title, $GroupTitle);
	}

	/**
	 * @When I Delete the Group :arg1
	 */
	public function iDeleteTheGroup($GroupTitle)
	{
		$I = $this;
		$I->amOnPage(UserGroupPage::$groupPageURL);
		$I->fillField(UserManagerPage::$filterSearch, $GroupTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('delete');
		$I->acceptPopup();
	}

	/**
	 * @Then I confirm the group should have been deleted by getting the message :arg1
	 */
	public function iDeleteUserGroup($message)
	{
		$I = $this;
		$I->checkForPhpNoticesOrWarnings();
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given There is a add viewing access level link
	 */
	public function thereIsAAddViewingAccessLevelLink()
	{
		$I = $this;
		$I->amOnPage(UserAclPage::$aclPageURL);
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I fill Level Title as a :levelTitle and set Access as a public
	 */
	public function iFillAccessLevelDetail($levelTitle)
	{
		$I = $this;
		$I->fillField(UserManagerPage::$title, $levelTitle);
		$I->checkOption('#1group_1');
	}

	/**
	 * @When I save the Access Level
	 */
	public function iSaveTheAccessLevel()
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * @Then I should be see the :arg1 message
	 */
	public function iShouldBeSeeTheMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Users: Viewing Access Levels');
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given I search and select the Access Level with name :arg1
	 */
	public function iSearchAndSelectTheAccessLevelWithName($LevelTitle)
	{
		$I = $this;
		$I->amOnPage(UserAclPage::$aclPageURL);
		$I->fillField(UserManagerPage::$filterSearch, $LevelTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * @Given I set Access Level title as a :arg1
	 */
	public function iSetAccessLevelTitleAsA($LevelTitle)
	{
		$I = $this;
		$I->fillField(UserManagerPage::$title, $LevelTitle);
	}

	/**
	 * @When I save Access Level
	 */
	public function iSaveAccessLevel()
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * @When I Delete the Access level :arg1
	 */
	public function iDeleteTheAccessLeVel($LevelTitle)
	{
		$I = $this;
		$I->amOnPage(UserAclPage::$aclPageURL);
		$I->fillField(UserManagerPage::$filterSearch, $LevelTitle);
		$I->click(UserManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('delete');
		$I->acceptPopup();
	}

	/**
	 * @Then I confirm the  Access Level have been deleted by getting the message :arg1
	 */
	public function iDeleteAccessLevel($message)
	{
		$I = $this;
		$I->checkForPhpNoticesOrWarnings();
		$I->see($message, AdminPage::$systemMessageContainer);
	}
}
