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
 * @since    3.7
 */
class UserManagerPage extends AdminPage
{
	/**
	 * Url to user manager listing page.
	 *
	 * @var    string
	 * @since  3.7
	 */
	public static $url = "administrator/index.php?option=com_users&view=users";

	/**
	 * Page title of the user manager listing page.
	 *
	 * @var    string
	 * @since  3.7
	 */
	public static $pageTitleText = "Users";

	/**
	 * Locator for user's name input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $nameField = ['id' => 'jform_name'];

	/**
	 * Locator for user's username input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $usernameField = ['id' => 'jform_username'];

	/**
	 * Locator for user's password input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $passwordField = ['id' => 'jform_password'];

	/**
	 * Locator for user's password input field for frontend
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $password1Field = ['id' => 'jform_password1'];

	/**
	 * Locator for user's repeat password input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $password2Field = ['id' => 'jform_password2'];

	/**
	 * Locator for user's email input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $emailField = ['id' => 'jform_email'];

	/**
	 * Locator for user's email input field for frontend
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $email1Field = ['id' => 'jform_email1'];

	/**
	 * Locator for user's repeat email input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $email2Field = ['id' => 'jform_email2'];

	/**
	 * Locator for user's search input field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $filterSearch = ['id' => 'filter_search'];

	/**
	 * Locator for user's search button icon
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $iconSearch = ['class' => 'icon-search'];

	/**
	 * Locator for user's page title
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $title = ['id' => 'jform_title'];

	/**
	 * Locator for user's username field in frontend
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $seeUserName = ['xpath' => "//table[@id='userList']//tr[1]/td[3]"];

	/**
	 * Locator for user's name field in frontend
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $seeName = ['xpath' => "//table[@id='userList']//tr[1]/td[2]"];

	/**
	 * Locator for user's last login date field in backend listing.
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $lastLoginDate = ['xpath' => "//table[@id='userList']//tr[1]/td[8]"];
}
