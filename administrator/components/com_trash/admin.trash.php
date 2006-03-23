<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Trash
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

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_trash', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class', 'com_frontpage' ) );

$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
$mid = JRequest::getVar( 'mid', array(0), 'post', 'array' );
if ( !is_array( $cid ) ) {
	$cid = array(0);
}

switch ($task) {
	case 'deleteconfirm':
		viewdeleteTrash( $cid, $mid, $option );
		break;

	case 'delete':
		deleteTrash( $cid, $option );
		break;

	case 'restoreconfirm':
		viewrestoreTrash( $cid, $mid, $option );
		break;

	case 'restore':
		restoreTrash( $cid, $option );
		break;
	
	case 'viewMenu':
		viewTrashMenu( $option );
		break;	

	case 'viewContent':
		viewTrashContent( $option );
		break;
		
	default:	
		$return = JRequest::getVar( 'return', 'viewContent', 'post' );
		if ( $return == 'viewMenu' ) {
			viewTrashMenu( $option );
		} else {
			viewTrashContent( $option );
		}
		break;
}


/**
* Compiles a list of trash items
*/
function viewTrashContent( $option ) {
	global $database, $mainframe;
	
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.viewContent.filter_order", 		'filter_order', 	'sectname' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.viewContent.filter_order_Dir",	'filter_order_Dir',	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 								'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.viewContent.limitstart", 		'limitstart', 		0 );	
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 						'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );	

	$where[] = "c.state = -2";
		if ($search) {
		$where[] = "LOWER(c.title) LIKE '%$search%'";
	}
	
	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby = "\n ORDER BY $filter_order $filter_order_Dir, s.name, cc.name, c.title";

	// get the total number of content
	$query = "SELECT count(*)"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();
	
	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );
	
	// Query content items
	$query = "SELECT c.*, g.name AS groupname, cc.name AS catname, s.name AS sectname"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = c.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope='content'"
	. "\n INNER JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. $where
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$contents = $database->loadObjectList();
	
	for ( $i = 0; $i < $total; $i++ ) {
		if ( ( $contents[$i]->sectionid == 0 ) && ( $contents[$i]->catid == 0 ) ) {
			$contents[$i]->sectname = 'Typed Content';
		}
	}	
	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;	
	// search filter
	$lists['search']= $search;

	HTML_trash::showListContent( $option, $contents, $pageNav, $lists );
}

/**
* Compiles a list of trash items
*/
function viewTrashMenu( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.viewMenu.filter_order", 	'filter_order', 	'm.menutype' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.viewMenu.filter_order_Dir",	'filter_order_Dir',	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 							'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.viewMenu.limitstart", 		'limitstart', 		0 );	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 					'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );	
		
	$where[] = "m.published = -2";
	
	if ($search) {
		$where[] = "LOWER(m.name) LIKE '%$search%'";
	}	

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );	
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, m.menutype, m.ordering, m.ordering, m.name";		

	$query = "SELECT count(*)"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();
	
	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	// Query menu items
	$query = "SELECT m.*, com.name AS com_name"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__components AS com ON com.id = m.componentid AND m.type = 'components'"
	. $where
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$menus = $database->loadObjectList();

	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;
	
	$i = 0;
	foreach ( $menus as $row ) {
		// pulls name and description from menu type xml
		$row = ReadMenuXML( $row->type, $row->com_name );
		$menus[$i]->type 	= $row[0];
		$i++;
	}	
	// search filter
	$lists['search']= $search;

	HTML_trash::showListMenu( $option, $menus, $pageNav, $lists );
}


/**
* Compiles a list of the items you have selected to permanently delte
*/
function viewdeleteTrash( $cid, $mid, $option ) {
	global $database;

	$return = JRequest::getVar( 'return', 'viewContent', 'post' );

	// seperate contentids
	$cids = implode( ',', $cid );
	$mids = implode( ',', $mid );

	if ( $cids ) {
		// Content Items query
		$query = 	"SELECT a.title AS name"
		. "\n FROM #__content AS a"
		. "\n WHERE ( a.id IN ( $cids ) )"
		. "\n ORDER BY a.title"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $cid;
		$type = "content";
	} else if ( $mids ) {
		// Content Items query
		$query = 	"SELECT a.name"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.id IN ( $mids ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $mid;
		$type = "menu";
	}

	HTML_trash::showDelete( $option, $id, $items, $type, $return );
}


/**
* Permanently deletes the selected list of trash items
*/
function deleteTrash( $cid, $option ) {
	global $database;

	$return = JRequest::getVar( 'return', 'viewContent', 'post' );
	$type 	= JRequest::getVar( 'type', array(0), 'post' );

	$total = count( $cid );

	if ( $type == 'content' ) {
		$obj =& JTable::getInstance('content', $database );
		$fp = new JTableFrontPage( $database );
		foreach ( $cid as $id ) {
			$id = intval( $id );
			$obj->delete( $id );
			$fp->delete( $id );
		}
	} else if ( $type == "menu" ) {
		$obj =& JTable::getInstance('menu', $database );
		foreach ( $cid as $id ) {
			$id = intval( $id );
			$obj->delete( $id );
		}
	}

	$msg = sprintf( JText::_( 'Item(s) successfully Deleted' ), $total );
	josRedirect( "index2.php?option=$option&task=$return&mosmsg=$msg" );
}


/**
* Compiles a list of the items you have selected to permanently delte
*/
function viewrestoreTrash( $cid, $mid, $option ) {
	global $database;

	$return = JRequest::getVar( 'return', 'viewContent', 'post' );
	
	// seperate contentids
	$cids = implode( ',', $cid );
	$mids = implode( ',', $mid );

	if ( $cids ) {
		// Content Items query
		$query = "SELECT a.title AS name"
		. "\n FROM #__content AS a"
		. "\n WHERE ( a.id IN ( $cids ) )"
		. "\n ORDER BY a.title"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $cid;
		$type = "content";
	} else if ( $mids ) {
		// Content Items query
		$query = "SELECT a.name"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.id IN ( $mids ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id = $mid;
		$type = "menu";
	}

	HTML_trash::showRestore( $option, $id, $items, $type, $return );
}


/**
* Restores items selected to normal - restores to an unpublished state
*/
function restoreTrash( $cid, $option ) {
	global $database;

	$return = JRequest::getVar( 'return', 'viewContent', 'post' );
	$type 	= JRequest::getVar( 'type', array(0), 'post' );

	$total = count( $cid );

	// restores to an unpublished state
	$state 		= 0;
	$ordering 	= 9999;
	//seperate contentids
	$cids = implode( ',', $cid );

	if ( $type == 'content' ) {
		$query = "UPDATE #__content"
		. "\n SET state = $state, ordering = $ordering"
		. "\n WHERE id IN ( $cids )"
		;
	} else if ( $type == "menu" ) {
		$query = "UPDATE #__menu"
		. "\n SET published = $state, ordering = 9999"
		. "\n WHERE id IN ( $cids )"
		;
	}

	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = sprintf( JText::_( 'Item(s) successfully Restored' ), $total );
	josRedirect( "index2.php?option=$option&task=$return&mosmsg=$msg" );
}

function ReadMenuXML( $type, $component=-1 ) 
{
	// xml file for module
	$xmlfile = JPATH_ADMINISTRATOR .'/components/com_menus/'. $type .'/'. $type .'.xml';
	
	$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
	
	if ( $data['type'] == 'component' || $data['type'] == 'menu' ) 
	{
		if ( ( $component <> -1 ) && ( $data['name'] == 'Component') ) {
			$data['name'] .= ' - '. $component;
		}
		
		$row[0]	= $data['name'];
	}
	
	return $row;
}
?>