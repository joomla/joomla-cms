<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
$user = & JFactory::getUser();
if (!$user->authorize( 'com_weblinks', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// Load the html class
require_once( JApplicationHelper::getPath( 'admin_html' ) );

// Set the table directory
JTable::addTableDir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_weblinks'.DS.'tables');

$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
$id 	= JRequest::getVar( 'id', 0, 'get', 'int' );

switch ($task)
{
	case 'add' :
	case 'edit':
		editWeblink();
		break;

	case 'save':
	case 'apply':
		saveWeblink( $task );
		break;

	case 'remove':
		removeWeblinks( $cid );
		break;

	case 'publish':
		publishWeblinks( $cid, 1 );
		break;

	case 'unpublish':
		publishWeblinks( $cid, 0 );
		break;

	case 'approve':
		break;

	case 'cancel':
		cancelWeblink();
		break;

	case 'orderup':
		orderWeblinks( $cid[0], -1 );
		break;

	case 'orderdown':
		orderWeblinks( $cid[0], 1 );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	default:
		showWeblinks( $option );
		break;
}

/**
* Compiles a list of records
* @param database A database connector object
*/
function showWeblinks( $option )
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'a.ordering' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$filter_catid 		= $mainframe->getUserStateFromRequest( "$option.filter_catid", 		'filter_catid', 	0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );
	
	$limit		= $mainframe->getUserStateFromRequest("$option.limit", 'limit', $mainframe->getCfg('list_limit'), 0);
	$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

	$where = array();

	if ($filter_catid > 0) {
		$where[] = "a.catid = $filter_catid";
	}
	if ($search) {
		$where[] = "LOWER(a.title) LIKE '%$search%'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "a.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "a.published = 0";
		}
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, category, a.ordering";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__weblinks AS a"
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT a.*, cc.name AS category, u.name AS editor"
	. "\n FROM #__weblinks AS a"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. $where
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );

	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	// build list of categories
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['catid'] = JAdminMenus::ComponentCategory( 'filter_catid', $option, intval( $filter_catid ), $javascript );

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

	HTML_weblinks::showWeblinks( $option, $rows, $lists, $pageNav );
}

/**
* Compiles information to add or edit
* @param integer The unique id of the record to edit (0 if new)
*/
function editWeblink()
{
	global $mainframe, $option;

	$db		=& JFactory::getDBO();
	$user 	=& JFactory::getUser();
	$cid 	= JRequest::getVar( 'cid', array(0));
	
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	$lists = array();

	$row =& JTable::getInstance('weblink', $db, 'Table');
	// load the row from the db table
	$row->load( $cid[0] );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $user->get('id') )) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
		$mainframe->redirect( 'index.php?option='. $option, $msg );
	}

	if ($cid[0]) {
		$row->checkout( $user->get('id') );
	} else {
		// initialise new record
		$row->published = 1;
		$row->approved 	= 1;
		$row->order 	= 0;
		$row->catid 	= JRequest::getVar( 'catid', 0, 'post', 'int' );
	}

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__weblinks"
	. "\n WHERE catid = " . (int) $row->catid
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 			= JAdminMenus::SpecificOrdering( $row, $cid[0], $query, 1 );

	// build list of categories
	$lists['catid'] 			= JAdminMenus::ComponentCategory( 'catid', $option, intval( $row->catid ) );
	// build the html select list
	$lists['published'] 		= JHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

	$file 	= JPATH_ADMINISTRATOR .'/components/com_weblinks/weblinks_item.xml';
	$params = new JParameter( $row->params, $file, 'component' );

	HTML_weblinks::editWeblink( $row, $lists, $params, $option );
}

/**
* Saves the record on an edit form submit
* @param database A database connector object
*/
function saveWeblink( $task )
{
	global $mainframe;

	$db	=& JFactory::getDBO();
	$row =& JTable::getInstance('weblink', $db, 'Table');
	if (!$row->bind(JRequest::get('post'))) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	// save params
	$params = JRequest::getVar( 'params', '', 'post', 'array' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->params = implode( "\n", $txt );
	}

	$row->date = date( 'Y-m-d H:i:s' );
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->reorder( "catid = " . (int) $row->catid );

	switch ($task) 
	{
		case 'apply':
			$msg = JText::_( 'Changes to Weblink saved' );
			$link = 'index.php?option=com_weblinks&task=edit&cid[]='. $row->id .'&hidemainmenu=1';
			break;

		case 'save':
		default:
			$msg = JText::_( 'Weblink saved' );
			$link = 'index.php?option=com_weblinks';
			break;
	}

	$mainframe->redirect($link, $msg);
}

/**
* Deletes one or more records
* @param array An array of unique category id numbers
* @param string The current url option
*/
function removeWeblinks( $cid, $option )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__weblinks"
		. "\n WHERE id IN ( $cids )"
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	$mainframe->redirect( 'index.php?option=com_weblinks' );
}

/**
* Publishes or Unpublishes one or more records
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current url option
*/
function publishWeblinks( $cid=null, $publish=1,  $option )
{
	global $mainframe;

	$db		=& JFactory::getDBO();
	$user 	=& JFactory::getUser();
	$catid	= JRequest::getVar( 'catid', array(0), 'post', 'array' );

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
		echo "<script> alert('". JText::_( 'Select an item to' ) . $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__weblinks"
	. "\n SET published = " . intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = " .$user->get('id'). " ) )"
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('weblink', $db, 'Table');
		$row->checkin( $cid[0] );
	}
	$mainframe->redirect( "index.php?option=". $option );
}
/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderWeblinks( $uid, $inc )
{
	global $mainframe;

	$option = JRequest::getVar( 'option');

	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('weblink', $db, 'Table');
	$row->load( $uid );
	$row->move( $inc, "published >= 0" );

	$mainframe->redirect( 'index.php?option='. $option );
}

/**
* Cancels an edit operation
* @param string The current url option
*/
function cancelWeblink()
{
	global $mainframe;

	$id	= JRequest::getVar( 'id', 0, '', 'int' );
	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('weblink', $db, 'Table');
	$row->checkin($id);

	$mainframe->redirect( 'index.php?option=com_weblinks' );
}

function saveOrder( &$cid )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );

	for( $i=0; $i < $total; $i++ ) {
		$query = "UPDATE #__weblinks"
		. "\n SET ordering = " . (int) $order[$i]
		. "\n WHERE id = " . (int) $cid[$i];
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// update ordering
		$row =& JTable::getInstance('weblink', $db, 'Table');
		$row->load( $cid[$i] );
		$row->reorder();
	}

	$msg = 'New ordering saved';
	$mainframe->redirect( 'index.php?option=com_weblinks', $msg );
}
?>