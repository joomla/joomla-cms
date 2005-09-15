<?php
/**
* @version $Id: admin.contact.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Contact
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
 | $acl->acl_check( 'com_contact', 'manage', 'users', $my->usertype ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

switch ($task) {
	case 'cancel':
		cancelContact();
		break;

	case 'orderup':
	case 'orderdown':
		orderModule( $cid[0], ($task == 'orderup' ? -1 : 1), $option );
		break;

	case 'new':
		editContact( '0', $option);
		break;

	case 'edit':
		editContact( $cid[0], $option );
		break;

	case 'editA':
		editContact( $id, $option );
		break;

	case 'save':
	case 'apply':
		saveContact( $task );
		break;

	case 'remove':
		removeContacts( $cid, $option );
		break;

	case 'publish':
		changeContact( $cid, 1, $option );
		break;

	case 'unpublish':
		changeContact( $cid, 0, $option );
		break;

	case 'orderup':
		orderContacts( $cid[0], -1, $option );
		break;

	case 'orderdown':
		orderContacts( $cid[0], 1, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'checkin':
		checkin( $id );
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

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'cd.published' ) ) {
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

	// used by filter
	if ( $search ) {
		$where[] = "cd.name LIKE '%$search%'";
	}
	if ( $catid ) {
		$where[] = "cd.catid = '$catid'";
	}
	if ( $filter_state <> NULL ) {
		$where[] = "cd.published = '$filter_state'";
	}
	if ( isset( $where ) ) {
		$where = "\n WHERE ". implode( ' AND ', $where );
	} else {
		$where = '';
	}

	// table column ordering
	switch ( $tOrder ) {
		default:
			$order = "\n ORDER BY $tOrder $tOrderDir, category ASC, cd.ordering ASC";
			break;
	}

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__contact_details AS cd"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	// main query
	// get the subset (based on limits) of required records
	$query = "SELECT cd.*, cc.title AS category, u.name AS user, v.name as editor"
	. "\n FROM #__contact_details AS cd"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = cd.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = cd.user_id"
	. "\n LEFT JOIN #__users AS v ON v.id = cd.checked_out"
	. $where
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

	// build list of categories
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['catid'] = mosComponentFactory::buildCategoryList( 'catid', 'com_contact_details', intval( $catid ), $javascript );

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	$lists['search'] = stripslashes( $search );

	HTML_contact::showcontacts( $rows, $pageNav, $option, $lists );
}

/**
* Creates a new or edits and existing user record
* @param int The id of the record, 0 if a new entry
* @param string The current GET/POST option
*/
function editContact( $id, $option ) {
	global $database, $my, $mainframe;
	global $mosConfig_absolute_path;

	$mainframe->set('disableMenu', true);

	mosFS::load( '@class', 'com_components' );

	$row = new mosContact( $database );
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
	$lists['user_id'] 			= mosAdminHTML::userSelect( 'user_id', $row->user_id, 1 );
	// build list of categories
	$lists['catid'] 			= mosComponentFactory::buildCategoryList( 'catid', 'com_contact_details', intval( $row->catid ) );
	// build the html select list for images
	$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= mosAdminMenus::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= mosHTML::yesnoradioList( 'published', '', $row->published );
	// build the html radio buttons for default
	$lists['default_con'] 		= mosHTML::yesnoradioList( 'default_con', '', $row->default_con );

	// get params definitions
	$file = $mosConfig_absolute_path .'/administrator/components/com_contact/contact_items.xml';
	$params = new mosParameters( $row->params, $file, 'component' );

	HTML_contact::editcontact( $row, $lists, $option, $params );
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveContact( $task ) {
	global $database;
	global $_LANG;

	$row = new mosContact( $database );
	if ( !$row->bind( $_POST ) ) {
		mosErrorAlert( $row->getError() );
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

	// pre-save checks
	if ( !$row->check() ) {
		mosErrorAlert( $row->getError() );
	}

	// save the changes
	if ( !$row->store() ) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	$row->updateOrder( "catid='$row->catid' AND published >= 0" );

	switch ( $task ) {
		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes' );
			mosRedirect( 'index2.php?option=com_contact&amp;task=editA&amp;id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved' );

			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer ) {
				mosRedirect( $referer, $msg );
			} else {
				mosRedirect( 'index2.php?option=com_contact', $msg );
			}
			break;
	}
}

/**
* Removes records
* @param array An array of id keys to remove
* @param string The current GET/POST option
*/
function removeContacts( &$cid, $option ) {
	global $database;

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__contact_details WHERE id IN ( $cids )";
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $database->getErrorMsg() );
		}
	}

	mosRedirect( "index2.php?option=$option" );
}

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current option
*/
function changeContact( $cid=null, $state=0, $option ) {
	global $database, $my;
	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $state == 1 ? 'publish' : 'unpublish';
		echo "<script> alert('". $_LANG->_( 'Select a record to' ) .' '. $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__contact_details"
	. "\n SET published = '$state'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )";
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosContact( $database );
		$row->checkin( intval( $cid[0] ) );
	}

	mosRedirect( "index2.php?option=$option" );
}

/** JJC
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderContacts( $uid, $inc, $option ) {
	global $database;

	$row = new mosContact( $database );
	$row->load( $uid );
	$row->move( $inc, "published >= 0" );

	mosRedirect( "index2.php?option=$option" );
}

/** PT
* Cancels editing and checks in the record
*/
function cancelContact() {
	global $database;

	$row = new mosContact( $database );
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect('index2.php?option=com_contact');
}

function saveOrder( &$cid ) {
	global $database;
  	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosContact( $database );
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
			$condition = "catid='$row->catid'";
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
	mosRedirect( 'index2.php?option=com_contact', $msg );
} // saveOrder

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderModule( $uid, $inc, $option ) {
	global $database;

	$row = new mosContact( $database );
	$row->load( $uid );

	$row->move( $inc, "catid='$row->catid' AND published >= 0"  );

	mosRedirect( 'index2.php?option=com_contact' );
}

function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosContact( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_contact', $msg );
}
?>