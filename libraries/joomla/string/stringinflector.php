<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JSringInflector class
 *
 * The Inflector transforms words
 *
 * @package     Joomla.Platform
 * @subpackage  String
 * @since       11.3
 * @tutorial	Joomla.Platform/jinflector.cls
 * @link		http://docs.joomla.org/JInflector
 */
abstract class JStringInflector
{
	/**
	 * List of rules.
	 *
	 * @var    Array
	 * @since  11.3
	 */
	static private $_rules = array(
		'pluralize' => array(
			'/(x|ch|ss|sh)$/i' => "$1es",
			'/([^aeiouy]|qu)y$/i' => "$1ies",
			'/([^aeiouy]|qu)ies$/i' => "$1y",
			'/(bu)s$/i' => "$1ses",
			'/s$/i' => "s",
			'/$/' => "s"
		),
		'singularize' => array(
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
	 * Return true if word is countable.
	 *
	 * @var    String
	 * 
	 * @return Boolean	TRUE if word is in countable list
	 * 
	 * @since  11.3
	 */
	static function countable($word)
	{
		return (array_search($word,self::$_rules['countable']) !== false) ? true : false ;
	}
	
	/**
	 * pluralize a word
	 *
	 * @var    String
	 * 
	 * @return String
	 * 
	 * @since  11.3
	 */
	static function pluralize($word)
	{
		return self::matchRegexRule($word,'pluralize');
	}
	
	/**
	 * singularize a word
	 *
	 * @var    String
	 * 
	 * @return String
	 * 
	 * @since  11.3
	 */
	static function singularize($word)
	{
		return self::matchRegexRule($word,'singularize');
	}
	
	/**
	 * Execute a regex from rules
	 *
	 * @var		String
	 * @var		String
	 *
	 * @return	String
	 * 
	 * @since  11.3
	 */
	static function matchRegexRule($word,$ruletype)
	{
		if (isset(self::$_rules[$ruletype][$word])) {
			return self::$_rules[$ruletype][$word];
		}

		foreach (self::$_rules[$ruletype] as $regex => $replacement) {
			$matches = 0;
			$matchedWord = preg_replace($regex, $replacement, $word, -1, $matches);
			if ($matches > 0) {
					self::$_rules[$ruletype][$word] = $matchedWord;
					return $matchedWord;
			}
		}
		
		return $word;
	}
	
	/**
	 * Add new data to rules
	 *
	 * @var    Array
	 * @var    String
	 * 
	 * @return boolean	TRUE if success
	 * 
	 * @since  11.3
	 */
	static function add($data,$ruletype)
	{
		if (!array_key_exists(self::$_rules,$ruletype)) {
			return false;
		}
		
		array_push(self::$_rules[$ruletype], $data);
		
		return true;
	}
}