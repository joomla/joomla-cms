<?php
namespace Step\Acceptance\Administrator;

class User extends \AcceptanceTester
{
	/**
	 * @Given There is a add user link
	 */
	public function thereIsAAddUserLink()
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=users');
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I create new user fulfilling mandatory fields: Name, Login Name, Password, Confirm Password and Email
	 */
	public function iCreateNewUser()
	{
		$I = $this;
		$I->fillField(['id' => 'jform_name'], 'register');
		$I->fillField(['id' => 'jform_username'], 'register');
		$I->fillField(['id' => 'jform_password'], 'register');
		$I->fillField(['id' => 'jform_password2'], 'register');
		$I->fillField(['id' => 'jform_email'], 'register@gmail.com');
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I search and select the user with user name :arg1
	 */
	public function iSearchAndSelectTheUserWithUserName($username)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=users');
		$I->fillField(['id' => 'filter_search'], $username);
		$I->click('.icon-search');
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 *  @When I set name as an :arg1 and User Group as :arg1
	 */
	public function iAssignedNameAndUserGroup($name, $userGroup)
	{
		$I = $this;
		$I->fillField(['id' => 'jform_name'], $name);
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I have a user with user name :arg1
	 */
	public function iHaveAUserWithUserName($username)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=users');
		$I->fillField(['id' => 'filter_search'], $username);
		$I->click('.icon-search');
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I have a blocked user with user name :arg1
	 */
	public function iHaveABlockedUserWithUserName($username)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=users');
		$I->fillField(['id' => 'filter_search'], $username);
		$I->click('.icon-search');
		$I->checkAllResults();
	}

	/**
	 * @When I unblock the user
	 */
	public function iUnblockTheUser()
	{
		$I = $this;
		$I->waitForPageTitle('Users');
		$I->click(['xpath' => "//div[@id='toolbar-unblock']//button"]);
	}

	/**
	 * @Then I should see the user unblock message :arg1
	 */
	public function iShouldSeeTheUserUnblockMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Users');
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @When I Delete the user :arg1
	 */
	public function iDeleteTheUser($username)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=users');
		$I->fillField(['id' => 'filter_search'], $username);
		$I->click('.icon-search');
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given There is an user link
	 */
	public function thereIsAnUserLink()
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=users');
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
	 * @When I create a super admin fulfilling mandatory fields: Name, Login Name, Password, Confirm Password and Email
	 */
	public function iCreateASuperAdmin()
	{
		$I = $this;
		$I->fillField(['id' => 'jform_name'], 'prital');
		$I->fillField(['id' => 'jform_username'], 'prital');
		$I->fillField(['id' => 'jform_password'], 'prital');
		$I->fillField(['id' => 'jform_password2'], 'prital');
		$I->fillField(['id' => 'jform_email'], 'prital@gmail.com');
	}

	/**
	 * @When I set assigned user group as an :arg1
	 */
	public function iSetAssignedUserGroupAsAn($arg1)
	{
		$I = $this;
		$I->click('Assigned User Groups');
		$I->checkOption('#1group_7');
	}

	/**
	 * @Then Login in backend with username and password
	 */
	public function loginInBackendWithUsernameAndPassword()
	{
		$I = $this;
		$I->doAdministratorLogout();
		$I->fillField(['id' => 'mod-login-username'], 'prital');
		$I->fillField(['id' => 'mod-login-password'], 'prital');
		$I->click('Log in');
	}

	/**
	 * @When I don't fill Login Name but fulfill remaining mandatory fields: Name, Password, Confirm Password and Email
	 */
	public function iDontFillLoginName()
	{
		$I = $this;
		$I->fillField(['id' => 'jform_name'], 'piyu');
		$I->fillField(['id' => 'jform_password'], 'piyu');
		$I->fillField(['id' => 'jform_password2'], 'piyu');
		$I->fillField(['id' => 'jform_email'], 'piyu@gmail.com');
	}

	/**
	 * @Then I see the :arg1 alert error
	 */
	public function iSeeTheAlertError($arg1)
	{
		$I = $this;
		$I->see('Invalid field:  Login Name', ['id' => 'system-message-container']);
	}


	/**
	 * @Given There is a add new group link
	 */
	public function thereIsAAddNewGroupLink()
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=groups');
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I fill Group Title as a :arg1
	 */
	public function iFillGroupTitleAsA($GroupTitle)
	{
		$I = $this;
		$I->fillField(['id' => 'jform_title'], $GroupTitle);
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I search and select the Group with name :arg1
	 */
	public function iSearchAndSelectTheGroupWithName($GroupTitle)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=groups');
		$I->fillField(['id' => 'filter_search'], $GroupTitle);
		$I->click('.icon-search');
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * @Given I set group Title as a :arg1
	 */
	public function iSetGroupTitleAsA($GroupTitle)
	{
		$I = $this;
		$I->fillField(['id' => 'jform_title'], $GroupTitle);
	}

	/**
	 * @When I Delete the Group :arg1
	 */
	public function iDeleteTheGroup($GroupTitle)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=groups');
		$I->fillField(['id' => 'filter_search'], $GroupTitle);
		$I->click('.icon-search');
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given There is a add viewing access level link
	 */
	public function thereIsAAddViewingAccessLevelLink()
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=levels');
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I fill Level Title as a :arg1 and set Access as a public
	 */
	public function iFillAccessLevelDetail($LevelTitle)
	{
		$I = $this;
		$I->fillField(['id' => 'jform_title'], $LevelTitle);
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
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I search and select the Access Level with name :arg1
	 */
	public function iSearchAndSelectTheAccessLevelWithName($LevelTitle)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_users&view=levels');
		$I->fillField(['id' => 'filter_search'], $LevelTitle);
		$I->click('.icon-search');
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}

	/**
	 * @Given I set Access Level title as a :arg1
	 */
	public function iSetAccessLevelTitleAsA($LevelTitle)
	{
		$I = $this;
		$I->fillField(['id' => 'jform_title'], $LevelTitle);
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
		$I->amOnPage('administrator/index.php?option=com_users&view=levels');
		$I->fillField(['id' => 'filter_search'], $LevelTitle);
		$I->click('.icon-search');
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
		$I->see($message, ['id' => 'system-message-container']);
	}
}
