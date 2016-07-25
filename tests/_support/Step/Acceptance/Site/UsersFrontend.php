<?php
namespace Step\Acceptance\Site;

use \Codeception\Util\Locator;
use Page\Acceptance\Administrator\UserManagerPage;
use Page\Acceptance\Site\Frontpage;
use Page\Acceptance\Site\Frontendlogin;


class UsersFrontend extends \AcceptanceTester
{
    /**
     * @Given that user registration is enabled
     */
    public function thatUserRegistrationIsEnabled()
    {
        $I = $this;
        $I->amOnPage(UserManagerPage::$url);
        $I->clickToolbarButton('options');
        $I->click(Locator::contains('label', 'Yes'));
        $I->clickToolbarButton('Save');
    }

    /**
     * @Given there is no user with Username :arg1 or Email :arg2
     */
    public function thereIsNoUserWithUsernameOrEmail($username, $email)
    {
        $I = $this;
        $I->amOnPage(UserManagerPage::$url);
        $I->fillField(UserManagerPage::$filterSearch, $username);
        $I->click(UserManagerPage::$iconSearch);
        $I->see('No Matching Results');
        $I->fillField(UserManagerPage::$filterSearch, $email);
        $I->click(UserManagerPage::$iconSearch);
        $I->see('No Matching Results');
    }

    /**
     * @When I press on the link :arg1
     */
    public function iPressOnTheLink($CreateAccount)
    {
        $I = $this;
        $I->amOnPage(Frontpage::$url);
        $I->click($CreateAccount);
        $I->waitForText('User Registration', TIMEOUT);
    }

    /**
     * @When I create a user with fields Name :arg1, Uaername :arg1, Password :arg1 and Email :arg4
     */
    public function iCreateAUserWithFieldsNameUaernamePasswordAndEmail($name, $username, $password, $email)
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
     * @When I press the :arg1 button
     */
    public function iPressTheButton($register)
    {
        $I = $this;
        $I->click($register);
    }

    /**
     * @Then I should see :arg1 message
     */
    public function iShouldSeeMessage($message)
    {
        $I = $this;
        $I->see($message, Frontpage::$alertMessage);
    }

    /**
     * @Then user is created
     */
    public function userIsCreated()
    {
        $I = $this;
    }

    /**
     * @Given I am on the User Manager page
     */
    public function iAmOnTheUserManagerPage()
    {
        $I = $this;
        $I->amOnPage(UserManagerPage::$url);
    }

    /**
     * @When I search the user with user name :arg1
     */
    public function iSearchTheUserWithUserName($username)
    {
        $I = $this;
        $I->waitForText('Users', TIMEOUT);
        $I->fillField(UserManagerPage::$filterSearch, $username);
        $I->click(UserManagerPage::$iconSearch);
    }

    /**
     * @Then I should see the user :arg1
     */
    public function iShouldSeeTheUser($username)
    {
        $I = $this;
        $I->see($username, UserManagerPage::$seeUserName);
    }

    /**
     * @Given A not yet activated user with username :arg1 exists
     */
    public function aNotYetActivatedUserWithUsernameExists($username)
    {
        $I = $this;
        $I->amOnPage(UserManagerPage::$url);
        $I->see($username, UserManagerPage::$seeName);
    }

    /**
     * @Given I am on a frontend page with a login module
     */
    public function iAmOnAFrontendPageWithALoginModule()
    {
        $I = $this;
        $I->amOnPage(Frontpage::$url);
        $I->see('Login Form', Frontendlogin::$mdlLogin);
    }

    /**
     * @When I enter username :arg1 and password :arg1 into the login module
     */
    public function iEnterUsernameAndPasswordIntoTheLoginModule($username, $password)
    {
        $I = $this;
        $I->fillField(Frontendlogin::$modlgnUsername, $username);
        $I->fillField(Frontendlogin::$modlgnPasswd, $password);
    }

    /**
     * @When I press on :arg1
     */
    public function iPressOnButton($login)
    {
        $I = $this;
        $I->click($login);
    }

    /**
     * @Then I should see the :arg1 warning
     */
    public function iShouldSeeTheWarning($warning)
    {
        $I = $this;
        $I->see($warning, Frontpage::$alertMessage);
    }

    /**
     * @When I unblock the user :arg1
     */
    public function iUnblockTheUser($username)
    {
        $I = $this;
        $I->fillField(UserManagerPage::$filterSearch, $username);
        $I->click(UserManagerPage::$iconSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('unblock');
    }

    /**
     * @When I activate the user :arg1
     */
    public function iActivateTheUser($username)
    {
        $I = $this;
        $I->fillField(UserManagerPage::$filterSearch, $username);
        $I->click(UserManagerPage::$iconSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('publish');
    }

    /**
     * @When I login with user :arg1 with password :arg1 in frontend
     */
    public function iLoginWithUserWithPasswordInFrontend($username, $password)
    {
        $I = $this;
        $I->amOnPage(Frontpage::$url);
        $I->fillField(Frontendlogin::$modlgnUsername, $username);
        $I->fillField(Frontendlogin::$modlgnPasswd, $password);
        $I->click('Log in');
    }

    /**
     * @Then I should see the message :arg1
     */
    public function iShouldSeeTheMessage($message)
    {
        $I = $this;
        $I->see($message, Frontpage::$loginGreeting);
    }

    /**
     * @Given I am logged in into the frontend as user :arg1 with password :arg2
     */
    public function iAmLoggedInIntoTheFrontendAsUser($username, $password)
    {
        $I = $this;
        $I->amOnPage(Frontpage::$url);
        $I->fillField(Frontendlogin::$modlgnUsername, $username);
        $I->fillField(Frontendlogin::$modlgnPasswd, $password);
        $I->click('Log in');
    }

    /**
     * @When I press on the :arg1 button
     */
    public function iPressOnTheButton($editProfile)
    {
        $I = $this;
        $I->amOnPage(Frontendlogin::$profile);
        $I->click($editProfile);
    }

    /**
     * @When I change the name to :arg1
     */
    public function iChangeTheNameTo($name)
    {
        $I = $this;
        $I->waitForText('Edit Your Profile', TIMEOUT);
        $I->fillField(UserManagerPage::$nameField, $name);
    }

    /**
     * @When I press on :arg1 button
     */
    public function iPressOn($submit)
    {
        $I = $this;
        $I->click($submit);
    }

    /**
     * @When I search the user with name :arg1
     */
    public function iSearchTheUserWithName($name)
    {
        $I = $this;
        $I->fillField(UserManagerPage::$filterSearch, $name);
        $I->click(UserManagerPage::$iconSearch);
    }

    /**
     * @Then I should see the name :name
     */
    public function iShouldSeeTheName($name)
    {
        $I = $this;
        $I->see($name, UserManagerPage::$seeName);
    }

    /**
     * @Given Needs to user :arg1 logged in at least once
     */
    public function needsToUserLoggedInAtLeastOnce($arg1)
    {
        // Do nothing as user will be already logged in previous tests.
        $I = $this;
    }

    /**
     * @When I login as a super admin from backend
     */
    public function iLoginAsASuperAdminFromBackend()
    {
        $I = $this;
        $I->amOnPage(UserManagerPage::$url);
    }

    /**
     * @Then I should see last login date for :name
     */
    public function iShouldSeeLastLoginDate($name)
    {
        $I = $this;

        // @TODO needs to create common function to use search.
        $I->fillField(UserManagerPage::$filterSearch, $name);
        $I->click(UserManagerPage::$iconSearch);

        // Just make sure that we don't see "Never".
        $I->dontSee('Never', UserManagerPage::$lastLoginDate);
    }
}
