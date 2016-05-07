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
 * Stemmer base class for the Finder indexer package.
 *
 * @since  2.5
 */
abstract class FinderIndexerStemmer
{
	/**
	 * An internal cache of stemmed tokens.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $cache = array();

	/**
	 * Method to get a stemmer, creating it if necessary.
	 *
	 * @param   string  $adapter  The type of stemmer to load.
	 *
	 * @return  FinderIndexerStemmer  A FinderIndexerStemmer instance.
	 *
	 * @since   2.5
	 * @throws  Exception on invalid stemmer.
	 */
	public static function getInstance($adapter)
	{
		static $instances;

		// Only create one stemmer for each adapter.
		if (isset($instances[$adapter]))
		{
			return $instances[$adapter];
		}

		// Create an array of instances if necessary.
		if (!is_array($instances))
		{
			$instances = array();
		}

		// Setup the adapter for the stemmer.
		$adapter = JFilterInput::getInstance()->clean($adapter, 'cmd');
		$path = __DIR__ . '/stemmer/' . $adapter . '.php';
		$class = 'FinderIndexerStemmer' . ucfirst($adapter);

		// Check if a stemmer exists for the adapter.
		if (file_exists($path))
		{
			// Instantiate the stemmer.
			include_once $path;
			$instances[$adapter] = new $class;
		}
		else
		{
			// Throw invalid adapter exception.
			throw new Exception(JText::sprintf('COM_FINDER_INDEXER_INVALID_STEMMER', $adapter));
		}

		return $instances[$adapter];
	}

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
	abstract public function stem($token, $lang);
}
