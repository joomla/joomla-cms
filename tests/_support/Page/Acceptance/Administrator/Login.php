<?php
namespace Page\Acceptance\Administrator;

class Login extends \AcceptanceTester
{
	/**
	 * Install joomla CMS
	 *
	 * @Given Joomla CMS is installed
	 */
	public function joomlaCMSIsInstalled()
	{
		// throw new \Codeception\Exception\Incomplete("Step `Joomla CMS is installed` is not defined");
	}

	/**
	 * @When Login into Joomla administrator with username :arg1 and password :arg1
	 */
	public function loginIntoJoomlaAdministrator($username, $password)
	{
		$I = $this;
		$I->amOnPage('administrator/');
		$I->fillField(['css' => 'input[data-tests="username"]'], $username);
		$I->fillField(['css' => 'input[data-tests="password"]'], $password);
		$I->click(['css' => 'button[data-tests="log in"]']);
	}

	/**
	 * @Then I see administrator dashboard
	 */
	public function iSeeAdministratorDashboard()
	{
		$I = $this;
		$I->waitForPageTitle('Control Panel', 4);
	}
}
