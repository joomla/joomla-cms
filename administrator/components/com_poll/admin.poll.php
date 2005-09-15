<?php
/**
* @version $Id: admin.poll.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Polls
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
		| $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'com_poll' ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

switch( $task ) {
	case 'new':
		editPoll( 0, $option );
		break;

	case 'edit':
		editPoll( $cid[0], $option );
		break;

	case 'editA':
		editPoll( $id, $option );
		break;

	case 'save':
	case 'apply':
		savePoll( $task );
		break;

	case 'remove':
		removePoll( $cid, $option );
		break;

	case 'publish':
		publishPolls( $cid, 1, $option );
		break;

	case 'unpublish':
		publishPolls( $cid, 0, $option );
		break;

	case 'cancel':
		cancelPoll( $option );
		break;

	case 'checkin':
		checkin( $id );
		break;

	case 'preview':
		HTML_poll::popupPreview();
		break;

	default:
		showPolls( $option );
		break;
}

function showPolls( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$filter_state 	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 		= trim( strtolower( $search ) );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'm.published' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'm.published' );

	$where = array();
	if ( $search ) {
		$where[] = "m.title LIKE '%$search%'";
	}
	if ( $filter_state <> NULL ) {
		$where[] = "m.published = '$filter_state'";
	}
	if ( count( $where ) ) {
		$where = "\n WHERE ". implode( ' AND ', $where );
	} else {
		$where = '';
	}

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'm.published' ) ) {
		$tOrderDir = 'ASC';
	} else {
		$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'DESC' );
	}
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] = $tOrder;

	// table column ordering
	$order = "\n ORDER BY $tOrder $tOrderDir, m.published DESC, m.title ASC";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__polls AS m"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	// main query
	$query = "SELECT m.*, u.name AS editor, COUNT( d.id ) AS numoptions"
	. "\n FROM #__polls AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__poll_data AS d ON d.pollid = m.id AND d.text <> ''"
	. $where
	. "\n GROUP BY m.id"
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if ( $database->getErrorNum() ) {
		mosErrorAlert( $database->stderr() );
	}

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	$lists['search'] = stripslashes( $search );

	HTML_poll::showPolls( $rows, $pageNav, $option, $lists );
}

function editPoll( $uid=0, $option='com_poll' ) {
	global $database, $my, $mainframe;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	$row = new mosPoll( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut()) {
		mosErrorAlert( $_LANG->_( 'The poll' ) .' '. $row->title .' '. $_LANG->_( 'descBeingEditted' ) );
	}

	$options = array();

	if ($uid) {
		$row->checkout( $my->id );
		$query = "SELECT id, text"
		. "\n FROM #__poll_data"
		. "\n WHERE pollid = '$uid'"
		. "\n ORDER BY id"
		;
		$database->setQuery($query);
		$options = $database->loadObjectList();
	} else {
		$row->published = 1;
		$row->lag 		= 3600*24;
	}

	// get selected pages
	if ( $uid ) {
		$query = "SELECT menuid AS value"
		. "\n FROM #__poll_menu"
		. "\n WHERE pollid = '$row->id'"
		;
		$database->setQuery( $query );
		$lookup = $database->loadObjectList();
	} else {
		$lookup = array( mosHTML::makeOption( 0, $_LANG->_( 'All' ) ) );
	}

	// build the html select list
	mosFS::load( '@class', 'com_menus' );
	$lists['select'] 	= mosMenuFactory::buildMenuLinks( $lookup, 1, 1 );

	// build the html select list
	$lists['published'] = mosAdminMenus::Published( $row );

	HTML_poll::editPoll($row, $options, $lists );
}

function savePoll( $task ) {
	global $database, $my;
	global $_LANG;

	// save the poll parent information
	$row = new mosPoll( $database );
	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}
	$isNew = ( $row->id == 0 );

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}

	// save the poll options
	$options = mosGetParam( $_POST, 'polloption', '0' );

	// error check to ensure more than two options havebeen created
	if ( @$options[0] == '' && @$options[1] == '' ) {
		$alert = $_LANG->_( 'validNumPollOptions' );
		mosErrorAlert( $alert );
	}

	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();

	foreach ( $options as $i => $text ) {
		if ( $text ) {
			// 'slash' the options
			if ( !get_magic_quotes_gpc() ) {
				$text = addslashes( $text );
			}

			if ( $isNew ) {
				$query = "INSERT INTO #__poll_data"
				/ "\n ( pollid, text ) VALUES ( $row->id, '$text' )"
				;
				$database->setQuery( $query );
				$database->query();
			} else {
				$query = "UPDATE #__poll_data"
				. "\n SET text = '$text'"
				. "\n WHERE id = '$i'"
				. "\n AND pollid = '$row->id'"
				;
				$database->setQuery( $query );
				$database->query();
			}
		}
	}

	// update the menu visibility
	$selections = mosGetParam( $_POST, 'selections', array() );

	$query = "DELETE FROM #__poll_menu"
	. "\n WHERE pollid = '$row->id'"
	;
	$database->setQuery( $query );
	$database->query();

	for ( $i=0, $n=count($selections); $i < $n; $i++ ) {
		$query = "INSERT INTO #__poll_menu"
		. "\n SET pollid = '$row->id', menuid = '$selections[$i]'"
		;
		$database->setQuery( $query );
		$database->query();
	}

	switch ( $task ) {
		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes' );
			mosRedirect( 'index2.php?option=com_poll&task=editA&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved' );
			mosRedirect( 'index2.php?option=com_poll', $msg );
			break;
	}
}

function removePoll( $cid, $option ) {
	global $database;

	$msg = '';
	$n = count( $cid );
	for ( $i=0; $i < $n; $i++ ) {
		$poll = new mosPoll( $database );
		// delete poll
		if ( !$poll->delete( $cid[$i] ) ) {
			$msg = $poll->getError();
		}
	}

	mosRedirect( 'index2.php?option='. $option .'&mosmsg='. $msg );
}

/**
* Publishes or Unpublishes one or more records
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current url option
*/
function publishPolls( $cid=null, $publish=1, $option ) {
	global $database, $my;
	global $_LANG;

	$catid = mosGetParam( $_POST, 'catid', array(0) );

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		mosErrorAlert( $_LANG->_( 'Select an item to' ) ." ". $action );
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__polls"
	. "\n SET published = '$publish'"
	. "\n WHERE id IN ($cids)"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosPoll( $database );
		$row->checkin( $cid[0] );
	}
	mosRedirect( 'index2.php?option='. $option );
}

function cancelPoll( $option ) {
	global $database;

	$row = new mosPoll( $database );
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect( 'index2.php?option='. $option );
}

function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosPoll( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_poll', $msg );
}
?>