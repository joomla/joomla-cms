<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class SearchViewSearch extends JView
{
	function display($tpl = null)
	{
		global $mainframe;
		
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'search.php' );
		
		// Initialize some variables
		$document =& JFactory::getDocument();
		$pathway  =& $mainframe->getPathWay();
		$uri      =& JFactory::getURI();

		$error	= '';
		$rows	= null;
		$total	= 0;
		
		// Get some data from the model
		$areas      = &$this->get('areas');
		$state 		= &$this->get('state');
		
		// Set page title information
		$document->setTitle(JText::_('Search'));

		// Get the parameters of the active menu item
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
		
		$lists = array();
		$lists['ordering'] = JHTML::_('select.genericlist',   $orders, 'ordering', 'class="inputbox"', 'value', 'text', $state->get('ordering)') );

		$searchphrases 		= array();
		$searchphrases[] 	= JHTML::_('select.option',  'any', JText::_( 'Any words' ) );
		$searchphrases[] 	= JHTML::_('select.option',  'all', JText::_( 'All words' ) );
		$searchphrases[] 	= JHTML::_('select.option',  'exact', JText::_( 'Exact phrase' ) );
		$lists['searchphrase' ]= JHTML::_('select.radiolist',  $searchphrases, 'searchphrase', '', 'value', 'text', $state->get('match)') );

		// log the search
		SearchHelper::logSearch( $state->get('keyword'));

		//limit searchword
		if(SearchHelper::limitSearchWord($state->get('keyword'))) {
			$error = JText::_( 'SEARCH_MESSAGE' );
		}

		//sanatise searchword
		if(SearchHelper::santiseSearchWord($state->get('keyword'), $state->get('match'))) {
			$error = JText::_( 'IGNOREKEYWORD' );
		}

		if (!$state->get('keyword') && count( JRequest::get('post') ) ) {
			//$error = JText::_( 'Enter a search keyword' );
		}

		if(!$error) 
		{
			$results	= &$this->get('data' );
			$total		= &$this->get('total');
			$pagination	= &$this->get('pagination');
			$searchword = $state->get('keyword');
			
			require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
		
			for ($i=0; $i < count($results); $i++)
			{
				$row = &$results[$i]->text;
				
				if ($state->get('match') == 'exact') 
				{
					$searchwords = array($searchword);
					$needle = $searchword;
				} 
				else 
				{
					$searchwords = preg_split("/\s+/", $searchword);
					$needle = $searchwords[0];
				}

				$row = SearchHelper::prepareSearchContent( $row, 200, $needle );

				foreach ($searchwords as $hlword) 
				{
					$hlword = htmlspecialchars( stripslashes( $hlword ) );
					$row = eregi_replace( $hlword, '<span class="highlight">\0</span>', $row );
				}

				if ( strpos( $results[$i]->href, 'http' ) == false )
				{
					$url = parse_url( $results[$i]->href );
					
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
						$results[$i]->href = $rows[$i]->href . $itemid;
					}
				}
			}
		}

		$this->result	= JText::sprintf( 'TOTALRESULTSFOUND', $total, $state->get('text') );
		$this->image	= JHTML::_('image.site',  'google.png', '/images/M_images/', NULL, NULL, 'Google', 'Google', 1 );

		for($i = 0; $i < count($results); $i++ )
		{
			$result =& $results[$i];
			if ($result->created) {
				$created = JHTML::Date ( $result->created );
			}
			else {
				$created = '';
			}

			$result->created	= $created;
			$result->count		= $i + 1;
		}

		$this->assignRef('pagination',  $pagination);
		$this->assignRef('results',		$results);
		$this->assignRef('lists',		$lists);
		$this->assignRef('params',		$params);
		
		$this->assign('ordering',		$state->get('ordering'));
		$this->assign('searchword',		$state->get('keyword'));
		$this->assign('searchphrase',	$state->get('match'));
		$this->assign('searchareas',	$areas);

		$this->assign('total',			$total);
		$this->assign('error',			$error);
		$this->assign('action', 	    $uri->toString());

		parent::display($tpl);
	}
}