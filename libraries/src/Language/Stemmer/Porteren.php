<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @copyright  Copyright (C) 2005 Richard Heyes (http://www.phpguru.org/). All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language\Stemmer;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\LanguageStemmer;

/**
 * Porter English stemmer class.
 *
 * This class was adapted from one written by Richard Heyes.
 * See copyright and link information above.
 *
 * @since       3.0.0
 * @deprecated  4.0 Use wamania/php-stemmer
 */
class Porteren extends LanguageStemmer
{
	/**
	 * Regex for matching a consonant.
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private static $_regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

	/**
	 * Regex for matching a vowel
	 * @var    string
	 * @since  3.0.0
	 */
	private static $_regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

	/**
	 * Method to stem a token and return the root.
	 *
	 * @param   string  $token  The token to stem.
	 * @param   string  $lang   The language of the token.
	 *
	 * @return  string  The root token.
	 *
	 * @since   3.0.0
	 */
	public function stem($token, $lang)
	{
		// Check if the token is long enough to merit stemming.
		if (strlen($token) <= 2)
		{
			return $token;
		}

		// Check if the language is English or All.
		if ($lang !== 'en')
		{
			return $token;
		}

		// Stem the token if it is not in the cache.
		if (!isset($this->cache[$lang][$token]))
		{
			// Stem the token.
			$result = $token;
			$result = self::_step1ab($result);
			$result = self::_step1c($result);
			$result = self::_step2($result);
			$result = self::_step3($result);
			$result = self::_step4($result);
			$result = self::_step5($result);

			// Add the token to the cache.
			$this->cache[$lang][$token] = $result;
		}

		return $this->cache[$lang][$token];
	}

	/**
	 * Step 1
	 *
	 * @param   string  $word  The token to stem.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private static function _step1ab($word)
	{
		// Part a
		if (substr($word, -1) == 's')
		{
				self::_replace($word, 'sses', 'ss')
			|| self::_replace($word, 'ies', 'i')
			|| self::_replace($word, 'ss', 'ss')
			|| self::_replace($word, 's', '');
		}

		// Part b
		if (substr($word, -2, 1) != 'e' || !self::_replace($word, 'eed', 'ee', 0))
		{
			// First rule
			$v = self::$_regex_vowel;

			// Check ing and ed
			// Note use of && and OR, for precedence reasons
			if (preg_match("#$v+#", substr($word, 0, -3)) && self::_replace($word, 'ing', '')
				|| preg_match("#$v+#", substr($word, 0, -2)) && self::_replace($word, 'ed', ''))
			{
				// If one of above two test successful
				if (!self::_replace($word, 'at', 'ate') && !self::_replace($word, 'bl', 'ble') && !self::_replace($word, 'iz', 'ize'))
				{
					// Double consonant ending
					if (self::_doubleConsonant($word) && substr($word, -2) != 'll' && substr($word, -2) != 'ss' && substr($word, -2) != 'zz')
					{
						$word = substr($word, 0, -1);
					}
					elseif (self::_m($word) == 1 && self::_cvc($word))
					{
						$word .= 'e';
					}
				}
			}
		}

		return $word;
	}

	/**
	 * Step 1c
	 *
	 * @param   string  $word  The token to stem.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private static function _step1c($word)
	{
		$v = self::$_regex_vowel;

		if (substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1)))
		{
			self::_replace($word, 'y', 'i');
		}

		return $word;
	}

	/**
	 * Step 2
	 *
	 * @param   string  $word  The token to stem.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private static function _step2($word)
	{
		switch (substr($word, -2, 1))
		{
			case 'a':
					self::_replace($word, 'ational', 'ate', 0)
				|| self::_replace($word, 'tional', 'tion', 0);
				break;
			case 'c':
					self::_replace($word, 'enci', 'ence', 0)
				|| self::_replace($word, 'anci', 'ance', 0);
				break;
			case 'e':
				self::_replace($word, 'izer', 'ize', 0);
				break;
			case 'g':
				self::_replace($word, 'logi', 'log', 0);
				break;
			case 'l':
					self::_replace($word, 'entli', 'ent', 0)
				|| self::_replace($word, 'ousli', 'ous', 0)
				|| self::_replace($word, 'alli', 'al', 0)
				|| self::_replace($word, 'bli', 'ble', 0)
				|| self::_replace($word, 'eli', 'e', 0);
				break;
			case 'o':
					self::_replace($word, 'ization', 'ize', 0)
				|| self::_replace($word, 'ation', 'ate', 0)
				|| self::_replace($word, 'ator', 'ate', 0);
				break;
			case 's':
					self::_replace($word, 'iveness', 'ive', 0)
				|| self::_replace($word, 'fulness', 'ful', 0)
				|| self::_replace($word, 'ousness', 'ous', 0)
				|| self::_replace($word, 'alism', 'al', 0);
				break;
			case 't':
					self::_replace($word, 'biliti', 'ble', 0)
				|| self::_replace($word, 'aliti', 'al', 0)
				|| self::_replace($word, 'iviti', 'ive', 0);
				break;
		}

		return $word;
	}

	/**
	 * Step 3
	 *
	 * @param   string  $word  The token to stem.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private static function _step3($word)
	{
		switch (substr($word, -2, 1))
		{
			case 'a':
				self::_replace($word, 'ical', 'ic', 0);
				break;
			case 's':
				self::_replace($word, 'ness', '', 0);
				break;
			case 't':
					self::_replace($word, 'icate', 'ic', 0)
				|| self::_replace($word, 'iciti', 'ic', 0);
				break;
			case 'u':
				self::_replace($word, 'ful', '', 0);
				break;
			case 'v':
				self::_replace($word, 'ative', '', 0);
				break;
			case 'z':
				self::_replace($word, 'alize', 'al', 0);
				break;
		}

		return $word;
	}

	/**
	 * Step 4
	 *
	 * @param   string  $word  The token to stem.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private static function _step4($word)
	{
		switch (substr($word, -2, 1))
		{
			case 'a':
				self::_replace($word, 'al', '', 1);
				break;
			case 'c':
					self::_replace($word, 'ance', '', 1)
				|| self::_replace($word, 'ence', '', 1);
				break;
			case 'e':
				self::_replace($word, 'er', '', 1);
				break;
			case 'i':
				self::_replace($word, 'ic', '', 1);
				break;
			case 'l':
					self::_replace($word, 'able', '', 1)
				|| self::_replace($word, 'ible', '', 1);
				break;
			case 'n':
					self::_replace($word, 'ant', '', 1)
				|| self::_replace($word, 'ement', '', 1)
				|| self::_replace($word, 'ment', '', 1)
				|| self::_replace($word, 'ent', '', 1);
				break;
			case 'o':
				if (substr($word, -4) == 'tion' || substr($word, -4) == 'sion')
				{
					self::_replace($word, 'ion', '', 1);
				}
				else
				{
					self::_replace($word, 'ou', '', 1);
				}
				break;
			case 's':
				self::_replace($word, 'ism', '', 1);
				break;
			case 't':
					self::_replace($word, 'ate', '', 1)
				|| self::_replace($word, 'iti', '', 1);
				break;
			case 'u':
				self::_replace($word, 'ous', '', 1);
				break;
			case 'v':
				self::_replace($word, 'ive', '', 1);
				break;
			case 'z':
				self::_replace($word, 'ize', '', 1);
				break;
		}

		return $word;
	}

	/**
	 * Step 5
	 *
	 * @param   string  $word  The token to stem.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private static function _step5($word)
	{
		// Part a
		if (substr($word, -1) == 'e')
		{
			if (self::_m(substr($word, 0, -1)) > 1)
			{
				self::_replace($word, 'e', '');
			}
			elseif (self::_m(substr($word, 0, -1)) == 1)
			{
				if (!self::_cvc(substr($word, 0, -1)))
				{
					self::_replace($word, 'e', '');
				}
			}
		}

		// Part b
		if (self::_m($word) > 1 && self::_doubleConsonant($word) && substr($word, -1) == 'l')
		{
			$word = substr($word, 0, -1);
		}

		return $word;
	}

	/**
	 * Replaces the first string with the second, at the end of the string. If third
	 * arg is given, then the preceding string must match that m count at least.
	 *
	 * @param   string   &$str   String to check
	 * @param   string   $check  Ending to check for
	 * @param   string   $repl   Replacement string
	 * @param   integer  $m      Optional minimum number of m() to meet
	 *
	 * @return  boolean  Whether the $check string was at the end
	 *                   of the $str string. True does not necessarily mean
	 *                   that it was replaced.
	 *
	 * @since   3.0.0
	 */
	private static function _replace(&$str, $check, $repl, $m = null)
	{
		$len = 0 - strlen($check);

		if (substr($str, $len) == $check)
		{
			$substr = substr($str, 0, $len);

			if (is_null($m) || self::_m($substr) > $m)
			{
				$str = $substr . $repl;
			}

			return true;
		}

		return false;
	}

	/**
	 * m() measures the number of consonant sequences in $str. if c is
	 * a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	 * presence,
	 *
	 * <c><v>       gives 0
	 * <c>vc<v>     gives 1
	 * <c>vcvc<v>   gives 2
	 * <c>vcvcvc<v> gives 3
	 *
	 * @param   string  $str  The string to return the m count for
	 *
	 * @return  integer  The m count
	 *
	 * @since   3.0.0
	 */
	private static function _m($str)
	{
		$c = self::$_regex_consonant;
		$v = self::$_regex_vowel;

		$str = preg_replace("#^$c+#", '', $str);
		$str = preg_replace("#$v+$#", '', $str);

		preg_match_all("#($v+$c+)#", $str, $matches);

		return count($matches[1]);
	}

	/**
	 * Returns true/false as to whether the given string contains two
	 * of the same consonant next to each other at the end of the string.
	 *
	 * @param   string  $str  String to check
	 *
	 * @return  boolean  Result
	 *
	 * @since   3.0.0
	 */
	private static function _doubleConsonant($str)
	{
		$c = self::$_regex_consonant;

		return preg_match("#$c{2}$#", $str, $matches) && $matches[0]{0} == $matches[0]{1};
	}

	/**
	 * Checks for ending CVC sequence where second C is not W, X or Y
	 *
	 * @param   string  $str  String to check
	 *
	 * @return  boolean  Result
	 *
	 * @since   3.0.0
	 */
	private static function _cvc($str)
	{
		$c = self::$_regex_consonant;
		$v = self::$_regex_vowel;

		$result = preg_match("#($c$v$c)$#", $str, $matches)
			&& strlen($matches[1]) == 3
			&& $matches[1]{2} != 'w'
			&& $matches[1]{2} != 'x'
			&& $matches[1]{2} != 'y';

		return $result;
	}
}
