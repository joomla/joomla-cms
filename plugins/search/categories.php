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

$mainframe->registerEvent( 'onSearch', 'plgSearchCategories' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchCategoryAreas' );

JPlugin::loadLanguage( 'plg_search_categories' );

/**
 * @return array An array of search areas
 */
function &plgSearchCategoryAreas() {
	static $areas = array(
		'categories' => 'Categories'
	);
	return $areas;
}

/**
 * Categories Search method
 *
 * The sql must return the following fields that are
 * used in a common display routine: href, title, section, created, text,
 * browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if restricted to areas, null if search all
 */
function plgSearchCategories( $text, $phrase='', $ordering='', $areas=null )
{
	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchCategoryAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('search', 'categories');
 	$pluginParams = new JParameter( $plugin->params );

	$limit = $pluginParams->def( 'search_limit', 50 );

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

	$text = $db->getEscaped($text);
	$query = 'SELECT a.name AS title,'
	. ' a.description AS text,'
	. ' "" AS created,'
	. ' "2" AS browsernav,'
	. ' "" AS section,'
	. ' "" AS href,'
	. ' s.id AS secid, a.id AS catid,'
	. ' m.id AS menuid, m.type AS menutype'
	. ' FROM #__categories AS a'
	. ' INNER JOIN #__sections AS s ON s.id = a.section'
	. ' LEFT JOIN #__menu AS m ON m.componentid = a.id'
	. ' WHERE ( a.name LIKE "%'.$text.'%"'
	. ' OR a.title LIKE "%'.$text.'%"'
	. ' OR a.description LIKE "%'.$text.'%" )'
	. ' AND a.published = 1'
	. ' AND s.published = 1'
	. ' AND a.access <= '.(int) $user->get('aid')
	. ' AND s.access <= '.(int) $user->get('aid')
	. ' AND ( m.type = "content_section" OR m.type = "content_blog_section"'
	. ' OR m.type = "content_category" OR m.type = "content_blog_category")'
	. ' GROUP BY a.id'
	. ' ORDER BY '. $order
	;
	$db->setQuery( $query, 0, $limit );
	$rows = $db->loadObjectList();

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
