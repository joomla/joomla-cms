<?php
/**
 * Part of the Joomla Framework String Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\String;

// PHP mbstring and iconv local configuration
@ini_set('default_charset', 'UTF-8');

/**
 * String handling class for UTF-8 data wrapping the phputf8 library. All functions assume the validity of UTF-8 strings.
 *
 * @since  1.3.0
 */
abstract class StringHelper
{
	/**
	 * Increment styles.
	 *
	 * @var    array
	 * @since  1.3.0
	 */
	protected static $incrementStyles = [
		'dash'    => [
			'#-(\d+)$#',
			'-%d'
		],
		'default' => [
			['#\((\d+)\)$#', '#\(\d+\)$#'],
			[' (%d)', '(%d)'],
		],
	];

	/**
	 * Increments a trailing number in a string.
	 *
	 * Used to easily create distinct labels when copying objects. The method has the following styles:
	 *
	 * default: "Label" becomes "Label (2)"
	 * dash:    "Label" becomes "Label-2"
	 *
	 * @param   string   $string  The source string.
	 * @param   string   $style   The the style (default|dash).
	 * @param   integer  $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
	 *
	 * @return  string  The incremented string.
	 *
	 * @since   1.3.0
	 */
	public static function increment($string, $style = 'default', $n = 0)
	{
		$styleSpec = isset(static::$incrementStyles[$style]) ? static::$incrementStyles[$style] : static::$incrementStyles['default'];

		// Regular expression search and replace patterns.
		if (is_array($styleSpec[0]))
		{
			$rxSearch = $styleSpec[0][0];
			$rxReplace = $styleSpec[0][1];
		}
		else
		{
			$rxSearch = $rxReplace = $styleSpec[0];
		}

		// New and old (existing) sprintf formats.
		if (is_array($styleSpec[1]))
		{
			$newFormat = $styleSpec[1][0];
			$oldFormat = $styleSpec[1][1];
		}
		else
		{
			$newFormat = $oldFormat = $styleSpec[1];
		}

		// Check if we are incrementing an existing pattern, or appending a new one.
		if (preg_match($rxSearch, $string, $matches))
		{
			$n = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
	}

	/**
	 * Tests whether a string contains only 7bit ASCII bytes.
	 *
	 * You might use this to conditionally check whether a string needs handling as UTF-8 or not, potentially offering performance
	 * benefits by using the native PHP equivalent if it's just ASCII e.g.;
	 *
	 * <code>
	 * if (StringHelper::is_ascii($someString))
	 * {
	 *     // It's just ASCII - use the native PHP version
	 *     $someString = strtolower($someString);
	 * }
	 * else
	 * {
	 *     $someString = StringHelper::strtolower($someString);
	 * }
	 * </code>
	 *
	 * @param   string  $str  The string to test.
	 *
	 * @return  boolean True if the string is all ASCII
	 *
	 * @since   1.3.0
	 */
	public static function is_ascii($str)
	{
		return utf8_is_ascii($str);
	}

	/**
	 * UTF-8 aware alternative to ord()
	 *
	 * Returns the unicode ordinal for a character.
	 *
	 * @param   string  $chr  UTF-8 encoded character
	 *
	 * @return  integer Unicode ordinal for the character
	 *
	 * @link    https://secure.php.net/ord
	 * @since   1.4.0
	 */
	public static function ord($chr)
	{
		return utf8_ord($chr);
	}

	/**
	 * UTF-8 aware alternative to strpos()
	 *
	 * Find position of first occurrence of a string.
	 *
	 * @param   string   $str     String being examined
	 * @param   string   $search  String being searched for
	 * @param   integer  $offset  Optional, specifies the position from which the search should be performed
	 *
	 * @return  mixed  Number of characters before the first match or FALSE on failure
	 *
	 * @link    https://secure.php.net/strpos
	 * @since   1.3.0
	 */
	public static function strpos($str, $search, $offset = false)
	{
		if ($offset === false)
		{
			return utf8_strpos($str, $search);
		}

		return utf8_strpos($str, $search, $offset);
	}

	/**
	 * UTF-8 aware alternative to strrpos()
	 *
	 * Finds position of last occurrence of a string.
	 *
	 * @param   string   $str     String being examined.
	 * @param   string   $search  String being searched for.
	 * @param   integer  $offset  Offset from the left of the string.
	 *
	 * @return  mixed  Number of characters before the last match or false on failure
	 *
	 * @link    https://secure.php.net/strrpos
	 * @since   1.3.0
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		return utf8_strrpos($str, $search, $offset);
	}

	/**
	 * UTF-8 aware alternative to substr()
	 *
	 * Return part of a string given character offset (and optionally length).
	 *
	 * @param   string   $str     String being processed
	 * @param   integer  $offset  Number of UTF-8 characters offset (from left)
	 * @param   integer  $length  Optional length in UTF-8 characters from offset
	 *
	 * @return  mixed string or FALSE if failure
	 *
	 * @link    https://secure.php.net/substr
	 * @since   1.3.0
	 */
	public static function substr($str, $offset, $length = false)
	{
		if ($length === false)
		{
			return utf8_substr($str, $offset);
		}

		return utf8_substr($str, $offset, $length);
	}

	/**
	 * UTF-8 aware alternative to strtolower()
	 *
	 * Make a string lowercase
	 *
	 * Note: The concept of a characters "case" only exists is some alphabets such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	 * not exist in the Chinese alphabet, for example. See Unicode Standard Annex #21: Case Mappings
	 *
	 * @param   string  $str  String being processed
	 *
	 * @return  mixed  Either string in lowercase or FALSE is UTF-8 invalid
	 *
	 * @link    https://secure.php.net/strtolower
	 * @since   1.3.0
	 */
	public static function strtolower($str)
	{
		return utf8_strtolower($str);
	}

	/**
	 * UTF-8 aware alternative to strtoupper()
	 *
	 * Make a string uppercase
	 *
	 * Note: The concept of a characters "case" only exists is some alphabets such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	 * not exist in the Chinese alphabet, for example. See Unicode Standard Annex #21: Case Mappings
	 *
	 * @param   string  $str  String being processed
	 *
	 * @return  mixed  Either string in uppercase or FALSE is UTF-8 invalid
	 *
	 * @link    https://secure.php.net/strtoupper
	 * @since   1.3.0
	 */
	public static function strtoupper($str)
	{
		return utf8_strtoupper($str);
	}

	/**
	 * UTF-8 aware alternative to strlen()
	 *
	 * Returns the number of characters in the string (NOT THE NUMBER OF BYTES).
	 *
	 * @param   string  $str  UTF-8 string.
	 *
	 * @return  integer  Number of UTF-8 characters in string.
	 *
	 * @link    https://secure.php.net/strlen
	 * @since   1.3.0
	 */
	public static function strlen($str)
	{
		return utf8_strlen($str);
	}

	/**
	 * UTF-8 aware alternative to str_ireplace()
	 *
	 * Case-insensitive version of str_replace()
	 *
	 * @param   string   $search   String to search
	 * @param   string   $replace  Existing string to replace
	 * @param   string   $str      New string to replace with
	 * @param   integer  $count    Optional count value to be passed by referene
	 *
	 * @return  string  UTF-8 String
	 *
	 * @link    https://secure.php.net/str_ireplace
	 * @since   1.3.0
	 */
	public static function str_ireplace($search, $replace, $str, $count = null)
	{
		if ($count === false)
		{
			return utf8_ireplace($search, $replace, $str);
		}

		return utf8_ireplace($search, $replace, $str, $count);
	}

	/**
	 * UTF-8 aware alternative to str_pad()
	 *
	 * Pad a string to a certain length with another string.
	 * $padStr may contain multi-byte characters.
	 *
	 * @param   string   $input   The input string.
	 * @param   integer  $length  If the value is negative, less than, or equal to the length of the input string, no padding takes place.
	 * @param   string   $padStr  The string may be truncated if the number of padding characters can't be evenly divided by the string's length.
	 * @param   integer  $type    The type of padding to apply
	 *
	 * @return  string
	 *
	 * @link    https://secure.php.net/str_pad
	 * @since   1.4.0
	 */
	public static function str_pad($input, $length, $padStr = ' ', $type = STR_PAD_RIGHT)
	{
		return utf8_str_pad($input, $length, $padStr, $type);
	}

	/**
	 * UTF-8 aware alternative to str_split()
	 *
	 * Convert a string to an array.
	 *
	 * @param   string   $str        UTF-8 encoded string to process
	 * @param   integer  $split_len  Number to characters to split string by
	 *
	 * @return  array
	 *
	 * @link    https://secure.php.net/str_split
	 * @since   1.3.0
	 */
	public static function str_split($str, $split_len = 1)
	{
		return utf8_str_split($str, $split_len);
	}

	/**
	 * UTF-8/LOCALE aware alternative to strcasecmp()
	 *
	 * A case insensitive string comparison.
	 *
	 * @param   string  $str1    string 1 to compare
	 * @param   string  $str2    string 2 to compare
	 * @param   mixed   $locale  The locale used by strcoll or false to use classical comparison
	 *
	 * @return  integer   < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 *
	 * @link    https://secure.php.net/strcasecmp
	 * @link    https://secure.php.net/strcoll
	 * @link    https://secure.php.net/setlocale
	 * @since   1.3.0
	 */
	public static function strcasecmp($str1, $str2, $locale = false)
	{
		if ($locale)
		{
			// Get current locale
			$locale0 = setlocale(LC_COLLATE, 0);

			if (!$locale = setlocale(LC_COLLATE, $locale))
			{
				$locale = $locale0;
			}

			// See if we have successfully set locale to UTF-8
			if (!stristr($locale, 'UTF-8') && stristr($locale, '_') && preg_match('~\.(\d+)$~', $locale, $m))
			{
				$encoding = 'CP' . $m[1];
			}
			elseif (stristr($locale, 'UTF-8') || stristr($locale, 'utf8'))
			{
				$encoding = 'UTF-8';
			}
			else
			{
				$encoding = 'nonrecodable';
			}

			// If we successfully set encoding it to utf-8 or encoding is sth weird don't recode
			if ($encoding == 'UTF-8' || $encoding == 'nonrecodable')
			{
				return strcoll(utf8_strtolower($str1), utf8_strtolower($str2));
			}

			return strcoll(
				static::transcode(utf8_strtolower($str1), 'UTF-8', $encoding),
				static::transcode(utf8_strtolower($str2), 'UTF-8', $encoding)
			);
		}

		return utf8_strcasecmp($str1, $str2);
	}

	/**
	 * UTF-8/LOCALE aware alternative to strcmp()
	 *
	 * A case sensitive string comparison.
	 *
	 * @param   string  $str1    string 1 to compare
	 * @param   string  $str2    string 2 to compare
	 * @param   mixed   $locale  The locale used by strcoll or false to use classical comparison
	 *
	 * @return  integer  < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 *
	 * @link    https://secure.php.net/strcmp
	 * @link    https://secure.php.net/strcoll
	 * @link    https://secure.php.net/setlocale
	 * @since   1.3.0
	 */
	public static function strcmp($str1, $str2, $locale = false)
	{
		if ($locale)
		{
			// Get current locale
			$locale0 = setlocale(LC_COLLATE, 0);

			if (!$locale = setlocale(LC_COLLATE, $locale))
			{
				$locale = $locale0;
			}

			// See if we have successfully set locale to UTF-8
			if (!stristr($locale, 'UTF-8') && stristr($locale, '_') && preg_match('~\.(\d+)$~', $locale, $m))
			{
				$encoding = 'CP' . $m[1];
			}
			elseif (stristr($locale, 'UTF-8') || stristr($locale, 'utf8'))
			{
				$encoding = 'UTF-8';
			}
			else
			{
				$encoding = 'nonrecodable';
			}

			// If we successfully set encoding it to utf-8 or encoding is sth weird don't recode
			if ($encoding == 'UTF-8' || $encoding == 'nonrecodable')
			{
				return strcoll($str1, $str2);
			}

			return strcoll(static::transcode($str1, 'UTF-8', $encoding), static::transcode($str2, 'UTF-8', $encoding));
		}

		return strcmp($str1, $str2);
	}

	/**
	 * UTF-8 aware alternative to strcspn()
	 *
	 * Find length of initial segment not matching mask.
	 *
	 * @param   string   $str     The string to process
	 * @param   string   $mask    The mask
	 * @param   integer  $start   Optional starting character position (in characters)
	 * @param   integer  $length  Optional length
	 *
	 * @return  integer  The length of the initial segment of str1 which does not contain any of the characters in str2
	 *
	 * @link    https://secure.php.net/strcspn
	 * @since   1.3.0
	 */
	public static function strcspn($str, $mask, $start = null, $length = null)
	{
		if ($start === false && $length === false)
		{
			return utf8_strcspn($str, $mask);
		}

		if ($length === false)
		{
			return utf8_strcspn($str, $mask, $start);
		}

		return utf8_strcspn($str, $mask, $start, $length);
	}

	/**
	 * UTF-8 aware alternative to stristr()
	 *
	 * Returns all of haystack from the first occurrence of needle to the end. Needle and haystack are examined in a case-insensitive manner to
	 * find the first occurrence of a string using case insensitive comparison.
	 *
	 * @param   string  $str     The haystack
	 * @param   string  $search  The needle
	 *
	 * @return string the sub string
	 *
	 * @link    https://secure.php.net/stristr
	 * @since   1.3.0
	 */
	public static function stristr($str, $search)
	{
		return utf8_stristr($str, $search);
	}

	/**
	 * UTF-8 aware alternative to strrev()
	 *
	 * Reverse a string.
	 *
	 * @param   string  $str  String to be reversed
	 *
	 * @return  string   The string in reverse character order
	 *
	 * @link    https://secure.php.net/strrev
	 * @since   1.3.0
	 */
	public static function strrev($str)
	{
		return utf8_strrev($str);
	}

	/**
	 * UTF-8 aware alternative to strspn()
	 *
	 * Find length of initial segment matching mask.
	 *
	 * @param   string   $str     The haystack
	 * @param   string   $mask    The mask
	 * @param   integer  $start   Start optional
	 * @param   integer  $length  Length optional
	 *
	 * @return  integer
	 *
	 * @link    https://secure.php.net/strspn
	 * @since   1.3.0
	 */
	public static function strspn($str, $mask, $start = null, $length = null)
	{
		if ($start === null && $length === null)
		{
			return utf8_strspn($str, $mask);
		}

		if ($length === null)
		{
			return utf8_strspn($str, $mask, $start);
		}

		return utf8_strspn($str, $mask, $start, $length);
	}

	/**
	 * UTF-8 aware alternative to substr_replace()
	 *
	 * Replace text within a portion of a string.
	 *
	 * @param   string   $str     The haystack
	 * @param   string   $repl    The replacement string
	 * @param   integer  $start   Start
	 * @param   integer  $length  Length (optional)
	 *
	 * @return  string
	 *
	 * @link    https://secure.php.net/substr_replace
	 * @since   1.3.0
	 */
	public static function substr_replace($str, $repl, $start, $length = null)
	{
		// Loaded by library loader
		if ($length === false)
		{
			return utf8_substr_replace($str, $repl, $start);
		}

		return utf8_substr_replace($str, $repl, $start, $length);
	}

	/**
	 * UTF-8 aware replacement for ltrim()
	 *
	 * Strip whitespace (or other characters) from the beginning of a string. You only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise ltrim will work normally on a UTF-8 string.
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @link    https://secure.php.net/ltrim
	 * @since   1.3.0
	 */
	public static function ltrim($str, $charlist = false)
	{
		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		if ($charlist === false)
		{
			return utf8_ltrim($str);
		}

		return utf8_ltrim($str, $charlist);
	}

	/**
	 * UTF-8 aware replacement for rtrim()
	 *
	 * Strip whitespace (or other characters) from the end of a string. You only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise rtrim will work normally on a UTF-8 string.
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @link    https://secure.php.net/rtrim
	 * @since   1.3.0
	 */
	public static function rtrim($str, $charlist = false)
	{
		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		if ($charlist === false)
		{
			return utf8_rtrim($str);
		}

		return utf8_rtrim($str, $charlist);
	}

	/**
	 * UTF-8 aware replacement for trim()
	 *
	 * Strip whitespace (or other characters) from the beginning and end of a string. You only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise trim will work normally on a UTF-8 string
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @link    https://secure.php.net/trim
	 * @since   1.3.0
	 */
	public static function trim($str, $charlist = false)
	{
		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		if ($charlist === false)
		{
			return utf8_trim($str);
		}

		return utf8_trim($str, $charlist);
	}

	/**
	 * UTF-8 aware alternative to ucfirst()
	 *
	 * Make a string's first character uppercase or all words' first character uppercase.
	 *
	 * @param   string  $str           String to be processed
	 * @param   string  $delimiter     The words delimiter (null means do not split the string)
	 * @param   string  $newDelimiter  The new words delimiter (null means equal to $delimiter)
	 *
	 * @return  string  If $delimiter is null, return the string with first character as upper case (if applicable)
	 *                  else consider the string of words separated by the delimiter, apply the ucfirst to each words
	 *                  and return the string with the new delimiter
	 *
	 * @link    https://secure.php.net/ucfirst
	 * @since   1.3.0
	 */
	public static function ucfirst($str, $delimiter = null, $newDelimiter = null)
	{
		if ($delimiter === null)
		{
			return utf8_ucfirst($str);
		}

		if ($newDelimiter === null)
		{
			$newDelimiter = $delimiter;
		}

		return implode($newDelimiter, array_map('utf8_ucfirst', explode($delimiter, $str)));
	}

	/**
	 * UTF-8 aware alternative to ucwords()
	 *
	 * Uppercase the first character of each word in a string.
	 *
	 * @param   string  $str  String to be processed
	 *
	 * @return  string  String with first char of each word uppercase
	 *
	 * @link    https://secure.php.net/ucwords
	 * @since   1.3.0
	 */
	public static function ucwords($str)
	{
		return utf8_ucwords($str);
	}

	/**
	 * Transcode a string.
	 *
	 * @param   string  $source         The string to transcode.
	 * @param   string  $from_encoding  The source encoding.
	 * @param   string  $to_encoding    The target encoding.
	 *
	 * @return  mixed  The transcoded string, or null if the source was not a string.
	 *
	 * @link    https://bugs.php.net/bug.php?id=48147
	 *
	 * @since   1.3.0
	 */
	public static function transcode($source, $from_encoding, $to_encoding)
	{
		if (is_string($source))
		{
			switch (ICONV_IMPL)
			{
				case 'glibc':
					return @iconv($from_encoding, $to_encoding . '//TRANSLIT,IGNORE', $source);

				case 'libiconv':
				default:
					return iconv($from_encoding, $to_encoding . '//IGNORE//TRANSLIT', $source);
			}
		}

		return null;
	}

	/**
	 * Tests a string as to whether it's valid UTF-8 and supported by the Unicode standard.
	 *
	 * Note: this function has been modified to simple return true or false.
	 *
	 * @param   string  $str  UTF-8 encoded string.
	 *
	 * @return  boolean  true if valid
	 *
	 * @author  <hsivonen@iki.fi>
	 * @link    https://hsivonen.fi/php-utf8/
	 * @see     compliant
	 * @since   1.3.0
	 */
	public static function valid($str)
	{
		return utf8_is_valid($str);
	}

	/**
	 * Tests whether a string complies as UTF-8.
	 *
	 * This will be much faster than StringHelper::valid() but will pass five and six octet UTF-8 sequences, which are not supported by Unicode and
	 * so cannot be displayed correctly in a browser. In other words it is not as strict as StringHelper::valid() but it's faster. If you use it to
	 * validate user input, you place yourself at the risk that attackers will be able to inject 5 and 6 byte sequences (which may or may not be a
	 * significant risk, depending on what you are are doing).
	 *
	 * @param   string  $str  UTF-8 string to check
	 *
	 * @return  boolean  TRUE if string is valid UTF-8
	 *
	 * @see     StringHelper::valid
	 * @link    https://secure.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
	 * @since   1.3.0
	 */
	public static function compliant($str)
	{
		return utf8_compliant($str);
	}

	/**
	 * Converts Unicode sequences to UTF-8 string.
	 *
	 * @param   string  $str  Unicode string to convert
	 *
	 * @return  string  UTF-8 string
	 *
	 * @since   1.3.0
	 */
	public static function unicode_to_utf8($str)
	{
		if (extension_loaded('mbstring'))
		{
			return preg_replace_callback(
				'/\\\\u([0-9a-fA-F]{4})/',
				function ($match)
				{
					return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
				},
				$str
			);
		}

		return $str;
	}

	/**
	 * Converts Unicode sequences to UTF-16 string.
	 *
	 * @param   string  $str  Unicode string to convert
	 *
	 * @return  string  UTF-16 string
	 *
	 * @since   1.3.0
	 */
	public static function unicode_to_utf16($str)
	{
		if (extension_loaded('mbstring'))
		{
			return preg_replace_callback(
				'/\\\\u([0-9a-fA-F]{4})/',
				function ($match)
				{
					return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
				},
				$str
			);
		}

		return $str;
	}
}
