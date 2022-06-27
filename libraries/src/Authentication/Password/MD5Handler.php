<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

\defined('JPATH_PLATFORM') or die;

use Joomla\Authentication\Password\HandlerInterface;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\User\UserHelper;

/**
 * Password handler for MD5 hashed passwords
 *
 * @since       4.0.0
 * @deprecated  5.0  Support for MD5 hashed passwords will be removed
 */
class MD5Handler implements HandlerInterface, CheckIfRehashNeededHandlerInterface
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
		$salt    = UserHelper::genRandomPassword(32);
		$crypted = md5($plaintext . $salt);

		return $crypted . ':' . $salt;
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
		// Check the password
		$parts = explode(':', $hashed);
		$salt  = @$parts[1];

		// Compile the hash to compare
		// If the salt is empty AND there is a ':' in the original hash, we must append ':' at the end
		$testcrypt = md5($plaintext . $salt) . ($salt ? ':' . $salt : (strpos($hashed, ':') !== false ? ':' : ''));

		return Crypt::timingSafeCompare($hashed, $testcrypt);
	}
}
