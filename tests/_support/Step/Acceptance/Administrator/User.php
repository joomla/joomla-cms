<?php
namespace Step\Acceptance\Administrator;

class User extends \AcceptanceTester
{
	/**
	 * @Given I am registered administrator named :arg1
	 */
	public function iAmRegisteredAdministratorNamed($arg1)
	{
		$I = $this;
		$I->comment('@todo');
	}

	/**
	 * @Then I should see administrator dashboard
	 */
	public function iShouldSeeAdministratorDashboard()
	{
		$I = $this;
		$I->comment('@todo');
	}
}