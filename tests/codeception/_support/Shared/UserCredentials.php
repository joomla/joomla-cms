<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Shared;

/**
 * Credentials for the user accounts
 *
 * @since  __DEPLOY_VERSION__
 */
class UserCredentials
{
	/**
	 * Name of the user
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $name = 'Test User';

	/**
	 * Username
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $username = 'testuser';

	/**
	 * Password
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $password = 'secure42';

	/**
	 * Email of the user
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $email = 'noreply@joomla.org';
}
