<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt;

\defined('JPATH_PLATFORM') or die;

use Joomla\Crypt\Crypt as JCrypt;

/**
 * Crypt is a Joomla Platform class for handling basic encryption/decryption of data.
 *
 * @since  3.0.0
 */
class Crypt extends JCrypt
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
		/**
		 * Explanation about the function_exists
		 *
		 * Yes, hash_equals has existed since PHP 5.6.0 and Joomla's minimum requirements are higher
		 * than that. However, this does not prevent a misguided server administrator from disabling
		 * hash_equals in php.ini. Hence the need for checking whether the function exists or not.
		 */
		if (function_exists('hash_equals'))
		{
			return hash_equals($known, $unknown);
		}

		/**
		 * If hash_equals is not available we use a pure PHP implementation by Anthony Ferrara.
		 *
		 * @see https://blog.ircmaxell.com/2014/11/its-all-about-time.html
		 */
		$safeLen = strlen($known);
		$userLen = strlen($unknown);

		if ($userLen != $safeLen)
		{
			return false;
		}

		$result = 0;

		for ($i = 0; $i < $userLen; $i++)
		{
			$result |= (ord($known[$i]) ^ ord($unknown[$i]));
		}

		// They are only identical strings if $result is exactly 0...
		return $result === 0;
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
			$exists = \function_exists('mb_strlen');
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
			$exists = \function_exists('mb_substr');
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
