<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();
if (!$user->authorize( 'com_frontpage', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// Set the table directory
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_frontpage'.DS.'tables');

$cid = JRequest::getVar( 'cid', array(0), 'post' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ( JRequest::getVar( 'task' ) )
{
	case 'publish':
		changeFrontPage( $cid, 1, $option );
		break;

	case 'unpublish':
		changeFrontPage( $cid, 0, $option );
		break;

	case 'archive':
		changeFrontPage( $cid, -1, $option );
		break;

	case 'remove':
		removeFrontPage( $cid, $option );
		break;

	case 'orderup':
		orderFrontPage( $cid[0], -1, $option );
		break;

	case 'orderdown':
		orderFrontPage( $cid[0], 1, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0 );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1 );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2 );
		break;

	default:
		viewFrontPage( $option );
		break;
}


/**
* Compiles a list of frontpage items
*/
function viewFrontPage( $option )
{
	global $mainframe;

	$db 				=& JFactory::getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'fpordering' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'*' );
	$catid 				= $mainframe->getUserStateFromRequest( "$option.catid", 			'catid', 			0 );
	$filter_authorid 	= $mainframe->getUserStateFromRequest( "$option.filter_authorid", 	'filter_authorid', 	0 );
	$filter_sectionid 	= $mainframe->getUserStateFromRequest( "$option.filter_sectionid", 	'filter_sectionid', 0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

	$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
	$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0 );

	$where = array(
		"c.state >= 0"
	);

	// used by filter
	if ( $filter_sectionid > 0 ) {
		$where[] = "c.sectionid = '$filter_sectionid'";
	}
	if ( $catid > 0 ) {
		$where[] = "c.catid = '$catid'";
	}
	if ( $filter_authorid > 0 ) {
		$where[] = "c.created_by = $filter_authorid";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "c.state = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "c.state = 0";
		}
	}

	if ($search) {
		$where[] = "LOWER( c.title ) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, fpordering";

	// get the total number of records
	$query = "SELECT count(*)"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT c.*, g.name AS groupname, cc.name, s.name AS sect_name, u.name AS editor, f.ordering AS fpordering, v.name AS author"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. "\n INNER JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__users AS v ON v.id = c.created_by"
	. $where
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart,$pageNav->limit );
	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	// get list of categories for dropdown filter
	$query = "SELECT cc.id AS value, cc.title AS text, section"
	. "\n FROM #__categories AS cc"
	. "\n INNER JOIN #__sections AS s ON s.id = cc.section "
	. "\n ORDER BY s.ordering, cc.ordering"
	;
	$db->setQuery( $query );
	$categories[] 	= JHTMLSelect::option( '0', '- '. JText::_( 'Select Category' ) .' -' );
	$categories 	= array_merge( $categories, $db->loadObjectList() );
	$lists['catid'] = JHTMLSelect::genericList( $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $catid );

	// get list of sections for dropdown filter
	$javascript			= 'onchange="document.adminForm.submit();"';
	$lists['sectionid']	= JAdminMenus::SelectSection( 'filter_sectionid', $filter_sectionid, $javascript );

	// get list of Authors for dropdown filter
	$query = "SELECT c.created_by, u.name"
	. "\n FROM #__content AS c"
	. "\n INNER JOIN #__sections AS s ON s.id = c.sectionid"
	. "\n LEFT JOIN #__users AS u ON u.id = c.created_by"
	. "\n WHERE c.state <> -1"
	. "\n AND c.state <> -2"
	. "\n GROUP BY u.name"
	. "\n ORDER BY u.name"
	;
	$db->setQuery( $query );
	$authors[] 			= JHTMLSelect::option( '0', '- '. JText::_( 'Select Author' ) .' -', 'created_by', 'name' );
	$authors 			= array_merge( $authors, $db->loadObjectList() );
	$lists['authorid']	= JHTMLSelect::genericList( $authors, 'filter_authorid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'created_by', 'name', $filter_authorid );

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

	require_once(JPATH_COMPONENT.DS.'views'.DS.'frontpage.php');
	FrontpageView::showList( $rows, $pageNav, $option, $lists );
}

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function changeFrontPage( $cid=null, $state=0, $option )
{
	global $mainframe;

	$db 	=& JFactory::getDBO();
	$user 	=& JFactory::getUser();

	if (count( $cid ) < 1) {
		$action = $state == 1 ? 'publish' : ($state == -1 ? 'archive' : 'unpublish');
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		$mainframe->exit();
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__content"
	. "\n SET state = $state"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = " .$user->get ('id' ). " ) )"
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		$mainframe->exit();
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('content');
		$row->checkin( $cid[0] );
	}

	$cache = & JFactory::getCache('com_content');
	$cache->cleanCache();

	$mainframe->redirect( "index.php?option=$option" );
}

function removeFrontPage( &$cid, $option )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete', true ) ."'); window.history.go(-1);</script>\n";
		$mainframe->exit();
	}
	$fp =& JTable::getInstance('frontpage', 'Table');
	foreach ($cid as $id) {
		if (!$fp->delete( $id )) {
			echo "<script> alert('".$fp->getError()."'); </script>\n";
			$mainframe->exit();
		}
		$obj =& JTable::getInstance('content');
		$obj->load( $id );
		$obj->mask = 0;
		if (!$obj->store()) {
			echo "<script> alert('".$fp->getError()."'); </script>\n";
			$mainframe->exit();
		}
	}
	$fp->reorder();

	$cache = & JFactory::getCache('com_content');
	$cache->cleanCache();

	$mainframe->redirect( "index.php?option=$option" );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderFrontPage( $uid, $inc, $option )
{
	global $mainframe;

	$db =& JFactory::getDBO();

	$fp =& JTable::getInstance('frontpage','Table');
	$fp->load( $uid );
	$fp->move( $inc );

	$cache = & JFactory::getCache('com_content');
	$cache->cleanCache();

	$mainframe->redirect( "index.php?option=$option" );
}

/**
* @param integer The id of the article
* @param integer The new access level
* @param string The URL option
*/
function accessMenu( $uid, $access )
{
	global $mainframe;

	$db = & JFactory::getDBO();
	$row =& JTable::getInstance('content');
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	$cache = & JFactory::getCache('com_content');
	$cache->cleanCache();

	$mainframe->redirect( 'index.php?option=com_frontpage' );
}

function saveOrder( &$cid )
{
	global $mainframe;

	$db 	=& JFactory::getDBO();
	$total	= count( $cid );
	$order 	= JRequest::getVar( 'order', array(0), 'post', 'array' );

	for( $i=0; $i < $total; $i++ )
	{
		$query = "UPDATE #__content_frontpage"
		. "\n SET ordering = " . (int) $order[$i]
		. "\n WHERE content_id = " . (int) $cid[$i];
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			$mainframe->exit();
		}

		// update ordering
		$row =& JTable::getInstance('frontpage','Table');
		$row->load( $cid[$i] );
		$row->reorder();
	}

	$cache = & JFactory::getCache('com_content');
	$cache->cleanCache();

	$msg 	= JText::_( 'New ordering saved' );
	$mainframe->redirect( 'index.php?option=com_frontpage', $msg );
}
?>