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
 * @since       11.4
 */
class JStringInflector
{
	/**
	 * The singleton instance.
	 *
	 * @var    JStringInflector
	 * @since  11.4
	 */
	private static $_instance;

	/**
	 * The inflector rules.
	 *
	 * @var    array
	 * @since  11.4
	 */
	private $_rules = array(
		'singular' => array(
			'/([^aeiouy]|qu)ies$/i' => '$1y',
			'/$1ses$/i' => '$1s',
			'/ses$/i' => '$1s',
			'/s$/i' => '',
		),
		'plural' => array(
			'/(x|ch|ss|sh)$/i' => '$1es',
			'/([^aeiouy]|qu)y$/i' => '$1ies',
			'/([^aeiouy]|qu)ies$/i' => '$1y',
			'/(bu)s$/i' => '$1ses',
			'/s$/i' => 's',
			'/$/' => 's',
		),
		'countable' => array(
			'id',
			'hits',
			'clicks',
		),
	);

	/**
	 * Cached inflections.
	 *
	 * @var    array
	 * @since  11.4
	 */
	private $_cache = array(
		'identical' => array(
			'deer', 'moose', 'sheep', 'bison', 'salmon', 'pike', 'trout', 'fish', 'swine'
		),
		'singular' => array(),
		'plural'   => array()
	);

	/**
	 * Protected constructor.
	 *
	 * @since  11.4
	 */
	protected function __construct()
	{
	}

	/**
	 * Adds inflection rules to the inflector.
	 *
	 * @param   mixed   $data      A string or an array of strings or regex rules to add.
	 * @param   string  $ruleType  The rule type: singular | plural | countable
	 *
	 * @return  void
	 *
	 * @since   11.4
	 * @throws  InvalidArgumentException
	 */
	private function _addRule($data, $ruleType)
	{
		if (!isset($this->_rules[$ruleType]))
		{
			// Do not translate.
			throw new InvalidArgumentException(sprintf('Invalid inflector rule type [%s].', $ruleType));
		}

		if (is_string($data))
		{
			$data = array($data);
		}
		elseif (!is_array($data))
		{
			settype($data, 'array');
		}

		foreach ($data as $rule)
		{
			// Ensure a string is pushed.
			array_push($this->_rules[$ruleType], (string) $data);
		}
	}

	/**
	 * Gets an inflected word from the cache.
	 *
	 * @param   string  $word       A string.
	 * @param   string  $cacheType  The rule type: singular | plural
	 *
	 * @return  mixed  The cached inflection or false if none found.
	 *
	 * @since   11.4
	 * @throws  InvalidArgumentException
	 */
	private function _getCache($word, $cacheType)
	{
		$word = JString::strtolower(word);
		$cacheType = strtolower($cacheType);

		// First check for identicals.
		if (in_array($word, $this->_cache['identical']))
		{
			return $this->_cache['identical'];
		}
		// Check for an invalid cache type.
		elseif (!isset($this->_cache[$cacheType]))
		{
			// Do not translate.
			throw new InvalidArgumentException(sprintf('Invalid inflector cache type [%s].', $cacheType));
		}

		//get singular cache
		if (isset($this->_cache[$cacheType][$word]))
		{
			return $this->_cache[$cacheType][$word];
		}

		return false;
	}

	/**
	 * Execute a regex from rules
	 *
	 * @param   string  $word      The string input.
	 * @param   string  $ruleType  String (eg, singular|plural|countable)
	 *
	 * @return  string  matched string
	 *
	 * @since  11.4
	 */
	private function _matchRegexRule($word,$ruleType)
	{
		$cache = $this->_getCache($word, $ruleType);

		if ($cache !== false)
		{
			return $this->_cache[$ruleType][$word];
		}

		foreach ($this->_rules[$ruleType] as $regex => $replacement)
		{
			if ($regex[0] == '/')
			{
				$matches = 0;
				$matchedWord = preg_replace($regex, $replacement, $word, -1, $matches);

				if ($matches > 0)
				{
					$this->_cache[$ruleType][$word] = $matchedWord;

					return $matchedWord;
				}
			}
		}

		return $word;
	}

	/**
	 * Sets an inflected word in the cache.
	 *
	 * @param   string  $word        A string.
	 * @param   string  $inflection  The inflected string.
	 * @param   string  $cacheType   The rule type: singular | plural
	 *
	 * @return  void
	 *
	 * @since   11.4
	 * @throws  InvalidArgumentException
	 */
	private function _setCache($word, $inflection, $cacheType)
	{
		$word = JString::strtolower(word);
		$inflection = JString::strtolower($inflection);
		$cacheType = strtolower($cacheType);

		if (!isset($this->_cache[$cacheType]))
		{
			// Do not translate.
			throw new InvalidArgumentException(sprintf('Invalid inflector cache type [%s].', $cacheType));
		}

		if ($cacheType == 'identical')
		{
			array_push($this->_cache['identical'], $word);
		}
		else
		{
			$this->_cache[$cacheType][$word] = $inflection;
		}
	}

	/**
	 * Adds a countable word.
	 *
	 * @param   mixed  $data  A string or an array of strings to add.
	 *
	 * @return  JStringInflector  Returns this object to support chaining.
	 *
	 * @since   11.4
	 */
	public function addCountableRule($data)
	{
		$this->_addRule($data, 'countable');

		return $this;
	}

	/**
	 * Adds a pluralisation rule.
	 *
	 * @param   string  $singular  The singular form of the word.
	 * @param   string  $plural    The plural form of the word. If omitted, it is assumed the singular and plural are identical.
	 *
	 * @return  JStringInflector  Returns this object to support chaining.
	 *
	 * @since   11.4
	 */
	public function addIrregularRule($singular, $plural =null)
	{
		if (JString::strcasecmp($singular, $plural) === 0)
		{
			$this->_setCache($singular, null, 'identical');
		}
		else
		{
			$this->_setCache($singular, $plural, 'plural');
			$this->_setCache($plural, $singular, 'singular');
		}

		return $this;
	}

	/**
	 * Adds a pluralisation rule.
	 *
	 * @param   mixed  $data  A string or an array of regex rules to add.
	 *
	 * @return  JStringInflector  Returns this object to support chaining.
	 *
	 * @since   11.4
	 */
	public function addPluraliseRule($data)
	{
		$this->_addRule($data, 'plural');

		return $this;
	}

	/**
	 * Adds a singularisation rule.
	 *
	 * @param   mixed  $data  A string or an array of regex rules to add.
	 *
	 * @return  JStringInflector  Returns this object to support chaining.
	 *
	 * @since   11.4
	 */
	public function addSingulariseRule($data)
	{
		$this->_addRule($data, 'singular');

		return $this;
	}

	/**
	 * Gets an instance of the JStringInflector singleton.
	 *
	 * @return  JStringInflector
	 *
	 * @since   11.4
	 */
	public static function getInstance()
	{
		if (!is_object(self::$_instance))
		{
			self::$_instance = new JStringInflector;
		}

		return self::$_instance;
	}

	/**
	 * Checks if a word is countable.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  boolean  True if word is countable, false otherwise.
	 *
	 * @since  11.4
	 */
	public function isCountable($word)
	{
		return (boolean) in_array($word, $this->_rules['countable']);
	}

	/**
	 * Checks if a word is in a plural form.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  boolean  True if word is plural, false if not.
	 *
	 * @since  11.4
	 */
	public function isPlural($word)
	{
		// Try the cache.
		$plural = $this->_getCache($word, 'plural');

		if ($plural !== false)
		{
			$singular = $this->_getCache($word, 'singular');
		}
		else
		{
			$singular = false;
		}

		// Test is we already know the singular and plural form of the word.
		if ($singular !== false && $plural !== false)
		{
			// We can just test directly.
			return $word != $plural;
		}

		// Compute the inflection to cache the values, and compare.
		return $this->toPlural($this->toSingular($word)) == $word;
	}

	/**
	 * Checks if a word is in a singular form.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  boolean  True if word is singular, false if not.
	 *
	 * @since  11.4
	 */
	public function isSingular($word)
	{
		// Try the cache.
		$singular = $this->_getCache($word, 'singular');

		if ($singular !== false)
		{
			$plural = $this->_getCache($word, 'plural');
		}
		else
		{
			$plural = false;
		}

		// Test is we already know the singular and plural form of the word.
		if ($singular !== false && $plural !== false)
		{
			// We can just test directly.
			return $word != $singular;
		}

		// Compute the inflection to cache the values, and compare.
				return $this->toSingular($this->toPlural($word)) == $word;
	}

	/**
	 * Converts a word into its plural form.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  string  The pluralised string.
	 *
	 * @since  11.4
	 */
	public function toPlural($word)
	{
		return $this->_matchRegexRule($word, 'plural');
	}

	/**
	 * Converts a word into its singular form.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  string  The singular string.
	 *
	 * @since  11.4
	 */
	public function toSingular($word)
	{
		return $this->_matchRegexRule($word, 'singular');
	}
}
