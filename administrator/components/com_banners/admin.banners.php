<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
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
if (!$acl->acl_check( 'com_banners', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
if (!is_array( $cid )) {
	$cid = array(0);
}

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
		removeBannerClients( $cid, $option );
		break;

	case 'cancelclient':
		cancelEditClient( $option );
		break;

	case 'listclients':
		viewBannerClients( $option );
		break;

	// BANNER EVENTS

	case 'new':
		editBanner( null, $option );
		break;

	case 'cancel':
		cancelEditBanner();
		break;

	case 'save':
	case 'resethits':
	case 'apply':
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

	default:
		viewBanners( $option );
		break;
}

function viewBanners( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_order", 		'filter_order', 	'b.bid' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_state", 		'filter_state', 	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 								'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.viewbanners.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.viewbanners.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );	
	
	$where = array();
	
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "\n WHERE b.showBanner = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "\n WHERE b.showBanner = 0";
		}
	}	
	if ($search) {
		$where[] = "LOWER(b.name) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, b.bid";

	// get the total number of records
	$query = "SELECT COUNT(b.*)"
	. "\n FROM #__banner AS b"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT b.*, u.name AS editor"
	. "\n FROM #__banner AS b"
	. "\n LEFT JOIN #__users AS u ON u.id = b.checked_out"
	. $where
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

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

	HTML_banners::showBanners( $rows, $pageNav, $option, $lists );
}

function editBanner( $bannerid, $option ) {
	global $database, $my;

	$lists = array();

	$row = new mosBanner($database);
	$row->load( $bannerid );

  if ( $bannerid ){
	$row->checkout( $my->id );
  }

	// Build Client select list
	$sql	= "SELECT cid, name"
	. "\n FROM #__bannerclient"
	;
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->stderr();
		return;
	}

	$clientlist[] 	= mosHTML::makeOption( '0', JText::_( 'Select Client' ), 'cid', 'name' );
	$clientlist 	= array_merge( $clientlist, $database->loadObjectList() );
	$lists['cid'] 	= mosHTML::selectList( $clientlist, 'cid', 'class="inputbox" size="1"','cid', 'name', $row->cid);

	// Imagelist
	$javascript 	= 'onchange="changeDisplayImage();"';
	$directory 		= '/images/banners';
	$lists['imageurl'] = mosAdminMenus::Images( 'imageurl', $row->imageurl, $javascript, $directory );


	// make the select list for the image positions
	$yesno[] = mosHTML::makeOption( '0', JText::_( 'No' ) );
  	$yesno[] = mosHTML::makeOption( '1', JText::_( 'Yes' ) );

  	$lists['showBanner'] = mosHTML::selectList( $yesno, 'showBanner', 'class="inputbox" size="1"' , 'value', 'text', $row->showBanner );

	HTML_banners::bannerForm( $row, $lists, $option );
}

function saveBanner( $task ) {
	global $database;

	$row = new mosBanner($database);

	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	
	// Resets clicks when `Reset Clicks` button is used instead of `Save` button
	if ( $task == 'resethits' ) {
		$row->clicks = 0;
		$msg = JText::_( 'Reset Banner clicks' );
	}
	
	// Sets impressions to unlimited when `unlimited` checkbox ticked
	$unlimited = mosGetParam( $_POST, 'unlimited', 0 );
	if ( $unlimited ) {
		$row->imptotal = 0;
	}
	
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	switch ($task) {
		case 'apply':
			$link = 'index2.php?option=com_banners&task=editA&id='. $row->bid .'&hidemainmenu=1';
			break;
		
		case 'save':
		default:
			$link = 'index2.php?option=com_banners';
			break;
	}	
	
	$msg = JText::_( 'Saved Banner info' );
	
	mosRedirect( $link, $msg );
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

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit();
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__banner"
	. "\n SET showBanner = " . intval( $publish )
	. "\n WHERE bid IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new mosBanner( $database );
		$row->checkin( $cid[0] );
	}
	mosRedirect( 'index2.php?option=com_banners' );

}

function removeBanner( $cid ) {
	global $database;
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__banner"
		. "\n WHERE bid IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}
	mosRedirect( 'index2.php?option=com_banners' );
}

// ---------- BANNER CLIENTS ----------

function viewBannerClients( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.viewbannerclient.filter_order", 	'filter_order', 	'a.cid' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.viewbannerclient.filter_order_Dir",	'filter_order_Dir',	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 									'limit', 			$mosConfig_list_limit );
	$limitstart 		= $mainframe->getUserStateFromRequest( "com_banners.viewbannerclient.limitstart", 	'limitstart', 		0 );	
	$search 			= $mainframe->getUserStateFromRequest( "$option.viewbannerclient.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );	

	$where = array();

	if ($search) {
		$where[] = "LOWER(a.name) LIKE '%$search%'";
	}
	
	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby = "\n ORDER BY $filter_order $filter_order_Dir, a.cid";
	
	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__bannerclient"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT a.*, count(b.bid) AS bid, u.name AS editor"
	. "\n FROM #__bannerclient AS a"
	. "\n LEFT JOIN #__banner AS b ON a.cid = b.cid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. $where
	. "\n GROUP BY a.cid"
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	//$database->setQuery( $query );
	$rows = $database->loadObjectList();	
	
	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;	
	
	// search filter
	$lists['search']= $search;

	HTML_bannerClient::showClients( $rows, $pageNav, $option, $lists );
}

function editBannerClient( $clientid, $option ) {
	global $database, $my;

	$row = new mosBannerClient($database);
	$row->load($clientid);

	// fail if checked out not by 'me'
	if ($row->checked_out && $row->checked_out <> $my->id) {
    	$msg = sprintf( JText::_( 'WARNEDITEDBYPERSON' ), $row->name );
		mosRedirect( 'index2.php?option='. $option .'&task=listclients', $msg );
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

	$row = new mosBannerClient( $database );
	
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->check()) {
		mosRedirect( "index2.php?option=com_banners&task=editclient&cid[]=$row->cid", $row->getError() );
	}

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	switch ($task) {
		case 'applyclient':
			$link = 'index2.php?option=com_banners&task=editclientA&id='. $row->cid .'&hidemainmenu=1';
			break;
		
		case 'saveclient':
		default:
			$link = 'index2.php?option=com_banners&task=listclients';
			break;
	}

	mosRedirect( $link );
}

function cancelEditClient( $option ) {
	global $database;
	$row = new mosBannerClient( $database );
	$row->bind( $_POST );
	$row->checkin();
	mosRedirect( "index2.php?option=$option&task=listclients" );
}

function removeBannerClients( $cid, $option ) {
	global $database;

	for ($i = 0; $i < count($cid); $i++) {
		$query = "SELECT COUNT( bid )"
		. "\n FROM #__banner"
		. "\n WHERE cid = ".$cid[$i]
		;
		$database->setQuery($query);

		if(($count = $database->loadResult()) == null) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}

		if ($count != 0) {
			mosRedirect( "index2.php?option=$option&task=listclients", JText::_( 'WARNCANNOTDELCLIENTBANNER' ) );
		} else {
			$query="DELETE FROM #__bannerfinish"
			. "\n WHERE cid = ". $cid[$i]
			;
			$database->setQuery($query);
			$database->query();

			$query = "DELETE FROM #__bannerclient"
			. "\n WHERE cid = ". $cid[$i]
			;
			$database->setQuery($query);
			$database->query();
		}
	}
	mosRedirect("index2.php?option=$option&task=listclients");
}
?>