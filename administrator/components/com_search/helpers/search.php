<?php
/**
 * @version		$Id$
 * @package  Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package		Joomla.Administrator
 * @subpackage	Search
 */
class SearchHelper
{
	function santiseSearchWord(&$searchword, $searchphrase)
	{
		$ignored = false;

		$lang = &JFactory::getLanguage();

		$search_ignore	= array();
		$tag			= $lang->getTag();
		$ignoreFile		= $lang->getLanguagePath().DS.$tag.DS.$tag.'.ignore.php';
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
		foreach($aterms AS $aterm) {
			if (JString::strlen($aterm) < 3) {
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

	function limitSearchWord(&$searchword)
	{
		$restriction = false;

		// limit searchword to 20 characters
		if (JString::strlen($searchword) > 20) {
			$searchword 	= JString::substr($searchword, 0, 19);
			$restriction 	= true;
		}

		// searchword must contain a minimum of 3 characters
		if ($searchword && JString::strlen($searchword) < 3) {
			$searchword 	= '';
			$restriction 	= true;
		}

		return $restriction;
	}

	function logSearch($search_term)
	{
		$db = &JFactory::getDbo();

		$params = &JComponentHelper::getParams('com_search');
		$enable_log_searches = $params->get('enabled');

		$search_term = $db->getEscaped(trim($search_term));

		if (@$enable_log_searches)
		{
			$db = &JFactory::getDbo();
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
	 * @param int Number of chars to trim
	 * @param string The searchword to select around
	 * @return string
	 */
	function prepareSearchContent($text, $length = 200, $searchword)
	{
		// strips tags won't remove the actual jscript
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
		$text = preg_replace('/{.+?}/', '', $text);
		//$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);
		// replace line breaking tags with whitespace
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return SearchHelper::_smartSubstr(strip_tags($text), $length, $searchword);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML)
	 *
	 * @param object The object to check
	 * @param string Search words to check for
	 * @param array List of object variables to check against
	 * @returns boolean True if searchTerm is in object, false otherwise
	 */
	function checkNoHtml($object, $searchTerm, $fields) {
		$searchRegex = array(
				'#<script[^>]*>.*?</script>#si',
				'#<style[^>]*>.*?</style>#si',
				'#<!.*?(--|]])>#si',
				'#<[^>]*>#i'
				);
		$terms = explode(' ', $searchTerm);
		if (empty($fields)) return false;
		foreach($fields AS $field) {
			if (!isset($object->$field)) continue;
			$text = $object->$field;
			foreach($searchRegex As $regex) {
				$text = preg_replace($regex, '', $text);
			}
			foreach($terms AS $term) {
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
	function _smartSubstr($text, $length = 200, $searchword)
	{
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
			return (($pos > 0) ? '...&nbsp;' : '') . $chunk . '&nbsp;...';
		} else {
			if (($wordpos = @JString::strpos($text, ' ', $length)) !== false) {
				return JString::substr($text, 0, $wordpos) . '&nbsp;...';
			} else {
				return JString::substr($text, 0, $length);
			}
		}
	}
}
