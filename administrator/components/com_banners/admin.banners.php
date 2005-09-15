<?php
/**
* @version $Id: admin.banners.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Banners
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
	| $acl->acl_check( 'com_banners', 'manage', 'users', $my->usertype ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

switch ($task) {
	case 'newclient':
		editBannerClient( 0, $option );
		break;

	case 'editclient':
		editBannerClient( $cid[0], $option );
		break;

	case 'editclientA':
		editBannerClient( $id, $option );
		break;

	case 'saveclient':
	case 'applyclient':
		saveBannerClient( $task );
		break;

	case 'removeclients':
		removeBannerClients( $cid );
		break;

	case 'cancelclient':
		cancelEditClient( );
		break;

	case 'listclients':
		viewBannerClients( $option );
		break;

	case 'checkinclients':
		checkinClients( $id );
		break;

	// BANNER EVENTS

	case 'new':
		editBanner( null, $option );
		break;

	case 'cancel':
		cancelEditBanner();
		break;

	case 'save':
	case 'apply':
	case 'resethits':
		saveBanner( $task );
		break;

	case 'edit':
		editBanner( $cid[0], $option );
		break;

	case 'editA':
		editBanner( $id, $option );
		break;

	case 'remove':
		removeBanner( $cid );
		break;

	case 'publish':
		publishBanner( $cid,1 );
		break;

	case 'unpublish':
		publishBanner( $cid, 0 );
		break;

	case 'checkin':
		checkin( $id );
		break;

	default:
		viewBanners( $option );
		break;
}

function viewBanners( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$filter_state	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "viewban{$option}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 		= trim( strtolower( $search ) );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'b.showBanner' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'b.showBanner' );

	// used by filter
	$where = array();
	if ( $search ) {
		$where[] = "b.name LIKE '%$search%'";
	}
	if ( $filter_state <> NULL ) {
		$where[] = "b.showBanner = '$filter_state'";
	}
	if ( count( $where ) ) {
		$where = "\n WHERE ". implode( ' AND ', $where );
	} else {
		$where = '';
	}

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'b.showBanner' ) ) {
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
			$order = "\n ORDER BY $tOrder $tOrderDir, b.showBanner, b.bid DESC, b.name ASC";
			break;
	}

	// get the total number of records
	$query = "SELECT COUNT( * )"
	. "\n FROM #__banner AS b"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	$query = "SELECT b.*, u.name as editor"
	. "\n FROM #__banner AS b "
	. "\n LEFT JOIN #__users AS u ON u.id = b.checked_out"
	. $where
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if( !$result = $database->query() ) {
		mosErrorAlert( $database->stderr() );
	}

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	$lists['search'] = stripslashes( $search );

	HTML_banners::showBanners( $rows, $pageNav, $option, $lists );
}

function editBanner( $bannerid, $option ) {
	global $database, $my, $mainframe;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	$lists = array();

	$row = new mosBanner($database);
	$row->load( $bannerid );

  if ( $bannerid ){
    $row->checkout( $my->id );
  }

	// Build Client select list
	$sql	= "SELECT cid as value, name as text FROM #__bannerclient";
	$database->setQuery($sql);
	if (!$database->query()) {
		mosErrorAlert( $database->stderr() );
	}

	$clientlist[] = mosHTML::makeOption( '0', $_LANG->_( 'Select Client' ) );
	$clientlist = array_merge( $clientlist, $database->loadObjectList() );
	$lists['cid'] = mosHTML::selectList( $clientlist, 'cid', 'class="inputbox" size="1"','value', 'text', $row->cid);

	// Imagelist
	$javascript = 'onchange="changeDisplayImage();"';
	$directory = '/images/banners';
	$lists['imageurl'] = mosAdminMenus::Images( 'imageurl', $row->imageurl, $javascript, $directory );


	// make the select list for the image positions
	$yesno[] = mosHTML::makeOption( '0', $_LANG->_( 'No' ) );
  	$yesno[] = mosHTML::makeOption( '1', $_LANG->_( 'Yes' ) );

  	$lists['showBanner'] = mosHTML::selectList( $yesno, 'showBanner', 'class="inputbox" size="1"' , 'value', 'text', $row->showBanner );

	HTML_banners::bannerForm( $row, $lists, $option );
}

function saveBanner( $task ) {
	global $database;
	global $_LANG;
	$row = new mosBanner($database);

	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

	if ( $task == 'resethits' ) {
		$row->clicks = 0;
		$msg = $_LANG->_( 'Reset Banner clicks' );
	}

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}

	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();

	switch ( $task ) {
		case 'resethits':
			mosRedirect( 'index2.php?option=com_banners&task=editA&id='. $row->bid, $msg );
			break;

		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes' );
			mosRedirect( 'index2.php?option=com_banners&task=editA&id='. $row->bid, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved' );
			mosRedirect( 'index2.php?option=com_banners', $msg );
			break;
	}
}

function cancelEditBanner() {
	global $database;

	$row = new mosBanner($database);
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect( 'index2.php?option=com_banners' );
}

function publishBanner( $cid, $publish=1 ) {
	global $database, $my;
    global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		mosErrorAlert( $_LANG->_( 'Select an item to' ) ." ". $action );
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__banner"
	. "\n SET showBanner = '$publish'"
	. "\n WHERE bid IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $row->getError() );
	}

	if (count( $cid ) == 1) {
		$row = new mosBanner( $database );
		$row->checkin( $cid[0] );
	}
	mosRedirect( 'index2.php?option=com_banners' );

}

function removeBanner( $cid ) {
	global $database;

	if ( count( $cid ) ) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__banner WHERE bid IN ($cids)";
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $row->getError() );
		}
	}
	mosRedirect( 'index2.php?option=com_banners' );
}


function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosBanner( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_banners', $msg );
}

// ---------- BANNER CLIENTS ----------

function viewBannerClients( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "viewcli{$option}limitstart", 'limitstart', 0 );
	$tOrder		= mosGetParam( $_POST, 'tOrder', 'a.name' );
	$tOrder_old	= mosGetParam( $_POST, 'tOrder_old', 'a.name' );
	//$search 	= $mainframe->getUserStateFromRequest( "viewcli{$option}search", 'search', '' );
	//$search 	= trim( strtolower( $search ) );

	// used by filter
	$where = '';
	//if ( $search ) {
	//	$where = "\n WHERE a.name LIKE '%$search%'";
	//}

	// table column ordering values
	$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'ASC' );
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] = $tOrder;

	// table column ordering
	switch ( $tOrder ) {
		default:
			$order = "\n ORDER BY $tOrder $tOrderDir, a.name ASC";
			break;
	}

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__bannerclient AS a"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	$sql = "SELECT a.*,	COUNT( b.bid ) AS num, u.name AS editor"
	. "\n FROM #__bannerclient AS a"
	. "\n LEFT JOIN #__banner AS b ON a.cid = b.cid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. $where
	. "\n GROUP BY a.cid"
	. $order
	;
	$database->setQuery( $sql, $pageNav->limitstart, $pageNav->limit );
	if(!$result = $database->query()) {
		mosErrorAlert( $database->stderr() );
	}
	$rows = $database->loadObjectList();

	//$lists['search'] = stripslashes( $search );

	HTML_bannerClient::showClients( $rows, $pageNav, $option, $lists );
}

function editBannerClient( $clientid, $option ) {
	global $database, $my, $mainframe;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	$row = new mosBannerClient($database);
	$row->load($clientid);

	// fail if checked out not by 'me'
	if ($row->isCheckedOut()) {
		$msg = $_LANG->_( 'The client' ) .' [ '. $row->name. ' ] '. $_LANG->_( 'WARNEDITEDBYPERSON' );
		mosRedirect( 'index2.php?option=com_banners&task=listclients', $msg );
	}

	if ($clientid) {
		// do stuff for existing record
		$row->checkout( $my->id );
	} else {
		// do stuff for new record
		$row->published = 0;
		$row->approved = 0;
	}

	HTML_bannerClient::bannerClientForm( $row, $option );
}

function saveBannerClient( $task ) {
	global $database;
    global $_LANG;

	$row = new mosBannerClient( $database );

	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

	if (!$row->check()) {
		mosRedirect( "index2.php?option=com_banners&task=editclient&id=$row->id", $row->getError() );
	}

	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();

	switch ( $task ) {
		case 'applyclient':
			$msg = $_LANG->_( 'Successfully Saved changes' );
			mosRedirect( 'index2.php?option=com_banners&task=editclientA&id='. $row->cid, $msg );

		case 'saveclient':
		default:
			$msg = $_LANG->_( 'Successfully Saved' );
			mosRedirect( 'index2.php?option=com_banners&task=listclients', $msg );
			break;
	}
}

function cancelEditClient() {
	global $database;

	$row = new mosBannerClient( $database );
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect( "index2.php?option=com_banners&task=listclients" );
}

function removeBannerClients( $cid ) {
	global $database;
    global $_LANG;

	for ($i = 0; $i < count($cid); $i++) {
		$query = "SELECT COUNT( bid )"
		. "\n FROM #__banner"
		. "\n WHERE cid='".$cid[$i]."'"
		;
		$database->setQuery($query);

		if( ( $count = $database->loadResult() ) == null ) {
			mosErrorAlert( $row->getError() );
		}

		if ($count != 0) {
			mosRedirect( "index2.php?option=$option&task=listclients", $_LANG->_( 'WARNCANNOTDELCLIENTBANNER' ) );
		} else {
			$query = "DELETE FROM #__bannerfinish"
			. "\n WHERE `cid` = '".$cid[$i]."'"
			;
			$database->setQuery($query);
			$database->query();

			$query = "DELETE FROM #__bannerclient"
			. "\n WHERE `cid` = '".$cid[$i]."'"
			;
			$database->setQuery($query);
			$database->query();
		}
	}
	mosRedirect("index2.php?option=com_banners&task=listclients");
}

function checkinClients( $id ) {
	global $database;
	global $_LANG;

	$row = new mosBannerClient( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_banners&task=listclients', $msg );
}
?>