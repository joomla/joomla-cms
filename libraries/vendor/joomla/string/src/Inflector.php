<?php
/**
 * Part of the Joomla Framework String Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\String;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

/**
 * Joomla Framework String Inflector Class
 *
 * The Inflector transforms words
 *
 * @since  1.0
 */
class Inflector extends DoctrineInflector
{
	/**
	 * The singleton instance.
	 *
	 * @var    Inflector
	 * @since  1.0
	 * @deprecated  3.0
	 */
	private static $instance;

	/**
	 * The inflector rules for countability.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private static $countable = [
		'rules' => [
			'id',
			'hits',
			'clicks',
		],
	];

	/**
	 * Adds inflection regex rules to the inflector.
	 *
	 * @param   mixed   $data      A string or an array of strings or regex rules to add.
	 * @param   string  $ruleType  The rule type: singular | plural | countable
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	private function addRule($data, string $ruleType)
	{
		if (\is_string($data))
		{
			$data = [$data];
		}
		elseif (!\is_array($data))
		{
			throw new \InvalidArgumentException('Invalid inflector rule data.');
		}
		elseif (!\in_array($ruleType, ['singular', 'plural', 'countable']))
		{
			throw new \InvalidArgumentException('Unsupported rule type.');
		}

		if ($ruleType === 'countable')
		{
			foreach ($data as $rule)
			{
				// Ensure a string is pushed.
				array_push(self::$countable['rules'], (string) $rule);
			}
		}
		else
		{
			static::rules($ruleType, $data);
		}
	}

	/**
	 * Adds a countable word.
	 *
	 * @param   mixed  $data  A string or an array of strings to add.
	 *
	 * @return  $this
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
	 * @return  $this
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use Doctrine\Common\Inflector\Inflector::rules() instead.
	 */
	public function addWord($singular, $plural = '')
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::rules() instead.',
				__METHOD__,
				DoctrineInflector::class
			),
			E_USER_DEPRECATED
		);

		if ($plural !== '')
		{
			static::rules(
				'plural',
				[
					'irregular' => [$plural => $singular],
				]
			);

			static::rules(
				'singular',
				[
					'irregular' => [$singular => $plural],
				]
			);
		}
		else
		{
			static::rules(
				'plural',
				[
					'uninflected' => [$singular],
				]
			);

			static::rules(
				'singular',
				[
					'uninflected' => [$singular],
				]
			);
		}

		return $this;
	}

	/**
	 * Adds a pluralisation rule.
	 *
	 * @param   mixed  $data  A string or an array of regex rules to add.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use Doctrine\Common\Inflector\Inflector::rules() instead.
	 */
	public function addPluraliseRule($data)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::rules() instead.',
				__METHOD__,
				DoctrineInflector::class
			),
			E_USER_DEPRECATED
		);

		$this->addRule($data, 'plural');

		return $this;
	}

	/**
	 * Adds a singularisation rule.
	 *
	 * @param   mixed  $data  A string or an array of regex rules to add.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use Doctrine\Common\Inflector\Inflector::rules() instead.
	 */
	public function addSingulariseRule($data)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::rules() instead.',
				__METHOD__,
				DoctrineInflector::class
			),
			E_USER_DEPRECATED
		);

		$this->addRule($data, 'singular');

		return $this;
	}

	/**
	 * Gets an instance of the Inflector singleton.
	 *
	 * @param   boolean  $new  If true (default is false), returns a new instance regardless if one exists. This argument is mainly used for testing.
	 *
	 * @return  static
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use static methods without a class instance instead.
	 */
	public static function getInstance($new = false)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		if ($new)
		{
			return new static;
		}

		if (!\is_object(self::$instance))
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
		return \in_array($word, self::$countable['rules']);
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
		return $this->toPlural($this->toSingular($word)) === $word;
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
		return $this->toSingular($word) === $word;
	}

	/**
	 * Converts a word into its plural form.
	 *
	 * @param   string  $word  The singular word to pluralise.
	 *
	 * @return  string  The word in plural form.
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use Doctrine\Common\Inflector\Inflector::pluralize() instead.
	 */
	public function toPlural($word)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::pluralize() instead.',
				__METHOD__,
				DoctrineInflector::class
			),
			E_USER_DEPRECATED
		);

		return static::pluralize($word);
	}

	/**
	 * Converts a word into its singular form.
	 *
	 * @param   string  $word  The plural word to singularise.
	 *
	 * @return  string  The word in singular form.
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use Doctrine\Common\Inflector\Inflector::singularize() instead.
	 */
	public function toSingular($word)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::singularize() instead.',
				__METHOD__,
				DoctrineInflector::class
			),
			E_USER_DEPRECATED
		);

		return static::singularize($word);
	}
}
