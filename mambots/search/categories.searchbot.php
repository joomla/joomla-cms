<?php
/**
* @version $Id: categories.searchbot.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onSearch', 'botSearchCategories' );
$_MAMBOTS->registerFunction( 'onSearchAreas', 'botSearchCategoryAreas' );

$GLOBALS['_SEARCH_CATEGORY_AREAS'] = array(
	'categories' => 'Categories'
);

/**
* @return array An array of search areas
*/
function &botSearchCategoryAreas() {
	return $GLOBALS['_SEARCH_CATEGORY_AREAS'];
}

/**
 * Categories Search method
 *
 * The sql must return the following fields that are used in a common display
 * routine: href, title, section, created, text, browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
 */
function botSearchCategories( $text, $phrase='', $ordering='', $areas=null ) {
	global $database, $my, $_LANG;

	if ( is_array( $areas ) ) {
		if ( !array_intersect( $areas, array_keys( $GLOBALS['_SEARCH_CATEGORY_AREAS'] ) ) ) {
			return array();
		}
	}

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
	. "\n s.id AS secid, a.id AS catid,"
	. "\n m.id AS menuid, m.type AS menutype"
	. "\n FROM #__categories AS a"
	. "\n INNER JOIN #__sections AS s ON s.id = a.section"
	. "\n LEFT JOIN #__menu AS m ON m.componentid = a.id"
	. "\n WHERE ( a.name LIKE '%$text%'"
	. "\n OR a.title LIKE '%$text%'"
	. "\n OR a.description LIKE '%$text%' )"
	. "\n AND a.published = '1'"
	. "\n AND a.access <= '$my->gid'"
	. "\n AND ( m.type = 'content_category' OR m.type = 'content_blog_category' )"
	. "\n AND m.published = '1'"
	. "\n ORDER BY $order"
	;
	$database->setQuery( $query );
	$rowsA = $database->loadObjectList();

	$count = count( $rowsA );
	for ( $i = 0; $i < $count; $i++ ) {
		switch ( $rowsA[$i]->menutype ) {
			case 'content_category':
				$rowsA[$i]->href 	= 'index.php?option=com_content&task=category&sectionid='. $rowsA[$i]->secid .'&id='. $rowsA[$i]->catid .'&Itemid='. $rowsA[$i]->menuid;
				$rowsA[$i]->section = 'Table - Content Category';
				break;

			case 'content_blog_category':
				$rowsA[$i]->href 	= 'index.php?option=com_content&task=blogcategory&id='. $rowsA[$i]->catid .'&Itemid='. $rowsA[$i]->menuid;
				$rowsA[$i]->section = 'Blog - Content Category';
				break;
		}
	}

	// extra handling for Categories available via Link - `Content Section`
	$query = "SELECT a.name AS title,"
	. "\n a.description AS text,"
	. "\n '' AS created,"
	. "\n '2' AS browsernav,"
	. "\n s.id AS secid, a.id AS catid,"
	. "\n m.id AS menuid, m.type AS menutype"
	. "\n FROM #__categories AS a"
	. "\n INNER JOIN #__sections AS s ON s.id = a.section"
	. "\n LEFT JOIN #__menu AS m ON m.componentid = s.id"
	. "\n WHERE ( a.name LIKE '%$text%'"
	. "\n OR a.title LIKE '%$text%'"
	. "\n OR a.description LIKE '%$text%' )"
	. "\n AND a.published = '1'"
	. "\n AND a.access <= '$my->gid'"
	. "\n AND m.type = 'content_section'"
	. "\n AND m.published = '1'"
	. "\n ORDER BY $order"
	;
	$database->setQuery( $query );
	$rowsB = $database->loadObjectList();

	$count = count( $rowsB );
	for ( $i = 0; $i < $count; $i++ ) {
		$rowsB[$i]->href 	= 'index.php?option=com_content&task=category&sectionid='. $rowsB[$i]->secid .'&id='. $rowsB[$i]->catid .'&Itemid='. $rowsB[$i]->menuid;
		$rowsB[$i]->section = 'Table - Content Category';
	}

	$rows = $rowsA + $rowsB;

	return $rows;
}
?>