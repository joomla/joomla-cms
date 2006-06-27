<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * @package Joomla
 * @subpackage Banners
 */
class JBannerClientController {

	function viewBannerClients( $option ) {
		global $mainframe;
		
		$db =& $mainframe->getDBO();
		$user =& $mainframe->getUser();
		$filter_order		= $mainframe->getUserStateFromRequest( "$option.viewbannerclient.filter_order", 	'filter_order', 	'a.cid' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.viewbannerclient.filter_order_Dir",	'filter_order_Dir',	'' );
		$limit 				= $mainframe->getUserStateFromRequest( "limit", 									'limit', 			$mainframe->getCfg('list_limit') );
		$limitstart 		= $mainframe->getUserStateFromRequest( "com_banners.viewbannerclient.limitstart", 	'limitstart', 		0 );
		$search 			= $mainframe->getUserStateFromRequest( "$option.viewbannerclient.search", 			'search', 			'' );
		$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );
	
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
		$db->setQuery( $query );
		$total = $db->loadResult();
	
		jimport('joomla.presentation.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );
	
		$query = "SELECT a.*, count(b.bid) AS bid, u.name AS editor"
		. "\n FROM #__bannerclient AS a"
		. "\n LEFT JOIN #__banner AS b ON a.cid = b.cid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
		. $where
		. "\n GROUP BY a.cid"
		. $orderby
		;
		$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		$rows = $db->loadObjectList();
	
		// table ordering
		if ( $filter_order_Dir == 'DESC' ) {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
	
		// search filter
		$lists['search']= $search;
	
		JViewBannerClients::showClients( $rows, $pageNav, $option, $lists );
	}
	
	function editBannerClient( ) {
		global $mainframe;
	
		$db     =& $mainframe->getDBO();
		$user =& $mainframe->getUser();
		$cid 	= JRequest::getVar( 'cid', array(0));
		$option = JRequest::getVar( 'option');
		if (!is_array( $cid )) {
			$cid = array(0);
		}
		
		$row = new mosBannerClient($db);
		$row->load($cid[0]);
	
		// fail if checked out not by 'me'
		if ($row->checked_out && $row->checked_out <> $user->get ( 'id' )) {
	    	$msg = sprintf( JText::_( 'WARNEDITEDBYPERSON' ), $row->name );
			josRedirect( 'index2.php?option='. $option .'&task=listclients', $msg );
		}
	
		if ($cid[0]) {
			// do stuff for existing record
			$row->checkout( $user->get('id') );
		} else {
			// do stuff for new record
			$row->published = 0;
			$row->approved = 0;
		}
	
		JViewBannerClients::bannerClientForm( $row, $option );
	}
	
	function saveBannerClient( $task ) {
		global $mainframe;
	
		$db  =& $mainframe->getDBO();
		$row = new mosBannerClient( $db );
	
		if (!$row->bind( $_POST )) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		if (!$row->check()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
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
	
		josRedirect( $link );
	}
	
	function cancelEditClient( $option ) {
		global $mainframe;
		
		$db  =& $mainframe->getDBO();
		$row = new mosBannerClient( $db );
		$row->bind( $_POST );
		$row->checkin();
		josRedirect( "index2.php?option=$option&task=listclients" );
	}
	
	function removeBannerClients( $cid, $option ) {
		global $mainframe;
	
		$db =& $mainframe->getDBO();
		if (!count( $cid ) || $cid[0] == 0) {
			unset($cid);
			$cid[0] = JRequest::getVar( 'client_id', 0, 'post' );
		}
	
		for ($i = 0; $i < count($cid); $i++) {
			$query = "SELECT COUNT( bid )"
			. "\n FROM #__banner"
			. "\n WHERE cid = ".$cid[$i]
			;
			$db->setQuery($query);
			if(($count = $db->loadResult()) == null) {
				echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			}
	
			if ($count != 0) {
				josRedirect( "index2.php?option=$option&task=listclients", JText::_( 'WARNCANNOTDELCLIENTBANNER' ) );
			} else {
				$query="DELETE FROM #__bannerfinish"
				. "\n WHERE cid = ". $cid[$i]
				;
				$db->setQuery($query);
				$db->query();
	
				$query = "DELETE FROM #__bannerclient"
				. "\n WHERE cid = ". $cid[$i]
				;
				$db->setQuery($query);
				$db->query();
			}
		}
		josRedirect("index2.php?option=$option&task=listclients");
	}
}
?>