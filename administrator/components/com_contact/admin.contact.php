<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Contact
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_contact', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$id 	= mosGetParam( $_GET, 'id', 0 );
$cid 	= mosGetParam( $_POST, 'cid', array(0) );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'new':
		editContact( '0', $option);
		break;

	case 'edit':
		editContact( $cid[0], $option );
		break;

	case 'editA':
		editContact( $id, $option );
		break;

	case 'apply':
	case 'save':
	case 'save2new':
	case 'save2copy':
		saveContact( $task );
		break;

	case 'remove':
		removeContacts( $cid );
		break;

	case 'publish':
		changeContact( $cid, 1 );
		break;

	case 'unpublish':
		changeContact( $cid, 0 );
		break;

	case 'orderup':
		orderContacts( $cid[0], -1 );
		break;

	case 'orderdown':
		orderContacts( $cid[0], 1 );
		break;
	
	case 'accesspublic':
		changeAccess( $cid[0], 0 );
		break;
	
	case 'accessregistered':
		changeAccess( $cid[0], 1 );
		break;
	
	case 'accessspecial':
		changeAccess( $cid[0], 2 );
		break;
		
	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'cancel':
		cancelContact();
		break;

	default:
		showContacts( $option );
		break;
}

/**
* List the records
* @param string The current GET/POST option
*/
function showContacts( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'category' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$filter_catid 		= $mainframe->getUserStateFromRequest( "$option.filter_catid", 		'filter_catid',		0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.view.limitstart",	'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );
	
	$where = array();	

	if ( $search ) {
		$where[] = "cd.name LIKE '%$search%'";
	}
	if ( $filter_catid ) {
		$where[] = "cd.catid = '$filter_catid'";
	}	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "cd.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "cd.published = 0";
		}
	}
	
	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, category, cd.ordering";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__contact_details AS cd"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	// get the subset (based on limits) of required records
	$query = "SELECT cd.*, cc.title AS category, u.name AS user, v.name as editor, g.name AS groupname"
	. "\n FROM #__contact_details AS cd"
	. "\n LEFT JOIN #__groups AS g ON g.id = cd.access"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = cd.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = cd.user_id"
	. "\n LEFT JOIN #__users AS v ON v.id = cd.checked_out"
	. $where
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

	// build list of categories
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['catid'] = mosAdminMenus::ComponentCategory( 'filter_catid', 'com_contact_details', intval( $filter_catid ), $javascript );
	
	// state filter 
	$lists['state']	= mosCommonHTML::selectState( $filter_state );		
	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;	
	
	// search filter
	$lists['search']= $search;	

	HTML_contact::showcontacts( $rows, $pageNav, $option, $lists );
}

/**
* Creates a new or edits and existing user record
* @param int The id of the record, 0 if a new entry
* @param string The current GET/POST option
*/
function editContact( $id, $option ) {
	global $database, $my;

	$row = new JModelContact( $database );
	// load the row from the db table
	$row->load( $id );

	if ($id) {
		// do stuff for existing records
		$row->checkout($my->id);
	} else {
		// do stuff for new records
		$row->imagepos = 'top';
		$row->ordering = 0;
		$row->published = 1;
	}
	$lists = array();

	// build the html select list for ordering
	$query = "SELECT ordering AS value, name AS text"
	. "\n FROM #__contact_details"
	. "\n WHERE published >= 0"
	. "\n AND catid = '$row->catid'"
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $id, $query, 1 );

	// build list of users
	$lists['user_id'] 			= mosAdminMenus::UserSelect( 'user_id', $row->user_id, 1, NULL, 'name', 0 );
	// build list of categories
	$lists['catid'] 			= mosAdminMenus::ComponentCategory( 'catid', 'com_contact_details', intval( $row->catid ) );
	// build the html select list for images
	$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= mosAdminMenus::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= mosHTML::yesnoradioList( 'published', '', $row->published );
	// build the html radio buttons for default
	$lists['default_con'] 		= mosHTML::yesnoradioList( 'default_con', '', $row->default_con );

	// get params definitions
	$file 	= JPATH_ADMINISTRATOR .'/components/com_contact/contact_items.xml';
	$params = new JParameter( $row->params, $file, 'component' );

	HTML_contact::editcontact( $row, $lists, $option, $params );
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveContact( $task ) {
	global $database;

	$row = new JModelContact( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// save params
	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->params = implode( "\n", $txt );
	}

	// save to a copy, reset the primary key
	if ($task == 'save2copy') {
		$row->id = 0;
	}

	// pre-save checks
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// save the changes
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->updateOrder();
	if ($row->default_con) {
		$query = "UPDATE #__contact_details"
		. "\n SET default_con = 0"
		. "\n WHERE id <> $row->id"
		. "\n AND default_con = 1"
		;
		$database->setQuery( $query );
		$database->query();
	}

	switch ($task) {
		case 'apply':
		case 'save2copy':
			$link = 'index2.php?option=com_contact&task=editA&id='. $row->id .'&hidemainmenu=1';
			break;
		
		case 'save2new':
			$link = 'index2.php?option=com_contact&task=edit&hidemainmenu=1';
			break;
		
		case 'save':
		default:
			$link = 'index2.php?option=com_contact';
			break;
	}
	
	mosRedirect( $link );
}

/**
* Removes records
* @param array An array of id keys to remove
* @param string The current GET/POST option
*/
function removeContacts( &$cid ) {
	global $database;

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__contact_details"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	mosRedirect( "index2.php?option=com_contact" );
}

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current option
*/
function changeContact( $cid=null, $state=0 ) {
	global $database, $my;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit();
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__contact_details"
	. "\n SET published = " . intval( $state )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new JModelContact( $database );
		$row->checkin( intval( $cid[0] ) );
	}

	mosRedirect( "index2.php?option=com_contact" );
}

/** JJC
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderContacts( $uid, $inc ) {
	global $database;

	$row = new JModelContact( $database );
	$row->load( $uid );
	$row->move( $inc, "catid = $row->catid AND published != 0" );

	mosRedirect( "index2.php?option=com_contact" );
}

/** PT
* Cancels editing and checks in the record
*/
function cancelContact() {
	global $database;

	$row = new JModelContact( $database );
	$row->bind( $_POST );
	$row->checkin();
	
	mosRedirect('index2.php?option=com_contact');
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function changeAccess( $id, $access  ) {
	global $database;

	$row = new JModelContact( $database );
	$row->load( $id );
	$row->access = $access;
	
	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}
	
	mosRedirect( 'index2.php?option=com_contact' );
}

function saveOrder( &$cid ) {
	global $database;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );

	for( $i=0; $i < $total; $i++ ) {
		$query = "UPDATE #__contact_details"
		. "\n SET ordering = $order[$i]"
		. "\n WHERE id = $cid[$i]";
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// update ordering
		$row = new JModelContact( $database );
		$row->load( $cid[$i] );
		$row->updateOrder( "catid = $row->catid AND published != 0" );
	}

	$msg 	= 'New ordering saved';
	mosRedirect( 'index2.php?option=com_contact', $msg );
}
?>