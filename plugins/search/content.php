<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'botSearchContent' );
$mainframe->registerEvent( 'onSearchAreas', 'botSearchContentAreas' );

/**
 * @return array An array of search areas
 */
function &botSearchContentAreas() {
	static $areas = array(
		'content' => 'Content'
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
function botSearchContent( $text, $phrase='', $ordering='', $areas=null ) 
{
	global $mainframe, $my;
	global $mosConfig_offset;
	
	$database =& $mainframe->getDBO();

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( botSearchContentAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('search', 'content'); 
 	$pluginParams = new JParameter( $plugin->params );

	$sContent 			= $pluginParams->get( 'search_content', 		1 );
	$sStatic 			= $pluginParams->get( 'search_static', 			1 );
	$sArchived 			= $pluginParams->get( 'search_archived', 		1 );
	$sStatic_nonmenu	= $pluginParams->get( 'search_static_nonmenu', 	1 );

	$limit 				= $pluginParams->def( 'search_limit', 		50 );

	$nullDate 	= $database->getNullDate();
	$now 		= date( 'Y-m-d H:i:s', time()+$mosConfig_offset*60*60 );

	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	$wheres = array();
	switch ($phrase) {
		case 'exact':
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
				$wheres2 	= array();
				$wheres2[] 	= "LOWER(a.title) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.introtext) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.`fulltext`) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.metakey) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.metadesc) LIKE '%$word%'";
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

	// search content items
	if ( $sContent ) {
		$query = "SELECT a.title AS title,"
		. "\n a.created AS created,"
		. "\n CONCAT(a.introtext, a.`fulltext`) AS text,"
		. "\n CONCAT_WS( '/', u.title, b.title ) AS section,"
		. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id ) AS href,"
		. "\n '2' AS browsernav"
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__categories AS b ON b.id=a.catid"
		. "\n INNER JOIN #__sections AS u ON u.id = a.sectionid"
		. "\n WHERE ( $where )"
		. "\n AND a.state = 1"
		. "\n AND u.published = 1"
		. "\n AND b.published = 1"
		. "\n AND a.access <= $my->gid"
		. "\n AND b.access <= $my->gid"
		. "\n AND u.access <= $my->gid"
		. "\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )"
		. "\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )"
		. "\n GROUP BY a.id"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query, 0, $limit );
		$list = $database->loadObjectList();

		$rows[] = $list;
	}

	// search static content
	if ( $sStatic ) {
		$query = "SELECT a.title AS title, a.created AS created,"
		. "\n a.introtext AS text,"
		. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id, '&Itemid=', m.id ) AS href,"
		. "\n '2' as browsernav, '". JText::_('Static Content') ."' AS section"
		. "\n FROM #__content AS a"
		. "\n LEFT JOIN #__menu AS m ON m.componentid = a.id"
		. "\n WHERE ($where)"
		. "\n AND a.state = 1"
		. "\n AND a.access <= $my->gid"
		. "\n AND m.type = 'content_typed'"
		. "\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )"
		. "\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )"
		. "\n ORDER BY ". ($morder ? $morder : $order)
		;
		$database->setQuery( $query, 0, $limit );
		$list2 = $database->loadObjectList();

		$rows[] = $list2;
	}

	// search archived content
	if ( $sArchived ) {
		$searchArchived = JText::_( 'Archived' );

		$query = "SELECT a.title AS title,"
		. "\n a.created AS created,"
		. "\n a.introtext AS text,"
		. "\n CONCAT_WS( '/', '". $searchArchived ." ', u.title, b.title ) AS section,"
		. "\n CONCAT('index.php?option=com_content&task=view&id=',a.id) AS href,"
		. "\n '2' AS browsernav"
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__categories AS b ON b.id=a.catid AND b.access <='$my->gid'"
		. "\n INNER JOIN #__sections AS u ON u.id = a.sectionid"
		. "\n WHERE ( $where )"
		. "\n AND a.state = -1"
		. "\n AND u.published = 1"
		. "\n AND b.published = 1"
		. "\n AND a.access <= $my->gid"
		. "\n AND b.access <= $my->gid"
		. "\n AND u.access <= $my->gid"
		. "\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )"
		. "\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query, 0, $limit );
		$list3 = $database->loadObjectList();

		$rows[] = $list3;
	}

	
	// search static content non linked to a menu
	if ( $sStatic_nonmenu ) {
		// collect ids of static content items linked to menu items
		// so they can be removed from query that follows
		$ids = null;
		if(count($list2)) {
			foreach($list2 as $static) {
				$ids[] = $static->id;
			}
			$ids = implode( '\',\'', $ids );
		}
		
		// search static content not connected to a menu
		$query = "SELECT a.title AS title, a.created AS created,"
		. "\n a.introtext AS text,"
		. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id ) AS href,"
		. "\n '2' as browsernav, '". JText::_('Static Content') ."' AS section,"
		. "\n a.id"
		. "\n FROM #__content AS a"
		. "\n WHERE ($where)"
		. "\n AND a.id NOT IN ( '$ids' )"
		. "\n AND a.state = 1"
		. "\n AND a.access <= $my->gid"
		. "\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )"
		. "\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query, 0, $limit );
		$list4 = $database->loadObjectList();

		$rows[] = $list4;
	}	
	
	$count = count( $rows );
	if ( $count > 1 ) {
		switch ( $count ) {
			case 2:
				$results = array_merge( $rows[0], $rows[1] );
				break;

			case 3:
				$results = array_merge( $rows[0], $rows[1], $rows[2] );
				break;

			case 4:
			default:
				$results = array_merge( $rows[0], $rows[1], $rows[2], $rows[3] );
				break;
		}

		return $results;
	} else if ( $count == 1 ) {
		return $rows[0];
	}
}
?>