<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Search
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'search.php' );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	case 'search' :
		SearchController::search();
		break;

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
		global $mainframe;

		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$pathway =& $mainframe->getPathWay();

		$error	= '';
		$rows	= null;
		$total	= 0;

		// Get some request variables
		$searchword 	= JRequest::getVar( 'searchword' );
		$phrase 		= JRequest::getVar( 'searchphrase' );
		$searchphrase 	= JRequest::getVar( 'searchphrase', 'any' );
		$ordering 		= JRequest::getVar( 'ordering', 'newest' );
		$activeareas 	= JRequest::getVar( 'areas' );
		$limit			= JRequest::getVar( 'limit', $mainframe->getCfg( 'list_limit' ), 'get', 'int' );
		$limitstart 	= JRequest::getVar( 'limitstart', 0, 'get', 'int' );

		// Set page title information
		$mainframe->setPageTitle(JText::_('Search'));

		// Get the paramaters of the active menu item
		$menus  = &JMenu::getInstance();
		$menu   = $menus->getActive();
		$params	= &$mainframe->getPageParameters();

		$params->def( 'page_title', $menu->name );

		// built select lists
		$orders = array();
		$orders[] = JHTML::_('select.option',  'newest', JText::_( 'Newest first' ) );
		$orders[] = JHTML::_('select.option',  'oldest', JText::_( 'Oldest first' ) );
		$orders[] = JHTML::_('select.option',  'popular', JText::_( 'Most popular' ) );
		$orders[] = JHTML::_('select.option',  'alpha', JText::_( 'Alphabetical' ) );
		$orders[] = JHTML::_('select.option',  'category', JText::_( 'Section/Category' ) );
		$ordering = JRequest::getVar( 'ordering', 'newest');
		$lists = array();
		$lists['ordering'] = JHTML::_('select.genericlist',   $orders, 'ordering', 'class="inputbox"', 'value', 'text', $ordering );

		$searchphrases 		= array();
		$searchphrases[] 	= JHTML::_('select.option',  'any', JText::_( 'Any words' ) );
		$searchphrases[] 	= JHTML::_('select.option',  'all', JText::_( 'All words' ) );
		$searchphrases[] 	= JHTML::_('select.option',  'exact', JText::_( 'Exact phrase' ) );
		$lists['searchphrase' ]= JHTML::_('select.radiolist',  $searchphrases, 'searchphrase', '', 'value', 'text', $searchphrase );

		$areas = array();
		$areas['active'] = $activeareas;
		$areas['search'] = array();

		JPluginHelper::importPlugin( 'search');
		$searchareas = $mainframe->triggerEvent( 'onSearchAreas' );

		foreach ($searchareas as $area) {
			$areas['search'] = array_merge( $areas['search'], $area );
		}

		// log the search
		SearchHelper::logSearch( $searchword );

		//limit searchword
		if(SearchHelper::limitSearchWord($searchword)) {
			$error = JText::_( 'SEARCH_MESSAGE' );
		}

		//sanatise searchword
		if(SearchHelper::santiseSearchWord($searchword, $searchphrase)) {
			$error = JText::_( 'IGNOREKEYWORD' );
		}

		if (!$searchword && count( JRequest::get('post') ) ) {
			//$error = JText::_( 'Enter a search keyword' );
		}

		if(!$error) {
			$rows	= SearchController::getResults($searchword, $phrase, $ordering, $activeareas);
			$total	= count($rows);
			$rows	= array_splice($rows, $limitstart, $limit);
		}

		require_once (JPATH_COMPONENT.DS.'views'.DS.'search'.DS.'view.php');
		$view = new SearchViewSearch();

		$view->assign('limit',			$limit);
		$view->assign('limitstart',		$limitstart);
		$view->assign('ordering',		$ordering);
		$view->assign('searchword',		$searchword);
		$view->assign('searchphrase',	$searchphrase);
		$view->assign('searchareas',	$areas);

		$view->assign('total',			$total);
		$view->assign('error',			$error);

		$view->assignRef('results',		$rows);
		$view->assignRef('lists',		$lists);
		$view->assignRef('params',		$params);
		//$view->assignRef('request' , $request);		// TODO: remove if unneded
		//$view->assignRef('data'	, $data);			// TODO: remove if unneded

		$view->display();
	}

	function search()
	{
		global $mainframe;
		$post = JRequest::get('post');

		unset($post['task']);
		unset($post['submit']);

		$uri = new JURI();
		$uri->setQuery($post);

		$mainframe->redirect(JRoute::_('index.php?'.$uri->getQuery(), false));

	}

	function getResults($searchword, $phrase, $ordering, $areas)
	{
		global $mainframe;

		$results 	= $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering, $areas ) );

		$rows = array();
		for ($i = 0, $n = count( $results); $i < $n; $i++) {
			$rows = array_merge( (array)$rows, (array)$results[$i] );
		}

		require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
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
					if (ContentHelperRoute::getArticleRoute( $link['id'] )) {
						$itemid = '&Itemid='. ContentHelperRoute::getArticleRoute( $link['id'] );
					}
					$rows[$i]->href = $rows[$i]->href . $itemid;
				}
			}
		}

		return $rows;
	}
}
?>