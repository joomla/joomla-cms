<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to define user manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    3.7.3
 */
class UserManagerPage extends AdminPage
{
	/**
	 * Url to user manager listing page.
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $url = "administrator/index.php?option=com_users&view=users";

	/**
	 * Page title of the user manager listing page.
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $pageTitleText = "Users";

	/**
	 * New User Button
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $newButton = ['class' => 'button-new'];

	/**
	 * Edit Button
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $editButton = ['class' => 'button-edit'];

	/**
	 * Locator for the id
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $userCheckbox = ['id' => 'cb1'];

	/**
	 * Save Button
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $saveButton = ['class' => 'button-save'];

	/**
	 * Locator for user's name input field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $nameField = ['id' => 'jform_name'];

	/**
	 * Locator for the success message
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $successMessage = 'User saved.';

	/**
	 * Account details
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $accountDetailsTab = ['xpath' => "//a[@href='#details']"];

	/**
	 * Locator for user's username input field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $usernameField = ['id' => 'jform_username'];

	/**
	 * Locator for user's password input field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $passwordField = ['id' => 'jform_password'];

	/**
	 * Locator for user's password input field for frontend
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $password1Field = ['id' => 'jform_password1'];

	/**
	 * Locator for user's repeat password input field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $password2Field = ['id' => 'jform_password2'];

	/**
	 * Locator for user's email input field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $emailField = ['id' => 'jform_email'];

	/**
	 * Locator for user's email input field for frontend
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $email1Field = ['id' => 'jform_email1'];

	/**
	 * Locator for user's repeat email input field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $email2Field = ['id' => 'jform_email2'];

	/**
	 * Locator for user's username field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $seeUserName = ['xpath' => "//table[@id='userList']//tr[1]/td[3]"];

	/**
	 * Locator for user's name field
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $seeName = ['xpath' => "//table[@id='userList']//tr[1]/td[2]"];

	/**
	 * Locator for user's last login date field in backend listing.
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $lastLoginDate = ['xpath' => "//table[@id='userList']//tr[1]/td[8]"];

	/**
	 * Locator for user is blocked
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $seeBlocked = ['xpath' => "//table[@id='userList']//*//td[4]//span[@class='icon-unpublish']"];

	/**
	 * Locator for user is unblocked
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $seeUnblocked = ['xpath' => "//table[@id='userList']//*//td[4]//span[@class='icon-publish']"];

	/**
	 * Locator for user is deleted and not found
	 *
	 * @var    array
	 * @since  3.7.3
	 */
	public static $noItems = ['class' => 'alert-no-items'];
}
