<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Page\Acceptance\Administrator\UserListPage;

/**
 * Administrator User Tests
 *
 * @since  3.7.3
 */
class UserListCest
{

	public function __construct()
	{
		$this->username = "testUser";
		$this->password = "test";
		$this->name = "Test Bot";
		$this->email = "Testbot@example.com";
	}

	/**
	 * Create a user
	 *
	 * @param   \Step\Acceptance\Administrator\Admin  $I The AcceptanceTester Object
	 *
	 * @since   3.7.3
	 *
	 * @return  void
	 */
	public function createUser(\Step\Acceptance\Administrator\Admin $I)
	{
		$I->comment('I am going to create a user');
		$I->doAdministratorLogin();
		$this->toggleSendMail($I);

		$I->amOnPage(UserListPage::$url);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForText(UserListPage::$pageTitleText);

		$I->clickToolbarButton('new');

		$I->waitForElement(UserListPage::$accountDetailsTab);
		$I->checkForPhpNoticesOrWarnings();

		$this->fillUserForm($I, $this->name, $this->username, $this->password, $this->email);

		$I->clickToolbarButton("Save");
		$I->waitForText(UserListPage::$pageTitleText);
		$I->seeSystemMessage(UserListPage::$successMessage);

		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Edit a user
	 *
	 * @param   \Step\Acceptance\Administrator\Admin $I  The AcceptanceTester Object
	 *
	 * @since   3.7.3
	 *
	 * @depends createUser
	 *
	 * @return  void
	 */
	public function editUser(\Step\Acceptance\Administrator\Admin $I)
	{
		$I->comment('I am going to edit a user');
		$I->doAdministratorLogin();

		$I->amOnPage(UserListPage::$url);
		$I->waitForText(UserListPage::$pageTitleText);
		$I->click('#menu-collapse-icon');

		$I->click($this->name);

		$I->waitForElement(UserListPage::$accountDetailsTab);
		$I->checkForPhpNoticesOrWarnings();

		$this->fillUserForm($I, $this->name, $this->username, $this->password, $this->email);

		$I->clickToolbarButton("Save");
		$I->waitForText(UserListPage::$pageTitleText);

		$I->seeSystemMessage(UserListPage::$successMessage);
		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Method is a page object to fill user form with given information and prepare to save user.
	 *
	 * @param   AcceptanceTester  $I         The AcceptanceTester Object
	 * @param   string            $name      User's name
	 * @param   string            $username  User's username
	 * @param   string            $password  User's password
	 * @param   string            $email     User's email
	 *
	 * @since   3.7.3
	 *
	 * @return  void  The user's form will be filled with given detail
	 */
	protected function fillUserForm($I, $name, $username, $password, $email)
	{
		$I->click(UserListPage::$accountDetailsTab);
		$I->waitForElementVisible(UserListPage::$nameField, 30);
		$I->fillField(UserListPage::$nameField, $name);
		$I->fillField(UserListPage::$usernameField, $username);
		$I->fillField(UserListPage::$passwordField, $password);
		$I->fillField(UserListPage::$password2Field, $password);
		$I->fillField(UserListPage::$emailField, $email);
	}

	/**
	 * Method to set Send Email to "NO"
	 *
	 * @param   AcceptanceTester  $I         The AcceptanceTester Object
	 *
	 * @since   4.0
	 *
	 * @return  void  The user's form will be filled with given detail
	 */
	protected function toggleSendMail($I)
	{
		$I->amOnPage('/administrator/index.php?option=com_config');
		$I->waitForText('Global Configuration', TIMEOUT, ['css' => '.page-title']);
		$I->comment('I open the Server Tab');
		$I->click('Server');
		$I->comment('I wait for error reporting dropdown');
		$I->click(['xpath' => "//input[@type='radio' and @value=0 and @name='jform[mailonline]']"]);
		$I->comment('I click on save');
		$I->clickToolbarButton("Save");
		$I->comment('I wait for global configuration being saved');
		$I->waitForText('Global Configuration', TIMEOUT, ['css' => '.page-title']);
		$I->see('Configuration saved.', ['id' => 'system-message-container']);

	}
}
