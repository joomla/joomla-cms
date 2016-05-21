<?php
namespace Page\Acceptance\Administrator;

class Login extends \AcceptanceTester
{
	/**
     * @When I login into Joomla Administrator with username :arg1 and password :arg1
     */
    public function login($username, $password)
    {
			$I = $this;
			$I->amOnPage('administrator/');
			$I->fillField(['css' => 'input[data-tests="username"]'], $username);
			$I->fillField(['css' => 'input[data-tests="password"]'], $password);
			$I->click(['css' => 'button[data-tests="log in"]']);
    }
}
