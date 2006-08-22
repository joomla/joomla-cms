<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Search
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

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Search'));

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) ) 
{
	default:
		SearchController::display();
		break;
}

/**
 * Static class to hold controller functions for the Search component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Search
 * @since		1.5
 */
class SearchController
{
	function display() 
	{
		global $mainframe, $Itemid;
	
		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$pathway =& $mainframe->getPathWay();

		$error = '';
		$rows  = null;
		$total = 0;
		
		// Get some request variables
		$searchword 	= JRequest::getVar( 'searchword' );
		$phrase 		= JRequest::getVar( 'searchphrase' );
		$searchphrase 	= JRequest::getVar( 'searchphrase', 'any' );
		$ordering 		= JRequest::getVar( 'ordering', 'newest' );
		$areas 			= JRequest::getVar( 'areas' );
		$limit			= JRequest::getVar( 'limit', $mainframe->getCfg( 'list_limit' ), 'get', 'int' );
		$limitstart 	= JRequest::getVar( 'limitstart', 0, 'get', 'int' );
		
	
		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_( 'Search' ) );
		
		// Get the paramaters of the active menu item
		$menus   =& JMenu::getInstance();
		$menu    = $menus->getItem($Itemid);
		$params  =& $menus->getParams($Itemid);
		$params->def( 'page_title', 1 );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', $menu->name, JText::_( 'Search' ) );
	
		// built select lists
		$orders = array();
		$orders[] = mosHTML::makeOption( 'newest', JText::_( 'Newest first' ) );
		$orders[] = mosHTML::makeOption( 'oldest', JText::_( 'Oldest first' ) );
		$orders[] = mosHTML::makeOption( 'popular', JText::_( 'Most popular' ) );
		$orders[] = mosHTML::makeOption( 'alpha', JText::_( 'Alphabetical' ) );
		$orders[] = mosHTML::makeOption( 'category', JText::_( 'Section/Category' ) );
		$ordering = JRequest::getVar( 'ordering', 'newest');
		$lists = array();
		$lists['ordering'] = mosHTML::selectList( $orders, 'ordering', 'class="inputbox"', 'value', 'text', $ordering );

		$searchphrases 		= array();
		$searchphrases[] 	= mosHTML::makeOption( 'any', JText::_( 'Any words' ) );
		$searchphrases[] 	= mosHTML::makeOption( 'all', JText::_( 'All words' ) );
		$searchphrases[] 	= mosHTML::makeOption( 'exact', JText::_( 'Exact phrase' ) );
		$lists['searchphrase' ]= mosHTML::radioList( $searchphrases, 'searchphrase', '', $searchphrase );

		JPluginHelper::importPlugin( 'search' );
		$lists['areas'] = $mainframe->triggerEvent( 'onSearchAreas' );
		
		// log the search
		SearchController::logSearch( $searchword );

		//limit searchword
		if(SearchController::limitSearchWord($searchword)) {
			$error = JText::_( 'SEARCH_MESSAGE' );
		}
		
		//sanatise searchword
		if(SearchController::santiseSearchWord($searchword)) {
			$error = JText::_( 'IGNOREKEYWORD' );
		}
		
		if (!$searchword && count( $_POST ) ) {
			$error = JText::_( 'No results were found' ); 
		} 
		
		if(!$error) {
			$rows  = SearchController::getResults($searchword, $phrase, $ordering, $areas);
			$total = count($rows);
			$rows  = array_splice($rows, $limitstart, $limit);
		}
		 
		require_once (dirname(__FILE__).DS.'views'.DS.'search'.DS.'search.php');
		$view = new SearchViewSearch();
		
		$request = new stdClass();
		$request->areas        = $areas;
		$request->searchword   = $searchword;
		$request->searchphrase = $searchphrase;
		$request->ordering     = $ordering;
		$request->limitstart   = $limitstart;
		$request->limit        = $limit;
		
		$data = new stdClass();
		$data->error   = $error;
		$data->results = $rows;
		$data->total   = $total;
		
		$view->set('lists'   , $lists);
		$view->set('params'  , $params);
		$view->set('request' , $request);
		$view->set('data'    , $data);
		$view->display();
	}
	
	function logSearch( $search_term ) 
	{
		global $mainframe;
		
		$db =& JFactory::getDBO();

		$enable_log_searches = $mainframe->getCfg( 'enable_log_searches' );
		
		$search_term = $db->getEscaped( trim( $search_term) );

		if ( @$enable_log_searches ) 
		{
			$db = JFactory::getDBO();
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
				$query = "INSERT INTO #__core_log_searches VALUES ( '$search_term', 1 )";
				$db->setQuery( $query );
				$db->query();
			}
		}
	}
	
	function getResults($searchword, $phrase, $ordering, $areas)
	{
		global $mainframe;
		
		$results 	= $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering, $areas ) );
		
		$rows = array();
		for ($i = 0, $n = count( $results); $i < $n; $i++) {
			$rows = array_merge( (array)$rows, (array)$results[$i] );
		}

		require_once (JApplicationHelper::getPath('helper', 'com_content'));
		$total = count( $rows );

		for ($i=0; $i < $total; $i++) 
		{
			$row = &$rows[$i]->text;
			if ($phrase == 'exact') {
				$searchwords = array($searchword);
				$needle = $searchword;
			} else {
				$searchwords = explode(' ', $searchword);
				$needle = $searchwords[0];
			}

			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php' );
			$row = SearchHelper::prepareSearchContent( $row, 200, $needle );

		  	foreach ($searchwords as $hlword) {
				$hlword = htmlspecialchars( stripslashes( $hlword ) );
				$row = eregi_replace( $hlword, '<span class="highlight">\0</span>', $row );
			}

			if ( strpos( $rows[$i]->href, 'http' ) == false ) 
			{
				$url = parse_url( $rows[$i]->href );
				if( !empty( $url['query'] ) ) {
					$link = null;
					parse_str( $url['query'], $link );
				} else {
					$link = '';
				}

				// determines Itemid for articles where itemid has not been included
				if ( !empty($link) && @$link['task'] == 'view' && isset($link['id']) && !isset($link['Itemid']) ) {
					$itemid = '';
					if (JContentHelper::getItemid( $link['id'] )) {
						$itemid = '&amp;Itemid='. JContentHelper::getItemid( $link['id'] );
					}
					$rows[$i]->href = $rows[$i]->href . $itemid;
				}
			}
		}
		
		return $rows;
	}
	
	function santiseSearchWord(&$searchword)
	{
		$ignored = false;
		
		$lang =& JFactory::getLanguage();

		$search_ignore = array();
		$tag           = $lang->getTag();
		@include $lang->getLanguagePath().$tag.DS.$tag.'.ignore.php' ;

	 	// check for words to ignore
		$aterms = explode( ' ', JString::strtolower( $searchword ) );

		// first case is single ignored word
		if ( count( $aterms ) == 1 && in_array( JString::strtolower( $searchword ), $search_ignore ) ) {
			$ignored = true;
		}
		
		// next is to remove ignored words from type 'all' searches with multiple words
		if ( count( $aterms ) > 1 && $searchphrase == 'any' ) {
			$pruned = array_diff( $aterms, $search_ignore );
			$searchword = implode( ' ', $pruned );
		}
		
		return $ignored;
	}
	
	function limitSearchWord(&$searchword) 
	{
		$restriction = false;
		
		// limit searchword to 20 characters
		if ( JString::strlen( $searchword ) > 20 ) {
			$searchword 	= JString::substr( $searchword, 0, 19 );
			$restriction 	= true;
		}

		// searchword must contain a minimum of 3 characters
		if ( $searchword && JString::strlen( $searchword ) < 3 ) {
			$searchword 	= '';
			$restriction 	= true;
		}
		
		return $restriction;
	}
}
?>