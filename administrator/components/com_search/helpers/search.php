<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Search component helper.
 *
 * @since  1.5
 */
class SearchHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
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
	 * @deprecated  3.2  Use JHelperContent::getActions() instead.
	 */
	public static function getActions()
	{
		// Log usage of deprecated function.
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHelperContent::getActions() with new arguments order instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Get list of actions.
		return JHelperContent::getActions('com_search');
	}

	/**
	 * Sanitise search word.
	 *
	 * @param   string  &$searchword   Search word to be sanitised.
	 * @param   string  $searchphrase  Either 'all', 'any' or 'exact'.
	 *
	 * @return  boolean  True if search word needs to be sanitised.
	 */
	public static function santiseSearchWord(&$searchword, $searchphrase)
	{
		$ignored = false;

		$lang          = JFactory::getLanguage();
		$tag           = $lang->getTag();
		$search_ignore = $lang->getIgnoredSearchWords();

		// Deprecated in 1.6 use $lang->getIgnoredSearchWords instead.
		$ignoreFile = JLanguageHelper::getLanguagePath() . '/' . $tag . '/' . $tag . '.ignore.php';

		if (file_exists($ignoreFile))
		{
			include $ignoreFile;
		}

		// Check for words to ignore.
		$aterms = explode(' ', StringHelper::strtolower($searchword));

		// First case is single ignored word.
		if (count($aterms) == 1 && in_array(StringHelper::strtolower($searchword), $search_ignore))
		{
			$ignored = true;
		}

		// Filter out search terms that are too small.
		$lower_limit = $lang->getLowerLimitSearchWord();

		foreach ($aterms as $aterm)
		{
			if (StringHelper::strlen($aterm) < $lower_limit)
			{
				$search_ignore[] = $aterm;
			}
		}

		// Next is to remove ignored words from type 'all' or 'any' (not exact) searches with multiple words.
		if (count($aterms) > 1 && $searchphrase != 'exact')
		{
			$pruned     = array_diff($aterms, $search_ignore);
			$searchword = implode(' ', $pruned);
		}

		return $ignored;
	}

	/**
	 * Does search word need to be limited?
	 *
	 * @param   string  &$searchword  Search word to be checked.
	 *
	 * @return  boolean  True if search word should be limited; false otherwise.
	 *
	 * @since  1.5
	 */
	public static function limitSearchWord(&$searchword)
	{
		$restriction = false;

		$lang = JFactory::getLanguage();

		// Limit searchword to a maximum of characters.
		$upper_limit = $lang->getUpperLimitSearchWord();

		if (StringHelper::strlen($searchword) > $upper_limit)
		{
			$searchword  = StringHelper::substr($searchword, 0, $upper_limit - 1);
			$restriction = true;
		}

		// Searchword must contain a minimum of characters.
		if ($searchword && StringHelper::strlen($searchword) < $lang->getLowerLimitSearchWord())
		{
			$searchword  = '';
			$restriction = true;
		}

		return $restriction;
	}

	/**
	 * Logs a search term.
	 *
	 * @param   string  $searchTerm  The term being searched.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use \Joomla\CMS\Helper\SearchHelper::logSearch() instead.
	 */
	public static function logSearch($searchTerm)
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use \Joomla\CMS\Helper\SearchHelper::logSearch() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		\Joomla\CMS\Helper\SearchHelper::logSearch($searchTerm, 'com_search');
	}

	/**
	 * Prepares results from search for display.
	 *
	 * @param   string  $text        The source string.
	 * @param   string  $searchword  The searchword to select around.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function prepareSearchContent($text, $searchword)
	{
		// Strips tags won't remove the actual jscript.
		$text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
		$text = preg_replace('/{.+?}/', '', $text);

		// $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);

		// Replace line breaking tags with whitespace.
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return self::_smartSubstr(strip_tags($text), $searchword);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML).
	 *
	 * @param   object  $object      The object to check.
	 * @param   string  $searchTerm  Search words to check for.
	 * @param   array   $fields      List of object variables to check against.
	 *
	 * @return  boolean True if searchTerm is in object, false otherwise.
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

				if (StringHelper::stristr($text, $term) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Transliterates given text to ASCII.
	 *
	 * @param   string  $str  String to remove accents from.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function remove_accents($str)
	{
		$str = JLanguageTransliterate::utf8_latin_to_ascii($str);

		// @TODO: remove other prefixes as well?
		return preg_replace("/[\"'^]([a-z])/ui", '\1', $str);
	}

	/**
	 * Returns substring of characters around a searchword.
	 *
	 * @param   string   $text        The source string.
	 * @param   integer  $searchword  Number of chars to return.
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
		$textlen     = StringHelper::strlen($ltext);
		$lsearchword = StringHelper::strtolower(self::remove_accents($searchword));
		$wordfound   = false;
		$pos         = 0;
		$length      = $length > $textlen ? $textlen : $length;

		while ($wordfound === false && $pos + $length < $textlen)
		{
			if (($wordpos = @StringHelper::strpos($ltext, ' ', $pos + $length)) !== false)
			{
				$chunk_size = $wordpos - $pos;
			}
			else
			{
				$chunk_size = $length;
			}

			$chunk     = StringHelper::substr($ltext, $pos, $chunk_size);
			$wordfound = StringHelper::strpos(StringHelper::strtolower($chunk), $lsearchword);

			if ($wordfound === false)
			{
				$pos += $chunk_size + 1;
			}
		}

		if ($wordfound !== false)
		{
			// Check if original text is different length than searched text (changed by function self::remove_accents)
			// Displayed text only, adjust $chunk_size
			if ($pos === 0)
			{
				$iOriLen = StringHelper::strlen(StringHelper::substr($text, 0, $pos + $chunk_size));
				$iModLen = StringHelper::strlen(self::remove_accents(StringHelper::substr($text, 0, $pos + $chunk_size)));

				$chunk_size += $iOriLen - $iModLen;
			}
			else
			{
				$iOriSkippedLen = StringHelper::strlen(StringHelper::substr($text, 0, $pos));
				$iModSkippedLen = StringHelper::strlen(self::remove_accents(StringHelper::substr($text, 0, $pos)));

				// Adjust starting position $pos
				if ($iOriSkippedLen !== $iModSkippedLen)
				{
					$pos += $iOriSkippedLen - $iModSkippedLen;
				}

				$iOriReturnLen = StringHelper::strlen(StringHelper::substr($text, $pos, $chunk_size));
				$iModReturnLen = StringHelper::strlen(self::remove_accents(StringHelper::substr($text, $pos, $chunk_size)));

				if ($iOriReturnLen !== $iModReturnLen)
				{
					$chunk_size += $iOriReturnLen - $iModReturnLen;
				}
			}

			$sPre = $pos > 0 ? '...&#160;' : '';
			$sPost = ($pos + $chunk_size) >= StringHelper::strlen($text) ? '' : '&#160;...';

			return $sPre . StringHelper::substr($text, $pos, $chunk_size) . $sPost;
		}
		else
		{
			if (($mbtextlen = StringHelper::strlen($text)) < $length)
			{
				$length = $mbtextlen;
			}

			if (($wordpos = StringHelper::strpos($text, ' ', $length)) !== false)
			{
				return StringHelper::substr($text, 0, $wordpos) . '&#160;...';
			}
			else
			{
				return StringHelper::substr($text, 0, $length);
			}
		}
	}
}
