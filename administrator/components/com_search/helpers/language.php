<?php
/**
 * @package    Search.Language
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search language class.
 *
 * @since  4.0
 */
class SearchLanguage
{
	protected $stopwords = array();

	protected static $instances = array();

	/**
	 * 
	 * @param   string  $language  Language ISO code to load
	 */
	public static function getInstance($language)
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
				require_once $file;
			}
		}

		if (class_exists($class))
		{
			self::$instances[$language] = new $class;

			return self::$instances[$language];
		}

		return false;
	}

	public function getStopWords($lowercase = true)
	{
		if ($lowercase)
		{
			return array_map('strtolower', $this->stopwords);
		}

		return $this->stopwords;
	}

	public function addStopWord($word)
	{
		$this->stopwords[] = $word;
	}

	public function removeStopWord($word)
	{
		if (in_array($word, $this->stopwords))
		{
			unset($this->stopwords[array_search($word, $this->stopwords)]);
		}
	}

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

	public function stem($word)
	{
		return $word;
	}
}