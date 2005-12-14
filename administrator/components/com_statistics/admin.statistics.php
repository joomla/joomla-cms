<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Statistics
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
require_once( $mainframe->getPath( 'admin_html' ) );

switch ($task) {
	case 'searches':
		showSearches( $option, $task );
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

function showSummary( $option, $task ) {
	global $database, $mainframe;

	// get sort field and check against allowable field names
	$field = strtolower( mosGetParam( $_REQUEST, 'field', '' ) );
	if (!in_array( $field, array( 'agent', 'hits' ) )) {
		$field = '';
	}

	// get field ordering or set the default field to order
	$order = strtolower( mosGetParam( $_REQUEST, 'order', 'asc' ) );
	if ($order != 'asc' && $order != 'desc' && $order != 'none') {
		$order = 'asc';
	} else if ($order == 'none') {
		$field = 'agent';
		$order = 'asc';
	}

	// browser stats
	$order_by = '';
	$sorts = array();
	$tab = mosGetParam( $_REQUEST, 'tab', 'tab1' );
	$sort_base = "index2.php?option=$option&task=$task";

	switch ($field) {
		case 'hits':
			$order_by = "hits $order";
			$sorts['b_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", "agent" );
			$sorts['b_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", "hits", $order );
			$sorts['o_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", "agent" );
			$sorts['o_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", "hits", $order );
			$sorts['d_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", "agent" );
			$sorts['d_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", "hits", $order );
			break;

		case 'agent':
		default:
			$order_by = "agent $order";
			$sorts['b_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", "agent", $order );
			$sorts['b_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", "hits" );
			$sorts['o_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", "agent", $order );
			$sorts['o_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", "hits" );
			$sorts['d_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", "agent", $order );
			$sorts['d_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", "hits" );
			break;
	}

	$query = "SELECT *"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 0"
	. "\n ORDER BY $order_by"
	;
	$database->setQuery( $query );
	$browsers = $database->loadObjectList();

	$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 0"
	;
	$database->setQuery( $query );
	$bstats = null;
	$database->loadObject( $bstats );

	// platform statistics
	$query = "SELECT *"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 1"
	. "\n ORDER BY hits DESC"
	;
	$database->setQuery( $query );
	$platforms = $database->loadObjectList();

	$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 1"
	;
	$database->setQuery( $query );
	$pstats = null;
	$database->loadObject( $pstats );

	// domain statistics
	$query = "SELECT *"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 2"
	. "\n ORDER BY hits DESC"
	;
	$database->setQuery( $query );
	$tldomains = $database->loadObjectList();

	$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
	. "\n FROM #__stats_agents"
	. "\n WHERE type = 2"
	;
	$database->setQuery( $query );
	$dstats = null;
	$database->loadObject( $dstats );

	HTML_statistics::show( $browsers, $platforms, $tldomains, $bstats, $pstats, $dstats, $sorts, $option );
}

function showPageImpressions( $option, $task ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$query = "SELECT COUNT( id )"
	. "\n FROM #__content"
	;
	$database->setQuery($query);
	$total = $database->loadResult();

	$limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}{$task}limitstart", 'limitstart', 0 );

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = "SELECT id, title, created, hits"
	. "\n FROM #__content"
	. "\n ORDER BY hits DESC"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery($query);

	$rows = $database->loadObjectList();

	HTML_statistics::pageImpressions( $rows, $pageNav, $option, $task );
}

function showSearches( $option, $task ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}{$task}limitstart", 'limitstart', 0 );

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__core_log_searches"
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT *"
	. "\n FROM #__core_log_searches"
	. "\n ORDER BY hits DESC"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	JPluginHelper::importGroup( 'search' );

	for ($i=0, $n = count($rows); $i < $n; $i++) {
		$results = $mainframe->triggerEvent( 'onSearch', array( $rows[$i]->search_term ) );

		$count = 0;
		for ($j = 0, $n2 = count( $results ); $j < $n2; $j++) {
			$count += count( $results[$j] );
		}

		$rows[$i]->returns = $count;
	}

	HTML_statistics::showSearches( $rows, $pageNav, $option, $task );
}

function resetStats( $option, $task ) {
		global $database, $mainfraime;

		$op = mosGetParam( $_REQUEST, 'op', '' );

		switch ($op) {
			case 'bod':
				// get the total number of records
				$query = "SELECT COUNT( * )"
				. "\n FROM #__stats_agents"
				;
				$database->setQuery( $query );
				$total = $database->loadResult();

					if ( $total == 0 ) {
						$msg = JText::_( 'reset statistics failed' );
						$redirecturl = 'index2.php?option=com_statistics';
					}
					else {
   						$query = "DELETE FROM #__stats_agents";
						$msg = JText::_( 'reset statistics success' );
						$redirecturl = 'index2.php?option=com_statistics';
					}
			break;

			case 'pi':
				// get the total number of records
				$query = "SELECT COUNT( * )"
				. "\n FROM #__content"
				. "\n WHERE hits != 0"
				;
				$database->setQuery( $query );
				$total = $database->loadResult();

					if ( $total == 0 ) {
						$msg = JText::_( 'reset statistics failed' );
						$redirecturl = 'index2.php?option=com_statistics&task=pageimp';
					}
					else {
						$query = "UPDATE #__content"
						. "\n SET hits = 0"
						. "\n WHERE hits != 0"
						;
						$msg = JText::_( 'reset statistics success' );
						$redirecturl = 'index2.php?option=com_statistics&task=pageimp';
					}
			break;

			case 'set':
				// get the total number of records
				$query = "SELECT COUNT( * )"
				. "\n FROM #__core_log_searches"
				;
				$database->setQuery( $query );
				$total = $database->loadResult();

					if ( $total == 0 ) {
						$msg = JText::_( 'reset statistics failed' );
						$redirecturl = 'index2.php?option=com_statistics&task=searches';
					}
					else {
   						$query = "DELETE FROM #__core_log_searches";
						$msg = JText::_( 'reset statistics success' );
						$redirecturl = 'index2.php?option=com_statistics&task=searches';
					}
			break;
		}

		$database->setQuery( $query );
		$database->query();

		mosRedirect( $redirecturl, $msg );
}
?>
