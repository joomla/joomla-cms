<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\LoginPage;
use Page\Acceptance\Administrator\ControlPanelPage;

/**
 * Acceptance Step object class contains suits for Login Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    3.7
 */
class Login extends \AcceptanceTester
{
	/**
	 * Login into joomla administrator
	 *
	 * @param   string  $username  The username
	 * @param   string  $password  The password
	 *
	 * @When I Login into Joomla administrator with username :arg1 and password :arg1
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function loginIntoJoomlaAdministrator($username, $password)
	{
		$I = $this;

		$I->amOnPage(ControlPanelPage::$url);
		$I->fillField(LoginPage::$usernameField, $username);
		$I->fillField(LoginPage::$passwordField, $password);
		$I->click(LoginPage::$loginButton);
	}

	/**
	 * Method to see administrator dashboard
	 *
	 * @Then I should see the administrator dashboard
	 * @When I see the administrator dashboard
	 *
	 * @since   3.7
	 *
	 * @return  void
	 */
	public function iShouldSeeTheAdministratorDashboard()
	{
		$I = $this;

		$I->waitForPageTitle(ControlPanelPage::$pageTitle);
		$I->see(ControlPanelPage::$pageTitle, AdminPage::$pageTitle);
	}
}
