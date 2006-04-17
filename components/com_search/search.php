<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Search
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

require_once( JApplicationHelper::getPath( 'front_html' ) );

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, JText::_( 'Search' ) );

switch ( $task ) {
	default:
		viewSearch();
		break;
}

function viewSearch() {
	global $mainframe;

	$restriction = 0;
	$lang = $mainframe->getCfg( 'lang' );
	$list_limit = $mainframe->getCfg( 'list_limit' );
	$Itemid = JRequest::getVar( 'Itemid' );
	$db = $mainframe->getDBO();

	// try to find search component's Itemid
	$query = "SELECT id"
		. "\n FROM #__menu"
		. "\n WHERE type = 'components'"
		. "\n AND published = 1"
		. "\n AND link = 'index.php?option=com_search'"
		;
	$db->setQuery( $query );
	$_Itemid = $db->loadResult();

	if ($_Itemid != '') {
		$Itemid = $_Itemid;
	}

	// Adds parameter handling
	if( $Itemid > 0 && $Itemid != 99999999 ) {
		$menu =& JTable::getInstance('menu', $db );
		$menu->load( $Itemid );
		$params = new JParameter( $menu->params );
		$params->def( 'page_title', 1 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', $menu->name, JText::_( 'Search' ) );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
	} else {
		$params = new JParameter('');
		$params->def( 'page_title', 1 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', JText::_( 'Search' ) );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
	}

	// html output
	search_html::openhtml( $params );

	$searchword = JRequest::getVar( 'searchword' );
	$searchword = $db->getEscaped( trim( $searchword ) );

	// limit searchword to 20 characters
	if ( JString::strlen( $searchword ) > 20 ) {
		$searchword 	= JString::substr( $searchword, 0, 19 );
		$restriction 	= 1;
	}

	// searchword must contain a minimum of 3 characters
	if ( $searchword && JString::strlen( $searchword ) < 3 ) {
		$searchword 	= '';
		$restriction 	= 1;
	}

	$search_ignore = array();
	@include JPATH_SITE . "/language/$lang.ignore.php";

	$orders = array();
	$orders[] = mosHTML::makeOption( 'newest', JText::_( 'Newest first' ) );
	$orders[] = mosHTML::makeOption( 'oldest', JText::_( 'Oldest first' ) );
	$orders[] = mosHTML::makeOption( 'popular', JText::_( 'Most popular' ) );
	$orders[] = mosHTML::makeOption( 'alpha', JText::_( 'Alphabetical' ) );
	$orders[] = mosHTML::makeOption( 'category', JText::_( 'Section/Category' ) );
	$ordering = JRequest::getVar( 'ordering', 'newest');
	$lists = array();
	$lists['ordering'] = mosHTML::selectList( $orders, 'ordering', 'class="inputbox"', 'value', 'text', $ordering );

	$searchphrase 		= JRequest::getVar( 'searchphrase', 'any' );
	$searchphrases 		= array();

	$phrase 			= new stdClass();
	$phrase->value 		= 'any';
	$phrase->text 		= JText::_( 'Any words' );
	$searchphrases[] 	= $phrase;

	$phrase 			= new stdClass();
	$phrase->value 		= 'all';
	$phrase->text 		= JText::_( 'All words' );
	$searchphrases[] 	= $phrase;

	$phrase 			= new stdClass();
	$phrase->value 		= 'exact';
	$phrase->text 		= JText::_( 'Exact phrase' );
	$searchphrases[] 	= $phrase;

	$lists['searchphrase' ]= mosHTML::radioList( $searchphrases, 'searchphrase', '', $searchphrase );

	JPluginHelper::importPlugin( 'search' );
	$lists['areas'] = $mainframe->triggerEvent( 'onSearchAreas' );
	$areas 			= JRequest::getVar( 'areas' );

	// html output
	search_html::searchbox( htmlspecialchars( stripslashes( $searchword ) ), $lists, $params, $areas );
	
	// meta pagetitle
	$mainframe->setPageTitle( JText::_( 'Search' ) );
		
	if (!$searchword) {
		if ( count( $_POST ) ) {
			// html output
			// no matches found
			search_html::emptyContainer( JText::_( 'No results were found' ) );
		} else if ( $restriction ) {
				// html output
				search_html::emptyContainer( JText::_( 'SEARCH_MESSAGE' ) );
		}
	} else if ( in_array( $searchword, $search_ignore ) ) {
		// html output
		search_html::emptyContainer( JText::_( 'IGNOREKEYWORD' ) );
	} else {
		// html output

		if ( $restriction ) {
			// html output
			search_html::emptyContainer( JText::_( 'SEARCH_MESSAGE' ) );
		}

		$searchword_clean = htmlspecialchars( stripslashes( $searchword ) );

		search_html::searchintro( $searchword_clean, $params );

		logSearch( $searchword );
		
		$phrase 	= JRequest::getVar( 'searchphrase' );
		$ordering 	= JRequest::getVar( 'ordering' );

		$results 	= $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering, $areas ) );
		$totalRows 	= 0;

		$rows = array();
		for ($i = 0, $n = count( $results); $i < $n; $i++) {
			$rows = array_merge( (array)$rows, (array)$results[$i] );
		}
		
		require_once (JApplicationHelper::getPath('helper', 'com_content'));
		$totalRows = count( $rows );

		for ($i=0; $i < $totalRows; $i++) {
			$row = &$rows[$i]->text;
			if ($phrase == 'exact') {
				$searchwords = array($searchword);
				$needle = $searchword;
			} else {
				$searchwords = explode(' ', $searchword);
				$needle = $searchwords[0];
			}

			$row = mosPrepareSearchContent( $row, 200, $needle );

		  	foreach ($searchwords as $hlword) {
				$hlword = htmlspecialchars( stripslashes( $hlword ) );
				$row = eregi_replace( $hlword, '<span class="highlight">\0</span>', $row );
			}

			if ( strpos( $rows[$i]->href, 'http' ) == false ) {
				$url = parse_url( $rows[$i]->href );
				if( !empty( $url['query'] ) ) {
					parse_str( $url['query'], $link );
				} else {
					$link = '';
				}
				
				// determines Itemid for Content items where itemid has not been included
				if ( !empty($link) && @$link['task'] == 'view' && isset($link['id']) && !isset($link['Itemid']) ) {
					$itemid = '';
					if (JContentHelper::getItemid( $link['id'] )) {
						$itemid = '&amp;Itemid='. JContentHelper::getItemid( $link['id'] );
					}
					$rows[$i]->href = $rows[$i]->href . $itemid;
				}
			}
		}

		$total 		= $totalRows;
		$limit		= JRequest::getVar( 'limit', $list_limit, 'get', 'int' );
		$limitstart = JRequest::getVar( 'limitstart', 0, 'get', 'int' );
		jimport('joomla.presentation.pagination');
		$page = new JPagination( $total, $limitstart, $limit );
		
		// prepares searchword for proper display in url
//		$searchword_clean = urlencode(stripslashes($searchword_clean));
		$searchword_clean = stripslashes($searchword_clean);
		
		if ( $n ) {
		// html output
			search_html::display( $rows, $params, $page, $limitstart, $limit, $total, $totalRows, $searchword_clean );
		} else {
		// html output
			search_html::displaynoresult();
		}

		// html output
		search_html::conclusion( $searchword_clean, $page );		
	}
}

function logSearch( $search_term ) {
	global $mainframe;
	
	$enable_log_searches = $mainframe->getCfg( 'enable_log_searches' );

	if ( @$enable_log_searches ) {
		$db = $mainframe->getDBO();
		$query = "SELECT hits"
		. "\n FROM #__core_log_searches"
		. "\n WHERE LOWER( search_term ) = '$search_term'"
		;
		$db->setQuery( $query );
		$hits = intval( $db->loadResult() );
		if ( $hits ) {
			$query = "UPDATE #__core_log_searches"
			. "\n SET hits = ( hits + 1 )"
			. "\n WHERE LOWER( search_term ) = '$search_term'"
			;
			$db->setQuery( $query );
			$db->query();
		} else {
			$query = "INSERT INTO #__core_log_searches VALUES ( '$search_term', 1 )"
			;
			$db->setQuery( $query );
			$db->query();
		}
	}
}
?>