<?php
/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Password;

/**
 * Password handler for Argon2i hashed passwords
 *
 * @since  1.2.0
 */
class Argon2iHandler implements HandlerInterface
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
	 * @throws  \LogicException
	 */
	public function hashPassword($plaintext, array $options = array())
	{
		// Use the password extension if able
		if (version_compare(PHP_VERSION, '7.2', '>=') && defined('PASSWORD_ARGON2I'))
		{
			return password_hash($plaintext, PASSWORD_ARGON2I, $options);
		}

		// Use the sodium extension (PHP 7.2 native or PECL 2.x) if able
		if (function_exists('sodium_crypto_pwhash_str_verify'))
		{
			$hash = sodium_crypto_pwhash_str(
				$plaintext,
				SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
				SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
			);
			sodium_memzero($plaintext);

			return $hash;
		}

		// Use the libsodium extension (PECL 1.x) if able
		if (extension_loaded('libsodium'))
		{
			$hash = \Sodium\crypto_pwhash_str(
				$plaintext,
				\Sodium\CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
				\Sodium\CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
			);
			\Sodium\memzero($plaintext);

			return $hash;
		}

		throw new \LogicException('Argon2i algorithm is not supported.');
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
		// Check for native PHP engine support in the password extension
		if (version_compare(PHP_VERSION, '7.2', '>=') && defined('PASSWORD_ARGON2I'))
		{
			return true;
		}

		// Check if the sodium_compat polyfill is installed and look for compatibility through that
		if (class_exists('\\ParagonIE_Sodium_Compat') && method_exists('\\ParagonIE_Sodium_Compat', 'crypto_pwhash_is_available'))
		{
			return \ParagonIE_Sodium_Compat::crypto_pwhash_is_available();
		}

		// Check for support from the (lib)sodium extension
		return function_exists('sodium_crypto_pwhash_str') || extension_loaded('libsodium');
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
	 * @throws  \LogicException
	 */
	public function validatePassword($plaintext, $hashed)
	{
		// Use the password extension if able
		if (version_compare(PHP_VERSION, '7.2', '>=') && defined('PASSWORD_ARGON2I'))
		{
			return password_verify($plaintext, $hashed);
		}

		// Use the sodium extension (PHP 7.2 native or PECL 2.x) if able
		if (function_exists('sodium_crypto_pwhash_str_verify'))
		{
			$valid = sodium_crypto_pwhash_str_verify($hashed, $plaintext);
			sodium_memzero($plaintext);

			return $valid;
		}

		// Use the libsodium extension (PECL 1.x) if able
		if (extension_loaded('libsodium'))
		{
			$valid = \Sodium\crypto_pwhash_str_verify($hashed, $plaintext);
			\Sodium\memzero($plaintext);

			return $valid;
		}

		throw new \LogicException('Argon2i algorithm is not supported.');
	}
}
