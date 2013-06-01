<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// PHP mbstring and iconv local configuration

// Check if mbstring extension is loaded and attempt to load it if not present except for windows
if (extension_loaded('mbstring'))
{
	// Make sure to suppress the output in case ini_set is disabled
	@ini_set('mbstring.internal_encoding', 'UTF-8');
	@ini_set('mbstring.http_input', 'UTF-8');
	@ini_set('mbstring.http_output', 'UTF-8');
}

// Same for iconv
if (function_exists('iconv'))
{
	// These are settings that can be set inside code
	iconv_set_encoding("internal_encoding", "UTF-8");
	iconv_set_encoding("input_encoding", "UTF-8");
	iconv_set_encoding("output_encoding", "UTF-8");
}

/**
 * Include the utf8 package
 */
jimport('phputf8.utf8');
jimport('phputf8.strcasecmp');

/**
 * String handling class for utf-8 data
 * Wraps the phputf8 library
 * All functions assume the validity of utf-8 strings.
 *
 * @package     Joomla.Platform
 * @subpackage  String
 * @since       11.1
 */
abstract class JString
{
	/**
	 * Increment styles.
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected static $incrementStyles = array(
		'dash' => array(
			'#-(\d+)$#',
			'-%d'
		),
		'default' => array(
			array('#\((\d+)\)$#', '#\(\d+\)$#'),
			array(' (%d)', '(%d)'),
		),
	);

	/**
	 * Split a string in camel case format
	 *
	 * "FooBarABCDef"            becomes  array("Foo", "Bar", "ABC", "Def");
	 * "JFooBar"                 becomes  array("J", "Foo", "Bar");
	 * "J001FooBar002"           becomes  array("J001", "Foo", "Bar002");
	 * "abcDef"                  becomes  array("abc", "Def");
	 * "abc_defGhi_Jkl"          becomes  array("abc_def", "Ghi_Jkl");
	 * "ThisIsA_NASAAstronaut"   becomes  array("This", "Is", "A_NASA", "Astronaut")),
	 * "JohnFitzgerald_Kennedy"  becomes  array("John", "Fitzgerald_Kennedy")),
	 *
	 * @param   string  $string  The source string.
	 *
	 * @return  array   The splitted string.
	 *
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use JStringNormalise::fromCamelCase()
	 * @since   11.3
	 */
	public static function splitCamelCase($string)
	{
		JLog::add('JString::splitCamelCase has been deprecated. Use JStringNormalise::fromCamelCase.', JLog::WARNING, 'deprecated');

		return JStringNormalise::fromCamelCase($string, true);
	}

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
	 * @since   11.3
	 */
	public static function increment($string, $style = 'default', $n = 0)
	{
		$styleSpec = isset(self::$incrementStyles[$style]) ? self::$incrementStyles[$style] : self::$incrementStyles['default'];

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
	 * UTF-8 aware alternative to strpos.
	 *
	 * Find position of first occurrence of a string.
	 *
	 * @param   string   $str     String being examined
	 * @param   string   $search  String being searched for
	 * @param   integer  $offset  Optional, specifies the position from which the search should be performed
	 *
	 * @return  mixed  Number of characters before the first match or FALSE on failure
	 *
	 * @see     http://www.php.net/strpos
	 * @since   11.1
	 */
	public static function strpos($str, $search, $offset = false)
	{
		if ($offset === false)
		{
			return utf8_strpos($str, $search);
		}
		else
		{
			return utf8_strpos($str, $search, $offset);
		}
	}

	/**
	 * UTF-8 aware alternative to strrpos
	 * Finds position of last occurrence of a string
	 *
	 * @param   string   $str     String being examined.
	 * @param   string   $search  String being searched for.
	 * @param   integer  $offset  Offset from the left of the string.
	 *
	 * @return  mixed  Number of characters before the last match or false on failure
	 *
	 * @see     http://www.php.net/strrpos
	 * @since   11.1
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		return utf8_strrpos($str, $search, $offset);
	}

	/**
	 * UTF-8 aware alternative to substr
	 * Return part of a string given character offset (and optionally length)
	 *
	 * @param   string   $str     String being processed
	 * @param   integer  $offset  Number of UTF-8 characters offset (from left)
	 * @param   integer  $length  Optional length in UTF-8 characters from offset
	 *
	 * @return  mixed string or FALSE if failure
	 *
	 * @see     http://www.php.net/substr
	 * @since   11.1
	 */
	public static function substr($str, $offset, $length = false)
	{
		if ($length === false)
		{
			return utf8_substr($str, $offset);
		}
		else
		{
			return utf8_substr($str, $offset, $length);
		}
	}

	/**
	 * UTF-8 aware alternative to strtlower
	 *
	 * Make a string lowercase
	 * Note: The concept of a characters "case" only exists is some alphabets
	 * such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	 * not exist in the Chinese alphabet, for example. See Unicode Standard
	 * Annex #21: Case Mappings
	 *
	 * @param   string  $str  String being processed
	 *
	 * @return  mixed  Either string in lowercase or FALSE is UTF-8 invalid
	 *
	 * @see http://www.php.net/strtolower
	 * @since   11.1
	 */
	public static function strtolower($str)
	{
		return utf8_strtolower($str);
	}

	/**
	 * UTF-8 aware alternative to strtoupper
	 * Make a string uppercase
	 * Note: The concept of a characters "case" only exists is some alphabets
	 * such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	 * not exist in the Chinese alphabet, for example. See Unicode Standard
	 * Annex #21: Case Mappings
	 *
	 * @param   string  $str  String being processed
	 *
	 * @return  mixed  Either string in uppercase or FALSE is UTF-8 invalid
	 *
	 * @see     http://www.php.net/strtoupper
	 * @since   11.1
	 */
	public static function strtoupper($str)
	{
		return utf8_strtoupper($str);
	}

	/**
	 * UTF-8 aware alternative to strlen.
	 *
	 * Returns the number of characters in the string (NOT THE NUMBER OF BYTES),
	 *
	 * @param   string  $str  UTF-8 string.
	 *
	 * @return  integer  Number of UTF-8 characters in string.
	 *
	 * @see http://www.php.net/strlen
	 * @since   11.1
	 */
	public static function strlen($str)
	{
		return utf8_strlen($str);
	}

	/**
	 * UTF-8 aware alternative to str_ireplace
	 * Case-insensitive version of str_replace
	 *
	 * @param   string   $search   String to search
	 * @param   string   $replace  Existing string to replace
	 * @param   string   $str      New string to replace with
	 * @param   integer  $count    Optional count value to be passed by referene
	 *
	 * @return  string  UTF-8 String
	 *
	 * @see     http://www.php.net/str_ireplace
	 * @since   11.1
	 */
	public static function str_ireplace($search, $replace, $str, $count = null)
	{
		jimport('phputf8.str_ireplace');

		if ($count === false)
		{
			return utf8_ireplace($search, $replace, $str);
		}
		else
		{
			return utf8_ireplace($search, $replace, $str, $count);
		}
	}

	/**
	 * UTF-8 aware alternative to str_split
	 * Convert a string to an array
	 *
	 * @param   string   $str        UTF-8 encoded string to process
	 * @param   integer  $split_len  Number to characters to split string by
	 *
	 * @return  array
	 *
	 * @see     http://www.php.net/str_split
	 * @since   11.1
	 */
	public static function str_split($str, $split_len = 1)
	{
		jimport('phputf8.str_split');

		return utf8_str_split($str, $split_len);
	}

	/**
	 * UTF-8/LOCALE aware alternative to strcasecmp
	 * A case insensitive string comparison
	 *
	 * @param   string  $str1    string 1 to compare
	 * @param   string  $str2    string 2 to compare
	 * @param   mixed   $locale  The locale used by strcoll or false to use classical comparison
	 *
	 * @return  integer   < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 *
	 * @see     http://www.php.net/strcasecmp
	 * @see     http://www.php.net/strcoll
	 * @see     http://www.php.net/setlocale
	 * @since   11.1
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
			else
			{
				return strcoll(
					self::transcode(utf8_strtolower($str1), 'UTF-8', $encoding),
					self::transcode(utf8_strtolower($str2), 'UTF-8', $encoding)
				);
			}
		}
		else
		{
			return utf8_strcasecmp($str1, $str2);
		}
	}

	/**
	 * UTF-8/LOCALE aware alternative to strcmp
	 * A case sensitive string comparison
	 *
	 * @param   string  $str1    string 1 to compare
	 * @param   string  $str2    string 2 to compare
	 * @param   mixed   $locale  The locale used by strcoll or false to use classical comparison
	 *
	 * @return  integer  < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 *
	 * @see     http://www.php.net/strcmp
	 * @see     http://www.php.net/strcoll
	 * @see     http://www.php.net/setlocale
	 * @since   11.1
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
			else
			{
				return strcoll(self::transcode($str1, 'UTF-8', $encoding), self::transcode($str2, 'UTF-8', $encoding));
			}
		}
		else
		{
			return strcmp($str1, $str2);
		}
	}

	/**
	 * UTF-8 aware alternative to strcspn
	 * Find length of initial segment not matching mask
	 *
	 * @param   string   $str     The string to process
	 * @param   string   $mask    The mask
	 * @param   integer  $start   Optional starting character position (in characters)
	 * @param   integer  $length  Optional length
	 *
	 * @return  integer  The length of the initial segment of str1 which does not contain any of the characters in str2
	 *
	 * @see     http://www.php.net/strcspn
	 * @since   11.1
	 */
	public static function strcspn($str, $mask, $start = null, $length = null)
	{
		jimport('phputf8.strcspn');

		if ($start === false && $length === false)
		{
			return utf8_strcspn($str, $mask);
		}
		elseif ($length === false)
		{
			return utf8_strcspn($str, $mask, $start);
		}
		else
		{
			return utf8_strcspn($str, $mask, $start, $length);
		}
	}

	/**
	 * UTF-8 aware alternative to stristr
	 * Returns all of haystack from the first occurrence of needle to the end.
	 * needle and haystack are examined in a case-insensitive manner
	 * Find first occurrence of a string using case insensitive comparison
	 *
	 * @param   string  $str     The haystack
	 * @param   string  $search  The needle
	 *
	 * @return string the sub string
	 *
	 * @see     http://www.php.net/stristr
	 * @since   11.1
	 */
	public static function stristr($str, $search)
	{
		jimport('phputf8.stristr');

		return utf8_stristr($str, $search);
	}

	/**
	 * UTF-8 aware alternative to strrev
	 * Reverse a string
	 *
	 * @param   string  $str  String to be reversed
	 *
	 * @return  string   The string in reverse character order
	 *
	 * @see     http://www.php.net/strrev
	 * @since   11.1
	 */
	public static function strrev($str)
	{
		jimport('phputf8.strrev');

		return utf8_strrev($str);
	}

	/**
	 * UTF-8 aware alternative to strspn
	 * Find length of initial segment matching mask
	 *
	 * @param   string   $str     The haystack
	 * @param   string   $mask    The mask
	 * @param   integer  $start   Start optional
	 * @param   integer  $length  Length optional
	 *
	 * @return  integer
	 *
	 * @see     http://www.php.net/strspn
	 * @since   11.1
	 */
	public static function strspn($str, $mask, $start = null, $length = null)
	{
		jimport('phputf8.strspn');

		if ($start === null && $length === null)
		{
			return utf8_strspn($str, $mask);
		}
		elseif ($length === null)
		{
			return utf8_strspn($str, $mask, $start);
		}
		else
		{
			return utf8_strspn($str, $mask, $start, $length);
		}
	}

	/**
	 * UTF-8 aware substr_replace
	 * Replace text within a portion of a string
	 *
	 * @param   string   $str     The haystack
	 * @param   string   $repl    The replacement string
	 * @param   integer  $start   Start
	 * @param   integer  $length  Length (optional)
	 *
	 * @return  string
	 *
	 * @see     http://www.php.net/substr_replace
	 * @since   11.1
	 */
	public static function substr_replace($str, $repl, $start, $length = null)
	{
		// Loaded by library loader
		if ($length === false)
		{
			return utf8_substr_replace($str, $repl, $start);
		}
		else
		{
			return utf8_substr_replace($str, $repl, $start, $length);
		}
	}

	/**
	 * UTF-8 aware replacement for ltrim()
	 *
	 * Strip whitespace (or other characters) from the beginning of a string
	 * You only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise ltrim will
	 * work normally on a UTF-8 string
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @see     http://www.php.net/ltrim
	 * @since   11.1
	 */
	public static function ltrim($str, $charlist = false)
	{
		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		jimport('phputf8.trim');

		if ($charlist === false)
		{
			return utf8_ltrim($str);
		}
		else
		{
			return utf8_ltrim($str, $charlist);
		}
	}

	/**
	 * UTF-8 aware replacement for rtrim()
	 * Strip whitespace (or other characters) from the end of a string
	 * You only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise rtrim will
	 * work normally on a UTF-8 string
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @see     http://www.php.net/rtrim
	 * @since   11.1
	 */
	public static function rtrim($str, $charlist = false)
	{
		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		jimport('phputf8.trim');

		if ($charlist === false)
		{
			return utf8_rtrim($str);
		}
		else
		{
			return utf8_rtrim($str, $charlist);
		}
	}

	/**
	 * UTF-8 aware replacement for trim()
	 * Strip whitespace (or other characters) from the beginning and end of a string
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise trim will
	 * work normally on a UTF-8 string
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @see     http://www.php.net/trim
	 * @since   11.1
	 */
	public static function trim($str, $charlist = false)
	{
		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		jimport('phputf8.trim');

		if ($charlist === false)
		{
			return utf8_trim($str);
		}
		else
		{
			return utf8_trim($str, $charlist);
		}
	}

	/**
	 * UTF-8 aware alternative to ucfirst
	 * Make a string's first character uppercase or all words' first character uppercase
	 *
	 * @param   string  $str           String to be processed
	 * @param   string  $delimiter     The words delimiter (null means do not split the string)
	 * @param   string  $newDelimiter  The new words delimiter (null means equal to $delimiter)
	 *
	 * @return  string  If $delimiter is null, return the string with first character as upper case (if applicable)
	 *                  else consider the string of words separated by the delimiter, apply the ucfirst to each words
	 *                  and return the string with the new delimiter
	 *
	 * @see     http://www.php.net/ucfirst
	 * @since   11.1
	 */
	public static function ucfirst($str, $delimiter = null, $newDelimiter = null)
	{
		jimport('phputf8.ucfirst');

		if ($delimiter === null)
		{
			return utf8_ucfirst($str);
		}
		else
		{
			if ($newDelimiter === null)
			{
				$newDelimiter = $delimiter;
			}
			return implode($newDelimiter, array_map('utf8_ucfirst', explode($delimiter, $str)));
		}
	}

	/**
	 * UTF-8 aware alternative to ucwords
	 * Uppercase the first character of each word in a string
	 *
	 * @param   string  $str  String to be processed
	 *
	 * @return  string  String with first char of each word uppercase
	 *
	 * @see     http://www.php.net/ucwords
	 * @since   11.1
	 */
	public static function ucwords($str)
	{
		jimport('phputf8.ucwords');

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
	 * @since   11.1
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
	 * @see     http://hsivonen.iki.fi/php-utf8/
	 * @see     compliant
	 * @since   11.1
	 */
	public static function valid($str)
	{
		// Cached expected number of octets after the current octet
		// until the beginning of the next UTF8 character sequence
		$mState = 0;

		// Cached Unicode character
		$mUcs4 = 0;

		// Cached expected number of octets in the current sequence
		$mBytes = 1;

		$len = strlen($str);

		for ($i = 0; $i < $len; $i++)
		{
			$in = ord($str{$i});

			if ($mState == 0)
			{
				// When mState is zero we expect either a US-ASCII character or a
				// multi-octet sequence.
				if (0 == (0x80 & ($in)))
				{
					// US-ASCII, pass straight through.
					$mBytes = 1;
				}
				elseif (0xC0 == (0xE0 & ($in)))
				{
					// First octet of 2 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x1F) << 6;
					$mState = 1;
					$mBytes = 2;
				}
				elseif (0xE0 == (0xF0 & ($in)))
				{
					// First octet of 3 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x0F) << 12;
					$mState = 2;
					$mBytes = 3;
				}
				elseif (0xF0 == (0xF8 & ($in)))
				{
					// First octet of 4 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x07) << 18;
					$mState = 3;
					$mBytes = 4;
				}
				elseif (0xF8 == (0xFC & ($in)))
				{
					/* First octet of 5 octet sequence.
					 *
					 * This is illegal because the encoded codepoint must be either
					 * (a) not the shortest form or
					 * (b) outside the Unicode range of 0-0x10FFFF.
					 * Rather than trying to resynchronize, we will carry on until the end
					 * of the sequence and let the later error handling code catch it.
					 */
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x03) << 24;
					$mState = 4;
					$mBytes = 5;
				}
				elseif (0xFC == (0xFE & ($in)))
				{
					// First octet of 6 octet sequence, see comments for 5 octet sequence.
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 1) << 30;
					$mState = 5;
					$mBytes = 6;

				}
				else
				{
					/* Current octet is neither in the US-ASCII range nor a legal first
					 * octet of a multi-octet sequence.
					 */
					return false;
				}
			}
			else
			{
				// When mState is non-zero, we expect a continuation of the multi-octet
				// sequence
				if (0x80 == (0xC0 & ($in)))
				{
					// Legal continuation.
					$shift = ($mState - 1) * 6;
					$tmp = $in;
					$tmp = ($tmp & 0x0000003F) << $shift;
					$mUcs4 |= $tmp;

					/**
					 * End of the multi-octet sequence. mUcs4 now contains the final
					 * Unicode codepoint to be output
					 */
					if (0 == --$mState)
					{
						/*
						 * Check for illegal sequences and codepoints.
						 */
						// From Unicode 3.1, non-shortest form is illegal
						if (((2 == $mBytes) && ($mUcs4 < 0x0080)) || ((3 == $mBytes) && ($mUcs4 < 0x0800)) || ((4 == $mBytes) && ($mUcs4 < 0x10000))
							|| (4 < $mBytes)
							|| (($mUcs4 & 0xFFFFF800) == 0xD800) // From Unicode 3.2, surrogate characters are illegal
							|| ($mUcs4 > 0x10FFFF)) // Codepoints outside the Unicode range are illegal
						{
							return false;
						}

						// Initialize UTF8 cache.
						$mState = 0;
						$mUcs4 = 0;
						$mBytes = 1;
					}
				}
				else
				{
					/**
					 *((0xC0 & (*in) != 0x80) && (mState != 0))
					 * Incomplete multi-octet sequence.
					 */
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Tests whether a string complies as UTF-8. This will be much
	 * faster than utf8_is_valid but will pass five and six octet
	 * UTF-8 sequences, which are not supported by Unicode and
	 * so cannot be displayed correctly in a browser. In other words
	 * it is not as strict as utf8_is_valid but it's faster. If you use
	 * it to validate user input, you place yourself at the risk that
	 * attackers will be able to inject 5 and 6 byte sequences (which
	 * may or may not be a significant risk, depending on what you are
	 * are doing)
	 *
	 * @param   string  $str  UTF-8 string to check
	 *
	 * @return  boolean  TRUE if string is valid UTF-8
	 *
	 * @see     valid
	 * @see     http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
	 * @since   11.1
	 */
	public static function compliant($str)
	{
		if (strlen($str) == 0)
		{
			return true;
		}

		/*
		 * If even just the first character can be matched, when the /u
		 * modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
		 * invalid, nothing at all will match, even if the string contains
		 * some valid sequences
		 */
		return (preg_match('/^.{1}/us', $str, $ar) == 1);
	}

	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 *
	 * @param   string  $url  URL to parse
	 *
	 * @return  mixed  Associative array or false if badly formed URL.
	 *
	 * @see     http://us3.php.net/manual/en/function.parse-url.php
	 * @since   11.1
	 */
	public static function parse_url($url)
	{
		$result = false;

		// Build arrays of values we need to decode before parsing
		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]");

		// Create encoded URL with special URL characters decoded so it can be parsed
		// All other characters will be encoded
		$encodedURL = str_replace($entities, $replacements, urlencode($url));

		// Parse the encoded URL
		$encodedParts = parse_url($encodedURL);

		// Now, decode each value of the resulting array
		if ($encodedParts)
		{
			foreach ($encodedParts as $key => $value)
			{
				$result[$key] = urldecode(str_replace($replacements, $entities, $value));
			}
		}
		return $result;
	}
}
