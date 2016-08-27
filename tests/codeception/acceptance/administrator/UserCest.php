<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class UserCest
{
	/**
	 * Constructor to set up the new User infos
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->faker    = Faker\Factory::create();
		$this->name     = 'User' . $this->faker->randomNumber();
		$this->username = 'uname' . $this->faker->randomNumber();
		$this->email    = 'test@joomla.org';
		$this->password = 'test';
	}

	/**
	 * Create User in the Backend
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function administratorCreateUser(\AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Creating a user');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Users page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_users&view=users');

		$I->expectTo('see Users page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a user with a filled name, email, username and password');
		$I->clickToolbarButton('New');

		$I->waitForText('Users: New', '30', ['css' => 'h1']);
		$I->checkForPhpNoticesOrWarnings();

		$I->fillField(['id' => 'jform_name'], $this->name);
		$I->fillField(['id' => 'jform_username'], $this->username);
		$I->fillField(['id' => 'jform_email'], $this->email);
		$I->fillField(['id' => 'jform_password'], $this->password);
		$I->fillField(['id' => 'jform_password2'], $this->password);

		$I->clickToolbarButton('Save & Close');

		$I->waitForText('Users', '30', ['css' => 'h1']);
		$I->expectTo('see a success message and the user added after saving the user');
		$I->see('User successfully saved', ['id' => 'system-message-container']);
	}
}