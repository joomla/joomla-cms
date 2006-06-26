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
class JBannerController {
	function viewBanners( $option )
	{
		global $mainframe;
		
		$db =& $mainframe->getDBO();

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_order", 		'filter_order', 	'b.bid' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_order_Dir",	'filter_order_Dir',	'' );
		$filter_catid		= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_catid",'filter_catid','' );
		$filter_state 		= $mainframe->getUserStateFromRequest( "$option.viewbanners.filter_state", 		'filter_state', 	'' );
		$limit 				= $mainframe->getUserStateFromRequest( "limit", 								'limit', 			$mainframe->getCfg('list_limit') );
		$limitstart 		= $mainframe->getUserStateFromRequest( "$option.viewbanners.limitstart", 		'limitstart', 		0 );
		$search 			= $mainframe->getUserStateFromRequest( "$option.viewbanners.search", 			'search', 			'' );
		$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

		$where = array();

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = "b.showBanner = 1";
			}
			else if ($filter_state == 'U' )
			{
				$where[] = "b.showBanner = 0";
			}
		}
		if ($filter_catid)
		{
			$where[] = 'cc.id = ' . (int) $filter_catid;
		}
		if ($search)
		{
			$where[] = "LOWER(b.name) LIKE '%$search%'";
		}

		$where 		= count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : '';
		$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, b.bid";

		// get the total number of records
		$query = "SELECT COUNT(*)"
		. "\n FROM #__banner AS b"
		. $where
		;
		$db->setQuery( $query );
		$total = $db->loadResult();

		jimport('joomla.presentation.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		$query = "SELECT b.*, c.name AS client_name, cc.name AS category_name, u.name AS editor"
		. "\n FROM #__banner AS b"
		. "\n INNER JOIN #__bannerclient AS c ON c.cid = b.cid"
		. "\n LEFT JOIN #__categories AS cc ON cc.id = b.catid"
		. "\n LEFT JOIN #__users AS u ON u.id = b.checked_out"
		. $where
		. $orderby
		;
		$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		$rows = $db->loadObjectList();

		// build list of categories
		$javascript 	= 'onchange="document.adminForm.submit();"';
		$lists['catid'] = mosAdminMenus::ComponentCategory( 'filter_catid', $option, (int) $filter_catid, $javascript );

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

		JViewBanners::showBanners( $rows, $pageNav, $option, $lists );
	}

	function edit( ) {
		global $mainframe;

		$db =& $mainframe->getDBO();
		$user =& $mainframe->getUser();
		$cid 	= JRequest::getVar('cid', array(0));
		$option = JRequest::getVar('option');
		if (!is_array( $cid )) {
			$cid = array(0);
		}
		
		$lists = array();

		$row = new mosBanner($db);
		$row->load( $cid[0] );

		if ($cid[0]){
			$row->checkout( $user->get( 'id' ) );
		} else {
			$row->showBanner = 1;
		}

		// Build Client select list
		$sql = "SELECT cid, name"
		. "\n FROM #__bannerclient"
		;
		$db->setQuery($sql);
		if (!$db->query()) {
			echo $db->stderr();
			return;
		}

		$clientlist[] 		= mosHTML::makeOption( '0', JText::_( 'Select Client' ), 'cid', 'name' );
		$clientlist 		= array_merge( $clientlist, $db->loadObjectList() );
		$lists['cid'] 		= mosHTML::selectList( $clientlist, 'cid', 'class="inputbox" size="1"','cid', 'name', $row->cid );

		// Imagelist
		$javascript 		= 'onchange="changeDisplayImage();"';
		$directory 			= '/images/banners';
		$lists['imageurl'] 	= mosAdminMenus::Images( 'imageurl', $row->imageurl, $javascript, $directory );

		// build list of categories
		$lists['catid']		= mosAdminMenus::ComponentCategory( 'catid', 'com_banner', intval( $row->catid ) );

		// sticky
		$lists['sticky']	= mosHTML::yesnoRadioList( 'sticky', 'class="inputbox"', $row->sticky );

		// published
		$lists['showBanner'] = mosHTML::yesnoradioList( 'showBanner', '', $row->showBanner );

		JViewBanners::edit( $row, $lists, $option );
	}

	function saveBanner( $task ) {
		global $mainframe;

		$db =& $mainframe->getDBO();
		$row = new mosBanner($db);

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
		$unlimited = JRequest::getVar( 'unlimited', 0 );
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

		josRedirect( $link, $msg );
	}

	function cancelEditBanner() {
		global $mainframe;
		
		$db =& $mainframe->getDBO();

		$row = new mosBanner($db);
		$row->bind( $_POST );
		$row->checkin();

		josRedirect( 'index2.php?option=com_banners' );
	}

	function publishBanner( $cid, $publish=1 ) {
		global $mainframe;
		
		$db =& $mainframe->getDBO();
		$user =& $mainframe->getUser();

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$action = $publish ? 'publish' : 'unpublish';
			echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
			exit();
		}

		$cids = implode( ',', $cid );

		$query = "UPDATE #__banner"
		. "\n SET showBanner = " . intval( $publish )
		. "\n WHERE bid IN ( $cids )"
		. "\n AND ( checked_out = 0 OR ( checked_out = " .$user->get( 'id' ). " ) )"
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (count( $cid ) == 1) {
			$row = new mosBanner( $db );
			$row->checkin( $cid[0] );
		}
		josRedirect( 'index2.php?option=com_banners' );

	}

	function removeBanner( $cid ) {
		global $mainframe;
		
		$db =& $mainframe->getDBO();

		if (count( $cid ) && $cid[0] != 0) {
			$cids = implode( ',', $cid );
		} else {
			$cids = JRequest::getVar( 'banner_id', 0, 'post' );
		}

		if ($cids) {
			$query = "DELETE FROM #__banner"
			. "\n WHERE bid IN ( $cids )"
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			}
		}

		josRedirect( 'index2.php?option=com_banners' );
	}
}
?>