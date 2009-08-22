<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
jimport('joomla.database.query');

class modRelatedItemsHelper
{
	/**
	 * The keywords from the Main Article
	 *
	 * @access public
	 * @var string array
	 */
	static $_mainArticleKeywords = null;
	static $_mainArticleAlias = null;
	static $_mainArticleAuthor = null;
	static $_mainArticleCategory = null;
	static $_authorTag = 'authid::';
	static $_aliasTag = 'alias::';
	static $_categoryTag = 'catid::';

	function getList($params)
	{
		global $mainframe;

		$db					=& JFactory::getDBO();
		$user				=& JFactory::getUser();
		$groups				= $user->authorisedLevels();

		$showDate			= $params->get('showDate', 'none');
		$showLimit			= intval($params->get('count', 5));
		$showCount 			= $params->get('showMatchCount', 0);
		$showMatchList 		= $params->get('showMatchList', 0);
		$orderBy			= $params->get('ordering', 'alpha');
		$catid				= trim($params->get('catid'));
		$matchAuthor			= trim($params->get('matchAuthor', 0));
		$matchAuthorAlias	= trim($params->get('matchAuthorAlias', 0));
		$matchAuthorAliasCondition = '';
		$matchCategory		= $params->get('matchCategory');
		$minimumMatches		= (int) $params->get('minimumMatches', 1);

		$showTooltip		= $params->get('showTooltip', 1);
		$tooltipLimit		= (int) $params->get('maxChars', 250);

		$ignoreKeywords 	= $params->get('ignoreKeywords', '');
		$ignoreAllKeywords	= $params->get('ignoreAllKeywords', 0);

		$nullDate			= $db->getNullDate();

		$date =& JFactory::getDate();
		$now  = $date->toMySQL();

		$related			= array();
		$matching_keywords 	= array();
		$metakey = '';
		$temp				= JRequest::getString('id');
		$temp				= explode(':', $temp);
		$id					= $temp[0];
		$articleView = 'false'; // indicates whether the current view is an article

		if (self::isArticle())  //only show for article pages
		{
			$articleView = 'true';
			// select the meta keywords and author info from the item
			$articleQuery = new JQuery();
			$articleQuery->select(array('a.metakey', 'a.catid', 'a.created_by', 'a.created_by_alias'));
			$articleQuery->select(' CASE WHEN a.catid = 0 THEN "Uncategorized" ELSE cc.title END as category_title');
			$articleQuery->select('u.name as author');
			$articleQuery->from('#__content AS a');
			$articleQuery->join('LEFT', '#__categories AS cc ON cc.id = a.catid');
			$articleQuery->join('LEFT', '#__users AS u ON u.id = a.created_by');
			$articleQuery->where('a.id = ' . (int) $id);
			$articleQuery->where('a.access IN (' . implode('', $groups) . ')');

			$db->setQuery($articleQuery);
			$mainArticle = $db->loadObject();
			$metakey = trim($mainArticle->metakey);

			if (($metakey) || 	// do the query if there are keywords
			($matchAuthor) || 						 	// or if the author match is on
			(($matchAuthorAlias) && ($mainArticle->created_by_alias)) ||	// or if the alias match is on and an alias
			($matchCategory))	// or if the match category parameter is yes
			{
				$query = new JQuery();
				// explode the meta keys on a comma
				$rawKeys = explode(',', $metakey);

				// get array of keywords to ignore
				$ignoreKeywordArray = array();
				if ($ignoreKeywords) {
					$ignoreKeywordArray =
					self::_cleanKeywordList($ignoreKeywords);
				}

				// put only good keys in $keys array
				// good = non-blank and not in ignore list
				$keys = array();
				foreach ($rawKeys as $key) {
					$key = trim($key);
					if (($key) && !(in_array(JString::strtoupper($key), $ignoreKeywordArray))) {
						$keys[] = $key;
					}
				}

				// save main article attributes in static variables

				self::$_mainArticleAlias = $mainArticle->created_by_alias;
				self::$_mainArticleAuthor = $mainArticle->author;
				if ($mainArticle->catid == 0) {
					self::$_mainArticleCategory = JText::_('Uncategorized');
				}
				else {
					self::$_mainArticleCategory = $mainArticle->category_title;
				}
				$likes = array ();

				// create likes array for query -- only if we are not ignoring all keywords
				// if we are ignoring all keywords, $likes is empty
				if (!$ignoreAllKeywords) {
					foreach ($keys as $key) {
						$likes[] = "'". $db->getEscaped($key) . "'"; // surround with quotes for SQL IN compare
					}
				}
				if ($matchAuthor) {
					$likes[] = "'" . self::$_authorTag . $mainArticle->created_by . "'";
				}
				if ($matchAuthorAlias && self::$_mainArticleAlias) {
					$likes[] = "'" . self::$_aliasTag . $mainArticle->created_by_alias . "'";
				}
				if ($matchCategory) {
					$likes[] = "'" . self::$_categoryTag . $mainArticle->catid . "'";
				}
				self::$_mainArticleKeywords = $keys;

				$keywordList = implode(',', $likes);
				if ($keywordList)
				{
					$query->innerJoin('#__content_keyword_article_map AS m ON a.id = m.article_id');
					$keywordSelection = 'm.keyword IN ('.$keywordList.')';
					$metakeySelection = 'a.metakey';
				}
				else // don't do the query if there is nothing to match
				{
					return $related;
				}

				// get the ordering for the query
				if ($showDate == 'modify') {
					$dateSelected = 'a.modified as date';
					$dateOrderby = 'a.modified';
				} else {
					$dateSelected = 'a.created as date';
					$dateOrderby = 'a.created';
				}
				switch ($orderBy)
				{
					case 'alpha' :
						$sqlSort = 'a.title';
						break;

					case 'rdate' :
						$sqlSort = $dateOrderby . ' desc, a.title';
						break;

					case 'date' :
						$sqlSort = $dateOrderby . ', a.title';
						break;

					case 'bestmatch' :
						$sqlSort = 'COUNT(m.keyword) DESC, a.title';
						break;

					case 'article_order' :
						$sqlSort = 'a.ordering, a.title' ;
						break;

					default:
						$sqlSort = 'a.title';
				}

				// get category selection

				if ($catid) {
					$ids = str_replace('C', $mainArticle->catid, JString::strtoupper($catid));
					$ids = explode( ',', $ids);
					JArrayHelper::toInteger( $ids );
					$catCondition = 'a.catid IN (' . implode(',', $ids ) . ')';
				}

				if ($matchAuthor) {
					$matchAuthorCondition = ' OR a.created_by = ' . $db->Quote($mainArticle->created_by) . ' ';
				}

				if (($matchAuthorAlias) && ($mainArticle->created_by_alias)) {
					$matchAuthorAliasCondition = ' OR UPPER(a.created_by_alias) = ' . $db->Quote(JString::strtoupper($mainArticle->created_by_alias)) . ' ';
				}

				if ($matchCategory) {
					$matchCategoryCondition = ' OR a.catid = ' . $db->Quote($mainArticle->catid) . ' ';
				}


				// select other items based on the metakey field 'like' the keys found
				$query->select(array('a.id', 'a.title', 'a.introtext', $dateSelected ));
				$query->select(array('a.catid', 'cc.access AS cat_access'));
				$query->select(array('a.created_by','a.created_by_alias','u.name AS author'));
				$query->select(array('cc.published AS cat_state'));
				$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug');
				$query->select('CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug');
				$query->select('CASE WHEN a.catid = 0 THEN "Uncategorized" ELSE cc.title END as category_title');
				$query->select($metakeySelection);
				// add new columns to query for counting keyword matches
				$query->select("GROUP_CONCAT(DISTINCT m.keyword SEPARATOR ',') as keyword_list");
				$query->from('#__content AS a');
				$query->leftJoin('#__categories AS cc ON cc.id = a.catid');
				$query->leftJoin('#__users AS u ON u.id = a.created_by');
				$query->where('a.id != '.(int) $id);
				$query->where('a.state = 1');
				$query->where('a.access IN ('.implode(',', $groups).')');
				$query->where($keywordSelection);
				$query->where('( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )');
				$query->where('( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )');
				if ($catid) $query->where($catCondition);
				if ($sqlSort) $query->order($sqlSort);
				$query->select('COUNT(m.keyword) AS match_count');
				$query->group('a.id');
				if ($minimumMatches > 1) {
					$query->having('COUNT(m.keyword) >= ' . $minimumMatches);
				}
				// if not sorting by best match, we can limit the sql query to the count parameter
				$db->setQuery($query, 0, $showLimit);
				$temp = $db->loadObjectList();

				if (count($temp)) // we have at least one related article
				{
					$ii = 1;
					foreach ($temp as $row)
					{
						if (($row->cat_state == 1 || $row->cat_state == '')
						&& (in_array($row->cat_access, $groups) || $row->cat_access == ''))
						$row->route = JRoute::_(ContentRoute::article($row->slug, $row->catslug));
						{
							$row->route = JRoute::_(ContentRoute::article($row->slug, $row->catslug));
							// add processing for intro text tooltip
							if ($showTooltip) {
								// limit introtext to length if parameter set & it is needed
								$strippedText = strip_tags($row->introtext);
								if (($tooltipLimit > 0) && (strlen($strippedText) > $tooltipLimit)) {
									$row->introtext =
									htmlspecialchars(
									self::_getPreview($row->introtext, $tooltipLimit)) . ' ...';
								}
								else {
									$row->introtext = htmlspecialchars($row->introtext);
								}
							}
							$related[] = $row;
						}
						if ($ii++ >= $showLimit) { break; }	// need to check this in case we are using bestmatch sort
					}
				}
				unset ($temp);
			}
			return $related;
		}
	}

	/**
	 * This function returns the text up to the last space in the string.
	 * This is used to always break the introtext at a space (to avoid breaking in
	 * the middle of a special character, for example.
	 * @param $rawText
	 * @return string
	 */
	protected function _getUpToLastSpace($rawText)
	{
		$throwAway = strrchr($rawText, ' ');
		$endPosition = strlen($rawText) - strlen($throwAway);
		return substr($rawText, 0, $endPosition);
	}

	/**
	 * Function to extract first n chars of text, ignoring HTML tags.
	 * Text is broken at last space before max chars in stripped text
	 * @param $rawText full text with tags
	 * @param $maxLength max length
	 * @return unknown_type
	 */
	protected function _getPreview($rawText, $maxLength) {
		$strippedText = substr(strip_tags($rawText), 0, $maxLength);
		$strippedText = self::_getUpToLastSpace($strippedText);
		$j = 0; // counter in $rawText
		// find the position in $rawText corresponding to the end of $strippedText
		for ($i = 0; $i < strlen($strippedText); $i++) {
			// skip chars in $rawText that were stripped
			while (substr($strippedText,$i,1) != substr($rawText, $j,1)) {
				$j++;
			}
			$j++; // we found the next match. now increment to keep in synch with $i
		}
		return (substr($rawText, 0, $j)); // return up to this char
	}

	/**
	 * Function to clean up ignore_keywords parameter to remove extra spaces
	 * and illegal characters. Also converts to upper case to allow for
	 * case-insensitive comparisons.
	 * @param $rawList - one or more keywords with possible bad characters
	 * returns array() of clean keywords
	 *
	 */
	protected function _cleanKeywordList($rawList) {
		$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
		$after_clean = JString::str_ireplace($bad_characters, "", $rawList); // remove bad characters
		$keys = explode(',', $after_clean); // create array using commas as delimiter
		$clean_keys = array();
		foreach($keys as $key) {
			if(trim($key)) {  // ignore blank keywords
				$clean_keys[] = JString::strtoupper( trim($key) );
			}
		}
		return $clean_keys; // return array of clean, upper-case keyword phrases
	}

	/**
	 * Function to test whether we are in an article view.
	 *
	 * returns boolean True if current view is an article
	 */
	function isArticle() {
		$option				= JRequest::getCmd('option');
		$view				= JRequest::getCmd('view');
		$temp				= JRequest::getString('id');
		$temp				= explode(':', $temp);
		$id					= $temp[0];
		// return True if this is an article
		return ($option == 'com_content' && $view == 'article' && $id);
	}

	/**
	 * Function to create listByKeyword for keyword layout
	 * @param $articleList - list of matching articles
	 * @param $params - parameters for this instance of module
	 * returns array(array()) of keywords with each a list of articles matching each keyword
	 */
	function getListByKeyword($articleList, $params) {
		$outputArray = array(array());
		$matchAuthor		= trim($params->get('matchAuthor', 0));
		$matchAuthorAlias	= trim($params->get('matchAuthorAlias', 0));
		$matchCategory		= $params->get('matchCategory');

		foreach ($articleList as $item) // loop through articles
		{
			$keywordArray = self::getKeywordArray($item);
			foreach ($keywordArray as $matchWord) // loop through match list for the article
			{
				foreach (self::$_mainArticleKeywords as $nextKey) // loop through the key words for the main aritcle
				{
					// find main article match. this eliminates duplcates
					// based on upper and lower case
					if (trim(JString::strtoupper($nextKey)) == JString::strtoupper($matchWord))
					{
						$thisWord = trim($nextKey);
					}
				}
				if (($matchAuthorAlias) && (self::$_mainArticleAlias)
				&& (JString::strtoupper(self::$_mainArticleAlias) == JString::strtoupper($matchWord))) {
					$thisWord = self::$_mainArticleAlias;
				}
				else if (($matchAuthor) && (self::$_mainArticleAuthor == $matchWord)) {
					$thisWord = $item->author;
				}
				if (($matchCategory) && (self::$_mainArticleCategory == $matchWord)) {
					$thisWord = $item->category_title;
				}

				$outputArray[$thisWord][] = $item;
				$thisWord = '';
			}
		}
		ksort($outputArray);  // sort keywords alphabetically
		return $outputArray;
	}

	/**
	 * Get keyword list as an array, replacing author and category tags with values
	 * @param $item -- article object
	 * @return array of keywords with tags converted to values
	 */
	static function getKeywordArray($item) {
		$keywordSearch = array(self::$_categoryTag . $item->catid, self::$_authorTag . $item->created_by,
				self::$_aliasTag . $item->created_by_alias);
		$keywordReplace = array(self::$_mainArticleCategory, self::$_mainArticleAuthor,
		self::$_mainArticleAlias);
		$keywordList = str_replace($keywordSearch, $keywordReplace, $item->keyword_list);
		$returnArray = explode(',',$keywordList);
		natcasesort($returnArray);
		return $returnArray;
	}
}
