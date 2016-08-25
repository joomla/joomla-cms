<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to define user manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class UserManagerPage extends AdminPage
{
	/**
	 * Url to user manager listing page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = "administrator/index.php?option=com_users&view=users";

	/**
	 * Page title of the user manager listing page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $pageTitleText = "Users";

	/**
	 * Locator for user's name input field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $nameField = ['id' => 'jform_name'];

	/**
	 * Locator for user's username input field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $usernameField = ['id' => 'jform_username'];

	/**
	 * Locator for user's password input field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $passwordField = ['id' => 'jform_password'];

	/**
	 * Locator for user's password input field for frontend
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $password1Field = ['id' => 'jform_password1'];

	/**
	 * Locator for user's repeat password input field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $password2Field = ['id' => 'jform_password2'];

	/**
	 * Locator for user's email input field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $emailField = ['id' => 'jform_email'];

	/**
	 * Locator for user's email input field for frontend
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $email1Field = ['id' => 'jform_email1'];

	/**
	 * Locator for user's repeat email input field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $email2Field = ['id' => 'jform_email2'];

	/**
	 * Locator for user's username field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeUserName = ['xpath' => "//table[@id='userList']//tr[1]/td[3]"];

	/**
	 * Locator for user's name field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeName = ['xpath' => "//table[@id='userList']//tr[1]/td[2]"];

	/**
	 * Locator for user's last login date field in backend listing.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $lastLoginDate = ['xpath' => "//table[@id='userList']//tr[1]/td[8]"];

	/**
	 * Locator for user is blocked
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeBlocked = ['xpath' => "//table[@id='userList']//*//td[4]//span[@class='icon-unpublish']"];

	/**
	 * Locator for user is unblocked
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeUnblocked = ['xpath' => "//table[@id='userList']//*//td[4]//span[@class='icon-publish']"];

	/**
	 * Locator for user is deleted and not found
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $noItems = ['class' => 'alert-no-items'];

	/**
	 * Method is a page object to fill user form with given information and prepare to save user.
	 *
	 * @param   string  $name      User's name
	 * @param   string  $username  User's username
	 * @param   string  $password  User's password
	 * @param   string  $email     User's email
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void  The user's form will be filled with given detail
	 */
	public function fillUserForm($name, $username, $password, $email)
	{
		$I = $this;

		$I->fillField(self::$nameField, $name);
		$I->fillField(self::$usernameField, $username);
		$I->fillField(self::$passwordField, $password);
		$I->fillField(self::$password2Field, $password);
		$I->fillField(self::$emailField, $email);
	}
}
