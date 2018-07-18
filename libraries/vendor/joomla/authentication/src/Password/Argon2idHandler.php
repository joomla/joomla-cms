<?php
/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Password;

/**
 * Password handler for Argon2id hashed passwords
 *
 * @since  __DEPLOY_VERSION__
 */
class Argon2idHandler implements HandlerInterface
{
	/**
	 * Generate a hash for a plaintext password
	 *
	 * @param   string  $plaintext  The plaintext password to validate
	 * @param   array   $options    Options for the hashing operation
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \LogicException
	 */
	public function hashPassword($plaintext, array $options = array())
	{
		// Use the password extension if able
		if (version_compare(PHP_VERSION, '7.3', '>=') && \defined('PASSWORD_ARGON2ID'))
		{
			return password_hash($plaintext, PASSWORD_ARGON2ID, $options);
		}

		throw new \LogicException('Argon2id algorithm is not supported.');
	}

	/**
	 * Check that the password handler is supported in this environment
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		// Check for native PHP engine support in the password extension
		if (version_compare(PHP_VERSION, '7.3', '>=') && \defined('PASSWORD_ARGON2ID'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Validate a password
	 *
	 * @param   string  $plaintext  The plain text password to validate
	 * @param   string  $hashed     The password hash to validate against
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \LogicException
	 */
	public function validatePassword($plaintext, $hashed)
	{
		// Use the password extension if able
		if (version_compare(PHP_VERSION, '7.3', '>=') && \defined('PASSWORD_ARGON2ID'))
		{
			return password_verify($plaintext, $hashed);
		}

		throw new \LogicException('Argon2id algorithm is not supported.');
	}
}
