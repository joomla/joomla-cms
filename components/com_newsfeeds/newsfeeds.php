<?php
/** module to display newsfeeds
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* modified by brian & rob
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// load the html drawing class
require_once( JApplicationHelper::getPath( 'front_html' ) );

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, 'News Feeds');

$feedid = intval( mosGetParam( $_REQUEST ,'feedid', 0 ) );
$catid 	= intval( mosGetParam( $_REQUEST ,'catid', 0 ) );

switch( $task ) {
	case 'view':
		showFeed( $option, $feedid );
		break;

	default:
		listFeeds( $catid );
		break;
}


function listFeeds( $catid ) {
	global $mainframe;
	global $Itemid;

	$database 			= & $mainframe->getDBO();
	$my 				= & $mainframe->getUser();
	$breadcrumbs 		= & $mainframe->getPathWay();
	$option 			= JRequest :: getVar('option');
	$limit 				= JRequest :: getVar('limit', 				0, '', 'int');
	$limitstart 		= JRequest :: getVar('limitstart', 			0, '', 'int');
	
	/* Query to retrieve all categories that belong under the contacts section and that are published. */
	$query = "SELECT cc.*, a.catid, COUNT(a.id) AS numlinks"
	. "\n FROM #__categories AS cc"
	. "\n LEFT JOIN #__newsfeeds AS a ON a.catid = cc.id"
	. "\n WHERE a.published = 1"
	. "\n AND cc.section = 'com_newsfeeds'"
	. "\n AND cc.published = 1"
	. "\n AND cc.access <= $my->gid"
	. "\n GROUP BY cc.id"
	. "\n ORDER BY cc.ordering"
	;
	$database->setQuery( $query );
	$categories = $database->loadObjectList();

	// Parameters
	$menu =& JModel::getInstance('menu', $database );
	$menu->load( $Itemid );
	$params = new JParameter( $menu->params );
	
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
	// pagination parameters	$params->def('display', 			1 );
	$params->def('display_num', 		$mainframe->getCfg('list_limit'));


	$rows 		= array();
	$currentcat = NULL;
	if ( $catid ) {

		$query = "SELECT COUNT(id) as numitems"
		. "\n FROM #__newsfeeds"
		. "\n WHERE catid = $catid"
		. "\n AND published = 1"
		;
		$database->setQuery($query);
		$counter = $database->loadObjectList();
		$total = $counter[0]->numitems;
		$limit = $limit ? $limit : $params->get('display_num');
		if ($total <= $limit) {
			$limitstart = 0;
		}
		
		jimport('joomla.pagination');
		$page = new JPagination($total, $limitstart, $limit);
		
		// url links info for category
		$query = "SELECT *"
		. "\n FROM #__newsfeeds"
		. "\n WHERE catid = $catid"
		 . "\n AND published = 1"
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		
		// current category info
		$query = "SELECT name, description, image, image_position"
		. "\n FROM #__categories"
		. "\n WHERE id = $catid"
		. "\n AND published = 1"
		. "\n AND access <= $my->gid"
		;
		$database->setQuery( $query );
		$database->loadObject( $currentcat );

		/*
		Check if the category is published
		*/
		if (!$currentcat->name) {
			mosNotAuth();
			return;
		}
	}

	if ( $catid ) {
		$params->set( 'type', 'category' );
	} else {
		$params->set( 'type', 'section' );
	}

	// page description
	$currentcat->descrip = '';
	if( ( @$currentcat->description ) <> '' ) {
		$currentcat->descrip = $currentcat->description;
	} else if ( !$catid ) {
		// show description
		if ( $params->get( 'description' ) ) {
			$currentcat->descrip = $params->get( 'description_text' );
		}
	}

	// page image
	$currentcat->img = '';
	$path = 'images/stories/';
	if ( ( @$currentcat->image ) <> '' ) {
		$currentcat->img = $path . $currentcat->image;
		$currentcat->align = $currentcat->image_position;
	} else if ( !$catid ) {
		if ( $params->get( 'image' ) <> -1 ) {
			$currentcat->img = $path . $params->get( 'image' );
			$currentcat->align = $params->get( 'image_align' );
		}
	}

	// page header and settings
	$currentcat->header = '';
	if ( @$currentcat->name <> '' ) {
		$currentcat->header = $currentcat->name;

		// Set page title per category
		$mainframe->setPageTitle( $menu->name. ' - ' .$currentcat->header );

		// Add breadcrumb item per category
		$breadcrumbs->addItem($currentcat->header, '');
	} else {
		$currentcat->header = $params->get( 'header' );

		// Set page title
		$mainframe->SetPageTitle( $menu->name );
	}

	// used to show table rows in alternating colours
	$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );


	HTML_newsfeed::displaylist( $categories, $rows, $catid, $currentcat, $params, $tabclass, $page );
}


function showFeed( $feedid ) {
	global $mainframe, $Itemid;

		// Get some objects from the JApplication
	$database 	= & $mainframe->getDBO();
	$my 		= & $mainframe->getUser();
	
	require_once( $mainframe->getPath( 'class' ) );
	
	$newsfeed = new mosNewsFeed($database);
	$newsfeed->load($feedid);
	
	/*
	* Check if newsfeed is published
	*/
	if(!$newsfeed->published) {
		mosNotAuth();
		return;
	}
		
	$category = new mosCategory($database);
	$category->load($newsfeed->catid);
	
	/*
	* Check if newsfeed category is published
	*/
	if(!$category->published) {
		mosNotAuth();
		return;
	}	/*
	* check whether category access level allows access
	*/
	if ( $category->access > $my->gid ) {	
		mosNotAuth();  
		return;
	}

	// full RSS parser used to access image information
	$cacheDir = JPATH_SITE . '/cache/';
	$LitePath = JPATH_SITE . '/includes/Cache/Lite.php';

	// Adds parameter handling
	$menu =& JModel::getInstance('menu', $database );
	$menu->load( $Itemid );
	$params = new JParameter( $menu->params );
	$params->def( 'page_title', 1 );
	$params->def( 'header', $menu->name );
	$params->def( 'pageclass_sfx', '' );
	$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
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
	$breadcrumbs =& $mainframe->getPathWay();
	$breadcrumbs->addItem($newsfeed->name, '');

	HTML_newsfeed::showNewsfeeds( $newsfeed, $LitePath, $cacheDir, $params );
}
?>