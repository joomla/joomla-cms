<?php
/**
* @version $Id: search.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Search
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@front_html' );

$searchword = mosGetParam( $_REQUEST, 'searchword', '' );

if ( $searchword ) {
	//processSearch();
	viewSearch();
} else {
	viewSearch();
}


function viewSearch() {
	global $mainframe, $mosConfig_absolute_path, $mosConfig_lang, $my;
	global $Itemid, $database, $_MAMBOTS, $_LANG;

	session_name( 'mossearch' );
	session_start();

	//$searchword = mosGetParam( $_SESSION, 'searchword', '' );
	$searchword = mosGetParam( $_REQUEST, 'searchword', '' );

	// Adds parameter handling
	if( $Itemid > 0 ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
		$params->def( 'header', 	$menu->name );
		$params->def( 'seo_title',	$menu->name );
	} else {
		$params = new mosParameters( '' );
		$params->def( 'header',		$_LANG->_( 'SEARCH_TITLE' ) );
		$params->def( 'seo_title',	$_LANG->_( 'SEARCH_TITLE' ) );
	}
	$params->def( 'page_title', 	1 );
	$params->def( 'pageclass_sfx', 	'' );
	$params->def( 'back_button', 	$mainframe->getCfg( 'back_button' ) );
	$params->def( 'meta_key', 		'' );
	$params->def( 'meta_descrip', 	'' );
	$params->def( 'show_google', 	1 );
	$params->def( 'show_section', 	1 );
	$params->def( 'show_preview', 	1 );
	$params->def( 'show_date', 		1 );
	$params->def( 'text_highlight', 1 );
	$params->set( 'Itemid', 		$Itemid );

	$search_ignore = array();
	@include $mosConfig_absolute_path .'/language/'. $mosConfig_lang .'.ignore.php';

	// build the html select list for ordering
	$orders[] = mosHTML::makeOption( 'newest', 		$_LANG->_( 'SEARCH_NEWEST' ) );
	$orders[] = mosHTML::makeOption( 'oldest', 		$_LANG->_( 'SEARCH_OLDEST' ) );
	$orders[] = mosHTML::makeOption( 'popular', 	$_LANG->_( 'SEARCH_POPULAR' ) );
	$orders[] = mosHTML::makeOption( 'alpha', 		$_LANG->_( 'SEARCH_ALPHABETICAL' ) );
	$orders[] = mosHTML::makeOption( 'category', 	$_LANG->_( 'SEARCH_CATEGORY' ) );
	//$ordering = mosGetParam( $_SESSION, 'ordering', '' );
	$ordering = mosGetParam( $_REQUEST, 'ordering', '' );

	//$searchphrase 	= mosGetParam( $_SESSION, 'searchphrase', '' );
	$searchphrase 	= mosGetParam( $_REQUEST, 'searchphrase', 'any' );
	$searchphrases 	= array();

	// build the html select list for ordering
	$phrase = new stdClass();
	$phrase->value 		= 'any';
	$phrase->text 		= $_LANG->_( 'SEARCH_ANYWORDS' );
	$searchphrases[] 	= $phrase;

	$phrase = new stdClass();
	$phrase->value 		= 'all';
	$phrase->text 		= $_LANG->_( 'SEARCH_ALLWORDS' );
	$searchphrases[] 	= $phrase;

	$phrase = new stdClass();
	$phrase->value 		= 'exact';
	$phrase->text 		= $_LANG->_( 'SEARCH_PHRASE' );
	$searchphrases[] 	= $phrase;

	$_MAMBOTS->loadBotGroup( 'search' );
	$lists['areas'] = $_MAMBOTS->trigger( 'onSearchAreas' );
	//$areas = mosGetParam( $_SESSION, 'areas', '' );
	$areas = mosGetParam( $_REQUEST, 'areas', '' );

	$params->set( 'ordering', 		mosHTML::selectList( $orders, 'ordering', 'class="inputbox"', 'value', 'text', $ordering ) );
	$params->set( 'searchphrase', 	mosHTML::radioList( $searchphrases, 'searchphrase', '', $searchphrase ) );
	$params->set( 'searchword', 	stripslashes( htmlspecialchars( $searchword ) ) );

	$rows = NULL;
	if ( !$searchword ) {
	// No Search word
		$params->set( 'result', 	'message' );
		$params->set( 'message', 	$_LANG->_( 'NOTERM' ) );
	} else if ( in_array( $searchword, $search_ignore ) ) {
	/// Searching for ignored words
		$params->set( 'result', 	'none' );
		$params->set( 'message', 	$_LANG->_( 'IGNOREKEYWORD' ) );
	} else {
		mosLogSearch( $searchword );
		//$phrase 	= mosGetParam( $_SESSION, 'searchphrase', '' );
		//$ordering 	= mosGetParam( $_SESSION, 'ordering', '' );
		$phrase 	= mosGetParam( $_REQUEST, 'searchphrase', '' );
		$ordering 	= mosGetParam( $_REQUEST, 'ordering', '' );

		$results 	= $_MAMBOTS->trigger( 'onSearch', array( $searchword, $phrase, $ordering, $areas ) );
		$totalRows 	= 0;

		$rows = array();

		$n = count( $results );
		for ( $i = 0; $i < $n; $i++ ) {
			if ( !is_array( $results[$i] ) ) {
				$results[$i] = array();
			}
			$rows = array_merge( $rows, $results[$i] );
		}

		$totalRows = count( $rows );

		$display_num = @$params->get( 'display_num' ) ? $params->get( 'display_num' ) : $GLOBALS['mosConfig_list_limit'];

		$limit 		= trim( mosGetParam( $_REQUEST, 'limit', $display_num ) );
		$limitstart = trim( mosGetParam( $_REQUEST, 'limitstart', 0 ) );
		if ( $limit > $totalRows ) {
			$limitstart = 0;
		}
		mosFS::load( '@pageNavigation' );
		$pageNav = new mosPageNav( $totalRows, $limitstart, $limit );

		$rows 	= array_slice( $rows, $limitstart, $limit );
		$total 	= count( $rows );

		$z = $limitstart;
		for ( $i=0; $i < $total; $i++ ) {
			$row = &$rows[$i]->text;
			if ( $phrase == 'exact' ) {
				$searchwords 	= array($searchword);
				$needle 		= $searchword;
			} else {
				$searchwords 	= explode(' ', $searchword);
				$needle 		= $searchwords[0];
			}

			$row = mosPrepareSearchContent( $row, 200, $needle );

			if ( $params->def( 'text_highlight' ) ) {
				foreach ( $searchwords as $hlword ) {
					$row = eregi_replace( $hlword, "<span class=\"highlight\">\\0</span>", $row);
				}
			}

			if ( !eregi( '^http', $rows[$i]->href ) ) {
				// determines Itemid for Content items
				if ( strstr( $rows[$i]->href, 'com_content' ) && strstr( $rows[$i]->href, 'view' ) ) {
					// tests to see if itemid has already been included - this occurs for typed content items
					if ( !strstr( $rows[$i]->href, 'Itemid' ) ) {
						$temp = explode( 'id=', $rows[$i]->href );
						$rows[$i]->href = $rows[$i]->href. '&amp;Itemid='. $mainframe->getItemid( $temp[1] );
					}
				}
			}

			$rows[$i]->num = $z + 1;
			$z++;
		}

		if ( $totalRows ) {
			$total = count( $rows );
			for ( $i=0; $i < $total; $i++ ) {
				if ( $rows[$i]->created ) {
					$rows[$i]->created = mosFormatDate ( $rows[$i]->created, $_LANG->_( 'DATE_FORMAT_LC3' ) );
				} else {
					$rows[$i]->created = '&nbsp;';
				}

				if ( $rows[$i]->browsernav == 1 ) {
					$rows[$i]->target = '_blank';
				} else {
					$rows[$i]->target = '_self';
				}

				$rows[$i]->href = sefRelToAbs( $rows[$i]->href );
			}

			$params->set( 'result', 'found' );
			//$params->set( 'page_links', 	$pageNav->writePagesLinks( 'index.php?option=com_search&Itemid='. $Itemid ) );
			//$params->set( 'page_limit', 	$pageNav->getLimitBox( 'index.php?option=com_search&Itemid='. $Itemid ) );
			$params->set( 'page_links', 	$pageNav->writePagesLinks( 'index.php?'. $_SERVER['QUERY_STRING'] ) );
			$params->set( 'page_limit', 	$pageNav->getLimitBox( 'index.php?'. $_SERVER['QUERY_STRING'] ) );
			$params->set( 'page_counter', 	$pageNav->writePagesCounter() );
		} else {
			$params->set( 'result', 'none' );
			$params->set( 'message', $_LANG->_( 'NOKEYWORD' ) );
		}
	}

	searchScreens_front::displaylist( $params, $lists, $areas, $rows );

	// SEO Meta Tags
	$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );

	session_write_close();
}

function processSearch() {
	global $database;

	$option = mosGetParam( $_REQUEST, 'option', 'com_search' );
	$Itemid = mosGetParam( $_REQUEST, 'Itemid', 0 );

	header( 'Location: '. sefRelToAbs( 'index.php?option='. $option .'&Itemid='. $Itemid ) );

	session_name( 'mossearch' );
	session_start();

	$searchword = mosGetParam( $_REQUEST, 'searchword', '' );
	$_SESSION['searchword'] 	= $database->getEscaped( trim( $searchword ) );
	$_SESSION['ordering'] 		= mosGetParam( $_REQUEST, 'ordering', 'newest');
	$_SESSION['searchphrase'] 	= mosGetParam( $_REQUEST, 'searchphrase', 'any' );
	$_SESSION['areas'] 			= mosGetParam( $_REQUEST, 'areas', null );
	$_SESSION['phrase'] 		= mosGetParam( $_REQUEST, 'searchphrase', '' );

	session_write_close();
}

function mosLogSearch( $search_term ) {
	global $database;
	global $mosConfig_enable_log_searches;

	if ( $mosConfig_enable_log_searches ) {
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
			$query = "INSERT INTO #__core_log_searches"
			. "\n VALUES ( '$search_term', '1' )"
			;
			$database->setQuery( $query );
			$database->query();
		}
	}
}
?>