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
require_once( $mainframe->getPath( 'front_html' ) );

$breadcrumbs =& $mainframe->getBreadCrumbs();
$breadcrumbs->setItemName(1, 'News Feeds');

$feedid = intval( mosGetParam( $_REQUEST ,'feedid', 0 ) );
$catid 	= intval( mosGetParam( $_REQUEST ,'catid', 0 ) );

switch( $task ) {
	case 'view':
		showFeed( $option, $feedid );
		break;

	default:
		listFeeds( $option, $catid );
		break;
}


function listFeeds( $option, $catid ) {
	global $mainframe, $database, $my;
	global $Itemid;

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

	$rows = array();
	$currentcat = NULL;
	if ( $catid ) {
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
	}

	// Parameters
	$menu = new JMenuModel( $database );
	$menu->load( $Itemid );
	$params = new JParameters( $menu->params );

	$params->def( 'page_title', 1 );
	$params->def( 'header', $menu->name );
	$params->def( 'pageclass_sfx', '' );
	$params->def( 'headings', 1 );
	$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
	$params->def( 'description_text', '' );
	$params->def( 'image', -1 );
	$params->def( 'image_align', 'right' );
	$params->def( 'other_cat_section', 1 );
	// Category List Display control
	$params->def( 'other_cat', 1 );
	$params->def( 'cat_description', 1 );
	$params->def( 'cat_items', 1 );
	// Table Display control
	$params->def( 'headings', 1 );
	$params->def( 'name', 1 );
	$params->def( 'articles', '1' );
	$params->def( 'link', '1' );

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
	$path = JURL_SITE .'/images/stories/';
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
		$breadcrumbs =& $mainframe->getBreadCrumbs();
		$breadcrumbs->addItem($currentcat->header, '');
	} else {
		$currentcat->header = $params->get( 'header' );
		
		// Set page title
		$mainframe->SetPageTitle( $menu->name );
	}

	// used to show table rows in alternating colours
	$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );


	HTML_newsfeed::displaylist( $categories, $rows, $catid, $currentcat, $params, $tabclass );
}


function showFeed( $option, $feedid ) {
	global $database, $mainframe, $Itemid;

	// full RSS parser used to access image information
	$cacheDir = JPATH_SITE . '/cache/';
	$LitePath = JPATH_SITE . '/includes/Cache/Lite.php';

	// Adds parameter handling
	$menu = new JMenuModel( $database );
	$menu->load( $Itemid );
	$params = new JParameters( $menu->params );
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

	$and = '';
	if ( $feedid ) {
		$and = "\n AND id = $feedid";
	}

	$query = "SELECT name, link, numarticles, cache_time"
	. "\n FROM #__newsfeeds"
	. "\n WHERE published = 1"
	. "\n AND checked_out = 0"
	. $and
	. "\n ORDER BY ordering"
	;
	$database->setQuery( $query );
	$newsfeeds = $database->loadObjectList();


	if (count($newsfeeds) == 1) {
		
		// Set page title per category
		$mainframe->setPageTitle( $menu->name. ' - ' .$newsfeeds[0]->name );
	
		// Add breadcrumb item per category
		$breadcrumbs =& $mainframe->getBreadCrumbs();
		$breadcrumbs->addItem($newsfeeds[0]->name, '');
	} else {

		$mainframe->SetPageTitle($menu->name);
	}

	HTML_newsfeed::showNewsfeeds( $newsfeeds, $LitePath, $cacheDir, $params );
}
?>