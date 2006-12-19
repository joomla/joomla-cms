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
class BannerController
{
	/**
	 * Display the list of banners
	 */
	function display()
	{
		global $mainframe, $option;

		$db =& JFactory::getDBO();

		$context			= $option.'.viewbanners.';
		$filter_order		= $mainframe->getUserStateFromRequest( $context.'filter_order', 	'filter_order', 	'cc.name' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',	'filter_order_Dir',	'' );
		$filter_catid		= $mainframe->getUserStateFromRequest( $context.'filter_catid',		'filter_catid',		'' );
		$filter_state 		= $mainframe->getUserStateFromRequest( $context.'filter_state', 	'filter_state', 	'*' );
		$search 			= $mainframe->getUserStateFromRequest( $context.'search', 			'search', 			'' );

		$limit		= $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0 );

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
			$where[] = 'LOWER(b.name) LIKE ' . $db->Quote( "%$search%" );
		}

		$where 		= count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : '';
		$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, b.ordering";

		// get the total number of records
		$query = "SELECT COUNT(*)"
		. "\n FROM #__banner AS b"
		. "\n LEFT JOIN #__categories AS cc ON cc.id = b.catid"
		. $where
		;
		$db->setQuery( $query );
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
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
		$lists['catid'] = JAdminMenus::ComponentCategory( 'filter_catid', 'com_banner', (int) $filter_catid, $javascript );

		// state filter
		$lists['state']	= JCommonHTML::selectState( $filter_state );

		// table ordering
		if ( $filter_order_Dir == 'DESC' ) {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;

		require_once(JPATH_COMPONENT.DS.'views'.DS.'banner.php');
		BannersView::showBanners( $rows, $pageNav, $option, $lists );
	}

	function edit( )
	{
		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();

		$cid 	= JRequest::getVar('cid', array(0));
		$option = JRequest::getVar('option');

		if (!is_array( $cid ))
		{
			$cid = array(0);
		}

		$lists = array();

		$row =& JTable::getInstance('banner', 'Table');
		$row->load( $cid[0] );

		if ($cid[0]){
			$row->checkout( $user->get('id') );
		} else {
			$row->showBanner = 1;
		}

		// Build Client select list
		$sql = "SELECT cid, name"
		. "\n FROM #__bannerclient"
		;
		$db->setQuery($sql);
		if (!$db->query())
		{
			echo $db->stderr();
			return;
		}

		$clientlist[] 		= JHTMLSelect::option( '0', JText::_( 'Select Client' ), 'cid', 'name' );
		$clientlist 		= array_merge( $clientlist, $db->loadObjectList() );
		$lists['cid'] 		= JHTMLSelect::genericList( $clientlist, 'cid', 'class="inputbox" size="1"','cid', 'name', $row->cid );

		// Imagelist
		$javascript 		= 'onchange="changeDisplayImage();"';
		$directory 			= '/images/banners';
		$lists['imageurl'] 	= JAdminMenus::Images( 'imageurl', $row->imageurl, $javascript, $directory );

		// build list of categories
		$lists['catid']		= JAdminMenus::ComponentCategory( 'catid', 'com_banner', intval( $row->catid ) );

		// sticky
		$lists['sticky']	= JHTMLSelect::yesnoList( 'sticky', 'class="inputbox"', $row->sticky );

		// published
		$lists['showBanner'] = JHTMLSelect::yesnoList( 'showBanner', '', $row->showBanner );

		require_once(JPATH_COMPONENT.DS.'views'.DS.'banner.php');
		BannersView::edit( $row, $lists, $option );
	}

	/**
	 * Save method
	 */
	function save( $task )
	{
		global $mainframe;

		// Initialize variables
		$db =& JFactory::getDBO();

		$post	= JRequest::get( 'post' );
		// fix up special html fields
		$post['custombannercode'] = JRequest::getVar( 'custombannercode', '', 'post', 'string', _J_ALLOWRAW );

		$row =& JTable::getInstance('banner', 'Table');

		if (!$row->bind( $post ))
		{
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// Resets clicks when `Reset Clicks` button is used instead of `Save` button
		if ( $task == 'resethits' )
		{
			$row->clicks = 0;
			$msg = JText::_( 'Reset Banner clicks' );
		}

		// Sets impressions to unlimited when `unlimited` checkbox ticked
		$unlimited = JRequest::getVar( 'unlimited', 0 );
		if ($unlimited) {
			$row->imptotal = 0;
		}

		if (!$row->check())
		{
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// if new item order last in appropriate group
		if (!$row->bid) {
			$where = "catid = " . $row->catid ;
			$row->ordering = $row->getNextOrder ( $where );
		}

		if (!$row->store())
		{
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$row->checkin();

		switch ($task)
		{
			case 'apply':
				$link = 'index.php?option=com_banners&amp;task=edit&cid[]='. $row->bid .'&hidemainmenu=1';
				break;

			case 'save':
			default:
				$link = 'index.php?option=com_banners';
				break;
		}

		$msg = JText::_( 'Saved Banner info' );

		$mainframe->redirect( $link, $msg );
	}

	function cancel()
	{
		global $mainframe;

		// Initialize variables
		$db		=& JFactory::getDBO();
		$post	= JRequest::get( 'post' );
		$row	=& JTable::getInstance('banner', 'Table');
		$row->bind( $post );
		$row->checkin();

		$mainframe->redirect( 'index.php?option=com_banners' );
	}

	/**
	 * Copies one or more banners
	 */
	function copy()
	{
		global $mainframe;

		$cid	= JRequest::getVar( 'cid', null, 'post', 'array' );

		$db		=& JFactory::getDBO();
		$table	=& JTable::getInstance('banner', 'Table');
		$user	= &JFactory::getUser();

		$msg = '';
		if (count( $cid ) > 0)
		{
			foreach ($cid as $id)
			{
				if ($table->load( (int)$id ))
				{
					$table->bid				= 0;
					$table->name			= 'Copy of ' . $table->name;
					$table->impmade			= 0;
					$table->clicks			= 0;
					$table->showBanner		= 0;
					$table->date			= $db->getNullDate();

					if (!$table->store())
					{
						$msg = $table->getError();
						break;
					}
				}
				else
				{
					$msg = $table->getError();
					break;
				}
			}
		}
		else
		{
			$msg = JText::_( 'No items selected' );
		}

		$mainframe->redirect( 'index.php?option=com_banners', $msg );

	}

	function publish( $cid, $publish=1 )
	{
		global $mainframe;

		// Initialize variables
		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();

		if (!is_array( $cid ) || count( $cid ) < 1)
		{
			$action = $publish ? 'publish' : 'unpublish';
			echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
			exit();
		}

		JArrayHelper::toInteger( $cid );
		$cids = implode( ',', $cid );

		$query = "UPDATE #__banner"
		. "\n SET showBanner = " . (int) $publish
		. "\n WHERE bid IN ( $cids )"
		. "\n AND ( checked_out = 0 OR ( checked_out = " .(int) $user->get('id'). " ) )"
		;
		$db->setQuery( $query );
		if (!$db->query())
		{
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (count( $cid ) == 1)
		{
			$row =& JTable::getInstance('banner', 'Table');
			$row->checkin( (int) $cid[0] );
		}
		$mainframe->redirect( 'index.php?option=com_banners' );

	}

	function remove()
	{
		global $mainframe;

		// Initialize variables
		$db		=& JFactory::getDBO();
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger( $cid );

		if (count( $cid ))
		{
			$query = "DELETE FROM #__banner"
			. "\n WHERE bid = " . implode( ' OR bid = ', $cid )
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				JError::raiseError( 1001, $db->getErrorMsg() );
				//echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			}
		}

		$mainframe->redirect( 'index.php?option=com_banners', $db->getErrorMsg(), 'error' );
	}

	/**
	 * Save the new order given by user
	 */
	function saveOrder( $cid )
	{
		global $mainframe;

		// Initialize variables
		$db			=& JFactory::getDBO();
		$total		= count( $cid );
		$order		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		$row		=& JTable::getInstance('banner', 'Table');
		$conditions	= array();

		// update ordering values
		for( $i=0; $i < $total; $i++ )
		{
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store())
				{
					JError::raiseError( 500, $db->getErrorMsg() );
					return false;
				}
				// remember to reorder this category
				$condition = "catid = " . (int) $row->catid;
				$found = false;
				foreach ($conditions as $cond)
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				if (!$found)
				{
					$conditions[] = array ( $row->bid, $condition );
				}
			}
		}

		// execute reorder for each category
		foreach ($conditions as $cond)
		{
			$row->load( $cond[0] );
			$row->reorder( $cond[1] );
		}

		// Clear the component's cache
		$cache =& JFactory::getCache( 'com_banners' );
		$cache->cleanCache();

		$msg = JText::_('New ordering saved');
		$mainframe->redirect( 'index.php?option=com_banners', $msg );
	}
}
?>