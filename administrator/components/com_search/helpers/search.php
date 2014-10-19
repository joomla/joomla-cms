<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_search
 * @since       1.5
 */
class SearchHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	The name of the active view.
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		// Not required.
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Get list of actions
		$result = JHelperContent::getActions('com_search');

		return $result;
	}

	public static function santiseSearchWord(&$searchword, $searchphrase)
	{
		$ignored = false;

		$lang          = JFactory::getLanguage();
		$tag           = $lang->getTag();
		$search_ignore = $lang->getIgnoredSearchWords();

		// Deprecated in 1.6 use $lang->getIgnoredSearchWords instead
		$ignoreFile = $lang->getLanguagePath() . '/' . $tag . '/' . $tag . '.ignore.php';

		if (file_exists($ignoreFile))
		{
			include $ignoreFile;
		}

		// Check for words to ignore
		$aterms = explode(' ', JString::strtolower($searchword));

		// First case is single ignored word
		if (count($aterms) == 1 && in_array(JString::strtolower($searchword), $search_ignore))
		{
			$ignored = true;
		}

		// Filter out search terms that are too small
		$lower_limit = $lang->getLowerLimitSearchWord();

		foreach ($aterms as $aterm)
		{
			if (JString::strlen($aterm) < $lower_limit)
			{
				$search_ignore[] = $aterm;
			}
		}

		// Next is to remove ignored words from type 'all' or 'any' (not exact) searches with multiple words
		if (count($aterms) > 1 && $searchphrase != 'exact')
		{
			$pruned     = array_diff($aterms, $search_ignore);
			$searchword = implode(' ', $pruned);
		}

		return $ignored;
	}

	/**
	 * @since  1.5
	 */
	public static function limitSearchWord(&$searchword)
	{
		$restriction = false;

		$lang = JFactory::getLanguage();

		// Limit searchword to a maximum of characters
		$upper_limit = $lang->getUpperLimitSearchWord();

		if (JString::strlen($searchword) > $upper_limit)
		{
			$searchword  = JString::substr($searchword, 0, $upper_limit - 1);
			$restriction = true;
		}

		// Searchword must contain a minimum of characters
		if ($searchword && JString::strlen($searchword) < $lang->getLowerLimitSearchWord())
		{
			$searchword  = '';
			$restriction = true;
		}

		return $restriction;
	}

	/**
	 * Logs a search term
	 *
	 * @param   string  $search_term  The term being searched
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use JSearchHelper::logSearch() instead
	 */
	public static function logSearch($search_term)
	{
		JLog::add(__METHOD__ . '() is deprecated, use JSearchHelper::logSearch() instead.', JLog::WARNING, 'deprecated');

		JSearchHelper::logSearch($search_term, 'com_search');
	}

	/**
	 * Prepares results from search for display
	 *
	 * @param   string  $text        The source string
	 * @param   string  $searchword  The searchword to select around
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function prepareSearchContent($text, $searchword)
	{
		// Strips tags won't remove the actual jscript
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
		$text = preg_replace('/{.+?}/', '', $text);

		// $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);

		// Replace line breaking tags with whitespace
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return self::_smartSubstr(strip_tags($text), $searchword);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML)
	 *
	 * @param   object  $object      The object to check
	 * @param   string  $searchTerm  Search words to check for
	 * @param   array   $fields      List of object variables to check against
	 *
	 * @return  boolean True if searchTerm is in object, false otherwise
	 */
	public static function checkNoHtml($object, $searchTerm, $fields)
	{
		$searchRegex = array(
			'#<script[^>]*>.*?</script>#si',
			'#<style[^>]*>.*?</style>#si',
			'#<!.*?(--|]])>#si',
			'#<[^>]*>#i'
		);
		$terms = explode(' ', $searchTerm);

		if (empty($fields))
		{
			return false;
		}

		foreach ($fields as $field)
		{
			if (!isset($object->$field))
			{
				continue;
			}

			$text = self::remove_accents($object->$field);

			foreach ($searchRegex as $regex)
			{
				$text = preg_replace($regex, '', $text);
			}

			foreach ($terms as $term)
			{
				$term = self::remove_accents($term);

				if (JString::stristr($text, $term) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Transliterates given text to ASCII
	 *
	 * @param   string  $str  String to remove accents from
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function remove_accents($str)
	{
		$str = JLanguageTransliterate::utf8_latin_to_ascii($str);

		//TODO: remove other prefixes as well?
		return preg_replace("/[\"'^]([a-z])/ui", '\1', $str);
	}

	/**
	 * returns substring of characters around a searchword
	 *
	 * @param   string   $text        The source string
	 * @param   integer  $searchword  Number of chars to return
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function _smartSubstr($text, $searchword)
	{
		$lang        = JFactory::getLanguage();
		$length      = $lang->getSearchDisplayedCharactersNumber();
		$ltext       = self::remove_accents($text);
		$textlen     = JString::strlen($ltext);
		$lsearchword = JString::strtolower(self::remove_accents($searchword));
		$wordfound   = false;
		$pos         = 0;

		while ($wordfound === false && $pos < $textlen)
		{
			if (($wordpos = @JString::strpos($ltext, ' ', $pos + $length)) !== false)
			{
				$chunk_size = $wordpos - $pos;
			}
			else
			{
				$chunk_size = $length;
			}

			$chunk     = JString::substr($ltext, $pos, $chunk_size);
			$wordfound = JString::strpos(JString::strtolower($chunk), $lsearchword);

			if ($wordfound === false)
			{
				$pos += $chunk_size + 1;
			}
		}

		if ($wordfound !== false)
		{
			return (($pos > 0) ? '...&#160;' : '') . JString::substr($text, $pos, $chunk_size) . '&#160;...';
		}
		else
		{
			if (($wordpos = @JString::strpos($text, ' ', $length)) !== false)
			{
				return JString::substr($text, 0, $wordpos) . '&#160;...';
			}
			else
			{
				return JString::substr($text, 0, $length);
			}
		}
	}
}
