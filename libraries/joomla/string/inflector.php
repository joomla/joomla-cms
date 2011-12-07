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
 * @link	http://docs.joomla.org/JStringInflector
 */
class JStringInflector
{
	/**
	 * List of rules.
	 *
	 * @var    Array
	 * @since  11.3
	 */
	static private $_rules = array(
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
			'/ses$/i' => "\1",
			'/s$/i' => "\1"
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
	protected static $_cache = array(
		'singular' => array(),
		'plural'   => array()
	);
	
	
	/**
	 * Return true if word is countable.
	 *
	 * @param   string  $word The string input.
	 * 
	 * @return  boolean  TRUE if word is in countable list
	 * 
	 * @since  11.3
	 */
	public static function isCountable($word)
	{
		return (array_search($word,self::$_rules['countable']) !== false) ? true : false ;
	}
	
	/**
	 * pluralize a word
	 *
	 * @param   string  $word The string input.
	 * 
	 * @return  string  The pluralised string
	 * 
	 * @since  11.3
	 */
	public static function toPlural($word)
	{
		return self::matchRegexRule($word,'plural');
	}
	
	/**
	 * singularize a word
	 *
	 * @param   string  
	 * 
	 * @return  string  
	 * 
	 * @since  11.3
	 */
	public static function toSingular($word)
	{
		return self::matchRegexRule($word,'singular');
	}
	
	/**
	 * Execute a regex from rules
	 *
	 * @param   string  $word The string input.
	 * @param   string  $ruletype (eg, singular|plural|countable)
	 *
	 * @return  string  matched string
	 * 
	 * @since  11.3
	 */
	private static function matchRegexRule($word,$ruletype)
	{
		if (isset(self::$_cache[$ruletype][$word])) {
			return self::$_cache[$ruletype][$word];
		}

		foreach (self::$_rules[$ruletype] as $regex => $replacement) {
			if ($regex[0] == '/') {
				$matches = 0;
				$matchedWord = preg_replace($regex, $replacement, $word, -1, $matches);
				if ($matches > 0) {
						self::$_cache[$ruletype][$word] = $matchedWord;
						return $matchedWord;
				}
			}
		}
		
		return $word;
	}
	
	/**
	 * Add new data to rules
	 *
	 * @param   mixerd  string to countable rule, and array otherwise
	 * @param   string  alias (eg, singular|plural|countable)
	 * 
	 * @return  boolean TRUE if success
	 * 
	 * @since  11.3
	 */
	public static function addRule($data,$ruletype)
	{
		if ($ruletype == 'countable' && !is_string($data)) {
			return false;
		}
		else if ( ($ruletype=='plural' || $ruletype == 'singular') && !is_array($data) ) {
			return false;
		}
		
		array_push(self::$_rules[$ruletype], $data);
		
		return true;
	}
}