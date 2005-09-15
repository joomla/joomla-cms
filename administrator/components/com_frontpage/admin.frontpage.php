<?php
/**
* @version $Id: admin.frontpage.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!($acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' )
		| $acl->acl_check( 'com_frontpage', 'manage', 'users', $my->usertype ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

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
	global $database, $mainframe, $mosConfig_list_limit, $_LANG;

	$catid 				= $mainframe->getUserStateFromRequest( "catid{$option}", 'catid', 0 );
	$filter_authorid 	= $mainframe->getUserStateFromRequest( "filter_authorid{$option}", 'filter_authorid', 0 );
	$filter_sectionid 	= $mainframe->getUserStateFromRequest( "filter_sectionid{$option}", 'filter_sectionid', 0 );
	$filter_state	 	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$filter_access	 	= $mainframe->getUserStateFromRequest( "filter_access{$option}", 'filter_access', NULL );
	$limit 				= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 		= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 			= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 			= trim( strtolower( $search ) );
	$tOrder				= mosGetParam( $_POST, 'tOrder', 'f.ordering' );
	$tOrder_old			= mosGetParam( $_POST, 'tOrder_old', 'f.ordering' );

	mosFS::load( '@class', 'com_content' );

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'c.state' ) ) {
		$tOrderDir = 'ASC';
	} else {
		$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'ASC' );
	}
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] 		= $tOrder;

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
		$where[] = "c.created_by = '$filter_authorid'";
	}
	if ( $filter_state <> NULL ) {
		$where[] = "c.state = '$filter_state'";
	}
	if ( $filter_access <> NULL ) {
		$where[] = "c.access = '$filter_access'";
	}

	if ($search) {
		$where[] = "LOWER( c.title ) LIKE '%$search%'";
	}

	$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

	// table column ordering
	$order = "\n ORDER BY $tOrder $tOrderDir, f.ordering ASC";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__content AS c"
	. "\n INNER JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n INNER JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	$query = "SELECT c.*, g.name AS groupname, cc.name, s.name AS sect_name, u.name AS editor, f.ordering AS fpordering, v.name AS author"
	//$query = "SELECT c.*, g.name AS groupname, u.name AS editor, f.ordering AS fpordering, v.name AS author"
	. "\n FROM #__content AS c"
	//. "\n INNER JOIN #__categories AS cc ON cc.id = c.catid"
	//. "\n INNER JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. "\n INNER JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__users AS v ON v.id = c.created_by"
	. $where
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		mosErrorAlert( $database->stderr() );
	}

	// get list of categories for dropdown filter
	$query = "SELECT cc.id AS value, cc.title AS text, section"
	. "\n FROM #__categories AS cc"
	. "\n INNER JOIN #__sections AS s ON s.id=cc.section "
	. "\n ORDER BY s.ordering, cc.ordering"
	;
	$categories[] = mosHTML::makeOption( '0', '- ' . $_LANG->_( 'Category' ) . ' -' );
	$database->setQuery( $query );
	$categories = array_merge( $categories, $database->loadObjectList() );
	$lists['catid'] = mosHTML::selectList( $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $catid );

	// get list of sections for dropdown filter
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['sectionid']	= mosContentFactory::buildSelectSection( 'filter_sectionid', $filter_sectionid, $javascript );

	// get list of Authors for dropdown filter
	$query = "SELECT c.created_by AS value, u.name AS text"
	. "\n FROM #__content AS c"
	. "\n INNER JOIN #__sections AS s ON s.id = c.sectionid"
	. "\n LEFT JOIN #__users AS u ON u.id = c.created_by"
	. "\n WHERE c.state <> '-1'"
	. "\n AND c.state <> '-2'"
	. "\n GROUP BY u.name"
	. "\n ORDER BY u.name"
	;
	$authors[] = mosHTML::makeOption( '0', '- ' . $_LANG->_( 'Author' ) . ' -' );
	$database->setQuery( $query );
	$authors = array_merge( $authors, $database->loadObjectList() );
	$lists['authorid']	= mosHTML::selectList( $authors, 'filter_authorid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_authorid );

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	// get list of Access for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['access']	= mosAdminHTML::accessList( 'filter_access', $filter_access, $javascript );

	$search = stripslashes( $search );

	mosContentFactory::contenttreeQueries( $lists );

	HTML_content::showList( $rows, $search, $pageNav, $option, $lists );
}

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function changeFrontPage( $cid=null, $state=0, $option ) {
	global $database, $my;
    global $_LANG;

	if (count( $cid ) < 1) {
		$action = $publish == 1 ? 'publish' : ($publish == -1 ? 'archive' : 'unpublish');
		echo "<script> alert('". $_LANG->_( 'Select an item to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__content SET state='$state'"
	. "\n WHERE id IN ($cids) AND (checked_out=0 OR (checked_out='$my->id'))"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new mosContent( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( "index2.php?option=$option" );
}

function removeFrontPage( &$cid, $option ) {
	global $database;
    global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". $_LANG->_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}
	$fp = new mosFrontPage( $database );
	foreach ($cid as $id) {
		if (!$fp->delete( $id )) {
			echo "<script> alert('".$fp->getError()."'); </script>\n";
			exit();
		}
		$obj = new mosContent( $database );
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

	$fp = new mosFrontPage( $database );
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

	$row = new mosContent( $database );
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
    global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );

	for( $i=0; $i < $total; $i++ ) {
		$query = "UPDATE #__content_frontpage SET ordering='$order[$i]' WHERE content_id = $cid[$i]";
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// update ordering
		$row = new mosFrontPage( $database );
		$row->load( $cid[$i] );
		$row->updateOrder();
	}

	$msg 	= $_LANG->_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_frontpage', $msg );
}
?>