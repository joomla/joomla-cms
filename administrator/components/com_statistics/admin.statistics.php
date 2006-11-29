<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Statistics
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( JApplicationHelper::getPath( 'admin_html' ) );

switch (JRequest::getVar('task')) 
{
	case 'searches':
		showSearches();
		break;

	case 'searchesresults':
		showSearches( 1 );
		break;

	case 'resetStats':
		resetStats();
		break;

	default:
		showSearches( );
		break;
}

function showSearches( $showResults=null )
{
	global $mainframe, $option, $task;
	
	$db					=& JFactory::getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( "com_statistics.$task.filter_order", 		'filter_order', 	'hits' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_statistics.$task.filter_order_Dir",	'filter_order_Dir',	'' );
	$limit 				= $mainframe->getUserStateFromRequest( 'limit', 							'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart			= $mainframe->getUserStateFromRequest( "com_statistics.$task.limitstart", 			'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "com_statistics.$task.search", 				'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );
	$where				= array();
	
	if ($search) {
		$where[] = "LOWER( search_term ) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, hits DESC";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__core_log_searches"
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT *"
	. "\n FROM #__core_log_searches"
	. $where
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );

	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	JPluginHelper::importPlugin( 'search' );

	for ($i=0, $n = count($rows); $i < $n; $i++) {
		// determine if number of results for search item should be calculated
		// by default it is `off` as it is highly query intensive
		if ( $showResults ) {
			$results = $mainframe->triggerEvent( 'onSearch', array( $rows[$i]->search_term ) );

			$count = 0;
			for ($j = 0, $n2 = count( $results ); $j < $n2; $j++) {
				$count += count( $results[$j] );
			}

			$rows[$i]->returns = $count;
		} else {
			$rows[$i]->returns = null;
		}
	}

	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_statistics::showSearches( $rows, $pageNav, $lists, $task, $showResults );
}

function resetStats()
{
	global $mainframe;

	$db =& JFactory::getDBO();
	$op = JRequest::getVar( 'op' );

	switch ($op) {
		case 'bod':
			// get the total number of records
			$query = "SELECT COUNT( * )"
			. "\n FROM #__stats_agents"
			;
			$db->setQuery( $query );
			$total = $db->loadResult();

			if ( $total == 0 ) {
				$msg = JText::_( 'reset statistics failed' );
				$redirecturl = 'index.php?option=com_statistics';
			}
			else {
   				$query = "DELETE FROM #__stats_agents";
				$msg = JText::_( 'reset statistics success' );
				$redirecturl = 'index.php?option=com_statistics';
			}
			break;

		case 'pi':
			// get the total number of records
			$query = "SELECT COUNT( * )"
			. "\n FROM #__content"
			. "\n WHERE hits != 0"
			;
			$db->setQuery( $query );
			$total = $db->loadResult();

			if ( $total == 0 ) {
				$msg = JText::_( 'reset statistics failed' );
				$redirecturl = 'index.php?option=com_statistics&amp;task=pageimp';
			}
			else {
				$query = "UPDATE #__content"
				. "\n SET hits = 0"
				. "\n WHERE hits != 0"
				;
				$msg = JText::_( 'reset statistics success' );
				$redirecturl = 'index.php?option=com_statistics&amp;task=pageimp';
			}
			break;

		case 'set':
			// get the total number of records
			$query = "SELECT COUNT( * )"
			. "\n FROM #__core_log_searches"
			;
			$db->setQuery( $query );
			$total = $db->loadResult();

			if ( $total == 0 ) {
				$msg = JText::_( 'reset statistics failed' );
				$redirecturl = 'index.php?option=com_statistics&amp;task=searches';
			}
			else {
   				$query = "DELETE FROM #__core_log_searches";
				$msg = JText::_( 'reset statistics success' );
				$redirecturl = 'index.php?option=com_statistics&amp;task=searches';
			}
			break;
		}

		$db->setQuery( $query );
		$db->query();

		$mainframe->redirect( $redirecturl, $msg );
}

function sortIcon( $text, $base_href, $field, $state='none' )
{
	$alts = array(
		'none' 	=> JText::_( 'No Sorting' ),
		'asc' 	=> JText::_( 'Sort Ascending' ),
		'desc' 	=> JText::_( 'Sort Descending' ),
	);

	$next_state = 'asc';
	if ($state == 'asc') {
		$next_state = 'desc';
	} else if ($state == 'desc') {
		$next_state = 'none';
	}

	if ($state == 'none') {
		$img = '';
	} else {
		$img = "<img src=\"images/sort_$state.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"{$alts[$next_state]}\" />";
	}

	$html = "<a href=\"$base_href&amp;field=$field&amp;order=$next_state\">"
	. JText::_( $text )
	. '&nbsp;&nbsp;'
	. $img
	. "</a>";

	return $html;
}
?>