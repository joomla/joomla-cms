<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Page\Acceptance\Administrator\UserListPage;
use Step\Acceptance\Administrator\Admin;

/**
 * Administrator User Tests.
 *
 * @since  3.7.3
 */
class UserListCest
{
	/**
	 * UserListCest constructor.
	 *
	 * @since   4.0.0
	 */
	public function __construct()
	{
		$this->username = "testUser";
		$this->password = "joomla17082005";
		$this->name     = "Test Bot";
		$this->email    = "Testbot@example.com";
	}

	/**
	 * Create a user.
	 *
	 * @param   mixed  \Step\Acceptance\Administrator\Admin  $I  The AcceptanceTester Object
	 *
	 * @return  void
	 * @since   3.7.3
	 *
	 * @throws Exception
	 */
	public function createUser(Admin $I)
	{
		$I->comment('I am going to create a user');
		$I->doAdministratorLogin();
		$this->toggleSendMail($I);

		$I->amOnPage(UserListPage::$url);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForText(UserListPage::$pageTitleText);
		$I->waitForJsOnPageLoad();
		$I->clickToolbarButton('new');

		$I->waitForElement(UserListPage::$accountDetailsTab);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForJsOnPageLoad();

		$this->fillUserForm($I, $this->name, $this->username, $this->password, $this->email);

		$I->clickToolbarButton("Save & Close");
		$I->waitForText(UserListPage::$pageTitleText);
		$I->seeSystemMessage(UserListPage::$successMessage);

		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Edit a user.
	 *
	 * @param   mixed   \Step\Acceptance\Administrator\Admin  $I  The AcceptanceTester Object
	 *
	 * @return  void
	 *
	 * @since   3.7.3
	 *
	 * @depends createUser
	 *
	 * @throws Exception
	 */
	public function editUser(Admin $I)
	{
		$I->comment('I am going to edit a user');
		$I->doAdministratorLogin();

		$I->amOnPage(UserListPage::$url);
		$I->waitForText(UserListPage::$pageTitleText);
		$I->waitForJsOnPageLoad();

		$I->click($this->name);

		$I->waitForElement(UserListPage::$accountDetailsTab);
		$I->waitForJsOnPageLoad();
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForJsOnPageLoad();

		$this->fillUserForm($I, $this->name, $this->username, $this->password, $this->email);

		$I->clickToolbarButton("Save");
		$I->waitForText(UserListPage::$pageTitleText);
		$I->waitForJsOnPageLoad();

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
	 * @return  void  The user's form will be filled with given detail
	 *
	 * @since   3.7.3
	 *
	 * @throws Exception
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
	 * Method to set Send Email to "NO".
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @return  void  The user's form will be filled with given detail
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	protected function toggleSendMail($I)
	{
		$I->amOnPage('/administrator/index.php?option=com_config');
		$I->waitForText('Global Configuration', $I->getConfig('timeout'), ['css' => '.page-title']);
		$I->comment('I open the Server Tab');
		$I->click(['xpath' => "//joomla-tab[@id='configTabs']/div[@role='tablist']/button[@aria-controls='page-server']"]);
		$I->comment('I click on the switcher to disable sending mails');
		$I->waitForJS('document.evaluate("//input[@type=\'radio\' and @value=0 and @name=\'jform[mailonline]\']", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click();return true;');
		$I->comment('I click on save');
		$I->clickToolbarButton("Save");
		$I->comment('I wait for global configuration being saved');
		$I->waitForText('Global Configuration', $I->getConfig('timeout'), ['css' => '.page-title']);
		$I->waitForElementVisible(['id' => 'system-message-container'], $I->getConfig('timeout'));
		$I->see('Configuration saved.', ['id' => 'system-message-container']);
	}
}
