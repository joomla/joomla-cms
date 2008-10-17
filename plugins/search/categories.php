<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
function &plgSearchCategoryAreas()
{
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

	require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

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

	$text	= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
	$query	= 'SELECT a.title, a.description AS text, "" AS created,'
	. ' "2" AS browsernav,'
	. ' s.id AS secid, a.id AS catid,'
	. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug'
	. ' FROM #__categories AS a'
	. ' INNER JOIN #__sections AS s ON s.id = a.section'
	. ' WHERE ( a.name LIKE '.$text
	. ' OR a.title LIKE '.$text
	. ' OR a.description LIKE '.$text.' )'
	. ' AND a.published = 1'
	. ' AND s.published = 1'
	. ' AND a.access <= '.(int) $user->get('aid')
	. ' AND s.access <= '.(int) $user->get('aid')
	. ' GROUP BY a.id'
	. ' ORDER BY '. $order
	;
	$db->setQuery( $query, 0, $limit );
	$rows = $db->loadObjectList();

	$count = count( $rows );
	for ( $i = 0; $i < $count; $i++ ) {
		$rows[$i]->href = ContentHelperRoute::getCategoryRoute($rows[$i]->slug, $rows[$i]->secid);
		$rows[$i]->section 	= JText::_( 'Category' );
	}

	return $rows;
}