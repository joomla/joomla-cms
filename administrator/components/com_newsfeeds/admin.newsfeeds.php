<?php
/**
* @version $Id: admin.newsfeeds.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Newsfeeds
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
 | $acl->acl_check( 'com_newsfeeds', 'manage', 'users', $my->usertype ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}
mosFS::load( '@class' );
mosFS::load( '@admin_html' );

switch ($task) {
	case 'cancel':
		cancelNewsFeed( $option );
		break;

	case 'new':
		editNewsFeed( 0, $option );
		break;

	case 'edit':
		editNewsFeed( $cid[0], $option );
		break;

	case 'editA':
		editNewsFeed( $id, $option );
		break;

	case 'apply':
	case 'save':
		saveNewsFeed( $task );
		break;

	case 'publish':
		publishNewsFeeds( $cid, 1, $option );
		break;

	case 'unpublish':
		publishNewsFeeds( $cid, 0, $option );
		break;

	case 'remove':
		removeNewsFeeds( $cid, $option );
		break;

	case 'orderup':
		orderNewsFeed( $cid[0], -1, $option );
		break;

	case 'orderdown':
		orderNewsFeed( $cid[0], 1, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'checkin':
		checkin( $id );
		break;

	default:
		showNewsFeeds( $option );
		break;
}

/**
* List the records
* @param string The current GET/POST option
*/
function showNewsFeeds( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$filter_state 	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$catid 			= $mainframe->getUserStateFromRequest( "catid{$option}", 'catid', 0 );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 		= trim( strtolower( $search ) );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'category' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'category' );

	mosFS::load( '@class', 'com_components' );

	// used by filter
	$filter = array();
	// used by filter
	if ( $search ) {
		$filter[] = "a.name LIKE '%$search%'";
	}
	if ( $catid ) {
		$filter[] = "a.catid = '$catid'";
	}
	if ( $filter_state <> NULL ) {
		$filter[] = "a.published = '$filter_state'";
	}
	if ( count( $filter ) ) {
		$filter = "\n WHERE ". implode( ' AND ', $filter );
	} else {
		$filter = '';
	}

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'a.published' ) ) {
		$tOrderDir = 'ASC';
	} else {
		$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'ASC' );
	}
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] = $tOrder;

	// table column ordering
	switch ( $tOrder ) {
		default:
			$order = "\n ORDER BY $tOrder $tOrderDir, category ASC, a.ordering ASC";
			break;
	}

	// get the total number of records
	$query = "SELECT COUNT( * )"
	. "\n FROM #__newsfeeds AS  a"
	. $filter
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	// get the subset (based on limits) of required records
	$query = "SELECT a.*, c.name AS catname, u.name AS editor, c.title AS category"
	. "\n FROM #__newsfeeds AS a"
	. "\n LEFT JOIN #__categories AS c ON c.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. $filter
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if ( $database->getErrorNum() ) {
		mosErrorAlert( $database->stderr() );
	}
	// build list of categories
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['catid'] 	= mosComponentFactory::buildCategoryList( 'catid', $option, $catid, $javascript );

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	$lists['search'] = stripslashes( $search );

	HTML_newsfeeds::showNewsFeeds( $rows, $lists, $pageNav, $option );
}

/**
* Creates a new or edits and existing user record
* @param int The id of the user, 0 if a new entry
* @param string The current GET/POST option
*/
function editNewsFeed( $id, $option ) {
	global $database, $my, $mainframe;

	$mainframe->set('disableMenu', true);

	mosFS::load( '@class', 'com_components' );

	$catid = intval( mosGetParam( $_REQUEST, 'catid', 0 ) );

	$row = new mosNewsFeed( $database );
	// load the row from the db table
	$row->load( $id );

	if ($id) {
		// do stuff for existing records
		$row->checkout( $my->id );
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
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $id, $query, 1 );

	// build list of categories
	$lists['category'] 			= mosComponentFactory::buildCategoryList( 'catid', $option, intval( $row->catid ) );
	// build the html select list
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

	HTML_newsfeeds::editNewsFeed( $row, $lists, $option );
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveNewsFeed( $task ) {
	global $database, $my;
	global $_LANG;

	$row = new mosNewsFeed( $database );
	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

	// pre-save checks
	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}

	// save the changes
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	$row->updateOrder();

	switch ( $task ) {
		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes' );
			mosRedirect( 'index2.php?option=com_newsfeeds&task=editA&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved' );

			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer ) {
				mosRedirect( $referer, $msg );
			} else {
				mosRedirect( 'index2.php?option=com_newsfeeds', $msg );
			}
			break;
	}
}

/**
* Publishes or Unpublishes one or more modules
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current GET/POST option
*/
function publishNewsFeeds( $cid, $publish, $option ) {
	global $database;
	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $publish ? $_LANG->_( 'publish' ) : $_LANG->_( 'unpublish' );
		mosErrorAlert( $_LANG->_( 'Select a module to' ) .' '. $action );
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__newsfeeds"
	. "\n SET published = '$publish'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosNewsFeed( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( 'index2.php?option='. $option );
}

/**
* Removes records
* @param array An array of id keys to remove
* @param string The current GET/POST option
*/
function removeNewsFeeds( &$cid, $option ) {
	global $database;
	global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to delete' ) );
	}
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__newsfeeds"
		. "\n WHERE id IN ( $cids )"
		. "\n AND checked_out='0'"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $database->getErrorMsg() );
		}
	}

	mosRedirect( 'index2.php?option='. $option );
}

/**
* Cancels an edit operation
* @param string The current GET/POST option
*/
function cancelNewsFeed( $option ) {
	global $database;

	$row = new mosNewsFeed( $database );
	$row->bind( $_POST );
	$row->checkin();
	mosRedirect( 'index2.php?option='. $option );
}

/**
* Moves the order of a record
* @param integer The id of the record to move
* @param integer The direction to reorder, +1 down, -1 up
* @param string The current GET/POST option
*/
function orderNewsFeed( $id, $inc, $option ) {
	global $database;

	$limit = mosGetParam( $_REQUEST, 'limit', 0 );
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', 0 );
	$catid = intval( mosGetParam( $_REQUEST, 'catid', 0 ) );

	$row = new mosNewsFeed( $database );
	$row->load( $id );
	$row->move( $inc );

	mosRedirect( 'index2.php?option='. $option );
}

function saveOrder( &$cid ) {
	global $database;
  	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosNewsFeed( $database );
	$conditions = array();

    // update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );

		if ( $row->ordering != $order[$i] ) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				mosErrorAlert( $database->getErrorMsg() );
			} // if

			// remember to updateOrder this group
			$condition = "catid = '$row->catid'";
			$found = false;
			foreach ( $conditions as $cond ) {
				if ($cond[1]==$condition) {
					$found = true;
					break;
				} // if
		   }
		   if ( !$found ) {
		   	$conditions[] = array($row->id, $condition);
		   }
		} // if
	} // for

	// execute updateOrder for each group
	foreach ( $conditions as $cond ) {
		$row->load( $cond[0] );
		$row->updateOrder( $cond[1] );
	} // foreach

	$msg = $_LANG->_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_newsfeeds', $msg );
} // saveOrder

function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosNewsFeed( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_newsfeeds', $msg );
}
?>