<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
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

/** load the html drawing class */
require_once( $mainframe->getPath( 'front_html' ) );
require_once( $mainframe->getPath( 'class' ) );
$mainframe->setPageTitle( JText::_( 'Web Links' ) );

$id 	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
$catid 	= intval( mosGetParam( $_REQUEST, 'catid', 0 ) );

switch ($task) {
	case 'new':
		editWebLink( 0, $option );
		break;

	case 'edit':
		/** disabled until permissions system can handle it */
		editWebLink( 0, $option );
		break;

	case 'save':
		saveWebLink( $option );
		break;

	case 'cancel':
		cancelWebLink( $option );
		break;

	case 'view':
		showItem( $id, $catid );
		break;

	default:
		listWeblinks( $catid );
		break;
}

function listWeblinks( $catid ) {
	global $mainframe, $database, $my;
	global $mosConfig_live_site;
	global $Itemid;

	/* Query to retrieve all categories that belong under the web links section and that are published. */
	$query = "SELECT *, COUNT(a.id) AS numlinks FROM #__categories AS cc"
	. "\n LEFT JOIN #__weblinks AS a ON a.catid = cc.id"
	. "\n WHERE a.published = 1"
	//. "\n AND a.approved = 1"
	. "\n AND section = 'com_weblinks'"
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
		$query = "SELECT id, url, title, description, date, hits, params"
		. "\n FROM #__weblinks"
		. "\n WHERE catid = $catid"
		. "\n AND published = 1"
		//. "\n AND approved = 1"
		. "\n AND archived = 0"
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// current cate info
		$query = "SELECT name, description, image, image_position"
		. "\n FROM #__categories"
		. "\n WHERE id = $catid"
		. "\n AND published = 1"
		;
		$database->setQuery( $query );
		$database->loadObject( $currentcat );
	}

	// Parameters
	$menu = new mosMenu( $database );
	$menu->load( $Itemid );
	$params = new mosParameters( $menu->params );
	$params->def( 'page_title', 1 );
	$params->def( 'header', $menu->name );
	$params->def( 'pageclass_sfx', '' );
	$params->def( 'headings', 1 );
	$params->def( 'hits', $mainframe->getCfg( 'hits' ) );
	$params->def( 'item_description', 1 );
	$params->def( 'other_cat_section', 1 );
	$params->def( 'other_cat', 1 );
	$params->def( 'description', 1 );
	$params->def( 'description_text', JText::_( 'WEBLINKS_DESC' ) );
	$params->def( 'image', '-1' );
	$params->def( 'weblink_icons', '' );
	$params->def( 'image_align', 'right' );
	$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

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
	$path = $mosConfig_live_site .'/images/stories/';
	if ( ( @$currentcat->image ) <> '' ) {
		$currentcat->img = $path . $currentcat->image;
		$currentcat->align = $currentcat->image_position;
	} else if ( !$catid ) {
		if ( $params->get( 'image' ) <> -1 ) {
			$currentcat->img = $path . $params->get( 'image' );
			$currentcat->align = $params->get( 'image_align' );
		}
	}

	// page header
	$currentcat->header = '';
	if ( @$currentcat->name <> '' ) {
		$currentcat->header = $currentcat->name;
	} else {
		$currentcat->header = $params->get( 'header' );
	}

	// used to show table rows in alternating colours
	$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );

	HTML_weblinks::displaylist( $categories, $rows, $catid, $currentcat, $params, $tabclass );
}


function showItem ( $id, $catid ) {
	global $database;

	//Record the hit
	$sql= "UPDATE #__weblinks"
	. "\n SET hits = hits + 1"
	. "\n WHERE id = $id"
	;
	$database->setQuery( $sql );
	$database->query();

	$query = "SELECT url"
	. "\n FROM #__weblinks"
	. "\n WHERE id = $id"
	;
	$database->setQuery( $query );
	$url = $database->loadResult();

	mosRedirect ( $url );

	listWeblinks( $catid );

}

function editWebLink( $id, $option ) {
	global $database, $my;

	if ($my->gid < 1) {
		mosNotAuth();
		return;
	}

	$row = new mosWeblink( $database );
	// load the row from the db table
	$row->load( $id );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
		mosRedirect( "index2.php?option=$option", 'The module $row->title is currently being edited by another administrator.' );
	}

	if ($id) {
		$row->checkout( $my->id );
	} else {
		// initialise new record
		$row->published 		= 0;
		$row->approved 		= 1;
		$row->ordering 		= 0;
	}
/*
	// make the select list for the image positions
	$yesno[] = mosHTML::makeOption( '0', 'No' );
	$yesno[] = mosHTML::makeOption( '1', 'Yes' );
	// build the html select list
	$applist = mosHTML::selectList( $yesno, 'approved', 'class="inputbox" size="2"', 'value', 'text', $row->approved );
	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__weblinks"
	. "\n WHERE catid='$row->catid'"
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $id, $query, 1 );
*/

	// build list of categories
	$lists['catid'] 			= mosAdminMenus::ComponentCategory( 'catid', $option, intval( $row->catid ) );

	HTML_weblinks::editWeblink( $option, $row, $lists );
}

function cancelWebLink( $option ) {
	global $database, $my;

	if ($my->gid < 1) {
		mosNotAuth();
		return;
	}

	$row = new mosWeblink( $database );
	$row->id = intval( mosGetParam( $_POST, 'id', 0 ) );
	$row->checkin();
	$Itemid = mosGetParam( $_POST, 'Returnid', '' );

	$referer = mosGetParam( $_POST, 'referer', '' );
	mosRedirect( $referer );
}

/**
* Saves the record on an edit form submit
* @param database A database connector object
*/
function saveWeblink( $option ) {
	global $database, $my;

	if ($my->gid < 1) {
		mosNotAuth();
		return;
	}

	$row = new mosWeblink( $database );
	if (!$row->bind( $_POST, "published" )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$isNew = $row->id < 1;

	$row->date = date( 'Y-m-d H:i:s' );
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	/** Notify admin's */
	$query = "SELECT email, name"
	. "\n FROM #__users"
	. "\n WHERE gid = 25"
	. "\n AND sendemail = 1"
	;
	$database->setQuery( $query );
	if(!$database->query()) {
		echo $database->stderr( true );
		return;
	}

	$adminRows = $database->loadObjectList();
	foreach( $adminRows as $adminRow) {
		mosSendAdminMail($adminRow->name, $adminRow->email, "", "Weblink", $row->title, $my->username );
	}

	$msg 	= $isNew ? JText::_( 'THANK_SUB' ) : '';
	mosRedirect( 'index.php', $msg );
}
?>
