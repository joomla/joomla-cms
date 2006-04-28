<?php
/**
* @version $Id: admin.plugins.php 1663 2006-01-05 23:12:32Z Jinx $
* @package Joomla
* @subpackage Plugins
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
if (!$user->authorize( 'com_plugins', 'manage' )) {
		josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );

$option = JRequest::getVar( 'option', '' );
$client = JRequest::getVar( 'client', 'site' );
$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
$id 	= JRequest::getVar( 'id', 0, '', 'int' );

if (!is_array( $cid )) {
	$cid = array(0);
}

switch ( $task )
{
	case 'new':
	case 'edit':
		editPlugin( $option, $cid[0], $client );
		break;

	case 'editA':
		editPlugin( $option, $id, $client );
		break;

	case 'save':
	case 'apply':
		savePlugin( $option, $client, $task );
		break;

	case 'remove':
		removePlugin( $cid, $option, $client );
		break;

	case 'cancel':
		cancelPlugin( $option, $client );
		break;

	case 'publish':
	case 'unpublish':
		publishPlugin( $cid, ($task == 'publish'), $option, $client );
		break;

	case 'orderup':
	case 'orderdown':
		orderPlugin( $cid[0], ($task == 'orderup' ? -1 : 1), $option, $client );
		break;

	case 'accesspublic':
	case 'accessregistered':
	case 'accessspecial':
		accessMenu( $cid[0], $task, $option, $client );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	default:
		viewPlugins( $option, $client );
		break;
}

/**
* Compiles a list of installed or defined modules
*/
function viewPlugins( $option, $client )
{
	global $database, $mainframe, $mosConfig_list_limit;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.$client.filter_order", 		'filter_order', 	'p.folder' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.$client.filter_state", 		'filter_state', 	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 							'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 				'limitstart', 		0 );
	$filter_type		= $mainframe->getUserStateFromRequest( "$option.$client.filter_type", 		'filter_type', 		1 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.$client.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( JString::strtolower( $search ) ) );

	if ($client == 'admin') {
		$where[] = "p.client_id = '1'";
		$client_id = 1;
	} else {
		$where[] = "p.client_id = '0'";
		$client_id = 0;
	}

	// used by filter
	if ( $filter_type != 1 ) {
		$where[] = "p.folder = '$filter_type'";
	}
	if ( $search ) {
		$where[] = "LOWER( p.name ) LIKE '%$search%'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "p.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "p.published = 0";
		}
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, p.ordering ASC";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__plugins AS p"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT p.*, u.name AS editor, g.name AS groupname"
	. "\n FROM #__plugins AS p"
	. "\n LEFT JOIN #__users AS u ON u.id = p.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = p.access"
	. $where
	. "\n GROUP BY p.id"
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	// get list of Positions for dropdown filter
	$query = "SELECT folder AS value, folder AS text"
	. "\n FROM #__plugins"
	. "\n WHERE client_id = '$client_id'"
	. "\n GROUP BY folder"
	. "\n ORDER BY folder"
	;
	$types[] = mosHTML::makeOption( 1, '- '. JText::_( 'Select Type' ) .' -' );
	$database->setQuery( $query );
	$types 			= array_merge( $types, $database->loadObjectList() );
	$lists['type']	= mosHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_type );

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

	HTML_modules::showPlugins( $rows, $client, $pageNav, $option, $lists );
}

/**
* Saves the module after an edit form submit
*/
function savePlugin( $option, $client, $task )
{
	global $database;

	$row =& JTable::getInstance('plugin', $database);

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

	if ($client == 'admin') {
		$where = "client_id='1'";
	} else {
		$where = "client_id='0'";
	}

	$row->reorder( "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000 AND ( $where )" );

	switch ( $task ) {
		case 'apply':
        	$msg = sprintf( JText::_( 'Successfully Saved changes to Plugin' ), $row->name );
			josRedirect( 'index2.php?option='. $option .'&client='. $client .'&task=editA&hidemainmenu=1&id='. $row->id, $msg );

		case 'save':
		default:
        	$msg = sprintf( JText::_( 'Successfully Saved Plugin' ), $row->name );
			josRedirect( 'index2.php?option='. $option .'&client='. $client, $msg );
			break;
	}
}

/**
* Compiles information to add or edit a module
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function editPlugin( $option, $uid, $client )
{
	global $database, $my, $mainframe;

	$lists 	= array();
	$row 	=& JTable::getInstance('plugin', $database);

	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
		mosErrorAlert( $msg, "document.location.href='index2.php?option=$option'" );
	}

	if ($client == 'admin') {
		$where = "client_id='1'";
	} else {
		$where = "client_id='0'";
	}

	// get list of groups
	if ($row->access == 99 || $row->client_id == 1) {
		$lists['access'] = 'Administrator<input type="hidden" name="access" value="99" />';
	} else {
		// build the html select list for the group access
		$lists['access'] = mosAdminMenus::Access( $row );
	}

	if ($uid) {
		$row->checkout( $my->id );

		if ( $row->ordering > -10000 && $row->ordering < 10000 ) {
			// build the html select list for ordering
			$query = "SELECT ordering AS value, name AS text"
			. "\n FROM #__plugins"
			. "\n WHERE folder = '$row->folder'"
			. "\n AND published > 0"
			. "\n AND $where"
			. "\n AND ordering > -10000"
			. "\n AND ordering < 10000"
			. "\n ORDER BY ordering"
			;
			$order = mosGetOrderingList( $query );
			$lists['ordering'] = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_( 'This plugin cannot be reordered' );
		}

        $lang =& $mainframe->getLanguage();
        $lang->load( trim('plg_'. $row->element), JPATH_SITE );

		$data = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . DS . 'plugins'. DS .$row->folder . DS . $row->element .'.xml');

		$row->description = $data['description'];

	} else {
		$row->folder 		= '';
		$row->ordering 		= 999;
		$row->published 	= 1;
		$row->description 	= '';
	}

	$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_( 'DESCNEWITEMSDEFAULTLASTPLACE' );

	$lists['published'] = mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

	// get params definitions
	$params = new JParameter( $row->params, JApplicationHelper::getPath( 'bot_xml', $row->folder.DS.$row->element ), 'plugin' );


	HTML_modules::editPlugin( $row, $lists, $params, $option );
}

/**
* Publishes or Unpublishes one or more modules
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishPlugin( $cid=null, $publish=1, $option, $client )
{
	global $database, $my;

	if (count( $cid ) < 1) {
		$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
		echo "<script> alert('". JText::_( 'Select a plugin to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__plugins SET published = '". intval( $publish ) ."'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ))"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('plugin', $database);
		$row->checkin( $cid[0] );
	}

	josRedirect( 'index2.php?option='. $option .'&client='. $client );
}

/**
* Cancels an edit operation
*/
function cancelPlugin( $option, $client )
{
	global $database;

	$row =& JTable::getInstance('plugin', $database);
	$row->bind( $_POST );
	$row->checkin();

	josRedirect( 'index2.php?option='. $option .'&client='. $client );
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderPlugin( $uid, $inc, $option, $client )
{
	global $database;

	// Currently Unsupported
	if ($client == 'admin') {
		$where = "client_id = 1";
	} else {
		$where = "client_id = 0";
	}
	$row =& JTable::getInstance('plugin', $database);
	$row->load( $uid );
	$row->move( $inc, "folder='$row->folder' AND ordering > -10000 AND ordering < 10000 AND ($where)"  );

	josRedirect( 'index2.php?option='. $option );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option, $client )
{
	global $database;

	switch ( $access ) {
		case 'accesspublic':
			$access = 0;
			break;

		case 'accessregistered':
			$access = 1;
			break;

		case 'accessspecial':
			$access = 2;
			break;
	}

	$row =& JTable::getInstance('plugin', $database);
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	josRedirect( 'index2.php?option='. $option );
}

function saveOrder( &$cid )
{
	global $database;

	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$row 		=& JTable::getInstance('plugin', $database);
	$conditions = array();

	// update ordering values
	for ( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
				exit();
			} // if
			// remember to updateOrder this group
			$condition = "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000 AND client_id = $row->client_id";
			$found = false;
			foreach ( $conditions as $cond )
				if ($cond[1]==$condition) {
					$found = true;
					break;
				} // if
			if (!$found) $conditions[] = array($row->id, $condition);
		} // if
	} // for

	// execute updateOrder for each group
	foreach ( $conditions as $cond ) {
		$row->load( $cond[0] );
		$row->reorder( $cond[1] );
	} // foreach

	$msg 	= JText::_( 'New ordering saved' );
	josRedirect( 'index2.php?option=com_plugins', $msg );
} // saveOrder
?>
