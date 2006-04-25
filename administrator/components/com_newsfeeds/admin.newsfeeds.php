<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
if (!$user->authorize( 'com_newsfeeds', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$task 	= JRequest::getVar( 'task');

switch ($task) {

	case 'new':
	case 'edit':
		editNewsFeed( );
		break;

	case 'save':
	case 'apply':
		saveNewsFeed( );
		break;

	case 'publish':
		publishNewsFeeds( );
		break;

	case 'unpublish':
		unPublishNewsFeeds( );
		break;

	case 'remove':
		removeNewsFeeds( );
		break;

	case 'cancel':
		cancelNewsFeed( );
		break;

	case 'orderup':
		moveUpNewsFeed( );
		break;

	case 'orderdown':
		moveDownNewsFeed( );
		break;

	case 'saveorder':
		saveOrder( );
		break;

	default:
		showNewsFeeds( );
		break;
}

/**
* List the records
*/
function showNewsFeeds(  ) {
	global $mainframe;

	$option = JRequest::getVar( 'option');
	$database 			= $mainframe->getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'catname' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$filter_catid 		= $mainframe->getUserStateFromRequest( "$option.filter_catid", 		'filter_catid',		0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( JString::strtolower( $search ) ) );

	$where = array();
	if ( $filter_catid ) {
		$where[] = "a.catid = $filter_catid";
	}
	if ($search) {
		$where[] = "LOWER(a.name) LIKE '%$search%'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "a.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "a.published = 0";
		}
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, catname, a.ordering";

	// get the total number of records
	$query = "SELECT COUNT(*) "
	. "\n FROM #__newsfeeds AS a"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	// get the subset (based on limits) of required records
	$query = "SELECT a.*, c.name AS catname, u.name AS editor"
	. "\n FROM #__newsfeeds AS a"
	. "\n LEFT JOIN #__categories AS c ON c.id = a.catid"
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
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['catid'] = mosAdminMenus::ComponentCategory( 'filter_catid', 'com_newsfeeds', $filter_catid, $javascript );

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

	HTML_newsfeeds::showNewsFeeds( $rows, $lists, $pageNav, $option );
}

/**
* Creates a new or edits and existing user record
*/
function editNewsFeed(  ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$user 		= $mainframe->getUser();
	$catid 		= JRequest::getVar( 'catid', 0, '', 'int' );
	$cid 		= JRequest::getVar( 'cid', array(0));
	$option 	= JRequest::getVar( 'option');
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	$row 		= new mosNewsFeed( $database );
	// load the row from the db table
	$row->load( $cid[0] );

	if ($cid[0]) {
		// do stuff for existing records
		$row->checkout( $user->get('id') );
	} else {
		// do stuff for new records
		$row->ordering 		= 0;
		$row->numarticles 	= 5;
		$row->cache_time 	= 3600;
		$row->published 	= 1;
	}

	// build the html select list for ordering
	$query = "SELECT a.ordering AS value, a.name AS text"
	. "\n FROM #__newsfeeds AS a"
	. "\n ORDER BY a.ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $cid[0], $query, 1 );

	// build list of categories
	$lists['category'] 			= mosAdminMenus::ComponentCategory( 'catid', $option, intval( $row->catid ) );
	// build the html select list
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

	HTML_newsfeeds::editNewsFeed( $row, $lists, $option );
}

/**
* Saves the record from an edit form submit
*/
function saveNewsFeed(  ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$task 	= JRequest::getVar( 'task');

	$row 		= new mosNewsFeed( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// Sets rtl value when rtl checkbox ticked
	$isRtl = JRequest::getVar( 'rtl', 0 );
	if ( $isRtl) {
		$row->rtl = 1;
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
	$row->reorder();

	switch ($task) {
		case 'apply':
			$link = 'index2.php?option=com_newsfeeds&task=edit&cid[]='. $row->id .'&hidemainmenu=1';
			break;

		case 'save':
		default:
			$link = 'index2.php?option=com_newsfeeds';
			break;
	}

	josRedirect( $link );
}

/**
* Publishes one or more modules
*/
function publishNewsFeeds(  ) {
	changePublishNewsFeeds( 1 );
}

/**
* Unpublishes one or more modules
*/
function unPublishNewsFeeds(  ) {
	changePublishNewsFeeds( 0 );
}

/**
* Publishes or Unpublishes one or more modules
* @param integer 0 if unpublishing, 1 if publishing
*/
function changePublishNewsFeeds( $publish ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$user 		= $mainframe->getUser();
	$cid 		= JRequest::getVar( 'cid', array(0));
	$option = JRequest::getVar( 'option');
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	if (count( $cid ) < 1) {
		$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
		echo "<script> alert('". JText::_( 'Select a module to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__newsfeeds"
	. "\n SET published = ". intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $user->get('id') ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new mosNewsFeed( $database );
		$row->checkin( $cid[0] );
	}

	josRedirect( 'index2.php?option='. $option );
}

/**
* Removes records
*/
function removeNewsFeeds( ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$cid 		= JRequest::getVar( 'cid', array(0));
	$option = JRequest::getVar( 'option');
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__newsfeeds"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	josRedirect( 'index2.php?option='. $option );
}

/**
* Cancels an edit operation
*/
function cancelNewsFeed(  ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$option = JRequest::getVar( 'option');

	$row = new mosNewsFeed( $database );
	$row->bind( $_POST );
	$row->checkin();
	josRedirect( 'index2.php?option='. $option );
}

/**
* Moves the record up one position
*/
function moveUpNewsFeed(  ) {
	orderNewsFeed( -1 );
}

/**
* Moves the record down one position
*/
function moveDownNewsFeed(  ) {
	orderNewsFeed( 1 );
}

/**
* Moves the order of a record
* @param integer The direction to reorder, +1 down, -1 up
*/
function orderNewsFeed( $inc ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$cid 		= JRequest::getVar( 'cid', array(0));
	$option = JRequest::getVar( 'option');
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	$limit 		= JRequest::getVar( 'limit', 0, '', 'int' );
	$limitstart = JRequest::getVar( 'limitstart', 0, '', 'int' );
	$catid 		= JRequest::getVar( 'catid', 0, '', 'int' );

	$row = new mosNewsFeed( $database );
	$row->load( $cid[0] );
	$row->move( $inc, "catid = $row->catid AND published != 0" );

	josRedirect( 'index2.php?option='. $option );
}

/**
* Saves user reordering entry
*/
function saveOrder(  ) {
	global $mainframe;

	$database 	= $mainframe->getDBO();
	$cid 		= JRequest::getVar( 'cid' );

	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );

	for( $i=0; $i < $total; $i++ ) {
		$query = "UPDATE #__newsfeeds"
		. "\n SET ordering = $order[$i]"
		. "\n WHERE id = $cid[$i]";
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// update ordering
		$row = new mosNewsFeed( $database );
		$row->load( $cid[$i] );
		$row->reorder( "catid = $row->catid AND published != 0" );
	}

	$msg 	= 'New ordering saved';
	josRedirect( 'index2.php?option=com_newsfeeds', $msg );
}
?>