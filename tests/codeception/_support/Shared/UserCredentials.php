<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Shared;

/**
 * Credentials for the user accounts
 *
 * @since  3.7.3
 */
class UserCredentials
{
	/**
	 * Name of the user
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $name = 'Test User';

	/**
	 * Username
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $username = 'testuser';

	/**
	 * Password
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $password = 'secure42';

	/**
	 * Email of the user
	 *
	 * @var    string
	 * @since  3.7.3
	 */
	public static $email = 'noreply@joomla.org';
}
