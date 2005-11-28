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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onSearch', 'botSearchCategories' );

/**
* Categories Search method
*
* The sql must return the following fields that are used in a common display
* routine: href, title, section, created, text, browsernav
* @param string Target search string
* @param string mathcing option, exact|any|all
* @param string ordering option, newest|oldest|popular|alpha|category
*/
function botSearchCategories( $text, $phrase='', $ordering='' ) {
	global $database, $my;

	// load mambot params info
	$query = "SELECT id"
	. "\n FROM #__mambots"
	. "\n WHERE element = 'categories.searchbot'"
	. "\n AND folder = 'search'"
	;
	$database->setQuery( $query );
	$id 	= $database->loadResult();
	$mambot = new mosMambot( $database );
	$mambot->load( $id );
	$botParams = new mosParameters( $mambot->params );

	$limit = $botParams->def( 'search_limit', 50 );

	$text = trim( $text );
	if ( $text == '' ) {
		return array();
	}

	switch ( $ordering ) {
		case 'alpha':
			$order = 'a.name ASC';
			break;

		case 'category':
		case 'popular':
		case 'newest':
		case 'oldest':
		default:
			$order = 'a.name DESC';
	}

	$query = "SELECT a.name AS title,"
	. "\n a.description AS text,"
	. "\n '' AS created,"
	. "\n '2' AS browsernav,"
	. "\n '' AS section,"
	. "\n '' AS href,"
	. "\n s.id AS secid, a.id AS catid,"
	. "\n m.id AS menuid, m.type AS menutype"
	. "\n FROM #__categories AS a"
	. "\n INNER JOIN #__sections AS s ON s.id = a.section"
	. "\n LEFT JOIN #__menu AS m ON m.componentid = a.id"
	. "\n WHERE ( a.name LIKE '%$text%'"
	. "\n OR a.title LIKE '%$text%'"
	. "\n OR a.description LIKE '%$text%' )"
	. "\n AND a.published = 1"
	. "\n AND a.access <= $my->gid"
	. "\n AND ( m.type = 'content_section' OR m.type = 'content_blog_section'"
	. "\n OR m.type = 'content_category' OR m.type = 'content_blog_category')"
	. "\n ORDER BY $order"
	;
	$database->setQuery( $query, 0, $limit );
	$rows = $database->loadObjectList();

	$count = count( $rows );
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $rows[$i]->menutype == 'content_blog_category' ) {
			$rows[$i]->href = 'index.php?option=com_content&task=blogcategory&id='. $rows[$i]->catid .'&Itemid='. $rows[$i]->menuid;
			$rows[$i]->section 	= JText::_( 'Category Blog' );
		} else {
			$rows[$i]->href = 'index.php?option=com_content&task=category&sectionid='. $rows[$i]->secid .'&id='. $rows[$i]->catid .'&Itemid='. $rows[$i]->menuid;
			$rows[$i]->section 	= JText::_( 'Category List' );
		}
	}

	return $rows;
}
?>