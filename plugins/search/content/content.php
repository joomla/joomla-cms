<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

require_once(JPATH_SITE.'/components/com_content/router.php');

/**
 * Content Search plugin
 *
 * @package		Joomla
 * @subpackage	Search
 * @since 		1.6
 */
class plgSearchContent extends JPlugin
{
	/**
	 * @return array An array of search areas
	 */
	function onSearchAreas()
	{
		static $areas = array(
			'content' => 'Articles'
			);
			return $areas;
	}

	/**
	 * Content Search method
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= &JFactory::getDbo();
		$user	= &JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());

		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
		require_once JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php';

		$searchText = $text;
		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys(plgSearchContentAreas()))) {
				return array();
			}
		}

		// load plugin params info
		$plugin			= &JPluginHelper::getPlugin('search', 'content');
		$pluginParams	= new JParameter($plugin->params);

		$sContent 		= $pluginParams->get('search_content', 		1);
		$sUncategorised = $pluginParams->get('search_uncategorised', 	1);
		$sArchived 		= $pluginParams->get('search_archived', 		1);
		$limit 			= $pluginParams->def('search_limit', 		50);

		$nullDate 		= $db->getNullDate();
		$date = &JFactory::getDate();
		$now = $date->toMySQL();

		$text = trim($text);
		if ($text == '') {
			return array();
		}

		$wheres = array();
		switch ($phrase) {
			case 'exact':
				$text		= $db->Quote('%'.$db->getEscaped($text, true).'%', false);
				$wheres2 	= array();
				$wheres2[] 	= 'a.title LIKE '.$text;
				$wheres2[] 	= 'a.introtext LIKE '.$text;
				$wheres2[] 	= 'a.fulltext LIKE '.$text;
				$wheres2[] 	= 'a.metakey LIKE '.$text;
				$wheres2[] 	= 'a.metadesc LIKE '.$text;
				$where 		= '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word) {
					$word		= $db->Quote('%'.$db->getEscaped($word, true).'%', false);
					$wheres2 	= array();
					$wheres2[] 	= 'a.title LIKE '.$word;
					$wheres2[] 	= 'a.introtext LIKE '.$word;
					$wheres2[] 	= 'a.fulltext LIKE '.$word;
					$wheres2[] 	= 'a.metakey LIKE '.$word;
					$wheres2[] 	= 'a.metadesc LIKE '.$word;
					$wheres[] 	= implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		$morder = '';
		switch ($ordering) {
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'b.title ASC, a.title ASC';
				$morder = 'a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}

		$rows = array();

		// search articles
		if ($sContent && $limit > 0)
		{
			$query = new JQuery();
			$query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created, '
						  .'CONCAT(a.introtext, a.fulltext) AS text, b.title AS section, '
						  .'CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug, '
						  .'CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug, '
						  .'"2" AS browsernav');
			$query->from('#__content AS a');
			$query->innerJoin('#__categories AS b ON b.id=a.catid');
			$query->where('('. $where .')' . 'AND a.state=1 AND b.published = 1 AND a.access IN ('.$groups.') ' 
						 .'AND b.access IN ('.$groups.') '
						 .'AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).') '
						 .'AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')' );
			$query->group('a.id');
			$query->order($order);
			
			$db->setQuery($query, 0, $limit);
			$list = $db->loadObjectList();
			$limit -= count($list);

			if (isset($list))
			{
				foreach($list as $key => $item)
				{
					$list[$key]->href = ContentRoute::article($item->slug, $item->catslug);
				}
			}
			$rows[] = $list;
		}
	
		// search uncategorised content
		if ($sUncategorised && $limit > 0)
		{
			$query = new JQuery();
			$query->select('id, a.title AS title, a.created AS created, a.metadesc, a.metakey, '
						  .'CONCAT(a.introtext, a.fulltext) AS text, '
						  .'"2" as browsernav, "'. $db->Quote(JText::_('Uncategorised Content')) .'" AS section');
			$query->from('#__content AS a');
			$query->where('('. $where .') AND a.state = 1 AND a.access IN ('. $groups. ') AND a.catid=0 '
						 .'AND (a.publish_up = '. $db->Quote($nullDate) .' OR a.publish_up <= '. $db->Quote($now) .') '
						 .'AND (a.publish_down = '. $db->Quote($nullDate) .' OR a.publish_down >= '. $db->Quote($now) .')');
			$query->order(($morder ? $morder : $order));
					
			$db->setQuery($query, 0, $limit);
			$list2 = $db->loadObjectList();
			$limit -= count($list2);

			if (isset($list2))
			{
				foreach($list2 as $key => $item)
				{
					$list2[$key]->href = ContentRoute::article($item->id);
				}
			}

			$rows[] = $list2;
		}

		// search archived content
		if ($sArchived && $limit > 0)
		{
			$searchArchived = JText::_('Archived');
			
			$query = new JQuery();
			$query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created, '
						  .'CONCAT(a.introtext, a.fulltext) AS text, '
						  .'CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug, '
						  .'CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug, '
						  .'CONCAT_WS("/", b.title) AS section, "2" AS browsernav' );
			$query->from('#__content AS a');
			$query->innerJoin('#__categories AS b ON b.id=a.catid AND b.access IN ('. $groups .')');
			$query->where('('. $where .') AND a.state = -1 AND b.published = 1 AND a.access IN ('. $groups
				.') AND b.access IN ('. $groups .') '
				.'AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).') '
				.'AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')' );
			$query->order($order);

			
			$db->setQuery($query, 0, $limit);
			$list3 = $db->loadObjectList();

			if (isset($list3))
			{
				foreach($list3 as $key => $item)
				{
					$list3[$key]->href = ContentRoute::article($item->slug, $item->catslug);
				}
			}

			$rows[] = $list3;
		}

		$results = array();
		if (count($rows))
		{
			foreach($rows as $row)
			{
				$new_row = array();
				foreach($row AS $key => $article) {
					if (searchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey'))) {
						$new_row[] = $article;
					}
				}
				$results = array_merge($results, (array) $new_row);
			}
		}

		return $results;
	}
}