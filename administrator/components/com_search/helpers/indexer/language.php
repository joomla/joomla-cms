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
 * Finder Indexer Language class.
 *
 * @since  4.0
 */
class SearchIndexerLanguage
{
	/**
	 * List of words to not index.
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected $stopwords = array();

	/**
	 * FinderIndexerLanguage instances container.
	 *
	 * @var    array|FinderIndexerLanguage
	 * 
	 * @since  4.0
	 */
	protected static $instances = array();

	/**
	 * Returns the language specific FinderIndexerLanguage object
	 *
	 * @param   string   $language  The language to be used
	 * @param   boolean  $fallback  Fallback on a generalised indexer language class
	 *
	 * @return  FinderIndexerLanguage  A FinderIndexerLanguage object.
	 *
	 * @since   4.0
	 */
	public static function getInstance($language, $fallback = true)
	{
		if (isset(self::$instances[$language]))
		{
			return self::$instances[$language];
		}

		$class = str_replace('-', '_', $language) . 'Search';

		if (!class_exists($class))
		{
			$file = JPATH_BASE . '/language/' . $language . '/' . $language . '.search.php';

			if (file_exists($file))
			{
				JLoader::register($class, $file);
			}
		}

		if (class_exists($class))
		{
			self::$instances[$language] = new $class;

			return self::$instances[$language];
		}

		if ($fallback)
		{
			self::$instances[$language] = new self;

			return self::$instances[$language];
		}

		return false;
	}

	/**
	 * Get a list of stopwords to NOT index in the search
	 * 
	 * @param   boolean  $lowercase  Make all stopwords lowercase
	 * 
	 * @return  array|string
	 * @since   4.0
	 */
	public function getStopWords($lowercase = true)
	{
		if ($lowercase)
		{
			return array_map('strtolower', $this->stopwords);
		}

		return $this->stopwords;
	}

	/**
	 * Add a word to the list of stopwords for this indexer object
	 * 
	 * @param   string  $word  Word to add to the stopword list
	 * 
	 * @since   4.0
	 */
	public function addStopWord($word)
	{
		$this->stopwords[] = $word;
	}

	/**
	 * Remvoe a word from the list of stopwords for this indexer object
	 * 
	 * @param  string  $word  Word to remove from the stopword list
	 * 
	 * @since   4.0
	 */
	public function removeStopWord($word)
	{
		if (in_array($word, $this->stopwords))
		{
			unset($this->stopwords[array_search($word, $this->stopwords)]);
		}
	}

	/**
	 * Take a string and tokenize it into an array of single, lowercase words
	 * This method should be overriden by languages that don't use white space
	 * to separate words
	 * 
	 * @param  string  $string  String to tokenize
	 * 
	 * @return  array  List of words
	 * 
	 * @since   4.0
	 */
	public function tokenize($string)
	{
		$string = strtolower(html_entity_decode(utf8_decode($string)));
		$string = preg_replace('/&#?\w+;/', ' ', $string);
		$string = preg_replace('/\s+/', ' ', $string);
		$string = strip_tags($string);
		$string = preg_replace('/\W+/', ' ', $string);
		$words = preg_split('/\s+/', trim($string));

		return $words;
	}

	/**
	 * Try to create the stem of the word given
	 * This method should be overriden by each language with a language specific
	 * stemmer.
	 * 
	 * @param   string  $word  Word to stem
	 * 
	 * @return  string  Stemmed word
	 * 
	 * @since   4.0
	 */
	public function stem($word)
	{
		return $word;
	}
}