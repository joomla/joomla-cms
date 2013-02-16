<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Search component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_search
 */
class SearchHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		// Not required.
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$assetName = 'com_search';

		$actions = JAccess::getActions($assetName);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	static function santiseSearchWord(&$searchword, $searchphrase)
	{
		$ignored = false;

		$lang = JFactory::getLanguage();

		$tag			= $lang->getTag();
		$search_ignore	= $lang->getIgnoredSearchWords();

		// Deprecated in 1.6 use $lang->getIgnoredSearchWords instead
		$ignoreFile		= $lang->getLanguagePath() . '/' . $tag . '/' . $tag.'.ignore.php';
		if (file_exists($ignoreFile)) {
			include $ignoreFile;
		}

		// check for words to ignore
		$aterms = explode(' ', JString::strtolower($searchword));

		// first case is single ignored word
		if (count($aterms) == 1 && in_array(JString::strtolower($searchword), $search_ignore)) {
			$ignored = true;
		}

		// filter out search terms that are too small
		$lower_limit = $lang->getLowerLimitSearchWord();
		foreach($aterms as $aterm) {
			if (JString::strlen($aterm) < $lower_limit) {
				$search_ignore[] = $aterm;
			}
		}

		// next is to remove ignored words from type 'all' or 'any' (not exact) searches with multiple words
		if (count($aterms) > 1 && $searchphrase != 'exact') {
			$pruned = array_diff($aterms, $search_ignore);
			$searchword = implode(' ', $pruned);
		}

		return $ignored;
	}

	static function limitSearchWord(&$searchword)
	{
		$restriction = false;

		$lang = JFactory::getLanguage();

		// limit searchword to a maximum of characters
		$upper_limit = $lang->getUpperLimitSearchWord();
		if (JString::strlen($searchword) > $upper_limit) {
			$searchword		= JString::substr($searchword, 0, $upper_limit - 1);
			$restriction	= true;
		}

		// searchword must contain a minimum of characters
		if ($searchword && JString::strlen($searchword) < $lang->getLowerLimitSearchWord()) {
			$searchword		= '';
			$restriction	= true;
		}

		return $restriction;
	}

	static function logSearch($search_term)
	{
		$db = JFactory::getDbo();

		$params = JComponentHelper::getParams('com_search');
		$enable_log_searches = $params->get('enabled');

		$search_term = $db->escape(trim($search_term));

		if (@$enable_log_searches)
		{
			$db = JFactory::getDbo();
			$query = 'SELECT hits'
			. ' FROM #__core_log_searches'
			. ' WHERE LOWER(search_term) = "'.$search_term.'"'
			;
			$db->setQuery($query);
			$hits = intval($db->loadResult());
			if ($hits) {
				$query = 'UPDATE #__core_log_searches'
				. ' SET hits = (hits + 1)'
				. ' WHERE LOWER(search_term) = "'.$search_term.'"'
				;
				$db->setQuery($query);
				$db->query();
			} else {
				$query = 'INSERT INTO #__core_log_searches VALUES ("'.$search_term.'", 1)';
				$db->setQuery($query);
				$db->query();
			}
		}
	}

	/**
	 * Prepares results from search for display
	 *
	 * @param string The source string
	 * @param string The searchword to select around
	 * @return string
	 */
	public static function prepareSearchContent($text, $searchword)
	{
		// strips tags won't remove the actual jscript
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
		$text = preg_replace('/{.+?}/', '', $text);
		//$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);
		// replace line breaking tags with whitespace
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return self::_smartSubstr(strip_tags($text), $searchword);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML)
	 *
	 * @param object The object to check
	 * @param string Search words to check for
	 * @param array List of object variables to check against
	 * @returns boolean True if searchTerm is in object, false otherwise
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
		if (empty($fields)) return false;
		foreach($fields as $field) {
			if (!isset($object->$field)) continue;
			$text = $object->$field;
			foreach($searchRegex as $regex) {
				$text = preg_replace($regex, '', $text);
			}
			foreach($terms as $term) {
				if (JString::stristr($text, $term) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * returns substring of characters around a searchword
	 *
	 * @param string The source string
	 * @param int Number of chars to return
	 * @param string The searchword to select around
	 * @return string
	 */
	static function _smartSubstr($text, $searchword)
	{
		$lang = JFactory::getLanguage();
		$length = $lang->getSearchDisplayedCharactersNumber();
		$textlen = JString::strlen($text);
		$lsearchword = JString::strtolower($searchword);
		$wordfound = false;
		$pos = 0;
		while ($wordfound === false && $pos < $textlen) {
			if (($wordpos = @JString::strpos($text, ' ', $pos + $length)) !== false) {
				$chunk_size = $wordpos - $pos;
			} else {
				$chunk_size = $length;
			}
			$chunk = JString::substr($text, $pos, $chunk_size);
			$wordfound = JString::strpos(JString::strtolower($chunk), $lsearchword);
			if ($wordfound === false) {
				$pos += $chunk_size + 1;
			}
		}

		if ($wordfound !== false) {
			return (($pos > 0) ? '...&#160;' : '') . $chunk . '&#160;...';
		} else {
			if (($wordpos = @JString::strpos($text, ' ', $length)) !== false) {
				return JString::substr($text, 0, $wordpos) . '&#160;...';
			} else {
				return JString::substr($text, 0, $length);
			}
		}
	}
}
