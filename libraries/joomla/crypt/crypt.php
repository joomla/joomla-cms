<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Crypt\Crypt;

/**
 * JCrypt is a Joomla Platform class for handling basic encryption/decryption of data.
 *
 * @since  12.1
 */
class JCrypt extends Crypt
{
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
	 * @throws  RuntimeException
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
				throw new RuntimeException('mb_strlen() failed unexpectedly');
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
