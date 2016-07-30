<?php
namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\LoginPage;
use Page\Acceptance\Administrator\ControlPanelPage;

class Login extends \AcceptanceTester
{
	/**
	 * @When I Login into Joomla administrator with username :arg1 and password :arg1
	 */
	public function loginIntoJoomlaAdministrator($username, $password)
	{
		$I = $this;
		$I->amOnPage(LoginPage::$url);
		$I->fillField(LoginPage::$usernameField, $username);
		$I->fillField(LoginPage::$passwordField, $password);
		$I->click(LoginPage::$loginButton);
	}

	/**
	 * @Then I should see the administrator dashboard
	 * @When I see the administrator dashboard
	 */
	public function iShouldSeeTheAdministratorDashboard()
	{
		$I = $this;
		$I->waitForPageTitle(ControlPanelPage::$pageTitle, 60, ControlPanelPage::$pageTitleContext);
		$I->see(ControlPanelPage::$pageTitle, ControlPanelPage::$pageTitleContext);
	}
}