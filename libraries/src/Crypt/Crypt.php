<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Crypt\Cipher\SimpleCipher;
use Joomla\CMS\Log\Log;

/**
 * Crypt is a Joomla Platform class for handling basic encryption/decryption of data.
 *
 * @since  3.0.0
 */
class Crypt
{
	/**
	 * @var    CipherInterface  The encryption cipher object.
	 * @since  3.0.0
	 */
	private $_cipher;

	/**
	 * @var    Key  The encryption key[/pair)].
	 * @since  3.0.0
	 */
	private $_key;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption. If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @param   CipherInterface  $cipher  The encryption cipher object.
	 * @param   Key              $key     The encryption key[/pair)].
	 *
	 * @since   3.0.0
	 */
	public function __construct(CipherInterface $cipher = null, Key $key = null)
	{
		// Set the encryption key[/pair)].
		$this->_key = $key;

		// Set the encryption cipher.
		$this->_cipher = isset($cipher) ? $cipher : new SimpleCipher;
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   3.0.0
	 * @throws  \InvalidArgumentException
	 */
	public function decrypt($data)
	{
		try
		{
			return $this->_cipher->decrypt($data, $this->_key);
		}
		catch (\InvalidArgumentException $e)
		{
			return false;
		}
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   3.0.0
	 */
	public function encrypt($data)
	{
		return $this->_cipher->encrypt($data, $this->_key);
	}

	/**
	 * Method to generate a new encryption key[/pair] object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   3.0.0
	 */
	public function generateKey(array $options = array())
	{
		return $this->_cipher->generateKey($options);
	}

	/**
	 * Method to set the encryption key[/pair] object.
	 *
	 * @param   Key  $key  The key object to set.
	 *
	 * @return  Crypt
	 *
	 * @since   3.0.0
	 */
	public function setKey(Key $key)
	{
		$this->_key = $key;

		return $this;
	}

	/**
	 * Generate random bytes.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 *
	 * @since   3.0.0
	 */
	public static function genRandomBytes($length = 16)
	{
		return random_bytes($length);
	}

	/**
	 * A timing safe comparison method.
	 *
	 * This defeats hacking attempts that use timing based attack vectors.
	 *
	 * NOTE: Length will leak.
	 *
	 * @param   string  $known    A known string to check against.
	 * @param   string  $unknown  An unknown string to check.
	 *
	 * @return  boolean  True if the two strings are exactly the same.
	 *
	 * @since   3.2
	 */
	public static function timingSafeCompare($known, $unknown)
	{
		// This function is native in PHP as of 5.6 and backported via the symfony/polyfill-56 library
		return hash_equals((string) $known, (string) $unknown);
	}

	/**
	 * Tests for the availability of updated crypt().
	 * Based on a method by Anthony Ferrera
	 *
	 * @return  boolean  Always returns true since 3.3
	 *
	 * @note    To be removed when PHP 5.3.7 or higher is the minimum supported version.
	 * @link    https://github.com/ircmaxell/password_compat/blob/master/version-test.php
	 * @since   3.2
	 * @deprecated  4.0
	 */
	public static function hasStrongPasswordSupport()
	{
		// Log usage of deprecated function
		Log::add(__METHOD__ . '() is deprecated without replacement.', Log::WARNING, 'deprecated');

		if (!defined('PASSWORD_DEFAULT'))
		{
			// Always make sure that the password hashing API has been defined.
			include_once JPATH_ROOT . '/vendor/ircmaxell/password-compat/lib/password.php';
		}

		return true;
	}

	/**
	 * Safely detect a string's length
	 *
	 * This method is derived from \ParagonIE\Halite\Util::safeStrlen()
	 *
	 * @param   string  $str  String to check the length of
	 *
	 * @return  integer
	 *
	 * @since   3.5
	 * @ref     mbstring.func_overload
	 * @throws  \RuntimeException
	 */
	public static function safeStrlen($str)
	{
		static $exists = null;

		if ($exists === null)
		{
			$exists = function_exists('mb_strlen');
		}

		if ($exists)
		{
			$length = mb_strlen($str, '8bit');

			if ($length === false)
			{
				throw new \RuntimeException('mb_strlen() failed unexpectedly');
			}

			return $length;
		}

		// If we reached here, we can rely on strlen to count bytes:
		return \strlen($str);
	}

	/**
	 * Safely extract a substring
	 *
	 * This method is derived from \ParagonIE\Halite\Util::safeSubstr()
	 *
	 * @param   string   $str     The string to extract the substring from
	 * @param   integer  $start   The starting position to extract from
	 * @param   integer  $length  The length of the string to return
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	public static function safeSubstr($str, $start, $length = null)
	{
		static $exists = null;

		if ($exists === null)
		{
			$exists = function_exists('mb_substr');
		}

		if ($exists)
		{
			// In PHP 5.3 mb_substr($str, 0, NULL, '8bit') returns an empty string, so we have to find the length ourselves.
			if ($length === null)
			{
				if ($start >= 0)
				{
					$length = static::safeStrlen($str) - $start;
				}
				else
				{
					$length = -$start;
				}
			}

			return mb_substr($str, $start, $length, '8bit');
		}

		// Unlike mb_substr(), substr() doesn't accept NULL for length
		if ($length !== null)
		{
			return substr($str, $start, $length);
		}

		return substr($str, $start);
	}
}
