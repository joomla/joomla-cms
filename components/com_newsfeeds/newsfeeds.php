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

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Newsfeeds'));

// Set the component name in the pathway
$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, 'News Feeds');

$cParams = JSiteHelper::getControlParams();
$task	 = JRequest::getVar( 'task', $cParams->get( 'task') );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch( $task )
{
	case 'view':
		NewsfeedsController::showNewsFeed( );
		break;
	
	case 'category' :
		NewsfeedsController::showCategory();
		break;

	default:
		NewsfeedsController::showCategories( );
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
	function showCategories(  )
	{
		global $mainframe, $Itemid, $option;

		$db		 	= & JFactory::getDBO();
		$user 		= & JFactory::getUser();
		$pathway 	= & $mainframe->getPathWay();
		$gid		= $user->get('gid');
		
		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_('News Feeds'));
		
		// Load the menu object and parameters
		$menus = &JMenu::getInstance();
		$menu  = $menus->getItem($Itemid);

		// Parameters
		$params = new JParameter($menu->params);
		$params->def( 'page_title', 		1 );
		$params->def( 'header', 			$menu->name );
		$params->def( 'pageclass_sfx', 		'' );
		$params->def( 'headings', 			1 );
		$params->def( 'back_button', 		$mainframe->getCfg( 'back_button' ) );
		$params->def( 'description_text', 	'' );
		$params->def( 'image', 				-1 );
		$params->def( 'image_align', 		'right' );
		$params->def( 'other_cat_section', 	1 );
		// Category List Display control
		$params->def( 'other_cat', 			1 );
		$params->def( 'cat_description', 	1 );
		$params->def( 'cat_items', 			1 );
		// Table Display control
		$params->def( 'headings', 			1 );
		$params->def( 'name',				1 );
		$params->def( 'articles', 			1 );
		$params->def( 'link', 				1 );
		// pagination parameters
		$params->def('display', 			1 );
		$params->def('display_num', 		$mainframe->getCfg('list_limit'));

		// Handle the type
		$params->set( 'type', 'section' );
		
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

		require_once (dirname(__FILE__).DS.'views'.DS.'categories'.DS.'categories.php');
		NewsfeedsViewCategories::show( $params, $categories );
	}
	
	function showCategory(  )
	{
		global $mainframe, $Itemid;

		$db		 	= & JFactory::getDBO();
		$user 		= & JFactory::getUser();
		$pathway 	= & $mainframe->getPathWay();
		$document	= & JFactory::getDocument();
		
		// Get the paramaters of the active menu item
		$menus   =& JMenu::getInstance();
		$menu    = $menus->getItem($Itemid);
		$params  =& $menus->getParams($Itemid);

		$option 		= JRequest::getVar('option');
		$limit 			= JRequest::getVar('limit', 		0, '', 'int');
		$limitstart 	= JRequest::getVar('limitstart',	0, '', 'int');
		$catid 			= JRequest::getVar( 'catid', (int) $params->get( 'category_id' ), '', 'int' );
		$gid			= $user->get('gid');
		
		// Parameters
		$params->def( 'page_title', 		1 );
		$params->def( 'header', 			$menu->name );
		$params->def( 'pageclass_sfx', 		'' );
		$params->def( 'headings', 			1 );
		$params->def( 'back_button', 		$mainframe->getCfg( 'back_button' ) );
		$params->def( 'description_text', 	'' );
		$params->def( 'image', 				-1 );
		$params->def( 'image_align', 		'right' );
		$params->def( 'other_cat_section', 	1 );
		// Category List Display control
		$params->def( 'other_cat', 			1 );
		$params->def( 'cat_description', 	1 );
		$params->def( 'cat_items', 			1 );
		// Table Display control
		$params->def( 'headings', 			1 );
		$params->def( 'name',				1 );
		$params->def( 'articles', 			1 );
		$params->def( 'link', 				1 );
		// pagination parameters
		$params->def('display', 			1 );
		$params->def('display_num', 		$mainframe->getCfg('list_limit'));
		
		$params->set( 'type', 'category' );

		$query = "SELECT COUNT(id) as numitems"
			. "\n FROM #__newsfeeds"
			. "\n WHERE catid = $catid"
			. "\n AND published = 1"
		;
		$db->setQuery($query);
		
		$counter = $db->loadObjectList();
		$total  = $counter[0]->numitems;
		
		$limit = $limit ? $limit : $params->get('display_num');
		
		if ($total <= $limit) {
			$limitstart = 0;
		}

		jimport('joomla.presentation.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		// We need to get a list of all newsfeeds in the given category
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
		$category = $db->loadObject();

		// Check if the category is published or if access level allows access
		if (!$category->name) {
			JError::raiseError(403, JText::_("ALERTNOTAUTH"));
			return;
		}

		// Set page title per category
		$document->setTitle( $menu->name. ' - ' .$category->name );

		// Add breadcrumb item per category
		$pathway->addItem($category->name, '');

		// Define image tag attributes
		if (isset ($category->image)) {
			$imgAttribs['align'] = '"'.$category->image_position.'"';
			$imgAttribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$category->imageTag = mosHTML::Image('/images/stories/'.$category->image, JText::_('News Feeds'), $imgAttribs);
		}
		
		require_once (dirname(__FILE__).DS.'views'.DS.'category'.DS.'category.php');
		NewsfeedsViewCategory::show( $rows, $catid, $category, $params, $pagination );
	}


	function showNewsFeed( )
	{
		global $mainframe, $Itemid;

		// check if cache directory is writeable
		$cacheDir = $mainframe->getCfg('cachepath') . DS;
		if ( !is_writable( $cacheDir ) ) {
			echo JText::_( 'Cache Directory Unwriteable' );
			return;
		}

		// Get some objects from the JApplication
		$db		 = & JFactory::getDBO();
		$user 	 = & JFactory::getUser();
		$pathway =& $mainframe->getPathWay();

		// Get the current menu item
		$menus   =& JMenu::getInstance();
		$menu    =& $menus->getItem($Itemid);
		$params =& $menus->getParams($Itemid);

		$feedid = JRequest::getVar( 'feedid', $params->get( 'feed_id' ), '', 'int' );

		require_once( $mainframe->getPath( 'class' ) );

		$newsfeed = new mosNewsFeed($db);
		$newsfeed->load($feedid);

		// Check if newsfeed is published
		if(!$newsfeed->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH'));
			return;
		}

		$category = new JTableCategory($db);
		$category->load($newsfeed->catid);

		// Check if newsfeed category is published
		if(!$category->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH'));
			return;
		}

		// check whether category access level allows access
		if ( $category->access > $user->get('gid') ) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH'));
			return;
		}

		//  get RSS parsed object
		$options = array();
		$options['rssUrl']      = $newsfeed->link;
		$options['cache_time'] = $newsfeed->cache_time;

		$rssDoc = JFactory::getXMLparser('RSS', $options);

		if ( $rssDoc == false ) {
			$msg = JText::_('Error: Feed not retrieved');
			$mainframe->redirect('index.php?option=com_newsfeeds&catid='. $newsfeed->catid .'&Itemid=' . $Itemid, $msg);
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
		$params->def( 'page_title', 1 );
		$params->def( 'header', $menu->name );
		$params->def( 'pageclass_sfx', '' );
		// Feed Display control
		$params->def( 'feed_image', 1 );
		$params->def( 'feed_descr', 1 );
		$params->def( 'item_descr', 1 );
		$params->def( 'word_count', 0 );

		if ( !$params->get( 'page_title' ) ) {
			$params->set( 'header', '' );
		}

		// Set page title per category
		$mainframe->setPageTitle( $menu->name. ' - ' .$newsfeed->name );

		// Add breadcrumb item per category
		$pathway->addItem($newsfeed->name, '');

		require_once (dirname(__FILE__).DS.'views'.DS.'newsfeed'.DS.'newsfeed.php');
		NewsfeedsViewNewsfeed::show( $newsfeed, $lists, $params );
	}
}
?>