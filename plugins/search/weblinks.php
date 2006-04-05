<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'botSearchWeblinks' );
$mainframe->registerEvent( 'onSearchAreas', 'botSearchWeblinksAreas' );

/**
 * @return array An array of search areas
 */
function &botSearchWeblinksAreas() {
	static $areas = array(
		'weblinks' => 'Weblinks'
	);
	return $areas;
}

/**
* Weblink Search method
*
* The sql must return the following fields that are used in a common display
* routine: href, title, section, created, text, browsernav
* @param string Target search string
* @param string mathcing option, exact|any|all
* @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
 */
function botSearchWeblinks( $text, $phrase='', $ordering='', $areas=null ) 
{
	global $mainframe;
	
	$db =& $mainframe->getDBO();
	$user =& $mainframe->getUser();

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( botSearchWeblinksAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('search', 'weblinks'); 
 	$pluginParams = new JParameter( $plugin->params );

	$limit = $pluginParams->def( 'search_limit', 50 );

	$text = trim( $text );
	if ($text == '') {
		return array();
	}
	$section 	= JText::_( 'Web Links' );

	$wheres 	= array();
	switch ($phrase) {
		case 'exact':
			$wheres2 	= array();
			$wheres2[] 	= "LOWER(a.url) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.description) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.title) LIKE '%$text%'";
			$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
			break;

		case 'all':
		case 'any':
		default:
			$words 	= explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word) {
				$wheres2 	= array();
		  		$wheres2[] 	= "LOWER(a.url) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.description) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.title) LIKE '%$word%'";
				$wheres[] 	= implode( ' OR ', $wheres2 );
			}
			$where 	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
			break;
	}

	switch ( $ordering ) {
		case 'oldest':
			$order = 'a.date ASC';
			break;

		case 'popular':
			$order = 'a.hits DESC';
			break;

		case 'alpha':
			$order = 'a.title ASC';
			break;

		case 'category':
			$order = 'b.title ASC, a.title ASC';
			break;

		case 'newest':
		default:
			$order = 'a.date DESC';
	}

	$query = "SELECT a.title AS title,"
	. "\n a.description AS text,"
	. "\n a.date AS created,"
	. "\n CONCAT_WS( ' / ', '$section', b.title ) AS section,"
	. "\n '1' AS browsernav,"
	. "\n a.url AS href"
	. "\n FROM #__weblinks AS a"
	. "\n INNER JOIN #__categories AS b ON b.id = a.catid"
	. "\n WHERE ($where)"
	. "\n AND a.published = 1"
	. "\n AND b.published = 1"
	. "\n AND b.access <= " .$user->get( 'gid' )
	. "\n ORDER BY $order"
	;
	$db->setQuery( $query, 0, $limit );
	$rows = $db->loadObjectList();

	return $rows;
}
?>