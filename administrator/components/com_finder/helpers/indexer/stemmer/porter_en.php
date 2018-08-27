<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderIndexerStemmer', dirname(__DIR__) . '/stemmer.php');

/**
 * Porter English stemmer class for the Finder indexer package.
 *
 * This class was adapted from one written by Richard Heyes.
 * See copyright and link information above.
 *
 * @since  2.5
 */
class FinderIndexerStemmerPorter_En extends FinderIndexerStemmer
{
	/**
	 * Regex for matching a consonant.
	 *
	 * @var    string
	 * @since  2.5
	 */
	private static $regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

	/**
	 * Regex for matching a vowel
	 *
	 * @var    string
	 * @since  2.5
	 */
	private static $regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

	/**
	 * Method to stem a token and return the root.
	 *
	 * @param   string  $token  The token to stem.
	 * @param   string  $lang   The language of the token.
	 *
	 * @return  string  The root token.
	 *
	 * @since   2.5
	 */
	public function stem($token, $lang)
	{
		// Check if the token is long enough to merit stemming.
		if (strlen($token) <= 2)
		{
			return $token;
		}

		// Check if the language is English or All.
		if ($lang !== 'en' && $lang !== '*')
		{
			return $token;
		}

		// Stem the token if it is not in the cache.
		if (!isset($this->cache[$lang][$token]))
		{
			// Stem the token.
			$result = $token;
			$result = self::step1ab($result);
			$result = self::step1c($result);
			$result = self::step2($result);
			$result = self::step3($result);
			$result = self::step4($result);
			$result = self::step5($result);

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
	 * @since   2.5
	 */
	private static function step1ab($word)
	{
		// Part a
		if (substr($word, -1) === 's')
		{
			self::replace($word, 'sses', 'ss')
			|| self::replace($word, 'ies', 'i')
			|| self::replace($word, 'ss', 'ss')
			|| self::replace($word, 's', '');
		}

		// Part b
		if (substr($word, -2, 1) !== 'e' || !self::replace($word, 'eed', 'ee', 0))
		{
			// First rule
			$v = self::$regex_vowel;

			// Words ending with ing and ed
			// Note use of && and OR, for precedence reasons
			if (preg_match("#$v+#", substr($word, 0, -3)) && self::replace($word, 'ing', '')
				|| preg_match("#$v+#", substr($word, 0, -2)) && self::replace($word, 'ed', ''))
			{
				// If one of above two test successful
				if (!self::replace($word, 'at', 'ate') && !self::replace($word, 'bl', 'ble') && !self::replace($word, 'iz', 'ize'))
				{
					// Double consonant ending
					$wordSubStr = substr($word, -2);

					if ($wordSubStr !== 'll' && $wordSubStr !== 'ss' && $wordSubStr !== 'zz' && self::doubleConsonant($word))
					{
						$word = substr($word, 0, -1);
					}
					elseif (self::m($word) === 1 && self::cvc($word))
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
	 * @since   2.5
	 */
	private static function step1c($word)
	{
		$v = self::$regex_vowel;

		if (substr($word, -1) === 'y' && preg_match("#$v+#", substr($word, 0, -1)))
		{
			self::replace($word, 'y', 'i');
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
	 * @since   2.5
	 */
	private static function step2($word)
	{
		switch (substr($word, -2, 1))
		{
			case 'a':
				self::replace($word, 'ational', 'ate', 0)
				|| self::replace($word, 'tional', 'tion', 0);
				break;
			case 'c':
				self::replace($word, 'enci', 'ence', 0)
				|| self::replace($word, 'anci', 'ance', 0);
				break;
			case 'e':
				self::replace($word, 'izer', 'ize', 0);
				break;
			case 'g':
				self::replace($word, 'logi', 'log', 0);
				break;
			case 'l':
				self::replace($word, 'entli', 'ent', 0)
				|| self::replace($word, 'ousli', 'ous', 0)
				|| self::replace($word, 'alli', 'al', 0)
				|| self::replace($word, 'bli', 'ble', 0)
				|| self::replace($word, 'eli', 'e', 0);
				break;
			case 'o':
				self::replace($word, 'ization', 'ize', 0)
				|| self::replace($word, 'ation', 'ate', 0)
				|| self::replace($word, 'ator', 'ate', 0);
				break;
			case 's':
				self::replace($word, 'iveness', 'ive', 0)
				|| self::replace($word, 'fulness', 'ful', 0)
				|| self::replace($word, 'ousness', 'ous', 0)
				|| self::replace($word, 'alism', 'al', 0);
				break;
			case 't':
				self::replace($word, 'biliti', 'ble', 0)
				|| self::replace($word, 'aliti', 'al', 0)
				|| self::replace($word, 'iviti', 'ive', 0);
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
	 * @since   2.5
	 */
	private static function step3($word)
	{
		switch (substr($word, -2, 1))
		{
			case 'a':
				self::replace($word, 'ical', 'ic', 0);
				break;
			case 's':
				self::replace($word, 'ness', '', 0);
				break;
			case 't':
				self::replace($word, 'icate', 'ic', 0)
				|| self::replace($word, 'iciti', 'ic', 0);
				break;
			case 'u':
				self::replace($word, 'ful', '', 0);
				break;
			case 'v':
				self::replace($word, 'ative', '', 0);
				break;
			case 'z':
				self::replace($word, 'alize', 'al', 0);
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
	 * @since   2.5
	 */
	private static function step4($word)
	{
		switch (substr($word, -2, 1))
		{
			case 'a':
				self::replace($word, 'al', '', 1);
				break;
			case 'c':
				self::replace($word, 'ance', '', 1)
				|| self::replace($word, 'ence', '', 1);
				break;
			case 'e':
				self::replace($word, 'er', '', 1);
				break;
			case 'i':
				self::replace($word, 'ic', '', 1);
				break;
			case 'l':
				self::replace($word, 'able', '', 1)
				|| self::replace($word, 'ible', '', 1);
				break;
			case 'n':
				self::replace($word, 'ant', '', 1)
				|| self::replace($word, 'ement', '', 1)
				|| self::replace($word, 'ment', '', 1)
				|| self::replace($word, 'ent', '', 1);
				break;
			case 'o':
				$wordSubStr = substr($word, -4);

				if ($wordSubStr === 'tion' || $wordSubStr === 'sion')
				{
					self::replace($word, 'ion', '', 1);
				}
				else
				{
					self::replace($word, 'ou', '', 1);
				}
				break;
			case 's':
				self::replace($word, 'ism', '', 1);
				break;
			case 't':
				self::replace($word, 'ate', '', 1)
				|| self::replace($word, 'iti', '', 1);
				break;
			case 'u':
				self::replace($word, 'ous', '', 1);
				break;
			case 'v':
				self::replace($word, 'ive', '', 1);
				break;
			case 'z':
				self::replace($word, 'ize', '', 1);
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
	 * @since   2.5
	 */
	private static function step5($word)
	{
		// Part a
		if (substr($word, -1) === 'e')
		{
			if (self::m(substr($word, 0, -1)) > 1)
			{
				self::replace($word, 'e', '');
			}
			elseif (self::m(substr($word, 0, -1)) === 1)
			{
				if (!self::cvc(substr($word, 0, -1)))
				{
					self::replace($word, 'e', '');
				}
			}
		}

		// Part b
		if (self::m($word) > 1 && self::doubleConsonant($word) && substr($word, -1) === 'l')
		{
			$word = substr($word, 0, -1);
		}

		return $word;
	}

	/**
	 * Replaces the first string with the second, at the end of the string. If third
	 * arg is given, then the preceding string must match that m count at least.
	 *
	 * @param   string   $str    String to check
	 * @param   string   $check  Ending to check for
	 * @param   string   $repl   Replacement string
	 * @param   integer  $m      Optional minimum number of m() to meet
	 *
	 * @return  boolean  Whether the $check string was at the end
	 *                   of the $str string. True does not necessarily mean
	 *                   that it was replaced.
	 *
	 * @since   2.5
	 */
	private static function replace(&$str, $check, $repl, $m = null)
	{
		$len = 0 - strlen($check);

		if (substr($str, $len) === $check)
		{
			$substr = substr($str, 0, $len);

			if ($m === null || self::m($substr) > $m)
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
	 * @since   2.5
	 */
	private static function m($str)
	{
		$c = self::$regex_consonant;
		$v = self::$regex_vowel;

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
	 * @since   2.5
	 */
	private static function doubleConsonant($str)
	{
		$c = self::$regex_consonant;

		return preg_match("#$c{2}$#", $str, $matches) && $matches[0][0] === $matches[0][1];
	}

	/**
	 * Checks for ending CVC sequence where second C is not W, X or Y
	 *
	 * @param   string  $str  String to check
	 *
	 * @return  boolean  Result
	 *
	 * @since   2.5
	 */
	private static function cvc($str)
	{
		$c = self::$regex_consonant;
		$v = self::$regex_vowel;

		return preg_match("#($c$v$c)$#", $str, $matches) && strlen($matches[1]) === 3 && $matches[1][2] !== 'w' && $matches[1][2] !== 'x'
			&& $matches[1][2] !== 'y';
	}
}
