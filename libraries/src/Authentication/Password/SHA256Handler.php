<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

defined('JPATH_PLATFORM') or die;

use Joomla\Authentication\Password\HandlerInterface;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\User\UserHelper;

/**
 * Password handler for SHA256 hashed passwords
 *
 * @since       4.0.0
 * @deprecated  5.0  Support for SHA256 hashed passwords will be removed
 */
class SHA256Handler implements HandlerInterface, CheckIfRehashNeededHandlerInterface
{
	/**
	 * Check if the password requires rehashing
	 *
	 * @param   string  $hash  The password hash to check
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function checkIfRehashNeeded(string $hash): bool
	{
		return true;
	}

	/**
	 * Generate a hash for a plaintext password
	 *
	 * @param   string  $plaintext  The plaintext password to validate
	 * @param   array   $options    Options for the hashing operation
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function hashPassword($plaintext, array $options = [])
	{
		$salt = $options['salt'] ?? '';

		if ($salt !== '')
		{
			$salt = preg_replace('|^{sha256}|i', '', $salt);
		}
		else
		{
			$salt = UserHelper::genRandomPassword(16);
		}

		$encrypted = ($salt) ? hash('sha256', $plaintext . $salt) . ':' . $salt : hash('sha256', $plaintext);

		return '{SHA256}' . $encrypted;
	}

	/**
	 * Check that the password handler is supported in this environment
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Validate a password
	 *
	 * @param   string  $plaintext  The plain text password to validate
	 * @param   string  $hashed     The password hash to validate against
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function validatePassword($plaintext, $hashed)
	{
		$parts     = explode(':', $hashed);
		$salt      = @$parts[1];
		$testcrypt = $this->hashPassword($plaintext, ['salt' => $salt]);

		return Crypt::timingSafeCompare($hashed, $testcrypt);
	}
}
