<?php
/**
* @version $Id: content.searchbot.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onSearch', 'botSearchContent' );
$_MAMBOTS->registerFunction( 'onSearchAreas', 'botSearchContentAreas' );

$GLOBALS['_SEARCH_CONTENT_AREAS'] = array(
	'content' => 'Content'
);

/**
 * @return array An array of search areas
 */
function &botSearchContentAreas() {
	return $GLOBALS['_SEARCH_CONTENT_AREAS'];
}

/**
 * Content Search method
 *
 * The sql must return the following fields that are used in a common display
 * routine: href, title, section, created, text, browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
 */
function botSearchContent( $text, $phrase='', $ordering='', $areas=null ) {
	global $my, $database, $_LANG, $mainframe;
	global $mosConfig_abolute_path, $mosConfig_zero_date;

	if ( is_array( $areas ) ) {
		if ( !array_intersect( $areas, array_keys( $GLOBALS['_SEARCH_CONTENT_AREAS'] ) ) ) {
			return array();
		}
	}

	$_SESSION['searchword'] = $text;

	$now = $mainframe->getDateTime();

	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	$wheres = array();
	switch ($phrase) {
		case 'exact':
			$wheres2 = array();
			$wheres2[] = "LOWER(a.title) LIKE '%$text%'";
			$wheres2[] = "LOWER(a.introtext) LIKE '%$text%'";
			$wheres2[] = "LOWER(a.fulltext) LIKE '%$text%'";
			$wheres2[] = "LOWER(a.metakey) LIKE '%$text%'";
			$wheres2[] = "LOWER(a.metadesc) LIKE '%$text%'";
			$text2 = htmlentities($text);
			if($text2!=$text){
				$wheres2[] = "LOWER(a.title) LIKE '%$text2%'";
				$wheres2[] = "LOWER(a.introtext) LIKE '%$text2%'";
				$wheres2[] = "LOWER(a.fulltext) LIKE '%$text2%'";
				$wheres2[] = "LOWER(a.metakey) LIKE '%$text2%'";
				$wheres2[] = "LOWER(a.metadesc) LIKE '%$text2%'";
			}
			$where = '(' . implode( ') OR (', $wheres2 ) . ')';
			break;
		case 'all':
		case 'any':
		default:
			$words = explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word) {
				$wheres2 = array();
				$wheres2[] = "LOWER(a.title) LIKE '%$word%'";
				$wheres2[] = "LOWER(a.introtext) LIKE '%$word%'";
				$wheres2[] = "LOWER(a.fulltext) LIKE '%$word%'";
				$wheres2[] = "LOWER(a.metakey) LIKE '%$word%'";
				$wheres2[] = "LOWER(a.metadesc) LIKE '%$word%'";
				$word2 = htmlentities($word);
				if($word2!=$word){
					$wheres2[] = "LOWER(a.title) LIKE '%$word2%'";
					$wheres2[] = "LOWER(a.introtext) LIKE '%$word2%'";
					$wheres2[] = "LOWER(a.fulltext) LIKE '%$word2%'";
					$wheres2[] = "LOWER(a.metakey) LIKE '%$word2%'";
					$wheres2[] = "LOWER(a.metadesc) LIKE '%$word2%'";
				}
				$wheres[] = implode( ' OR ', $wheres2 );
			}
			$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
			break;
	}

	$morder = '';
	switch ($ordering) {
		case 'newest':
		default:
			$order = 'a.created DESC';
			break;
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
	}

	$sql = "SELECT a.title AS title,"
	. "\n a.created AS created,"
	. "\n CONCAT(a.introtext, a.fulltext) AS text,"
	. "\n CONCAT_WS( ' / ', u.title, b.title ) AS section,"
	. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id ) AS href,"
	. "\n '2' AS browsernav"
	. "\n FROM #__content AS a"
	. "\n INNER JOIN #__categories AS b ON b.id=a.catid AND b.access <= '$my->gid'"
	. "\n LEFT JOIN #__sections AS u ON u.id = a.sectionid"
	. "\n WHERE ( $where )"
	. "\n AND a.state = '1'"
	. "\n AND a.access <= '$my->gid'"
	. "\n AND u.published = '1'"
	. "\n AND b.published = '1'"
	. "\n AND ( publish_up = '$mosConfig_zero_date' OR publish_up <= '$now' )"
	. "\n AND ( publish_down = '$mosConfig_zero_date' OR publish_down >= '$now' )"
	. "\n ORDER BY $order";

	$database->setQuery( $sql );

	$list = $database->loadObjectList();

	// search typed content
	$database->setQuery( "SELECT a.title AS title, a.created AS created,"
	. "\n a.introtext AS text,"
	. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id, '&Itemid=', m.id ) AS href,"
	. "\n '2' as browsernav, 'Menu' AS section"
	. "\n FROM #__content AS a"
	. "\n LEFT JOIN #__menu AS m ON m.componentid = a.id"
	. "\n WHERE ( $where )"
	. "\n AND a.state = '1'"
	. "\n AND a.access <= '$my->gid'"
	. "\n AND m.type = 'content_typed'"
	. "\n AND ( publish_up = $mosConfig_zero_date OR publish_up <= '$now' )"
	. "\n AND ( publish_down = $mosConfig_zero_date OR publish_down >= '$now' )"
	. "\n ORDER BY " . ( $morder ? $morder : $order )
	);
	$list2 = $database->loadObjectList();

	// search archived content
	$database->setQuery( "SELECT a.title AS title,"
	. "\n a.created AS created,"
	. "\n a.introtext AS text,"
	. "\n CONCAT_WS( '/', 'Archived ', u.title, b.title ) AS section,"
	. "\n CONCAT('index.php?option=com_content&task=view&id=',a.id) AS href,"
	. "\n '2' AS browsernav"
	. "\n FROM #__content AS a"
	. "\n INNER JOIN #__categories AS b ON b.id=a.catid AND b.access <= '$my->gid'"
	. "\n LEFT JOIN #__sections AS u ON u.id = a.sectionid"
	. "\n WHERE ( $where )"
	. "\n AND a.state = '-1'"
	. "\n AND a.access <= '$my->gid'"
	. "\n AND ( publish_up = $mosConfig_zero_date OR publish_up <= '$now' )"
	. "\n AND ( publish_down = $mosConfig_zero_date OR publish_down >= '$now' )"
	. "\n ORDER BY $order"
	);
	$list3 = $database->loadObjectList();

	if (!is_array($list2)) {
		$list2 = array();
	}
	if (!is_array($list3)) {
		$list3 = array();
	}

	return array_merge( $list, $list2, $list3 );
}
?>