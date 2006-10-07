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

switch ($task) {
	case 'searches':
		showSearches( $option, $task );
		break;

	case 'searchesresults':
		showSearches( $option, $task, 1 );
		break;

	case 'pageimp':
		showPageImpressions( $option, $task );
		break;

	case 'resetStats':
		resetStats ( $option, $task );
		break;

	default:
		showSummary( $option, $task );
		break;
}

function showSummary( $option, $task )
{
	$db	=& JFactory::getDBO();
	// get sort field and check against allowable field names
	$field = strtolower( JRequest::getVar( 'field' ) );
	if (!in_array( $field, array( 'agent', 'hits' ) )) {
		$field = '';
	}

	// get field ordering or set the default field to order
	$order = strtolower( JRequest::getVar( 'order', 'asc' ) );
	if ($order != 'asc' && $order != 'desc' && $order != 'none') {
		$order = 'asc';
	} else if ($order == 'none') {
		$field = 'agent';
		$order = 'asc';
	}

	// browser stats
	$order_by = '';
	$sorts = array();
	$tab = JRequest::getVar( 'tab', 'tab1' );
	$sort_base = "index.php?option=$option&task=$task";

	switch ($field) 
	{
		case 'hits':
			$order_by = "hits $order";
			$sorts['b_agent'] 	= sortIcon( 'Browser', "$sort_base&tab=tab1", 'agent' );
			$sorts['b_hits'] 	= sortIcon( ' % ', "$sort_base&tab=tab1", 'hits', $order );
			$sorts['o_agent'] 	= sortIcon( 'Operating System', "$sort_base&tab=tab2", 'agent' );
			$sorts['o_hits'] 	= sortIcon( ' % ', "$sort_base&tab=tab2", 'hits', $order );
			$sorts['d_agent'] 	= sortIcon( 'Domain', "$sort_base&tab=tab3", 'agent' );
			$sorts['d_hits'] 	= sortIcon( ' % ', "$sort_base&tab=tab3", 'hits', $order );
			break;

		case 'agent':
		default:
			$order_by = "agent $order";
			$sorts['b_agent'] 	= sortIcon( 'Browser', "$sort_base&tab=tab1", 'agent', $order );
			$sorts['b_hits'] 	= sortIcon( ' % ', "$sort_base&tab=tab1", 'hits' );
			$sorts['o_agent'] 	= sortIcon( 'Operating System', "$sort_base&tab=tab2", 'agent', $order );
			$sorts['o_hits'] 	= sortIcon( ' % ', "$sort_base&tab=tab2", 'hits' );
			$sorts['d_agent'] 	= sortIcon( 'Domain', "$sort_base&tab=tab3", 'agent', $order );
			$sorts['d_hits'] 	= sortIcon( ' % ', "$sort_base&tab=tab3", 'hits' );
			break;
	}

	$query = "SELECT *"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 0"
	. "\n ORDER BY $order_by"
	;
	$db->setQuery( $query );
	$browsers = $db->loadObjectList();

	$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 0"
	;
	$db->setQuery( $query );
	$bstats = $db->loadObject( );

	// platform statistics
	$query = "SELECT *"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 1"
	. "\n ORDER BY hits DESC"
	;
	$db->setQuery( $query );
	$platforms = $db->loadObjectList();

	$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 1"
	;
	$db->setQuery( $query );
	$pstats = null;
	$db->loadObject( $pstats );

	// domain statistics
	$query = "SELECT *"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 2"
	. "\n ORDER BY hits DESC"
	;
	$db->setQuery( $query );
	$tldomains = $db->loadObjectList();

	$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 2"
	;
	$db->setQuery( $query );
	$dstats = null;
	$db->loadObject( $dstats );

	HTML_statistics::show( $browsers, $platforms, $tldomains, $bstats, $pstats, $dstats, $sorts, $option );
}

function showPageImpressions( $option, $task )
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.$task.filter_order", 		'filter_order', 	'c.hits' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$task.filter_order_Dir",	'filter_order_Dir',	'DESC' );
	$filter_catid		= $mainframe->getUserStateFromRequest( "$option.$task.filter_catid", 		'filter_catid', 	'' );
	$filter_sectionid	= $mainframe->getUserStateFromRequest( "$option.$task.filter_sectionid", 	'filter_sectionid', '' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.$task.filter_state", 		'filter_state', 	'' );
	$search 			= $mainframe->getUserStateFromRequest( "$option.$task.search", 				'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );
	$where				= array();
	
	$limit		= $mainframe->getUserStateFromRequest("$option.$task.limit", 'limit', $mainframe->getCfg('list_limit'), 0);
	$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

	// used by filter
	if ( $filter_sectionid > 0 ) {
		$where[] = "c.sectionid = '$filter_sectionid'";
	}
	if ( $filter_catid > 0 ) {
		$where[] = "c.catid = '$filter_catid'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "c.state = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "c.state = 0";
		}
	}

	if ($search) {
		$where[] = "WHERE LOWER( c.title ) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, c.hits DESC";

	$query = "SELECT COUNT( c.id )"
	. "\n FROM #__content AS c"
	. $where
	;
	$db->setQuery($query);
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT c.id, c.title, c.created, c.hits, c.state, c.sectionid, c.catid, c.checked_out, cc.title AS cat_title, s.title AS sec_title"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'"
	. $where
	. $orderby
	;
	$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
	$rows = $db->loadObjectList();

	// get list of categories for dropdown filter
	$query = "SELECT cc.id AS value, cc.title AS text, section"
	. "\n FROM #__categories AS cc"
	. "\n INNER JOIN #__sections AS s ON s.id = cc.section "
	. "\n ORDER BY s.ordering, cc.ordering"
	;
	$db->setQuery( $query );
	$categories[] 	= JHTML::makeOption( '0', '- '. JText::_( 'Select Category' ) .' -' );
	$categories 	= array_merge( $categories, $db->loadObjectList() );
	$lists['catid'] = JHTML::selectList( $categories, 'filter_catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_catid );

	// get list of sections for dropdown filter
	$javascript			= 'onchange="document.adminForm.submit();"';
	$lists['sectionid']	= mosAdminMenus::SelectSection( 'filter_sectionid', $filter_sectionid, $javascript );

	// state filter
	$lists['state']	= mosCommonHTML::selectState( $filter_state );

	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_statistics::pageImpressions( $rows, $pageNav, $lists, $task );
}

function showSearches( $option, $task, $showResults=null )
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.$task.filter_order", 		'filter_order', 	'hits' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$task.filter_order_Dir",	'filter_order_Dir',	'' );
	$limit 				= $mainframe->getUserStateFromRequest( 'limit', 							'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart			= $mainframe->getUserStateFromRequest( "$option.$task.limitstart", 			'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.$task.search", 				'search', 			'' );
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

	HTML_statistics::showSearches( $rows, $pageNav, $lists, $option, $task, $showResults );
}

function resetStats( $option, $task )
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
				$redirecturl = 'index.php?option=com_statistics&task=pageimp';
			}
			else {
				$query = "UPDATE #__content"
				. "\n SET hits = 0"
				. "\n WHERE hits != 0"
				;
				$msg = JText::_( 'reset statistics success' );
				$redirecturl = 'index.php?option=com_statistics&task=pageimp';
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
				$redirecturl = 'index.php?option=com_statistics&task=searches';
			}
			else {
   				$query = "DELETE FROM #__core_log_searches";
				$msg = JText::_( 'reset statistics success' );
				$redirecturl = 'index.php?option=com_statistics&task=searches';
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

	$html = "<a href=\"$base_href&field=$field&order=$next_state\">"
	. JText::_( $text )
	. '&nbsp;&nbsp;'
	. $img
	. "</a>";

	return $html;
}
?>