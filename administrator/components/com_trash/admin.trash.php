<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Trash
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

// ensure user has access to this function
if (!($acl->acl_check( 'com_trash', 'manage', 'users', $my->usertype ))) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );
require_once( $mainframe->getPath( 'class', 'com_frontpage' ) );

$cid = mosGetParam( $_POST, 'cid', array(0) );
$mid = mosGetParam( $_POST, 'mid', array(0) );
if ( !is_array( $cid ) ) {
	$cid = array(0);
}

switch ($task) {
	case 'deleteconfirm':
		viewdeleteTrash( $cid, $mid, $option );
		break;

	case 'delete':
		deleteTrash( $cid, $option );
		break;

	case 'restoreconfirm':
		viewrestoreTrash( $cid, $mid, $option );
		break;

	case 'restore':
		restoreTrash( $cid, $option );
		break;

	default:
		viewTrash( $option );
		break;
}


/**
* Compiles a list of trash items
*/
function viewTrash( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;
	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{". $option ."}limitstart", 'limitstart', 0 );

	// get the total number of content
	$query = "SELECT count(*)"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'"
	. "\n WHERE c.state = -2"
	;
	$database->setQuery( $query );
	$total_content = $database->loadResult();
	$pageNav_content = new mosPageNav( $total_content, $limitstart, $limit );

	// Query content items
	$query = 	"SELECT c.*, g.name AS groupname, cc.name AS catname, s.name AS sectname"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n WHERE c.state = -2"
	. "\n ORDER BY s.name, cc.name, c.title"
	. "\n LIMIT $pageNav_content->limitstart, $pageNav_content->limit "
	;
	$database->setQuery( $query );
	$contents = $database->loadObjectList();


	$query = "SELECT count(*)"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n WHERE m.published = -2"
	;
	$database->setQuery( $query );
	$total_menu = $database->loadResult();
	//$total_menu = count( $total_menu );
	$pageNav_menu = new mosPageNav( $total_menu, $limitstart, $limit );

	// Query menu items
	$query = 	"SELECT m.*"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n WHERE m.published = -2"
	. "\n ORDER BY m.menutype, m.ordering, m.ordering, m.name"
	. "\n LIMIT $pageNav_menu->limitstart, $pageNav_menu->limit"
	;
	$database->setQuery( $query );
	$menus = $database->loadObjectList();

	for ( $i = 0; $i < $total_content; $i++ ) {
		if ( ( $contents[$i]->sectionid == 0 ) && ( $contents[$i]->catid == 0 ) ) {
			$contents[$i]->sectname = 'Typed Content';
		}
	}

	HTML_trash::showList( $option, $contents, $menus, $pageNav_content, $pageNav_menu );
}


/**
* Compiles a list of the items you have selected to permanently delte
*/
function viewdeleteTrash( $cid, $mid, $option ) {
	global $database;

	// seperate contentids
	$cids = implode( ',', $cid );
	$mids = implode( ',', $mid );

	if ( $cids ) {
		// Content Items query
		$query = 	"SELECT a.title AS name"
		. "\n FROM #__content AS a"
		. "\n WHERE ( a.id IN ( $cids ) )"
		. "\n ORDER BY a.title"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $cid;
		$type = "content";
	} else if ( $mids ) {
		// Content Items query
		$query = 	"SELECT a.name"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.id IN ( $mids ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $mid;
		$type = "menu";
	}

	HTML_trash::showDelete( $option, $id, $items, $type );
}


/**
* Permanently deletes the selected list of trash items
*/
function deleteTrash( $cid, $option ) {
	global $database;
	;

	$type = mosGetParam( $_POST, 'type', array(0) );

	$total = count( $cid );

	if ( $type == 'content' ) {
		$obj = new mosContent( $database );
		$fp = new mosFrontPage( $database );
		foreach ( $cid as $id ) {
			$id = intval( $id );
			$obj->delete( $id );
			$fp->delete( $id );
		}
	} else if ( $type == "menu" ) {
		$obj = new mosMenu( $database );
		foreach ( $cid as $id ) {
			$id = intval( $id );
			$obj->delete( $id );
		}
	}

	$msg = $total. " ". JText::_( 'Item(s) successfully Deleted' );
	mosRedirect( "index2.php?option=$option&mosmsg=". $msg ."" );
}


/**
* Compiles a list of the items you have selected to permanently delte
*/
function viewrestoreTrash( $cid, $mid, $option ) {
	global $database;

	// seperate contentids
	$cids = implode( ',', $cid );
	$mids = implode( ',', $mid );

	if ( $cids ) {
		// Content Items query
		$query = "SELECT a.title AS name"
		. "\n FROM #__content AS a"
		. "\n WHERE ( a.id IN ( $cids ) )"
		. "\n ORDER BY a.title"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $cid;
		$type = "content";
	} else if ( $mids ) {
		// Content Items query
		$query = "SELECT a.name"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.id IN ( $mids ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $mid;
		$type = "menu";
	}

	HTML_trash::showRestore( $option, $id, $items, $type );
}


/**
* Restores items selected to normal - restores to an unpublished state
*/
function restoreTrash( $cid, $option ) {
	global $database;
	;

	$type = mosGetParam( $_POST, 'type', array(0) );

	$total = count( $cid );

	// restores to an unpublished state
	$state 		= 0;
	$ordering 	= 9999;
	//seperate contentids
	$cids = implode( ',', $cid );

	if ( $type == 'content' ) {
		$query = "UPDATE #__content"
		. "\n SET state = $state, ordering = $ordering"
		. "\n WHERE id IN ( $cids )"
		;
	} else if ( $type == "menu" ) {
		$query = "UPDATE #__menu"
		. "\n SET published = $state, ordering = 9999"
		. "\n WHERE id IN ( $cids )"
		;
	}

	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = $total. " ". JText::_( 'Item(s) successfully Restored' );
	mosRedirect( "index2.php?option=$option&mosmsg=". $msg ."" );
}
?>