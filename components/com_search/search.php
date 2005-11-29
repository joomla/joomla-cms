<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Search
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

require_once( $mainframe->getPath( 'front_html' ) );

switch ( $task ) {
	default:
		viewSearch();
		break;
}

function viewSearch() {
	global $mainframe, $mosConfig_lang, $my;
	global $Itemid, $database;
	global $mosConfig_list_limit;

	$restriction = 0;

	// try to find search component's Itemid
	$query = "SELECT id"
		. "\n FROM #__menu"
		. "\n WHERE type = 'components'"
		. "\n AND published = 1"
		. "\n AND link = 'index.php?option=com_search'"
		;
	$database->setQuery( $query );
	$_Itemid = $database->loadResult();

	if ($_Itemid != "") {
		$Itemid = $_Itemid;
	}

	$gid = $my->gid;

	// Adds parameter handling
	if( $Itemid > 0 ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
		$params->def( 'page_title', 1 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', $menu->name, JText::_( 'Search' ) );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
	} else {
		$params = new mosParameters('');
		$params->def( 'page_title', 1 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', JText::_( 'Search' ) );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
	}

	// html output
	search_html::openhtml( $params );

	$searchword = mosGetParam( $_REQUEST, 'searchword', '' );
	$searchword = $database->getEscaped( trim( $searchword ) );

	// limit searchword to 20 characters
	if ( strlen( $searchword ) > 20 ) {
		$searchword 	= substr( $searchword, 0, 19 );
		$restriction 	= 1;
	}

	// searchword must contain a minimum of 3 characters
	if ( $searchword && strlen( $searchword ) < 3 ) {
		$searchword 	= '';
		$restriction 	= 1;
	}

	$search_ignore = array();
	@include JPATH_SITE . "/language/$mosConfig_lang.ignore.php";

	$orders = array();
	$orders[] = mosHTML::makeOption( 'newest', JText::_( 'Newest first' ) );
	$orders[] = mosHTML::makeOption( 'oldest', JText::_( 'Oldest first' ) );
	$orders[] = mosHTML::makeOption( 'popular', JText::_( 'Most popular' ) );
	$orders[] = mosHTML::makeOption( 'alpha', JText::_( 'Alphabetical' ) );
	$orders[] = mosHTML::makeOption( 'category', JText::_( 'Section/Category' ) );
	$ordering = mosGetParam( $_REQUEST, 'ordering', 'newest');
	$lists = array();
	$lists['ordering'] = mosHTML::selectList( $orders, 'ordering', 'id="search_ordering" class="inputbox"', 'value', 'text', $ordering );

	$searchphrase = mosGetParam( $_REQUEST, 'searchphrase', 'any' );
	$searchphrases = array();

	$phrase = new stdClass();
	$phrase->value = 'any';
	$phrase->text = JText::_( 'Any words' );
	$searchphrases[] = $phrase;

	$phrase = new stdClass();
	$phrase->value = 'all';
	$phrase->text = JText::_( 'All words' );
	$searchphrases[] = $phrase;

	$phrase = new stdClass();
	$phrase->value = 'exact';
	$phrase->text = JText::_( 'Exact phrase' );
	$searchphrases[] = $phrase;

	$lists['searchphrase']= mosHTML::radioList( $searchphrases, 'searchphrase', '', $searchphrase );

	// html output
	search_html::searchbox( htmlspecialchars( stripslashes( $searchword ) ), $lists, $params );

	if (!$searchword) {
		if ( count( $_POST ) ) {
			// html output
			// no matches found
			search_html::message( JText::_( 'No results were found' ), $params );
		} else if ( $restriction ) {
				// html output
				search_html::message( JText::_( 'SEARCH_MESSAGE' ), $params );
		}
	} else if ( in_array( $searchword, $search_ignore ) ) {
		// html output
		search_html::message( JText::_( 'IGNOREKEYWORD' ), $params );
	} else {
		// html output

		if ( $restriction ) {
			// html output
			search_html::message( JText::_( 'SEARCH_MESSAGE' ), $params );
		}

		$searchword_clean = htmlspecialchars( stripslashes( $searchword ) );

		search_html::searchintro( $searchword_clean, $params );

		mosLogSearch( $searchword );
		$phrase 	= mosGetParam( $_REQUEST, 'searchphrase', '' );
		$ordering 	= mosGetParam( $_REQUEST, 'ordering', '' );

		JBotLoader::importGroup( 'search' );
		$results 	= $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering ) );
		$totalRows 	= 0;

		$rows = array();
		for ($i = 0, $n = count( $results); $i < $n; $i++) {
			$rows = array_merge( (array)$rows, (array)$results[$i] );
		}

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

			if (!eregi( '^http', $rows[$i]->href )) {
				// determines Itemid for Content items
				if ( strstr( $rows[$i]->href, 'view' ) ) {
					// tests to see if itemid has already been included - this occurs for typed content items
					if ( !strstr( $rows[$i]->href, 'Itemid' ) ) {
						$temp = explode( 'id=', $rows[$i]->href );
						@$rows[$i]->href = $rows[$i]->href. '&amp;Itemid='. JApplicationHelper::getItemid($temp[1]);
					}
				}
			}
		}

		$mainframe->setPageTitle( JText::_( 'Search' ) );

		$total 		= $totalRows;
		$limit		= mosGetParam( $_GET, 'limit', $mosConfig_list_limit );
		$limitstart = mosGetParam( $_GET, 'limitstart', 0 );
		if ( $n ) {
		// html output
			require_once( JPATH_SITE . '/includes/pageNavigation.php' );
			$pageNav = new mosPageNav( $total, $limitstart, $limit );

			search_html::display( $rows, $params, $pageNav, $limitstart, $limit, $total, $totalRows, $searchword_clean );
		} else {
		// html output
			search_html::displaynoresult();
		}

		// html output
		search_html::conclusion( $totalRows, $searchword_clean, $pageNav );
	}

	// displays back button
	echo '<br/>';
	mosHTML::BackButton ( $params, 0 );
}

function mosLogSearch( $search_term ) {
	global $database;
	global $mosConfig_enable_log_searches;

	if ( @$mosConfig_enable_log_searches ) {
		$query = "SELECT hits"
		. "\n FROM #__core_log_searches"
		. "\n WHERE LOWER( search_term ) = '$search_term'"
		;
		$database->setQuery( $query );
		$hits = intval( $database->loadResult() );
		if ( $hits ) {
			$query = "UPDATE #__core_log_searches"
			. "\n SET hits = ( hits + 1 )"
			. "\n WHERE LOWER( search_term ) = '$search_term'"
			;
			$database->setQuery( $query );
			$database->query();
		} else {
			$query = "INSERT INTO #__core_log_searches VALUES ( '$search_term', 1 )"
			;
			$database->setQuery( $query );
			$database->query();
		}
	}
}
?>
