<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 * 
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform String Inflector Class
 *
 * The Inflector transforms words
 *
 * @package     Joomla.Platform
 * @subpackage  String
 * @since       11.3
 */
class JStringInflector
{
	/**
	 * List of rules.
	 *
	 * @var    Array
	 * @since  11.3
	 */
	static protected $rules = array(
		'plural' => array(
			'/(x|ch|ss|sh)$/i' => "$1es",
			'/([^aeiouy]|qu)y$/i' => "$1ies",
			'/([^aeiouy]|qu)ies$/i' => "$1y",
			'/(bu)s$/i' => "$1ses",
			'/s$/i' => "s",
			'/$/' => "s"
		),
		'singular' => array(
			'/([^aeiouy]|qu)ies$/i' => "$1y",
			'/$1ses$/i' => "$1s",
			'/ses$/i' => "$1s",
			'/s$/i' => ""
		),
		'countable' => array(
			'id',
			'hits',
			'clicks'
		)
	);

	/**
	 * Cached string
	 * 
	 * @var array
	 * @since  11.3
	 */
	protected static $cache = array(
		'singular' => array(),
		'plural'   => array()
	);

	/**
	 * Return true if word is countable.
	 *
	 * @param   string  $word  The string input.
	 * 
	 * @return  boolean  TRUE if word is in countable list
	 * 
	 * @since  11.3
	 */
	public static function isCountable($word)
	{
		if (array_search($word, self::$rules['countable']) !== false)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Return true if word is singular.
	 * 
	 * @param   string  $word  The string input.
	 * 
	 * @return  bollean  TRUE if word is singular
	 * 
	 * @since  11.3
	 */
	public static function isSingular($word)
	{
		//get cached values
		$singular = null;
		$plural   = null;

		//get singular cache
		if (isset(self::$cache['singular'][$word]))
		{
			$singular = self::$cache['singular'][$word];
		}

		//get plural cached if singular exists
		if (!is_null($singular) && isset(self::$cache['plural'][$word]))
		{
			$plural = self::$cache['plural'][$word];
		}

		//result if exists in cache
		if (!is_null($singular) && !is_null($plural))
		{
			return $word != $plural;
		}

		//cache word and check if its singular
		return self::toSingular(self::toPlural($word)) == $word;
	}

	/**
	 * Return true if word is plural
	 * 
	 * @param   string  $word  The string input
	 * 
	 * @return  boolean  TRUE if word is plural
	 * 
	 * @since  11.3
	 */
	public static function isPlural($word)
	{
		//get cached values
		$singular = null;
		$plural   = null;

		//get singular cache
		if (isset(self::$cache['plural'][$word]))
		{
			$plural = self::$cache['plural'][$word];
		}

		//get plural cached if singular exists
		if (!is_null($plural) && isset(self::$cache['singular'][$word]))
		{
			$singular = self::$cache['singular'][$word];
		}

		//result if exists in cache
		if (!is_null($singular) && !is_null($plural))
		{
			return $word != $plural;
		}

		//cache word and check if its plural
		return self::toPlural(self::toSingular($word)) == $word;
	}

	/**
	 * pluralize a word
	 *
	 * @param   string  $word  The string input.
	 * 
	 * @return  string  The pluralised string
	 * 
	 * @since  11.3
	 */
	public static function toPlural($word)
	{
		//add word to singular cache if not exists
		if (!isset(self::$cache['singular'][$word]))
		{
			self::$cache['singular'][$word] = $word;
		}

		return self::matchRegexRule($word, "plural");
	}

	/**
	 * singularize a word
	 *
	 * @param   string  $word  The string input.
	 * 
	 * @return  string  The singular string.
	 * 
	 * @since  11.3
	 */
	public static function toSingular($word)
	{
		//add word to plural cache if not exists
		if (!isset(self::$cache['plural'][$word]))
		{
			self::$cache['plural'][$word] = $word;
		}

		return self::matchRegexRule($word, "singular");
	}

	/**
	 * Execute a regex from rules
	 *
	 * @param   string  $word      The string input.
	 * @param   string  $ruletype  String (eg, singular|plural|countable)
	 *
	 * @return  string  matched string
	 * 
	 * @since  11.3
	 */
	private static function matchRegexRule($word,$ruletype)
	{
		if (isset(self::$cache[$ruletype][$word]))
		{
			return self::$cache[$ruletype][$word];
		}

		foreach (self::$rules[$ruletype] as $regex => $replacement)
		{
			if ($regex[0] == '/')
			{
				$matches = 0;
				$matchedWord = preg_replace($regex, $replacement, $word, -1, $matches);
				if ($matches > 0)
				{
						self::$cache[$ruletype][$word] = $matchedWord;
						return $matchedWord;
				}
			}
		}

		return $word;
	}

	/**
	 * Add new data to rules
	 *
	 * @param   mixed   $data      string if countable rule, else array
	 * @param   string  $ruletype  eg, singular|plural|countable
	 * 
	 * @return  boolean TRUE if success
	 * 
	 * @since  11.3
	 */
	public static function addRule($data,$ruletype)
	{
		if ($ruletype == 'countable' && !is_string($data))
		{
			return false;
		}
		elseif ( ($ruletype=='plural' || $ruletype == 'singular') && !is_array($data) )
		{
			return false;
		}
		array_push(self::$rules[$ruletype], $data);
		return true;
	}
}
