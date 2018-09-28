<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Password;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Crypt\CryptPassword;

/**
 * Joomla Platform Password Crypter
 *
 * @since       12.2
 * @deprecated  4.0  Use PHP 5.5's native password hashing API
 */
class SimpleCryptPassword implements CryptPassword
{
	/**
	 * @var    integer  The cost parameter for hashing algorithms.
	 * @since  12.2
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	protected $cost = 10;

	/**
	 * @var    string   The default hash type
	 * @since  12.3
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	protected $defaultType = '$2y$';

	/**
	 * Creates a password hash
	 *
	 * @param   string  $password  The password to hash.
	 * @param   string  $type      The hash type.
	 *
	 * @return  mixed  The hashed password or false if the password is too long.
	 *
	 * @since   12.2
	 * @throws  \InvalidArgumentException
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	public function create($password, $type = null)
	{
		if (empty($type))
		{
			$type = $this->defaultType;
		}

		switch ($type)
		{
			case '$2a$':
			case CryptPassword::BLOWFISH:

				$type = '$2a$';

				if (Crypt::hasStrongPasswordSupport())
				{
					$type = '$2y$';
				}

				$salt = $type . str_pad($this->cost, 2, '0', STR_PAD_LEFT) . '$' . $this->getSalt(22);

				return crypt($password, $salt);

			case CryptPassword::MD5:
				$salt = $this->getSalt(12);

				$salt = '$1$' . $salt;

				return crypt($password, $salt);

			case CryptPassword::JOOMLA:
				$salt = $this->getSalt(32);

				return md5($password . $salt) . ':' . $salt;

			default:
				throw new \InvalidArgumentException(sprintf('Hash type %s is not supported', $type));
				break;
		}
	}

	/**
	 * Sets the cost parameter for the generated hash for algorithms that use a cost factor.
	 *
	 * @param   integer  $cost  The new cost value.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	public function setCost($cost)
	{
		$this->cost = $cost;
	}

	/**
	 * Generates a salt of specified length. The salt consists of characters in the set [./0-9A-Za-z].
	 *
	 * @param   integer  $length  The number of characters to return.
	 *
	 * @return  string  The string of random characters.
	 *
	 * @since   12.2
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	protected function getSalt($length)
	{
		$bytes = ceil($length * 6 / 8);

		$randomData = str_replace('+', '.', base64_encode(Crypt::genRandomBytes($bytes)));

		return substr($randomData, 0, $length);
	}

	/**
	 * Verifies a password hash
	 *
	 * @param   string  $password  The password to verify.
	 * @param   string  $hash      The password hash to check.
	 *
	 * @return  boolean  True if the password is valid, false otherwise.
	 *
	 * @since   12.2
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	public function verify($password, $hash)
	{
		// Check if the hash is a blowfish hash.
		if (substr($hash, 0, 4) == '$2a$' || substr($hash, 0, 4) == '$2y$')
		{
			$type = '$2a$';

			if (Crypt::hasStrongPasswordSupport())
			{
				$type = '$2y$';
			}

			return password_verify($password, $hash);
		}

		// Check if the hash is an MD5 hash.
		if (substr($hash, 0, 3) == '$1$')
		{
			return Crypt::timingSafeCompare(crypt($password, $hash), $hash);
		}

		// Check if the hash is a Joomla hash.
		if (preg_match('#[a-z0-9]{32}:[A-Za-z0-9]{32}#', $hash) === 1)
		{
			// Check the password
			$parts = explode(':', $hash);
			$salt  = @$parts[1];

			// Compile the hash to compare
			// If the salt is empty AND there is a ':' in the original hash, we must append ':' at the end
			$testcrypt = md5($password . $salt) . ($salt ? ':' . $salt : (strpos($hash, ':') !== false ? ':' : ''));

			return Crypt::timingSafeCompare($hash, $testcrypt);
		}

		return false;
	}

	/**
	 * Sets a default type
	 *
	 * @param   string  $type  The value to set as default.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	public function setDefaultType($type)
	{
		if (!empty($type))
		{
			$this->defaultType = $type;
		}
	}

	/**
	 * Gets the default type
	 *
	 * @return   string  $type  The default type
	 *
	 * @since   12.3
	 * @deprecated  4.0  Use PHP 5.5's native password hashing API
	 */
	public function getDefaultType()
	{
		return $this->defaultType;
	}
}
