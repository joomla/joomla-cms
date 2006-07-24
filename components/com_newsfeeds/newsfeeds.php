<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
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

// Load the html output class
require_once( JApplicationHelper::getPath( 'front_html' ) );

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Newsfeeds'));

// Set the component name in the pathway
$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, 'News Feeds');

$cParams = JComponentHelper::getControlParams();
$task	 = JRequest::getVar( 'task', $cParams->get( 'task') );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch( $task ) 
{
	case 'view':
		NewsfeedsController::showFeed( );
		break;

	default:
		NewsfeedsController::listFeeds( );
		break;
}

/**
 * Static class to hold controller functions for the Weblink component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @since		1.5
 */
class NewsfeedsController
{
	function listFeeds(  ) 
	{
		global $mainframe, $Itemid;

		$db		 		= & $mainframe->getDBO();
		$user 			= & $mainframe->getUser();
		$breadcrumbs 	= & $mainframe->getPathWay();
		
		$option 		= JRequest::getVar('option');
		$limit 			= JRequest::getVar('limit', 		0, '', 'int');
		$limitstart 	= JRequest::getVar('limitstart',	0, '', 'int');
		$Itemdid   		= JREquest::getVar('Itemid');
		$gid			= $user->get('gid');
		
		$menu    =& JMenu::getInstance();
		$mParams =& $menu->getParams($Itemid);
		
		$catid 			= JRequest::getVar( 'catid', $mParams->get( 'category_id' ), '', 'int' );

		/* Query to retrieve all categories that belong under the contacts section and that are published. */
		$query = "SELECT cc.*, a.catid, COUNT(a.id) AS numlinks"
			. "\n FROM #__categories AS cc"
			. "\n LEFT JOIN #__newsfeeds AS a ON a.catid = cc.id"
			. "\n WHERE a.published = 1"
			. "\n AND cc.section = 'com_newsfeeds'"
			. "\n AND cc.published = 1"
			. "\n AND cc.access <= $gid"
			. "\n GROUP BY cc.id"
			. "\n ORDER BY cc.ordering"
		;
		$db->setQuery( $query );
		$categories = $db->loadObjectList();
		
		// Load the menu object and parameters
		$menu = &JMenu::getInstance();
		$menu = $menu->getItem($Itemid);

		// Parameters
		$mParams->def( 'page_title', 		1 );
		$mParams->def( 'header', 			$menu->name );
		$mParams->def( 'pageclass_sfx', 		'' );
		$mParams->def( 'headings', 			1 );
		$mParams->def( 'back_button', 		$mainframe->getCfg( 'back_button' ) );
		$mParams->def( 'description_text', 	'' );
		$mParams->def( 'image', 				-1 );
		$mParams->def( 'image_align', 		'right' );
		$mParams->def( 'other_cat_section', 	1 );
		// Category List Display control
		$mParams->def( 'other_cat', 			1 );
		$mParams->def( 'cat_description', 	1 );
		$mParams->def( 'cat_items', 			1 );
		// Table Display control
		$mParams->def( 'headings', 			1 );
		$mParams->def( 'name',				1 );
		$mParams->def( 'articles', 			1 );
		$mParams->def( 'link', 				1 );
		// pagination parameters
		$mParams->def('display', 			1 );
		$mParams->def('display_num', 		$mainframe->getCfg('list_limit'));

		$rows 		= array();
		$currentcat = NULL;
		if ( $catid ) {

			$query = "SELECT COUNT(id) as numitems"
				. "\n FROM #__newsfeeds"
				. "\n WHERE catid = $catid"
				. "\n AND published = 1"
			;
			$db->setQuery($query);
			$counter = $db->loadObjectList();
			$total = $counter[0]->numitems;
			$limit = $limit ? $limit : $mParams->get('display_num');
			if ($total <= $limit) {
				$limitstart = 0;
			}

			jimport('joomla.presentation.pagination');
			$page = new JPagination($total, $limitstart, $limit);

			// url links info for category
			$query = "SELECT *"
				. "\n FROM #__newsfeeds"
				. "\n WHERE catid = $catid"
				. "\n AND published = 1"
				. "\n ORDER BY ordering"
			;
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			// current category info
			$query = "SELECT id, name, description, image, image_position"
				. "\n FROM #__categories"
				. "\n WHERE id = $catid"
				. "\n AND published = 1"
				. "\n AND access <= $gid"
			;
			$db->setQuery( $query );
			$db->loadObject( $currentcat );

			/*
			Check if the category is published or if access level allows access
			*/
			if (!$currentcat->name) {
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return;
			}
		}

		if ( $catid ) {
			$mParams->set( 'type', 'category' );
		} else {
			$mParams->set( 'type', 'section' );
		}

		// page description
		$currentcat->descrip = '';
		if( ( @$currentcat->description ) <> '' ) {
			$currentcat->descrip = $currentcat->description;
		} else if ( !$catid ) {
			// show description
			if ( $mParams->get( 'description' ) ) {
				$currentcat->descrip = $mParams->get( 'description_text' );
			}
		}

		// page image
		$currentcat->img = '';
		$path = 'images/stories/';
		if ( ( @$currentcat->image ) <> '' ) {
			$currentcat->img = $path . $currentcat->image;
			$currentcat->align = $currentcat->image_position;
		} else if ( !$catid ) {
			if ( $mParams->get( 'image' ) <> -1 ) {
				$currentcat->img = $path . $mParams->get( 'image' );
				$currentcat->align = $mParams->get( 'image_align' );
			}
		}

		// page header and settings
		$currentcat->header = '';
		if ( @$currentcat->name <> '' ) 
		{
			$currentcat->header = $currentcat->name;

			// Set page title per category
			$mainframe->setPageTitle( $menu->name. ' - ' .$currentcat->header );

			// Add breadcrumb item per category
			$breadcrumbs->addItem($currentcat->header, '');
		} 
		else 
		{
			$currentcat->header = $mParams->get( 'header' );

			// Set page title
			$mainframe->SetPageTitle( $menu->name );
		}

		// used to show table rows in alternating colours
		$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );


		NewsfeedsView::displaylist( $categories, $rows, $catid, $currentcat, $mParams, $tabclass, $page );
	}


	function showFeed( ) 
	{
		global $mainframe;
		
		// check if cache directory is writeable
		$cacheDir = $mainframe->getCfg('cachepath') . DS;
		if ( !is_writable( $cacheDir ) ) {
			echo JText::_( 'Cache Directory Unwriteable' );
			return;
		}

		// Get some objects from the JApplication
		$db		 	= & $mainframe->getDBO();
		$user 		= & $mainframe->getUser();
		
		$Itemid = JRequest::getVar('Itemid');
		
		// Get the current menu item
		$menus   =& JMenu::getInstance();
		$menu    =& JMenu::getItem($Itemid);
		$mParams =& $menus->getParams($Itemid);
		
		$feedid = JRequest::getVar( 'feedid', $mParams->get( 'feed_id' ), '', 'int' );

		require_once( $mainframe->getPath( 'class' ) );

		$newsfeed = new mosNewsFeed($db);
		$newsfeed->load($feedid);

		/*
		* Check if newsfeed is published
		*/
		if(!$newsfeed->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH'));
			return;
		}

		$category = new JTableCategory($db);
		$category->load($newsfeed->catid);

		/*
		* Check if newsfeed category is published
		*/
		if(!$category->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH'));
			return;
		}
		/*
		* check whether category access level allows access
		*/
		if ( $category->access > $user->get('gid') ) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH'));
			return;
		}

		//  get RSS parsed object
		$options = array();
		$options['rssUrl'] = $newsfeed->link;
		$options['cache_time'] = $newsfeed->cache_time;

		$rssDoc = JFactory::getXMLparser('RSS', $options);

		if ( $rssDoc == false ) {
			$msg = JText::_('Error: Feed not retrieved');
			josRedirect('index.php?option=com_newsfeeds&catid='. $newsfeed->catid .'&Itemid=' . $Itemid, $msg);
			return;
		}
		$lists = array();
		// channel header and link
		$lists['channel'] = $rssDoc->channel;

		// channel image if exists
		$lists['image'] = $rssDoc->image;

		// items
		$lists['items'] = $rssDoc->items;

		// Adds parameter handling
		$mParams->def( 'page_title', 1 );
		$mParams->def( 'header', $menu->name );
		$mParams->def( 'pageclass_sfx', '' );
		$mParams->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
		// Feed Display control
		$mParams->def( 'feed_image', 1 );
		$mParams->def( 'feed_descr', 1 );
		$mParams->def( 'item_descr', 1 );
		$mParams->def( 'word_count', 0 );

		if ( !$mParams->get( 'page_title' ) ) {
			$mParams->set( 'header', '' );
		}

		// Set page title per category
		$mainframe->setPageTitle( $menu->name. ' - ' .$newsfeed->name );

		// Add breadcrumb item per category
		$breadcrumbs =& $mainframe->getPathWay();
		$breadcrumbs->addItem($newsfeed->name, '');

		NewsfeedsView::showNewsfeeds( $newsfeed, $lists, $mParams );
	}
}
?>