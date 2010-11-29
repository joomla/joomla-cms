<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die;

/**
 * PHP mbstring and iconv local configuration
 */
// check if mbstring extension is loaded and attempt to load it if not present except for windows
if (extension_loaded('mbstring') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('mbstring.so')))) {
	//Make sure to surpress the output in case ini_set is disabled
	@ini_set('mbstring.internal_encoding', 'UTF-8');
	@ini_set('mbstring.http_input', 'UTF-8');
	@ini_set('mbstring.http_output', 'UTF-8');
}

// same for iconv
if (function_exists('iconv') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('iconv.so')))) {
	// these are settings that can be set inside code
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
 * @static
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
abstract class JString
{
	/**
	 * UTF-8 aware alternative to strpos
	 * Find position of first occurrence of a string
	 *
	 * @static
	 * @access public
	 * @param $str - string String being examined
	 * @param $search - string String being searced for
	 * @param $offset - int Optional, specifies the position from which the search should be performed
	 * @return mixed Number of characters before the first match or FALSE on failure
	 * @see http://www.php.net/strpos
	 */
	public static function strpos($str, $search, $offset = FALSE)
	{
		if ( $offset === FALSE ) {
			return utf8_strpos($str, $search);
		} else {
			return utf8_strpos($str, $search, $offset);
		}
	}

	/**
	 * UTF-8 aware alternative to strrpos
	 * Finds position of last occurrence of a string
	 *
	 * @static
	 * @access public
	 * @param $str - string String being examined
	 * @param $search - string String being searced for
	 * @return mixed Number of characters before the last match or FALSE on failure
	 * @see http://www.php.net/strrpos
	 */
	public static function strrpos($str, $search, $offset = false)
	{
		return utf8_strrpos($str, $search);
	}

	/**
	 * UTF-8 aware alternative to substr
	 * Return part of a string given character offset (and optionally length)
	 *
	 * @static
	 * @access public
	 * @param string
	 * @param integer number of UTF-8 characters offset (from left)
	 * @param integer (optional) length in UTF-8 characters from offset
	 * @return mixed string or FALSE if failure
	 * @see http://www.php.net/substr
	 */
	public static function substr($str, $offset, $length = FALSE)
	{
		if ($length === FALSE) {
			return utf8_substr($str, $offset);
		} else {
			return utf8_substr($str, $offset, $length);
		}
	}

	/**
	 * UTF-8 aware alternative to strtlower
	 * Make a string lowercase
	 * Note: The concept of a characters "case" only exists is some alphabets
	 * such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	 * not exist in the Chinese alphabet, for example. See Unicode Standard
	 * Annex #21: Case Mappings
	 *
	 * @access public
	 * @param string
	 * @return mixed either string in lowercase or FALSE is UTF-8 invalid
	 * @see http://www.php.net/strtolower
	 */
	public static function strtolower($str){
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
	 * @access public
	 * @param string
	 * @return mixed either string in uppercase or FALSE is UTF-8 invalid
	 * @see http://www.php.net/strtoupper
	 */
	public static function strtoupper($str){
		return utf8_strtoupper($str);
	}

	/**
	 * UTF-8 aware alternative to strlen
	 * Returns the number of characters in the string (NOT THE NUMBER OF BYTES),
	 *
	 * @access public
	 * @param string UTF-8 string
	 * @return int number of UTF-8 characters in string
	 * @see http://www.php.net/strlen
	 */
	public static function strlen($str){
		return utf8_strlen($str);
	}

	/**
	 * UTF-8 aware alternative to str_ireplace
	 * Case-insensitive version of str_replace
	 *
	 * @static
	 * @access public
	 * @param string string to search
	 * @param string existing string to replace
	 * @param string new string to replace with
	 * @param int optional count value to be passed by referene
	 * @see http://www.php.net/str_ireplace
	*/
	public static function str_ireplace($search, $replace, $str, $count = NULL)
	{
		jimport('phputf8.str_ireplace');
		if ( $count === FALSE ) {
			return utf8_ireplace($search, $replace, $str);
		} else {
			return utf8_ireplace($search, $replace, $str, $count);
		}
	}

	/**
	 * UTF-8 aware alternative to str_split
	 * Convert a string to an array
	 *
	 * @static
	 * @access public
	 * @param string UTF-8 encoded
	 * @param int number to characters to split string by
	 * @return array
	 * @see http://www.php.net/str_split
	*/
	public static function str_split($str, $split_len = 1)
	{
		jimport('phputf8.str_split');
		return utf8_str_split($str, $split_len);
	}

	/**
	 * UTF-8/LOCALE aware alternative to strcasecmp
	 * A case insensivite string comparison
	 *
	 * @static
	 * @access public
	 * @param string string 1 to compare
	 * @param string string 2 to compare
	 * @param mixed The locale used by strcoll or false to use classical comparison
	 * @return int < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 * @see http://www.php.net/strcasecmp
	 * @see http://www.php.net/strcoll
	 * @see http://www.php.net/setlocale
	 */
	public static function strcasecmp($str1, $str2, $locale = false)
	{
		if ($locale)
		{
			// get current locale
			$locale0 = setlocale(LC_COLLATE, 0);
			if (!$locale = setlocale(LC_COLLATE, $locale)) {
				$locale = $locale0;
			}

			// See if we have successfully set locale to UTF-8
			if(!stristr($locale, 'UTF-8') && stristr($locale, '_') && preg_match('~\.(\d+)$~', $locale, $m)) {
				$encoding = 'CP' . $m[1];
			}
			else if(stristr($locale, 'UTF-8')){
				$encoding = 'UTF-8';
			}
			else {
				$encoding = 'nonrecodable';
			}

			// if we sucesfuly set encoding it to utf-8 or encoding is sth weird don't recode
			if ($encoding == 'UTF-8' || $encoding == 'nonrecodable') {
				return strcoll(utf8_strtolower($str1), utf8_strtolower($str2));
			} else {
				return strcoll(self::transcode(utf8_strtolower($str1),'UTF-8', $encoding), self::transcode(utf8_strtolower($str2),'UTF-8', $encoding));
			}
		}
		else
		{
			return utf8_strcasecmp($str1, $str2);
		}
	}

	/**
	 * UTF-8/LOCALE aware alternative to strcmp
	 * A case sensivite string comparison
	 *
	 * @static
	 * @access public
	 * @param string string 1 to compare
	 * @param string string 2 to compare
	 * @param mixed The locale used by strcoll or false to use classical comparison
	 * @return int < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 * @see http://www.php.net/strcmp
	 * @see http://www.php.net/strcoll
	 * @see http://www.php.net/setlocale
	 */
	public static function strcmp($str1, $str2, $locale = false)
	{
		if ($locale)
		{
			// get current locale
			$locale0 = setlocale(LC_COLLATE, 0);
			if (!$locale = setlocale(LC_COLLATE, $locale)) {
				$locale = $locale0;
			}

			// See if we have successfully set locale to UTF-8
			if(!stristr($locale, 'UTF-8') && stristr($locale, '_') && preg_match('~\.(\d+)$~', $locale, $m)) {
				$encoding = 'CP' . $m[1];
			}
			else if(stristr($locale, 'UTF-8')){
				$encoding = 'UTF-8';
			}
			else {
				$encoding = 'nonrecodable';
			}

			// if we sucesfuly set encoding it to utf-8 or encoding is sth weird don't recode
			if ($encoding == 'UTF-8' || $encoding == 'nonrecodable') {
				return strcoll($str1, $str2);
			}
			else {
				return strcoll(self::transcode($str1,'UTF-8', $encoding), self::transcode($str2,'UTF-8', $encoding));
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
	 * @static
	 * @access public
	 * @param string
	 * @param string the mask
	 * @param int Optional starting character position (in characters)
	 * @param int Optional length
	 * @return int the length of the initial segment of str1 which does not contain any of the characters in str2
	 * @see http://www.php.net/strcspn
	*/
	public static function strcspn($str, $mask, $start = NULL, $length = NULL)
	{
		jimport('phputf8.strcspn');
		if ( $start === FALSE && $length === FALSE ) {
			return utf8_strcspn($str, $mask);
		} else if ( $length === FALSE ) {
			return utf8_strcspn($str, $mask, $start);
		} else {
			return utf8_strcspn($str, $mask, $start, $length);
		}
	}

	/**
	 * UTF-8 aware alternative to stristr
	 * Returns all of haystack from the first occurrence of needle to the end.
	 * needle and haystack are examined in a case-insensitive manner
	 * Find first occurrence of a string using case insensitive comparison
	 *
	 * @static
	 * @access public
	 * @param string the haystack
	 * @param string the needle
	 * @return string the sub string
	 * @see http://www.php.net/stristr
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
	 * @static
	 * @access public
	 * @param string String to be reversed
	 * @return string The string in reverse character order
	 * @see http://www.php.net/strrev
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
	 * @static
	 * @access public
	 * @param string the haystack
	 * @param string the mask
	 * @param int start optional
	 * @param int length optional
	 * @see http://www.php.net/strspn
	*/
	public static function strspn($str, $mask, $start = NULL, $length = NULL)
	{
		jimport('phputf8.strspn');
		if ( $start === NULL && $length === NULL ) {
			return utf8_strspn($str, $mask);
		} else if ( $length === NULL ) {
			return utf8_strspn($str, $mask, $start);
		} else {
			return utf8_strspn($str, $mask, $start, $length);
		}
	}

	/**
	 * UTF-8 aware substr_replace
	 * Replace text within a portion of a string
	 *
	 * @static
	 * @access public
	 * @param string the haystack
	 * @param string the replacement string
	 * @param int start
	 * @param int length (optional)
	 * @see http://www.php.net/substr_replace
	*/
	public static function substr_replace($str, $repl, $start, $length = NULL)
	{
		// loaded by library loader
		if ( $length === FALSE ) {
			return utf8_substr_replace($str, $repl, $start);
		} else {
			return utf8_substr_replace($str, $repl, $start, $length);
		}
	}

	/**
	 * UTF-8 aware replacement for ltrim()
	 * Strip whitespace (or other characters) from the beginning of a string
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise ltrim will
	 * work normally on a UTF-8 string
	 *
	 * @static
	 * @access public
	 * @param string the string to be trimmed
	 * @param string the optional charlist of additional characters to trim
	 * @return string the trimmed string
	 * @see http://www.php.net/ltrim
	*/
	public static function ltrim($str, $charlist = FALSE)
	{
		if (empty($charlist) && $charlist !== false) {
			return $str;
		}

		jimport('phputf8.trim');
		if ( $charlist === FALSE ) {
			return utf8_ltrim( $str );
		} else {
			return utf8_ltrim( $str, $charlist );
		}
	}

	/**
	 * UTF-8 aware replacement for rtrim()
	 * Strip whitespace (or other characters) from the end of a string
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise rtrim will
	 * work normally on a UTF-8 string
	 *
	 * @static
	 * @access public
	 * @param string the string to be trimmed
	 * @param string the optional charlist of additional characters to trim
	 * @return string the trimmed string
	 * @see http://www.php.net/rtrim
	*/
	public static function rtrim($str, $charlist = FALSE)
	{
		if (empty($charlist) && $charlist !== false) {
			return $str;
		}

		jimport('phputf8.trim');
		if ( $charlist === FALSE ) {
			return utf8_rtrim($str);
		} else {
			return utf8_rtrim( $str, $charlist );
		}
	}

	/**
	 * UTF-8 aware replacement for trim()
	 * Strip whitespace (or other characters) from the beginning and end of a string
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise trim will
	 * work normally on a UTF-8 string
	 *
	 * @static
	 * @access public
	 * @param string the string to be trimmed
	 * @param string the optional charlist of additional characters to trim
	 * @return string the trimmed string
	 * @see http://www.php.net/trim
	*/
	public static function trim($str, $charlist = FALSE)
	{
		if (empty($charlist) && $charlist !== false) {
			return $str;
		}

		jimport('phputf8.trim');
		if ( $charlist === FALSE ) {
			return utf8_trim( $str );
		} else {
			return utf8_trim( $str, $charlist );
		}
	}

	/**
	 * UTF-8 aware alternative to ucfirst
	 * Make a string's first character uppercase
	 *
	 * @static
	 * @access public
	 * @param string
	 * @return string with first character as upper case (if applicable)
	 * @see http://www.php.net/ucfirst
	*/
	public static function ucfirst($str)
	{
		jimport('phputf8.ucfirst');
		return utf8_ucfirst($str);
	}

	/**
	 * UTF-8 aware alternative to ucwords
	 * Uppercase the first character of each word in a string
	 *
	 * @static
	 * @access public
	 * @param string
	 * @return string with first char of each word uppercase
	 * @see http://www.php.net/ucwords
	*/
	public static function ucwords($str)
	{
		jimport('phputf8.ucwords');
		return utf8_ucwords($str);
	}

	/**
	 * Transcode a string.
	 *
	 * @static
	 * @param string $source The string to transcode.
	 * @param string $from_encoding The source encoding.
	 * @param string $to_encoding The target encoding.
	 * @return string Transcoded string
	 * @since 1.5
	 */
	public static function transcode($source, $from_encoding, $to_encoding)
	{
		if (is_string($source)) {
			/*
			 * "//TRANSLIT" is appendd to the $to_encoding to ensure that when iconv comes
			 * across a character that cannot be represented in the target charset, it can
			 * be approximated through one or several similarly looking characters.
			 */
			return iconv($from_encoding, $to_encoding.'//TRANSLIT', $source);
		}
	}

	/**
	 * Tests a string as to whether it's valid UTF-8 and supported by the
	 * Unicode standard
	 * Note: this function has been modified to simple return true or false
	 * @author <hsivonen@iki.fi>
	 * @param string UTF-8 encoded string
	 * @return boolean true if valid
	 * @since 1.6
	 * @see http://hsivonen.iki.fi/php-utf8/
	 * @see compliant
	 */
	public static function valid($str)
	{
		$mState = 0;	// cached expected number of octets after the current octet
						// until the beginning of the next UTF8 character sequence
		$mUcs4  = 0;	// cached Unicode character
		$mBytes = 1;	// cached expected number of octets in the current sequence

		$len = strlen($str);

		for ($i = 0; $i < $len; $i++)
		{
			$in = ord($str{$i});

			if ($mState == 0)
			{
				// When mState is zero we expect either a US-ASCII character or a
				// multi-octet sequence.
				if (0 == (0x80 & ($in))) {
					// US-ASCII, pass straight through.
					$mBytes = 1;
				} else if (0xC0 == (0xE0 & ($in))) {
					// First octet of 2 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x1F) << 6;
					$mState = 1;
					$mBytes = 2;
				} else if (0xE0 == (0xF0 & ($in))) {
					// First octet of 3 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x0F) << 12;
					$mState = 2;
					$mBytes = 3;
				} else if (0xF0 == (0xF8 & ($in))) {
					// First octet of 4 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x07) << 18;
					$mState = 3;
					$mBytes = 4;
				} else if (0xF8 == (0xFC & ($in))) {
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
				} else if (0xFC == (0xFE & ($in))) {
					// First octet of 6 octet sequence, see comments for 5 octet sequence.
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 1) << 30;
					$mState = 5;
					$mBytes = 6;

				} else {
					/* Current octet is neither in the US-ASCII range nor a legal first
					 * octet of a multi-octet sequence.
					 */
					return FALSE;
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
						if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
							((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
							((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
							(4 < $mBytes) ||
							// From Unicode 3.2, surrogate characters are illegal
							(($mUcs4 & 0xFFFFF800) == 0xD800) ||
							// Codepoints outside the Unicode range are illegal
							($mUcs4 > 0x10FFFF)) {
								return FALSE;
							}

						// Initialize UTF8 cache.
						$mState = 0;
						$mUcs4  = 0;
						$mBytes = 1;
					}
				}
				else
				{
					/**
					 *((0xC0 & (*in) != 0x80) && (mState != 0))
					 * Incomplete multi-octet sequence.
					 */
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	/**
	 * Tests whether a string complies as UTF-8. This will be much
	 * faster than utf8_is_valid but will pass five and six octet
	 * UTF-8 sequences, which are not supported by Unicode and
	 * so cannot be displayed correctly in a browser. In other words
	 * it is not as strict as utf8_is_valid but it's faster. If you use
	 * is to validate user input, you place yourself at the risk that
	 * attackers will be able to inject 5 and 6 byte sequences (which
	 * may or may not be a significant risk, depending on what you are
	 * are doing)
	 * @see valid
	 * @see http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
	 * @param string UTF-8 string to check
	 * @return boolean TRUE if string is valid UTF-8
	 * @since 1.6
	 */
	public static function compliant($str)
	{
		if (strlen($str) == 0) {
			return TRUE;
		}
		// If even just the first character can be matched, when the /u
		// modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
		// invalid, nothing at all will match, even if the string contains
		// some valid sequences
		return (preg_match('/^.{1}/us',$str,$ar) == 1);
	}
}
