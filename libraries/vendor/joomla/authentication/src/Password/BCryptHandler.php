<?php
/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Password;

/**
 * Password handler for BCrypt hashed passwords
 *
 * @since  1.2.0
 */
class BCryptHandler implements HandlerInterface
{
	/**
	 * Generate a hash for a plaintext password
	 *
	 * @param   string  $plaintext  The plaintext password to validate
	 * @param   array   $options    Options for the hashing operation
	 *
	 * @return  string
	 *
	 * @since   1.2.0
	 */
	public function hashPassword($plaintext, array $options = array())
	{
		return password_hash($plaintext, PASSWORD_BCRYPT, $options);
	}

	/**
	 * Check that the password handler is supported in this environment
	 *
	 * @return  boolean
	 *
	 * @since   1.2.0
	 */
	public static function isSupported()
	{
		// Check the password_verify() function exists, either as part of PHP core or through a polyfill
		return function_exists('password_verify');
	}

	/**
	 * Validate a password
	 *
	 * @param   string  $plaintext  The plain text password to validate
	 * @param   string  $hashed     The password hash to validate against
	 *
	 * @return  boolean
	 *
	 * @since   1.2.0
	 */
	public function validatePassword($plaintext, $hashed)
	{
		return password_verify($plaintext, $hashed);
	}
}
