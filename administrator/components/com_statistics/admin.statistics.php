<?php
/**
* @version $Id: admin.statistics.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Statistics
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

/**
 * @package Statistics
 * @subpackage Statistics
 */
class statisticsTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function statisticsTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'summary' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	function summary( ) {
		global $database, $mainframe;
		global $task;

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
		$order_by 	= '';
		$sorts 		= array();
		$tab 		= mosGetParam( $_REQUEST, 'tab', 'tab1' );
		$sort_base 	= 'index2.php?option=com_statistics&task='. $task;

		switch ( $field ) {
			case 'hits':
				$order_by = 'hits '. $order;
				$sorts['b_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", 'agent' );
				$sorts['b_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", 'hits', $order );
				$sorts['o_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", 'agent' );
				$sorts['o_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", 'hits', $order );
				$sorts['d_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", 'agent' );
				$sorts['d_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", 'hits', $order );
				break;

			case 'agent':
			default:
				$order_by = 'agent '. $order;
				$sorts['b_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", 'agent', $order );
				$sorts['b_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab1", 'hits' );
				$sorts['o_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", 'agent', $order );
				$sorts['o_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab2", 'hits' );
				$sorts['d_agent'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", 'agent', $order );
				$sorts['d_hits'] 	= mosHTML::sortIcon( "$sort_base&tab=tab3", 'hits' );
				break;
		}

		$query = "SELECT *"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = '0'"
		. "\n ORDER BY $order_by"
		;
		$database->setQuery( $query );
		$browsers = $database->loadObjectList();

		$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = '0'"
		;
		$database->setQuery( $query );
		$bstats = null;
		$database->loadObject( $bstats );

		// platform statistics
		$query = "SELECT *"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = '1'"
		. "\n ORDER BY hits DESC"
		;
		$database->setQuery( $query  );
		$platforms = $database->loadObjectList();

		$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = '1'"
		;
		$database->setQuery( $query );
		$pstats = null;
		$database->loadObject( $pstats );

		// domain statistics
		$query = "SELECT *"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = '2'"
		. "\n ORDER BY hits DESC"
		;
		$database->setQuery( $query );
		$tldomains = $database->loadObjectList();

		$query = "SELECT SUM( hits ) AS totalhits, MAX( hits ) AS maxhits"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = '2'"
		;
		$database->setQuery( $query );
		$dstats = null;
		$database->loadObject( $dstats );

		HTML_statistics::show( $browsers, $platforms, $tldomains, $bstats, $pstats, $dstats, $sorts, 'com_statistics' );
	}


	function pageimp( ) {
		global $database, $mainframe, $mosConfig_list_limit;
		global $task, $option;

		$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
		$limitstart = $mainframe->getUserStateFromRequest( "view{$option}{$task}limitstart", 'limitstart', 0 );

		$query = "SELECT count( id )"
		. "\n FROM #__content"
		;
		$database->setQuery( $query );
		$total = $database->loadResult();

		mosFS::load( '@pageNavigationAdmin' );
		$pageNav = new mosPageNav( $total, $limitstart, $limit  );

		$query = "SELECT id, title, created, hits"
		. "\n FROM #__content"
		. "\n ORDER BY hits DESC";
		$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		$rows = $database->loadObjectList();

		HTML_statistics::pageImpressions( $rows, $pageNav, $option, $task );
	}

	function searches( ) {
		global $database, $mainframe, $mosConfig_list_limit;
		global $task, $option;
		global $_MAMBOTS;

		$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
		$limitstart = $mainframe->getUserStateFromRequest( "view{$option}{$task}limitstart", 'limitstart', 0 );

		// get the total number of records
		$query = "SELECT COUNT( * )"
		. "\n FROM #__core_log_searches"
		;
		$database->setQuery( $query );
		$total = $database->loadResult();

		mosFS::load( '@pageNavigationAdmin' );
		$pageNav = new mosPageNav( $total, $limitstart, $limit );

		$query = "SELECT *"
		. "\n FROM #__core_log_searches"
		. "\n ORDER BY hits DESC"
		;
		$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		$rows = $database->loadObjectList();
		if ($database->getErrorNum()) {
			mosErrorAlert( $database->stderr() );
		}

		$_MAMBOTS->loadBotGroup( 'search' );

		for ( $i=0, $n = count( $rows ); $i < $n; $i++ ) {
			$results = $_MAMBOTS->trigger( 'onSearch', array( $rows[$i]->search_term ) );

			$count 	= 0;
			for ( $j = 0, $n2 = count( $results ); $j < $n2; $j++ ) {
				$count += count( $results[$j] );
			}

			$rows[$i]->returns = $count;
		}

		HTML_statistics::showSearches( $rows, $pageNav, $option, $task );
	}

	function resetStats() {
		global $database, $mainfraime, $_LANG;

		$res = mosGetParam( $_REQUEST, 'res', '' );
		$task = mosGetParam( $_REQUEST, 'task', '' );

		switch ($res) {
			case 'resetBOD':
				// get the total number of records
				$query = "SELECT COUNT( * )"
				. "\n FROM #__stats_agents"
				;
				$database->setQuery( $query );
				$total = $database->loadResult();

					if ( $total == 0 ) {
						$msg = $_LANG->_( 'Reset bod failed' );
						$redirecturl = 'index2.php?option=com_statistics';
					}
					else {
   						$query = "DELETE FROM #__stats_agents";
						$msg = $_LANG->_( 'Reset bod success' );
						$redirecturl = 'index2.php?option=com_statistics';
					}
   			break;

			case 'resetST':
				// get the total number of records
				$query = "SELECT COUNT( * )"
				. "\n FROM #__core_log_searches"
				;
				$database->setQuery( $query );
				$total = $database->loadResult();

					if ( $total == 0 ) {
						$msg = $_LANG->_( 'Reset st failed' );
						$redirecturl = 'index2.php?option=com_statistics&task=searches';
					}
					else {
   						$query = "DELETE FROM #__core_log_searches";
						$msg = $_LANG->_( 'Reset st success' );
						$redirecturl = 'index2.php?option=com_statistics&task=searches';
					}
   			break;

			case 'resetPI':
				// get the total number of records
				$query = "SELECT COUNT( * )"
				. "\n FROM #__content"
				. "\n WHERE hits != 0"
				;
				$database->setQuery( $query );
				$total = $database->loadResult();

					if ( $total == 0 ) {
						$msg = $_LANG->_( 'Reset pi failed' );
						$redirecturl = 'index2.php?option=com_statistics&task=pageimp';
					}
					else {
						$query = "UPDATE #__content"
						. "\n SET hits = 0"
						. "\n WHERE hits != 0"
						;
						$msg = $_LANG->_( 'Reset pi success' );
						$redirecturl = 'index2.php?option=com_statistics&task=pageimp';
					}
   			break;

		}

		$database->setQuery( $query );
		$database->query();

		mosRedirect( $redirecturl, $msg );
	}
}

$tasker = new statisticsTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>
