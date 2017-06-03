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
 * @since    __DEPLOY_VERSION__
 */
class Login extends Admin
{
	/**
	 * Login into joomla administrator
	 *
	 * @When I Login into Joomla administrator
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function loginIntoJoomlaAdministrator()
	{
		$I = $this;

		$I->amOnPage(ControlPanelPage::$url);
		$conf = $this->getSuiteConfiguration();
		$modules = $conf['modules'];
		$config = $modules['config'];
		$joomlaBrowser = $config['JoomlaBrowser'];
		$username = $joomlaBrowser['username'];
		$password = $joomlaBrowser['password'];
		$I->fillField(LoginPage::$usernameField, $username);
		$I->fillField(LoginPage::$passwordField, $password);
		$I->click(LoginPage::$loginButton);
	}

	/**
	 * Login into joomla backend
	 *
	 * @param   string  $username  The username
	 * @param   string  $password  The password
	 *
	 * @When I Login into Joomla backend with username :arg1 and password :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function loginIntoJoomlaBackend($username, $password)
	{
		$I = $this;

		$I->amOnPage(ControlPanelPage::$url);
		if ($username == null || $password == null)
		{
			$conf = $this->getSuiteConfiguration();
			$modules = $conf['modules'];
			$config = $modules['config'];
			$joomlaBrowser = $config['JoomlaBrowser'];
			$username = $joomlaBrowser['username'];
			$password = $joomlaBrowser['password'];
		}
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
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheAdministratorDashboard()
	{
		$I = $this;

		$I->adminPage->waitForPageTitle(ControlPanelPage::$pageTitle);
		$I->see(ControlPanelPage::$pageTitle, AdminPage::$pageTitle);
	}
}
