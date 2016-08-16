<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Site;

use Codeception\Scenario;
use Codeception\Util\Locator;
use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\UserManagerPage;
use Page\Acceptance\Site\FrontPage;
use Page\Acceptance\Site\FrontendLogin;

/**
 * Acceptance Step object class contains suits for User frontend views.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class UsersFrontend extends \AcceptanceTester
{
	/**
	 * User Manager Page Object for this class
	 *
	 * @var     null|UserManagerPage
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $userManagerPage = null;

	/**
	 * Admin Page Object for this class
	 *
	 * @var     null|UserManagerPage
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $adminPage = null;

	/**
	 * User constructor.
	 *
	 * @param   Scenario  $scenario  Scenario object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Scenario $scenario)
	{
		parent::__construct($scenario);

		// Initialize User Page Objects
		$this->userManagerPage = new UserManagerPage($scenario);
		$this->adminPage       = new AdminPage($scenario);
	}

	/**
	 * Method to enable user registration
	 *
	 * @Given that user registration is enabled
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function thatUserRegistrationIsEnabled()
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->adminPage->clickToolbarButton('options');
		$I->click(Locator::contains('label', 'Yes'));
		$I->adminPage->clickToolbarButton('Save');
	}

	/**
	 * Method to confirm user not exist with given info.
	 *
	 * @param   string  $username  The username to look for.
	 * @param   string  $email     The email to look for
	 *
	 * @Given there is no user with Username :arg1 or Email :arg2
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function thereIsNoUserWithUsernameOrEmail($username, $email)
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);

		// Looking for username
		$I->adminPage->search($username);
		$I->see('No Matching Results');

		// Looking for email
		$I->adminPage->search($email);
		$I->see('No Matching Results');
	}

	/**
	 * Method to go to link
	 *
	 * @param   string  $createAccount  The create account link text
	 *
	 * @When I press on the link :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iPressOnTheLink($createAccount)
	{
		$I = $this;

		$I->amOnPage(FrontPage::$url);
		$I->click($createAccount);

		$I->waitForText('User Registration', TIMEOUT);
	}

	/**
	 * Method to create user using given info
	 *
	 * @param   string  $name      The name of user.
	 * @param   string  $username  The username of user
	 * @param   string  $password  The password of user
	 * @param   string  $email     The email of user
	 *
	 * @When I create a user with fields Name :arg1, Username :arg1, Password :arg1 and Email :arg4
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iCreateAUserWithFieldsNameUsernamePasswordAndEmail($name, $username, $password, $email)
	{
		$I = $this;

		$I->fillField(UserManagerPage::$nameField, $name);
		$I->fillField(UserManagerPage::$usernameField, $username);
		$I->fillField(UserManagerPage::$password1Field, $password);
		$I->fillField(UserManagerPage::$password2Field, $password);
		$I->fillField(UserManagerPage::$email1Field, $email);
		$I->fillField(UserManagerPage::$email2Field, $email);
	}

	/**
	 * Method to save user
	 *
	 * @param   string  $register  The text of register button
	 *
	 * @When I press the :arg1 button
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iPressTheButton($register)
	{
		$I = $this;

		$I->click($register);
	}

	/**
	 * Method to confirm message
	 *
	 * @param   string  $message  The name of the message
	 *
	 * @Then I should see :arg1 message
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeMessage($message)
	{
		$I = $this;

		$I->see($message, FrontPage::$alertMessage);
	}

	/**
	 * Method to declare user is created
	 *
	 * @Then user is created
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function userIsCreated()
	{
		$I = $this;
	}

	/**
	 * Method to goto user manager page
	 *
	 * @Given I am on the User Manager page
	 * @When I login as a super admin from backend
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iAmOnTheUserManagerPage()
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
	}

	/**
	 * Method to search user with username
	 *
	 * @param   string  $username  The username to search for
	 *
	 * @When I search the user with user name :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSearchTheUserWithUserName($username)
	{
		$I = $this;

		$I->waitForText('Users', TIMEOUT);
		$I->adminPage->search($username);
	}

	/**
	 * Method to see the user
	 *
	 * @param   string  $username  The username of the user
	 *
	 * @Then I should see the user :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheUser($username)
	{
		$I = $this;

		$I->see($username, UserManagerPage::$seeUserName);
	}

	/**
	 * Method to confirm user is not activated
	 *
	 * @param   string  $username  The username to be confirmed
	 *
	 * @Given A not yet activated user with username :arg1 exists
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function aNotYetActivatedUserWithUsernameExists($username)
	{
		$I = $this;

		$I->amOnPage(UserManagerPage::$url);
		$I->waitForText(UserManagerPage::$pageTitleText, 60, UserManagerPage::$pageTitle);

		$I->adminPage->search($username);
		$I->waitForElement(['link' => $username], 60);
		$I->see($username, UserManagerPage::$seeName);
	}

	/**
	 * Method to goto frontend user login module
	 *
	 * @Given I am on a frontend page with a login module
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iAmOnAFrontendPageWithALoginModule()
	{
		$I = $this;

		$I->amOnPage(FrontPage::$url);
		$I->see('Login Form', FrontendLogin::$moduleTitle);
	}

	/**
	 * Method to fill login module with detail
	 *
	 * @param   string  $username  The username of user to login
	 * @param   string  $password  The password of user to login
	 *
	 * @When I enter username :arg1 and password :arg1 into the login module
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iEnterUsernameAndPasswordIntoTheLoginModule($username, $password)
	{
		$I = $this;

		$I->fillField(FrontendLogin::$moduleUsername, $username);
		$I->fillField(FrontendLogin::$modulePassword, $password);
	}

	/**
	 * Method to see warning message
	 *
	 * @param   string  $warning  The message of warning
	 *
	 * @Then I should see the :arg1 warning
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheWarning($warning)
	{
		$I = $this;

		$I->see($warning, FrontPage::$alertMessage);
	}

	/**
	 * Method to unblock the user
	 *
	 * @param   string  $username  The username to be unblock
	 *
	 * @When I unblock the user :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iUnblockTheUser($username)
	{
		$I = $this;

		$I->userManagerPage->haveItemUsingSearch($username);
		$I->adminPage->clickToolbarButton('unblock');
	}

	/**
	 * Method to activate the user
	 *
	 * @param   string  $username  The username to be activated
	 *
	 * @When I activate the user :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iActivateTheUser($username)
	{
		$I = $this;

		$I->userManagerPage->haveItemUsingSearch($username);
		$I->adminPage->clickToolbarButton('publish');
	}

	/**
	 * Method to login using detail in frontend
	 *
	 * @param   string  $username  The username for login
	 * @param   string  $password  The password for login
	 *
	 * @Given I am logged in into the frontend as user :arg1 with password :arg2
	 * @When I login with user :arg1 with password :arg1 in frontend
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function loginInFrontend($username, $password)
	{
		$I = $this;

		$I->amOnPage(FrontPage::$url);
		$I->fillField(FrontendLogin::$moduleUsername, $username);
		$I->fillField(FrontendLogin::$modulePassword, $password);
		$I->click('Log in');
	}

	/**
	 * Method to see the message
	 *
	 * @param   string  $message  The message for login greeting
	 *
	 * @Then I should see the message :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheMessage($message)
	{
		$I = $this;

		$I->see($message, FrontPage::$loginGreeting);
	}

	/**
	 * Method to click the edit profile button
	 *
	 * @param   string  $editProfile  The text of the profile edit button
	 *
	 * @When I press on the :arg1 button
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iPressOnTheButton($editProfile)
	{
		$I = $this;

		$I->amOnPage(FrontendLogin::$profile);
		$I->click($editProfile);
	}

	/**
	 * Method to change name
	 *
	 * @param   string  $name  The name of user to be changed
	 *
	 * @When I change the name to :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iChangeTheNameTo($name)
	{
		$I = $this;

		$I->waitForText('Edit Your Profile', TIMEOUT);
		$I->fillField(UserManagerPage::$nameField, $name);
	}

	/**
	 * Method to search using user's name
	 *
	 * @param   string  $name  The name of user to search
	 *
	 * @When I search the user with name :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSearchTheUserWithName($name)
	{
		$this->adminPage->search($name);
	}

	/**
	 * Method to see the user's name
	 *
	 * @param   string  $name  The name of user
	 *
	 * @Then I should see the name :name
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheName($name)
	{
		$I = $this;

		$I->see($name, UserManagerPage::$seeName);
	}

	/**
	 * Method to login at least once
	 *
	 * @param   string  $name  The name of the user
	 *
	 * @Given Needs to user :arg1 logged in at least once
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function needsToUserLoggedInAtLeastOnce($name)
	{
		// Do nothing as user will be already logged in previous tests.
		$I = $this;
	}

	/**
	 * Method to see last login date
	 *
	 * @param   string  $name  The name of the user to check last login date
	 *
	 * @Then I should see last login date for :name
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeLastLoginDate($name)
	{
		$I = $this;

		$I->adminPage->search($name);

		// Just make sure that we don't see "Never".
		$I->dontSee('Never', UserManagerPage::$lastLoginDate);
	}
}
