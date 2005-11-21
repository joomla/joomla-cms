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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!$acl->acl_check( 'com_banners', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );
require_once( $mainframe->getPath( 'class' ) );

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
		saveBannerClient( $option );
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
	global $database, $mainframe, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "viewban{$option}limitstart", 'limitstart', 0 );

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__banner"
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT b.*, u.name AS editor"
	. "\n FROM #__banner AS b "
	. "\n LEFT JOIN #__users AS u ON u.id = b.checked_out"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit";
	$database->setQuery( $query );

	if(!$result = $database->query()) {
		echo $database->stderr();
		return;
	}
	$rows = $database->loadObjectList();

	HTML_banners::showBanners( $rows, $pageNav, $option );
}

function editBanner( $bannerid, $option ) {
	global $database, $my;
	;

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
	;

	$row = new mosBanner($database);

	$msg = JText::_( 'Saved Banner info' );
	if ( $task == 'resethits' ) {
		$row->clicks = 0;
		$msg = JText::_( 'Reset Banner clicks' );
	}
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
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

	mosRedirect( 'index2.php?option=com_banners', $msg );
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
	;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select an item to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
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

	$limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "viewcli{$option}limitstart", 'limitstart', 0 );

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__bannerclient"
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$sql = "SELECT a.*,	count(b.bid) AS bid, u.name AS editor"
	. "\n FROM #__bannerclient AS a"
	. "\n LEFT JOIN #__banner AS b ON a.cid = b.cid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. "\n GROUP BY a.cid"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit";
	$database->setQuery($sql);

	if(!$result = $database->query()) {
		echo $database->stderr();
		return;
	}
	$rows = $database->loadObjectList();

	HTML_bannerClient::showClients( $rows, $pageNav, $option );
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

function saveBannerClient( $option ) {
	global $database;

	$row = new mosBannerClient( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->check()) {
		mosRedirect( "index2.php?option=$option&task=editclient&cid[]=$row->id", $row->getError() );
	}

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	mosRedirect( "index2.php?option=$option&task=listclients" );
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
