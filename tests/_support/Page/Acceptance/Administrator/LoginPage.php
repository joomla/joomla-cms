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
 * Acceptance Page object class to define Login view page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    3.7
 */
class LoginPage extends AdminPage
{
	/**
	 * Locator for username login form textfield
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $usernameField = ['id' => 'mod-login-username'];

	/**
	 * Locator for password login form textfield
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $passwordField = ['id' => 'mod-login-password'];

	/**
	 * Locator for Log in button
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $loginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];
}