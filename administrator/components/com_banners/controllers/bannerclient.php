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
class BannerClientController
{
	function display()
	{
		global $mainframe;

		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$context			= "com_banners.viewbannerclient";
		$filter_order		= $mainframe->getUserStateFromRequest( "$context.filter_order",		'filter_order', 	'a.cid' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$context.filter_order_Dir",	'filter_order_Dir',	'' );
		$search 			= $mainframe->getUserStateFromRequest( "$context.search", 			'search', 			'' );
		$search 			= $db->getEscaped( JString::strtolower( $search ) );

		$limit		= (int) $mainframe->getUserStateFromRequest("$context.limit", 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart	= (int) $mainframe->getUserStateFromRequest("$context.limitstart", 'limitstart', 0);

		$where = array();

		if ($search) {
			$where[] = "LOWER(a.name) LIKE '%$search%'";
		}

		$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
		$orderby = "\n ORDER BY $filter_order $filter_order_Dir, a.cid";

		// get the total number of records
		$query = "SELECT a.*, count(b.bid) AS bid, u.name AS editor"
		. "\n FROM #__bannerclient AS a"
		. "\n LEFT JOIN #__banner AS b ON a.cid = b.cid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
		. $where
		. "\n GROUP BY a.cid"
		. $orderby
		;

		$db->setQuery( $query );
		$db->query();
		$total = $db->getNumRows();

		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

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

		require_once(JPATH_COMPONENT.DS.'views'.DS.'client.php');
		BannersViewClients::showClients( $rows, $pageNav, 'com_banners', $lists );
	}

	/**
	 * Edit a banner client record
	 */
	function edit()
	{
		global $mainframe;

		// Initialize variables
		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();

		$userId	= $user->get ( 'id' );
		$cid 	= JRequest::getVar( 'cid', array(0), 'method', 'array' );

		$row =& JTable::getInstance('bannerclient', 'Table');
		$row->load( (int) $cid[0] );

		// fail if checked out not by 'me'
		if ($row->isCheckedOut( $userId )) {
	    	$msg = JText::sprintf( 'WARNEDITEDBYPERSON', $row->name );
			$mainframe->redirect( 'index.php?option=com_banners&amp;task=listclients', $msg );
		}

		if ($row->cid) {
			// do stuff for existing record
			$row->checkout( $userId );
		} else {
			// do stuff for new record
			$row->published = 0;
			$row->approved = 0;
		}

		require_once(JPATH_COMPONENT.DS.'views'.DS.'client.php');
		BannersViewClients::bannerClientForm( $row, 'com_banners' );
	}

	function save()
	{
		global $mainframe;

		// Initialize variables
		$db		=& JFactory::getDBO();
		$table	=& JTable::getInstance('bannerclient', 'Table');

		if (!$table->bind( JRequest::get( 'post' ) )) {
			echo "<script> alert('".$table->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		if (!$table->check()) {
			echo "<script> alert('".$table->getError()."'); window.history.go(-1); </script>\n";
		}

		if (!$table->store()) {
			echo "<script> alert('".$table->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$table->checkin();

		switch (JRequest::getVar( 'task' ))
		{
			case 'applyclient':
				$link = 'index.php?option=com_banners&amp;task=editclient&cid[]='. $table->cid .'&hidemainmenu=1';
				break;

			case 'saveclient':
			default:
				$link = 'index.php?option=com_banners&amp;task=listclients';
				break;
		}

		$mainframe->redirect( $link );
	}

	function cancel()
	{
		global $mainframe;

		// Initialize variables
		$db			=& JFactory::getDBO();
		$table		=& JTable::getInstance('bannerclient', 'Table');
		$table->cid	= JRequest::getVar( 'cid', 0, 'post', 'int' );
		$table->checkin();

		$mainframe->redirect( "index.php?option=com_banners&amp;task=listclients" );
	}

	function remove()
	{
		global $mainframe;

		// Initialize variables
		$db		=& JFactory::getDBO();
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$table	=& JTable::getInstance('bannerclient', 'Table');
		$msg	= '';

		for ($i = 0, $n = count( $cid ); $i < $n; $i++)
		{
			$query = "SELECT COUNT( bid )"
			. "\n FROM #__banner"
			. "\n WHERE cid = ". (int) $cid[$i]
			;
			$db->setQuery($query);
			if (($count = $db->loadResult()) === null)
			{
				$msg = $db->getErrorMsg();
			}
			else if ($count > 0)
			{
				$msg = JText::_( 'WARNCANNOTDELCLIENTBANNER' );
			}
			else
			{
				$table->delete( (int) $cid[$i] );
			}
		}
		$mainframe->redirect( 'index.php?option=com_banners&amp;task=listclients', $msg, 'error' );
	}
}
?>