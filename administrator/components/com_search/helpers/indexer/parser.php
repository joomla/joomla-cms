<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Parser base class for the Search indexer package.
 *
 * @since  2.5
 */
abstract class SearchIndexerParser
{
	protected $tokenizer;

	/**
	 * Method to get a parser, creating it if necessary.
	 *
	 * @param   string  $format  The type of parser to load.
	 *
	 * @return  SearchIndexerParser  A SearchIndexerParser instance.
	 *
	 * @since   2.5
	 * @throws  Exception on invalid parser.
	 */
	public static function getInstance($format)
	{
		static $instances;

		// Only create one parser for each format.
		if (isset($instances[$format]))
		{
			return $instances[$format];
		}

		// Create an array of instances if necessary.
		if (!is_array($instances))
		{
			$instances = array();
		}

		// Setup the adapter for the parser.
		$format = JFilterInput::getInstance()->clean($format, 'cmd');
		$path = __DIR__ . '/parser/' . $format . '.php';
		$class = 'SearchIndexerParser' . ucfirst($format);

		// Check if a parser exists for the format.
		if (!file_exists($path))
		{
			// Throw invalid format exception.
			throw new Exception(JText::sprintf('COM_FINDER_INDEXER_INVALID_PARSER', $format));
		}

		// Instantiate the parser.
		include_once $path;
		$instances[$format] = new $class;

		return $instances[$format];
	}

	public function setTokenizer($tokenizer)
	{
		$this->tokenizer = $tokenizer;
	}

	/**
	 * Method to parse input and extract the plain text. Because this method is
	 * called from both inside and outside the indexer, it needs to be able to
	 * batch out its parsing functionality to deal with the inefficiencies of
	 * regular expressions. We will parse recursively in 2KB chunks.
	 *
	 * @param   string  $input  The input to parse.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	public function parse($input)
	{
		$stopwords = $this->tokenizer->getStopWords();
		$words = $this->tokenizer->tokenize($input);
		$words = array_diff($words, $stopwords);
		$newindex = array_count_values($words);
		$result = array();
		foreach ($newindex as $word => $count)
		{
			$stemmed = $this->tokenizer->stem($word);
			if (!isset($result[$stemmed]))
			{
				$result[$stemmed] = 0;
			}
			$result[$stemmed] += $count;
		}

		return $result;
	}
}
