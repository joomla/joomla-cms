<?php
/**
 * Part of the Joomla Framework String Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\String;

use InvalidArgumentException;

/**
 * Joomla Framework String Inflector Class
 *
 * The Inflector transforms words
 *
 * @since  1.0
 */
class Inflector
{
	/**
	 * The singleton instance.
	 *
	 * @var    Inflector
	 * @since  1.0
	 */
	private static $instance;

	/**
	 * The inflector rules for singularisation, pluralisation and countability.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $rules = array(
		'singular' => array(
			'/(matr)ices$/i' => '\1ix',
			'/(vert|ind)ices$/i' => '\1ex',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
			'/([ftw]ax)es/i' => '\1',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(shoe|slave)s$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/$1ses$/i' => '\s',
			'/ses$/i' => '\s',
			'/eaus$/' => 'eau',
			'/^(.*us)$/' => '\\1',
			'/s$/i' => '',
		),
		'plural' => array(
			'/([m|l])ouse$/i' => '\1ice',
			'/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',
			'/(x|ch|ss|sh)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/sis$/i' => 'ses',
			'/([ti])um$/i' => '\1a',
			'/(buffal|tomat)o$/i' => '\1\2oes',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
			'/us$/i' => 'uses',
			'/(ax|cris|test)is$/i' => '\1es',
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
	 * The array is in the form [singular => plural]
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $cache = array();

	/**
	 * Protected constructor.
	 *
	 * @since  1.0
	 */
	protected function __construct()
	{
		// Pre=populate the irregual singular/plural.
		$this
			->addWord('deer')
			->addWord('moose')
			->addWord('sheep')
			->addWord('bison')
			->addWord('salmon')
			->addWord('pike')
			->addWord('trout')
			->addWord('fish')
			->addWord('swine')

			->addWord('alias', 'aliases')
			->addWord('bus', 'buses')
			->addWord('foot', 'feet')
			->addWord('goose', 'geese')
			->addWord('hive', 'hives')
			->addWord('louse', 'lice')
			->addWord('man', 'men')
			->addWord('mouse', 'mice')
			->addWord('ox', 'oxen')
			->addWord('quiz', 'quizes')
			->addWord('status', 'statuses')
			->addWord('tooth', 'teeth')
			->addWord('woman', 'women');
	}

	/**
	 * Adds inflection regex rules to the inflector.
	 *
	 * @param   mixed   $data      A string or an array of strings or regex rules to add.
	 * @param   string  $ruleType  The rule type: singular | plural | countable
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	private function addRule($data, $ruleType)
	{
		if (is_string($data))
		{
			$data = array($data);
		}
		elseif (!is_array($data))
		{
			// Do not translate.
			throw new InvalidArgumentException('Invalid inflector rule data.');
		}

		foreach ($data as $rule)
		{
			// Ensure a string is pushed.
			array_push($this->rules[$ruleType], (string) $rule);
		}
	}

	/**
	 * Gets an inflected word from the cache where the singular form is supplied.
	 *
	 * @param   string  $singular  A singular form of a word.
	 *
	 * @return  mixed  The cached inflection or false if none found.
	 *
	 * @since   1.0
	 */
	private function getCachedPlural($singular)
	{
		$singular = StringHelper::strtolower($singular);

		// Check if the word is in cache.
		if (isset($this->cache[$singular]))
		{
			return $this->cache[$singular];
		}

		return false;
	}

	/**
	 * Gets an inflected word from the cache where the plural form is supplied.
	 *
	 * @param   string  $plural  A plural form of a word.
	 *
	 * @return  mixed  The cached inflection or false if none found.
	 *
	 * @since   1.0
	 */
	private function getCachedSingular($plural)
	{
		$plural = StringHelper::strtolower($plural);

		return array_search($plural, $this->cache);
	}

	/**
	 * Execute a regex from rules.
	 *
	 * The 'plural' rule type expects a singular word.
	 * The 'singular' rule type expects a plural word.
	 *
	 * @param   string  $word      The string input.
	 * @param   string  $ruleType  String (eg, singular|plural)
	 *
	 * @return  mixed  An inflected string, or false if no rule could be applied.
	 *
	 * @since   1.0
	 */
	private function matchRegexRule($word, $ruleType)
	{
		// Cycle through the regex rules.
		foreach ($this->rules[$ruleType] as $regex => $replacement)
		{
			$matches = 0;
			$matchedWord = preg_replace($regex, $replacement, $word, -1, $matches);

			if ($matches > 0)
			{
				return $matchedWord;
			}
		}

		return false;
	}

	/**
	 * Sets an inflected word in the cache.
	 *
	 * @param   string  $singular  The singular form of the word.
	 * @param   string  $plural    The plural form of the word. If omitted, it is assumed the singular and plural are identical.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function setCache($singular, $plural = null)
	{
		$singular = StringHelper::strtolower($singular);

		if ($plural === null)
		{
			$plural = $singular;
		}
		else
		{
			$plural = StringHelper::strtolower($plural);
		}

		$this->cache[$singular] = $plural;
	}

	/**
	 * Adds a countable word.
	 *
	 * @param   mixed  $data  A string or an array of strings to add.
	 *
	 * @return  Inflector  Returns this object to support chaining.
	 *
	 * @since   1.0
	 */
	public function addCountableRule($data)
	{
		$this->addRule($data, 'countable');

		return $this;
	}

	/**
	 * Adds a specific singular-plural pair for a word.
	 *
	 * @param   string  $singular  The singular form of the word.
	 * @param   string  $plural    The plural form of the word. If omitted, it is assumed the singular and plural are identical.
	 *
	 * @return  Inflector  Returns this object to support chaining.
	 *
	 * @since   1.0
	 */
	public function addWord($singular, $plural =null)
	{
		$this->setCache($singular, $plural);

		return $this;
	}

	/**
	 * Adds a pluralisation rule.
	 *
	 * @param   mixed  $data  A string or an array of regex rules to add.
	 *
	 * @return  Inflector  Returns this object to support chaining.
	 *
	 * @since   1.0
	 */
	public function addPluraliseRule($data)
	{
		$this->addRule($data, 'plural');

		return $this;
	}

	/**
	 * Adds a singularisation rule.
	 *
	 * @param   mixed  $data  A string or an array of regex rules to add.
	 *
	 * @return  Inflector  Returns this object to support chaining.
	 *
	 * @since   1.0
	 */
	public function addSingulariseRule($data)
	{
		$this->addRule($data, 'singular');

		return $this;
	}

	/**
	 * Gets an instance of the JStringInflector singleton.
	 *
	 * @param   boolean  $new  If true (default is false), returns a new instance regardless if one exists.
	 *                         This argument is mainly used for testing.
	 *
	 * @return  Inflector
	 *
	 * @since   1.0
	 */
	public static function getInstance($new = false)
	{
		if ($new)
		{
			return new static;
		}
		elseif (!is_object(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Checks if a word is countable.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  boolean  True if word is countable, false otherwise.
	 *
	 * @since   1.0
	 */
	public function isCountable($word)
	{
		return (boolean) in_array($word, $this->rules['countable']);
	}

	/**
	 * Checks if a word is in a plural form.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  boolean  True if word is plural, false if not.
	 *
	 * @since   1.0
	 */
	public function isPlural($word)
	{
		// Try the cache for an known inflection.
		$inflection = $this->getCachedSingular($word);

		if ($inflection !== false)
		{
			return true;
		}

		$singularWord = $this->toSingular($word);

		if ($singularWord === false)
		{
			return false;
		}

		// Compute the inflection to cache the values, and compare.
		return $this->toPlural($singularWord) == $word;
	}

	/**
	 * Checks if a word is in a singular form.
	 *
	 * @param   string  $word  The string input.
	 *
	 * @return  boolean  True if word is singular, false if not.
	 *
	 * @since   1.0
	 */
	public function isSingular($word)
	{
		// Try the cache for an known inflection.
		$inflection = $this->getCachedPlural($word);

		if ($inflection !== false)
		{
			return true;
		}

		$pluralWord = $this->toPlural($word);

		if ($pluralWord === false)
		{
			return false;
		}

		// Compute the inflection to cache the values, and compare.
		return $this->toSingular($pluralWord) == $word;
	}

	/**
	 * Converts a word into its plural form.
	 *
	 * @param   string  $word  The singular word to pluralise.
	 *
	 * @return  mixed  An inflected string, or false if no rule could be applied.
	 *
	 * @since   1.0
	 */
	public function toPlural($word)
	{
		// Try to get the cached plural form from the singular.
		$cache = $this->getCachedPlural($word);

		if ($cache !== false)
		{
			return $cache;
		}

		// Check if the word is a known singular.
		if ($this->getCachedSingular($word))
		{
			return false;
		}

		// Compute the inflection.
		$inflected = $this->matchRegexRule($word, 'plural');

		if ($inflected !== false)
		{
			$this->setCache($word, $inflected);

			return $inflected;
		}

		// Dead code
		return false;
	}

	/**
	 * Converts a word into its singular form.
	 *
	 * @param   string  $word  The plural word to singularise.
	 *
	 * @return  mixed  An inflected string, or false if no rule could be applied.
	 *
	 * @since   1.0
	 */
	public function toSingular($word)
	{
		// Try to get the cached singular form from the plural.
		$cache = $this->getCachedSingular($word);

		if ($cache !== false)
		{
			return $cache;
		}

		// Check if the word is a known plural.
		if ($this->getCachedPlural($word))
		{
			return false;
		}

		// Compute the inflection.
		$inflected = $this->matchRegexRule($word, 'singular');

		if ($inflected !== false)
		{
			$this->setCache($inflected, $word);

			return $inflected;
		}

		return false;
	}
}
