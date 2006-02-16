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

$mainframe->registerEvent( 'onSearch', 'botSearchNewsfeedslinks' );
$mainframe->registerEvent( 'onSearchAreas', 'botSearchNewfeedAreas' );

/**
 * @return array An array of search areas
 */
function &botSearchNewfeedAreas() {
	static $areas = array(
		'newsfeeds' => 'Newsfeeds'
	);
	return $areas;
}

/**
* Contacts Search method
*
* The sql must return the following fields that are used in a common display
* routine: href, title, section, created, text, browsernav
* @param string Target search string
* @param string mathcing option, exact|any|all
* @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
*/
function botSearchNewsfeedslinks( $text, $phrase='', $ordering='', $areas=null ) 
{
	global $mainframe, $my;
	
	$database =& $mainframe->getDBO();

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( botSearchCategoryAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
 	$plugin =& JPluginHelper::getPlugin('search', 'newsfeeds.searchbot'); 
 	$pluginParams = new JParameter( $plugin->params );

	$limit = $pluginParams->def( 'search_limit', 50 );

	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	$wheres = array();
	switch ($phrase) {
		case 'exact':
			$wheres2 	= array();
			$wheres2[] 	= "LOWER(a.name) LIKE '%$text%'";
			$wheres2[] 	= "LOWER(a.link) LIKE '%$text%'";
			$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
			break;

		case 'all':
		case 'any':
		default:
			$words 	= explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word) {
				$wheres2 	= array();
		  		$wheres2[] 	= "LOWER(a.name) LIKE '%$word%'";
				$wheres2[] 	= "LOWER(a.link) LIKE '%$word%'";
				$wheres[] 	= implode( ' OR ', $wheres2 );
			}
			$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
			break;
	}

	switch ( $ordering ) {
		case 'alpha':
			$order = 'a.name ASC';
			break;

		case 'category':
			$order = 'b.title ASC, a.name ASC';
			break;

		case 'oldest':
		case 'popular':
		case 'newest':
		default:
			$order = 'a.name ASC';
	}

	$searchNewsfeeds = JText::_( 'Newsfeeds' );

	$query = "SELECT a.name AS title,"
	. "\n '' AS created,"
	. "\n a.link AS text,"
	. "\n CONCAT_WS( ' / ','". $searchNewsfeeds ."', b.title )AS section,"
	. "\n CONCAT( 'index.php?option=com_newsfeeds&task=view&feedid=', a.id ) AS href,"
	. "\n '1' AS browsernav"
	. "\n FROM #__newsfeeds AS a"
	. "\n INNER JOIN #__categories AS b ON b.id = a.catid"
	. "\n WHERE ( $where )"
	. "\n AND a.published = 1"
	. "\n AND b.published = 1"
	. "\n AND b.access <= $my->gid"
	. "\n ORDER BY $order"
	;
	$database->setQuery( $query, 0, $limit );
	$rows = $database->loadObjectList();

	return $rows;
}
?>