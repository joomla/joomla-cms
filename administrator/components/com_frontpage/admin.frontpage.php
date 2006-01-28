<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
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

// ensure user has access to this function
if (!$acl->acl_check( 'com_frontpage', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

// call
require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$task 	= mosGetParam( $_REQUEST, 'task', array(0) );
$cid 	= mosGetParam( $_POST, 'cid', array(0) );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'publish':
		changeFrontPage( $cid, 1, $option );
		break;

	case 'unpublish':
		changeFrontPage( $cid, 0, $option );
		break;

	case 'archive':
		changeFrontPage( $cid, -1, $option );
		break;

	case 'remove':
		removeFrontPage( $cid, $option );
		break;

	case 'orderup':
		orderFrontPage( $cid[0], -1, $option );
		break;

	case 'orderdown':
		orderFrontPage( $cid[0], 1, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0 );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1 );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2 );
		break;

	default:
		viewFrontPage( $option );
		break;
}


/**
* Compiles a list of frontpage items
*/
function viewFrontPage( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'fpordering' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$catid 				= $mainframe->getUserStateFromRequest( "$option.catid", 			'catid', 			0 );
	$filter_authorid 	= $mainframe->getUserStateFromRequest( "$option.filter_authorid", 	'filter_authorid', 	0 );
	$filter_sectionid 	= $mainframe->getUserStateFromRequest( "$option.filter_sectionid", 	'filter_sectionid', 0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );

	$where = array(
		"c.state >= 0"
	);

	// used by filter
	if ( $filter_sectionid > 0 ) {
		$where[] = "c.sectionid = '$filter_sectionid'";
	}
	if ( $catid > 0 ) {
		$where[] = "c.catid = '$catid'";
	}
	if ( $filter_authorid > 0 ) {
		$where[] = "c.created_by = $filter_authorid";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "c.state = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "c.state = 0";
		}
	}

	if ($search) {
		$where[] = "LOWER( c.title ) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, fpordering";
	
	// get the total number of records
	$query = "SELECT count(*)"
	. "\n FROM #__content AS c"
	. "\n INNER JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n INNER JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT c.*, g.name AS groupname, cc.name, s.name AS sect_name, u.name AS editor, f.ordering AS fpordering, v.name AS author"
	. "\n FROM #__content AS c"
	. "\n INNER JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n INNER JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. "\n INNER JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__users AS v ON v.id = c.created_by"
	. $where
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart,$pageNav->limit );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	// get list of categories for dropdown filter
	$query = "SELECT cc.id AS value, cc.title AS text, section"
	. "\n FROM #__categories AS cc"
	. "\n INNER JOIN #__sections AS s ON s.id = cc.section "
	. "\n ORDER BY s.ordering, cc.ordering"
	;
	$database->setQuery( $query );
	$categories[] 	= mosHTML::makeOption( '0', '- '. JText::_( 'Select Category' ) .' -' );
	$categories 	= array_merge( $categories, $database->loadObjectList() );
	$lists['catid'] = mosHTML::selectList( $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $catid );

	// get list of sections for dropdown filter
	$javascript			= 'onchange="document.adminForm.submit();"';
	$lists['sectionid']	= mosAdminMenus::SelectSection( 'filter_sectionid', $filter_sectionid, $javascript );

	// get list of Authors for dropdown filter
	$query = "SELECT c.created_by, u.name"
	. "\n FROM #__content AS c"
	. "\n INNER JOIN #__sections AS s ON s.id = c.sectionid"
	. "\n LEFT JOIN #__users AS u ON u.id = c.created_by"
	. "\n WHERE c.state <> -1"
	. "\n AND c.state <> -2"
	. "\n GROUP BY u.name"
	. "\n ORDER BY u.name"
	;
	$database->setQuery( $query );
	$authors[] 			= mosHTML::makeOption( '0', '- '. JText::_( 'Select Author' ) .' -', 'created_by', 'name' );
	$authors 			= array_merge( $authors, $database->loadObjectList() );
	$lists['authorid']	= mosHTML::selectList( $authors, 'filter_authorid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'created_by', 'name', $filter_authorid );
	
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

	HTML_content::showList( $rows, $pageNav, $option, $lists );
}

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function changeFrontPage( $cid=null, $state=0, $option ) {
	global $database, $my;

	if (count( $cid ) < 1) {
		$action = $state == 1 ? 'publish' : ($state == -1 ? 'archive' : 'unpublish');
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__content"
	. "\n SET state = $state"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row =& JModel::getInstance('content', $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( "index2.php?option=$option" );
}

function removeFrontPage( &$cid, $option ) {
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}
	$fp = new JFrontPageModel( $database );
	foreach ($cid as $id) {
		if (!$fp->delete( $id )) {
			echo "<script> alert('".$fp->getError()."'); </script>\n";
			exit();
		}
		$obj =& JModel::getInstance('content', $database );
		$obj->load( $id );
		$obj->mask = 0;
		if (!$obj->store()) {
			echo "<script> alert('".$fp->getError()."'); </script>\n";
			exit();
		}
	}
	$fp->updateOrder();

	mosRedirect( "index2.php?option=$option" );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderFrontPage( $uid, $inc, $option ) {
	global $database;

	$fp = new JFrontPageModel( $database );
	$fp->load( $uid );
	$fp->move( $inc );

	mosRedirect( "index2.php?option=$option" );
}

/**
* @param integer The id of the content item
* @param integer The new access level
* @param string The URL option
*/
function accessMenu( $uid, $access ) {
	global $database;

	$row =& JModel::getInstance('content', $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	mosRedirect( 'index2.php?option=com_frontpage' );
}

function saveOrder( &$cid ) {
	global $database;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );

	for( $i=0; $i < $total; $i++ ) {
		$query = "UPDATE #__content_frontpage"
		. "\n SET ordering = $order[$i]"
		. "\n WHERE content_id = $cid[$i]";
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// update ordering
		$row = new JFrontPageModel( $database );
		$row->load( $cid[$i] );
		$row->updateOrder();
	}

	$msg 	= JText::_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_frontpage', $msg );
}
?>