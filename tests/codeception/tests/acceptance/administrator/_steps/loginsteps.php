<?php

namespace AcceptanceTester;
/**
 * Class LoginSteps
 *
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
class LoginSteps extends \AcceptanceTester
{
	/**
	 * Function to execute an Admin Login for Joomla 3
	 *
	 * @return void
	 */
	public function doAdministratorLogin($user, $password)
	{
		$I = $this;
		$I->am('Administrator');
		$I->amOnPage(\AdministratorLoginPage::$URL);
		$I->fillField(\AdministratorLoginPage::$elements['username'], $user);
		$I->fillField(\AdministratorLoginPage::$elements['password'], $password);
		$I->click('Log in');
		$I->waitForText('Control Panel',10,'H1');
	}
}
