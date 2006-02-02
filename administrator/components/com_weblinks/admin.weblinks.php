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
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_weblinks', 'manage' ))
{
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$cid 	= mosGetParam( $_POST, 'cid', array(0) );
$id 	= mosGetParam( $_GET, 'id', 0 );

switch ($task) {
	case 'new':
		editWeblink( $option, 0 );
		break;

	case 'edit':
		editWeblink( $option, $cid[0] );
		break;

	case 'editA':
		editWeblink( $option, $id );
		break;

	case 'save':
	case 'apply':
		saveWeblink( $task );
		break;

	case 'remove':
		removeWeblinks( $cid );
		break;

	case 'publish':
		publishWeblinks( $cid, 1 );
		break;

	case 'unpublish':
		publishWeblinks( $cid, 0 );
		break;

	case 'approve':
		break;

	case 'cancel':
		cancelWeblink();
		break;

	case 'orderup':
		orderWeblinks( $cid[0], -1 );
		break;

	case 'orderdown':
		orderWeblinks( $cid[0], 1 );
		break;
	
	case 'saveorder':
		saveOrder( $cid );
		break;		

	default:
		showWeblinks( $option );
		break;
}

/**
* Compiles a list of records
* @param database A database connector object
*/
function showWeblinks( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'category' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$filter_catid 		= $mainframe->getUserStateFromRequest( "$option.filter_catid", 		'filter_catid', 	0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart			= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );

	$where = array();

	if ($filter_catid > 0) {
		$where[] = "a.catid = $filter_catid";
	}
	if ($search) {
		$where[] = "LOWER(a.title) LIKE '%$search%'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "a.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "a.published = 0";
		}
	}	

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, category, a.ordering";
	
	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__weblinks AS a"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = "SELECT a.*, cc.name AS category, u.name AS editor"
	. "\n FROM #__weblinks AS a"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. $where
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	// build list of categories
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['catid'] = mosAdminMenus::ComponentCategory( 'filter_catid', $option, intval( $filter_catid ), $javascript );
	
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
	
	HTML_weblinks::showWeblinks( $option, $rows, $lists, $pageNav );
}

/**
* Compiles information to add or edit
* @param integer The unique id of the record to edit (0 if new)
*/
function editWeblink( $option, $id ) {
	global $database, $my;

	$lists = array();

	$row = new JWeblinkModel( $database );
	// load the row from the db table
	$row->load( $id );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
		mosRedirect( 'index2.php?option='. $option, $msg );
	}

	if ($id) {
		$row->checkout( $my->id );
	} else {
		// initialise new record
		$row->published = 1;
		$row->approved 	= 1;
		$row->order 	= 0;
		$row->catid 	= mosGetParam( $_POST, 'catid', 0 );
	}

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__weblinks"
	. "\n WHERE catid = $row->catid"
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $id, $query, 1 );

	// build list of categories
	$lists['catid'] 			= mosAdminMenus::ComponentCategory( 'catid', $option, intval( $row->catid ) );
	// build the html select list
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

	$file 	= JPATH_ADMINISTRATOR .'/components/com_weblinks/weblinks_item.xml';
	$params = new JParameter( $row->params, $file, 'component' );

	HTML_weblinks::editWeblink( $row, $lists, $params, $option );
}

/**
* Saves the record on an edit form submit
* @param database A database connector object
*/
function saveWeblink( $task ) {
	global $database, $my;

	$row = new JWeblinkModel( $database );
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
	$row->updateOrder( "catid = $row->catid" );

	switch ($task) {
		case 'apply':
			$link = 'index2.php?option=com_weblinks&task=editA&id='. $row->id .'&hidemainmenu=1';
			break;
		
		case 'save':
		default:
			$link = 'index2.php?option=com_weblinks';
			break;
	}
	
	mosRedirect($link);
}

/**
* Deletes one or more records
* @param array An array of unique category id numbers
* @param string The current url option
*/
function removeWeblinks( $cid, $option ) {
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__weblinks"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	mosRedirect( 'index2.php?option=com_weblinks' );
}

/**
* Publishes or Unpublishes one or more records
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current url option
*/
function publishWeblinks( $cid=null, $publish=1,  $option ) {
	global $database, $my;

	$catid = mosGetParam( $_POST, 'catid', array(0) );

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
		echo "<script> alert('". JText::_( 'Select an item to' ) . $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__weblinks"
	. "\n SET published = " . intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new JWeblinkModel( $database );
		$row->checkin( $cid[0] );
	}
	mosRedirect( "index2.php?option=". $option );
}
/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderWeblinks( $uid, $inc, $option ) {
	global $database;
	$row = new JWeblinkModel( $database );
	$row->load( $uid );
	$row->move( $inc, "published >= 0" );

	mosRedirect( "index2.php?option=". $option );
}

/**
* Cancels an edit operation
* @param string The current url option
*/
function cancelWeblink() {
	global $database;
	
	$row = new JWeblinkModel( $database );
	$row->bind( $_POST );
	$row->checkin();
	
	mosRedirect( 'index2.php?option=com_weblinks' );
}

function saveOrder( &$cid ) {
	global $database;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );

	for( $i=0; $i < $total; $i++ ) {
		$query = "UPDATE #__weblinks"
		. "\n SET ordering = $order[$i]"
		. "\n WHERE id = $cid[$i]";
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// update ordering
		$row = new JWeblinkModel( $database );
		$row->load( $cid[$i] );
		$row->updateOrder();
	}

	$msg 	= 'New ordering saved';
	mosRedirect( 'index2.php?option=com_weblinks', $msg );
}
?>