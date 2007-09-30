<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'plgSearchContent' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchContentAreas' );

JPlugin::loadLanguage( 'plg_search_content' );

/**
 * @return array An array of search areas
 */
function &plgSearchContentAreas() {
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
function plgSearchContent( $text, $phrase='', $ordering='', $areas=null )
{
	global $mainframe;

	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchContentAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
 	$plugin			=& JPluginHelper::getPlugin('search', 'content');
 	$pluginParams	= new JParameter( $plugin->params );

	$sContent 		= $pluginParams->get( 'search_content', 		1 );
	$sUncategorised = $pluginParams->get( 'search_uncategorised', 	1 );
	$sArchived 		= $pluginParams->get( 'search_archived', 		1 );
	$limit 			= $pluginParams->def( 'search_limit', 		50 );

	$nullDate 		= $db->getNullDate();
	jimport('joomla.utilities.date');
	$date = new JDate();
	$now = $date->toMySQL();
	//$now 			= date( 'Y-m-d H:i:s', time()+$mainframe->getCfg('offset')*60*60 );

	$text = trim( $text );
	if ($text == '') {
		return array();
	}
	
	$wheres = array();
	switch ($phrase) {
		case 'exact':
			$text = $db->getEscaped($text);
			$wheres2 	= array();
			$wheres2[] 	= "LOWER(a.title) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.introtext) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.`fulltext`) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.metakey) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.metadesc) LIKE '%$text%'";
			$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
			break;

		case 'all':
		case 'any':
		default:
			$words = explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word) {
				$word = $db->getEscaped($word);
				$wheres2 	= array();
				$wheres2[] 	= 'LOWER(a.title) LIKE "%'.$word.'%"';
				$wheres2[] 	= 'LOWER(a.introtext) LIKE "%'.$word.'%"';
				$wheres2[] 	= 'LOWER(a.`fulltext`) LIKE "%'.$word.'%"';
				$wheres2[] 	= 'LOWER(a.metakey) LIKE "%'.$word.'%"';
				$wheres2[] 	= 'LOWER(a.metadesc) LIKE "%'.$word.'%"';
				$wheres[] 	= implode( ' OR ', $wheres2 );
			}
			$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
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
	if ( $sContent ) 
	{
		$query = 'SELECT a.title AS title,'
		. ' a.created AS created,'
		. ' CONCAT(a.introtext, a.`fulltext`) AS text,'
		. ' CONCAT_WS( "/", u.title, b.title ) AS section,'
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'
		. ' CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug,'
		. ' u.id AS sectionid,'
		. ' "2" AS browsernav'
		. ' FROM #__content AS a'
		. ' INNER JOIN #__categories AS b ON b.id=a.catid'
		. ' INNER JOIN #__sections AS u ON u.id = a.sectionid'
		. ' WHERE ( '.$where.' )'
		. ' AND a.state = 1'
		. ' AND u.published = 1'
		. ' AND b.published = 1'
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' AND b.access <= '.(int) $user->get( 'aid' )
		. ' AND u.access <= '.(int) $user->get( 'aid' )
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		. ' GROUP BY a.id'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$list = $db->loadObjectList();

		if(isset($list))
		{
			foreach($list as $key => $item)
			{
				$list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid);
			}
		}
		$rows[] = $list;
	}

	// search uncategorised content
	if ( $sUncategorised ) 
	{
		$query = 'SELECT id, a.title AS title, a.created AS created,'
		. ' a.introtext AS text,'
		. ' "2" as browsernav, "'. $db->Quote(JText::_('Uncategorised Content')) .'" AS section'
		. ' FROM #__content AS a'
		. ' WHERE ('.$where.')'
		. ' AND a.state = 1'
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' AND a.sectionid = 0'
		. ' AND a.catid = 0'
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		. ' ORDER BY '. ($morder ? $morder : $order)
		;
		$db->setQuery( $query, 0, $limit );
		$list2 = $db->loadObjectList();

		if(isset($list2))
		{
			foreach($list2 as $key => $item)
			{
				$list2[$key]->href = ContentHelperRoute::getArticleRoute($item->id);
			}
		}

		$rows[] = $list2;
	}

	// search archived content
	if ( $sArchived ) 
	{
		$searchArchived = JText::_( 'Archived' );

		$query = 'SELECT a.title AS title,'
		. ' a.created AS created,'
		. ' a.introtext AS text,'
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'
		. ' CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug,'
		. ' u.id AS sectionid,'
		. ' "2" AS browsernav'
		. ' FROM #__content AS a'
		. ' INNER JOIN #__categories AS b ON b.id=a.catid AND b.access <= ' .$user->get( 'gid' )
		. ' INNER JOIN #__sections AS u ON u.id = a.sectionid'
		. ' WHERE ( '.$where.' )'
		. ' AND a.state = -1'
		. ' AND u.published = 1'
		. ' AND b.published = 1'
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' AND b.access <= '.(int) $user->get( 'aid' )
		. ' AND u.access <= '.(int) $user->get( 'aid' )
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$list3 = $db->loadObjectList();

		if(isset($list3))
		{
			foreach($list3 as $key => $item)
			{
				$list3[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid);
			}
		}

		$rows[] = $list3;
	}


	$count = count( $rows );
	if ( $count > 1 ) {
		switch ( $count ) {
			case 2:
				$results = array_merge( (array) $rows[0], (array) $rows[1] );
				break;

			case 3:
				$results = array_merge( (array) $rows[0], (array) $rows[1], (array) $rows[2] );
				break;

			case 4:
			default:
				$results = array_merge( (array) $rows[0], (array) $rows[1], (array) $rows[2], (array) $rows[3] );
				break;
		}

		return $results;
	} else if ( $count == 1 ) {
		return $rows[0];
	}
}
?>
