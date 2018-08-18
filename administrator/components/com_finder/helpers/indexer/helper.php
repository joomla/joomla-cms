<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Router;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

JLoader::register('FinderIndexerLanguage', __DIR__ . '/language.php');
JLoader::register('FinderIndexerParser', __DIR__ . '/parser.php');
JLoader::register('FinderIndexerToken', __DIR__ . '/token.php');

/**
 * Helper class for the Finder indexer package.
 *
 * @since  2.5
 */
class FinderIndexerHelper
{
	/**
	 * Method to parse input into plain text.
	 *
	 * @param   string  $input   The raw input.
	 * @param   string  $format  The format of the input. [optional]
	 *
	 * @return  string  The parsed input.
	 *
	 * @since   2.5
	 * @throws  Exception on invalid parser.
	 */
	public static function parse($input, $format = 'html')
	{
		// Get a parser for the specified format and parse the input.
		return FinderIndexerParser::getInstance($format)->parse($input);
	}

	/**
	 * Method to tokenize a text string.
	 *
	 * @param   string   $input   The input to tokenize.
	 * @param   string   $lang    The language of the input.
	 * @param   boolean  $phrase  Flag to indicate whether input could be a phrase. [optional]
	 *
	 * @return  array  An array of FinderIndexerToken objects.
	 *
	 * @since   2.5
	 */
	public static function tokenize($input, $lang, $phrase = false)
	{
		static $cache;
		$store = md5($input . '::' . $lang . '::' . $phrase);

		// Check if the string has been tokenized already.
		if (isset($cache[$store]))
		{
			return $cache[$store];
		}

		$language = FinderIndexerLanguage::getInstance($lang);
		$tokens = array();
		$terms = $language->tokenise($input);
		$terms = array_filter($terms);

		/*
		 * If we have to handle the input as a phrase, that means we don't
		 * tokenize the individual terms and we do not create the two and three
		 * term combinations. The phrase must contain more than one word!
		 */
		if ($phrase === true && count($terms) > 1)
		{
			// Create tokens from the phrase.
			$tokens[] = new FinderIndexerToken($terms, $language->language, $language->spacer);
		}
		else
		{
			// Create tokens from the terms.
			for ($i = 0, $n = count($terms); $i < $n; $i++)
			{
				$tokens[] = new FinderIndexerToken($terms[$i], $language->language);
			}

			// Create two and three word phrase tokens from the individual words.
			for ($i = 0, $n = count($tokens); $i < $n; $i++)
			{
				// Setup the phrase positions.
				$i2 = $i + 1;
				$i3 = $i + 2;

				// Create the two word phrase.
				if ($i2 < $n && isset($tokens[$i2]))
				{
					// Tokenize the two word phrase.
					$token = new FinderIndexerToken(array($tokens[$i]->term, $tokens[$i2]->term), $language->language, $language->spacer);
					$token->derived = true;

					// Add the token to the stack.
					$tokens[] = $token;
				}

				// Create the three word phrase.
				if ($i3 < $n && isset($tokens[$i3]))
				{
					// Tokenize the three word phrase.
					$token = new FinderIndexerToken(array($tokens[$i]->term, $tokens[$i2]->term, $tokens[$i3]->term), $language->language, $language->spacer);
					$token->derived = true;

					// Add the token to the stack.
					$tokens[] = $token;
				}
			}
		}

		$cache[$store] = $tokens;

		return $cache[$store];
	}

	/**
	 * Method to get the base word of a token.
	 *
	 * @param   string  $token  The token to stem.
	 * @param   string  $lang   The language of the token.
	 *
	 * @return  string  The root token.
	 *
	 * @since   2.5
	 */
	public static function stem($token, $lang)
	{
		$language = FinderIndexerLanguage::getInstance($lang);

		return $language->stem($token);
	}

	/**
	 * Method to add a content type to the database.
	 *
	 * @param   string  $title  The type of content. For example: PDF
	 * @param   string  $mime   The mime type of the content. For example: PDF [optional]
	 *
	 * @return  integer  The id of the content type.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function addContentType($title, $mime = null)
	{
		static $types;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Check if the types are loaded.
		if (empty($types))
		{
			// Build the query to get the types.
			$query->select('*')
				->from($db->quoteName('#__finder_types'));

			// Get the types.
			$db->setQuery($query);
			$types = $db->loadObjectList('title');
		}

		// Check if the type already exists.
		if (isset($types[$title]))
		{
			return (int) $types[$title]->id;
		}

		// Add the type.
		$query->clear()
			->insert($db->quoteName('#__finder_types'))
			->columns(array($db->quoteName('title'), $db->quoteName('mime')))
			->values($db->quote($title) . ', ' . $db->quote($mime));
		$db->setQuery($query);
		$db->execute();

		// Return the new id.
		return (int) $db->insertid();
	}

	/**
	 * Method to check if a token is common in a language.
	 *
	 * @param   string  $token  The token to test.
	 * @param   string  $lang   The language to reference.
	 *
	 * @return  boolean  True if common, false otherwise.
	 *
	 * @since   2.5
	 */
	public static function isCommon($token, $lang)
	{
		static $data;
		static $default;

		$langCode = $lang;

		// If language requested is wildcard, use the default language.
		if ($lang == '*')
		{
			$default = $default === null ? substr(self::getDefaultLanguage(), 0, 2) : $default;
			$langCode = $default;
		}

		// Load the common tokens for the language if necessary.
		if (!isset($data[$langCode]))
		{
			$data[$langCode] = self::getCommonWords($langCode);
		}

		// Check if the token is in the common array.
		return in_array($token, $data[$langCode], true);
	}

	/**
	 * Method to get an array of common terms for a language.
	 *
	 * @param   string  $lang  The language to use.
	 *
	 * @return  array  Array of common terms.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function getCommonWords($lang)
	{
		$db = JFactory::getDbo();

		// Create the query to load all the common terms for the language.
		$query = $db->getQuery(true)
			->select($db->quoteName('term'))
			->from($db->quoteName('#__finder_terms_common'))
			->where($db->quoteName('language') . ' = ' . $db->quote($lang));

		// Load all of the common terms for the language.
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Method to get the default language for the site.
	 *
	 * @return  string  The default language string.
	 *
	 * @since   2.5
	 */
	public static function getDefaultLanguage()
	{
		static $lang;

		// We need to go to com_languages to get the site default language, it's the best we can guess.
		if (empty($lang))
		{
			$lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		return $lang;
	}

	/**
	 * Method to parse a language/locale key and return a simple language string.
	 *
	 * @param   string  $lang  The language/locale key. For example: en-GB
	 *
	 * @return  string  The simple language string. For example: en
	 *
	 * @since   2.5
	 */
	public static function getPrimaryLanguage($lang)
	{
		static $data;

		// Only parse the identifier if necessary.
		if (!isset($data[$lang]))
		{
			if (is_callable(array('Locale', 'getPrimaryLanguage')))
			{
				// Get the language key using the Locale package.
				$data[$lang] = Locale::getPrimaryLanguage($lang);
			}
			else
			{
				// Get the language key using string position.
				$data[$lang] = StringHelper::substr($lang, 0, StringHelper::strpos($lang, '-'));
			}
		}

		return $data[$lang];
	}

	/**
	 * Method to get the path (SEF route) for a content item.
	 *
	 * @param   string  $url  The non-SEF route to the content item.
	 *
	 * @return  string  The path for the content item.
	 *
	 * @since   2.5
	 */
	public static function getContentPath($url)
	{
		static $router;

		// Only get the router once.
		if (!($router instanceof Router))
		{
			// Get and configure the site router.
			$router = Router::getInstance('site');
		}

		// Build the relative route.
		$uri = $router->build($url);
		$route = $uri->toString(array('path', 'query', 'fragment'));
		$route = str_replace(JUri::base(true) . '/', '', $route);

		return $route;
	}

	/**
	 * Method to get extra data for a content before being indexed. This is how
	 * we add Comments, Tags, Labels, etc. that should be available to Finder.
	 *
	 * @param   FinderIndexerResult  &$item  The item to index as a FinderIndexerResult object.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function getContentExtras(FinderIndexerResult &$item)
	{
		// Load the finder plugin group.
		JPluginHelper::importPlugin('finder');

		JFactory::getApplication()->triggerEvent('onPrepareFinderContent', array(&$item));

		return true;
	}

	/**
	 * Method to process content text using the onContentPrepare event trigger.
	 *
	 * @param   string               $text    The content to process.
	 * @param   Registry             $params  The parameters object. [optional]
	 * @param   FinderIndexerResult  $item    The item which get prepared. [optional]
	 *
	 * @return  string  The processed content.
	 *
	 * @since   2.5
	 */
	public static function prepareContent($text, $params = null, FinderIndexerResult $item = null)
	{
		static $loaded;

		// Load the content plugins if necessary.
		if (empty($loaded))
		{
			JPluginHelper::importPlugin('content');
			$loaded = true;
		}

		// Instantiate the parameter object if necessary.
		if (!($params instanceof Registry))
		{
			$registry = new Registry($params);
			$params = $registry;
		}

		// Create a mock content object.
		$content = JTable::getInstance('Content');
		$content->text = $text;

		if ($item)
		{
			$content->bind((array) $item);
			$content->bind($item->getElements());
		}

		if ($item && !empty($item->context))
		{
			$content->context = $item->context;
		}

		// Fire the onContentPrepare event.
		JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_finder.indexer', &$content, &$params, 0));

		return $content->text;
	}
}
