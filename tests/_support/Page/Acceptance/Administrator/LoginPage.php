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
 * @since    __DEPLOY_VERSION__
 */
class LoginPage extends AdminPage
{
	/**
	 * Locator for username login form textfield
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $usernameField = ['id' => 'mod-login-username'];

	/**
	 * Locator for password login form textfield
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $passwordField = ['id' => 'mod-login-password'];

	/**
	 * Locator for Log in button
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $loginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];
}