<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Plugins
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

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize( 'com_plugins', 'manage' )) {
		$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
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
	case 'add' :
	case 'edit':
		editPlugin( );
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
	global $mainframe, $option;

	$db =& JFactory::getDBO();

	JMenuBar::title( JText::_( 'Plugin Manager' ), 'plugin.png' );
	JMenuBar::publishList();
	JMenuBar::unpublishList();
	JMenuBar::editListX();
	JMenuBar::help( 'screen.plugins' );

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.$client.filter_order", 		'filter_order', 	'p.folder' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.$client.filter_state", 		'filter_state', 	'*' );
	$filter_type		= $mainframe->getUserStateFromRequest( "$option.$client.filter_type", 		'filter_type', 		1 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.$client.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

	$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
	$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0 );

	if ($client == 'admin') {
		$where[] = 'p.client_id = "1"';
		$client_id = 1;
	} else {
		$where[] = 'p.client_id = "0"';
		$client_id = 0;
	}

	// used by filter
	if ( $filter_type != 1 ) {
		$where[] = 'p.folder = "'.$filter_type.'"';
	}
	if ( $search ) {
		$where[] = 'LOWER( p.name ) LIKE "%'.$search.'%"';
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = 'p.published = 1';
		} else if ($filter_state == 'U' ) {
			$where[] = 'p.published = 0';
		}
	}

	$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	$orderby 	= ' ORDER BY '.$filter_order .' '. $filter_order_Dir .', p.ordering ASC';

	// get the total number of records
	$query = 'SELECT COUNT(*)'
	. ' FROM #__plugins AS p'
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = 'SELECT p.*, u.name AS editor, g.name AS groupname'
	. ' FROM #__plugins AS p'
	. ' LEFT JOIN #__users AS u ON u.id = p.checked_out'
	. ' LEFT JOIN #__groups AS g ON g.id = p.access'
	. $where
	. ' GROUP BY p.id'
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	// get list of Positions for dropdown filter
	$query = 'SELECT folder AS value, folder AS text'
	. ' FROM #__plugins'
	. ' WHERE client_id = "'.$client_id.'"'
	. ' GROUP BY folder'
	. ' ORDER BY folder'
	;
	$types[] = JHTMLSelect::option( 1, '- '. JText::_( 'Select Type' ) .' -' );
	$db->setQuery( $query );
	$types 			= array_merge( $types, $db->loadObjectList() );
	$lists['type']	= JHTMLSelect::genericList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_type );

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

	HTML_modules::showPlugins( $rows, $client, $pageNav, $option, $lists );
}

/**
* Saves the module after an edit form submit
*/
function savePlugin( $option, $client, $task )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('plugin');

	if (!$row->bind(JRequest::get('post'))) {
		JError::raiseError(500, $row->getError() );
	}
	if (!$row->check()) {
		JError::raiseError(500, $row->getError() );
	}
	if (!$row->store()) {
		JError::raiseError(500, $row->getError() );
	}
	$row->checkin();

	if ($client == 'admin') {
		$where = "client_id=1";
	} else {
		$where = "client_id=0";
	}

	$row->reorder( "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000 AND ( $where )" );

	switch ( $task ) {
		case 'apply':
			$msg = JText::sprintf( 'Successfully Saved changes to Plugin', $row->name );
			$mainframe->redirect( 'index.php?option='. $option .'&amp;client='. $client .'&amp;task=edit&amp;cid[]='. $row->id, $msg );

		case 'save':
		default:
			$msg = JText::sprintf( 'Successfully Saved Plugin', $row->name );
			$mainframe->redirect( 'index.php?option='. $option .'&amp;client='. $client, $msg );
			break;
	}
}

/**
* Compiles information to add or edit a module
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function editPlugin( )
{
	global $option, $mainframe;
	
	$db		=& JFactory::getDBO();
	$user 	=& JFactory::getUser();

	$client = JRequest::getVar( 'client', 'site' );
	$cid 	= JRequest::getVar( 'cid', array(0));
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	JMenuBar::title( JText::_( 'Plugin' ) .': <small><small>[' .JText::_('Edit'). ']</small></small>', 'plugin.png' );
	JMenuBar::save();
	JMenuBar::apply();
	JMenuBar::cancel( 'cancel', 'Close' );
	JMenuBar::help( 'screen.plugins.edit' );

	$lists 	= array();
	$row 	=& JTable::getInstance('plugin');

	// load the row from the db table
	$row->load( $cid[0] );

	// fail if checked out not by 'me'
	
	if ($row->isCheckedOut( $user->get('id') )) {
		$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The plugin' ), $row->title );
		$mainframe->redirect( 'index.php?option='. $option .'&amp;client='. $client, $msg, 'error' );
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
		$lists['access'] = JAdminMenus::Access( $row );
	}

	if ($cid[0])
	{
		$row->checkout( $user->get('id') );

		if ( $row->ordering > -10000 && $row->ordering < 10000 ) {
			// build the html select list for ordering
			$query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__plugins'
			. ' WHERE folder = "'.$row->folder.'"'
			. ' AND published > 0'
			. ' AND '. $where
			. ' AND ordering > -10000'
			. ' AND ordering < 10000'
			. ' ORDER BY ordering'
			;
			$order = JAdminMenus::GenericOrdering( $query );
			$lists['ordering'] = JHTMLSelect::genericList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_( 'This plugin cannot be reordered' );
		}

		$lang =& JFactory::getLanguage();
		$lang->load( 'plg_' . trim( $row->folder ) . '_' . trim( $row->element ), JPATH_ADMINISTRATOR );

		$data = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . DS . 'plugins'. DS .$row->folder . DS . $row->element .'.xml');

		$row->description = $data['description'];

	} else {
		$row->folder 		= '';
		$row->ordering 		= 999;
		$row->published 	= 1;
		$row->description 	= '';
	}

	$lists['published'] = JHTMLSelect::yesnoList( 'published', 'class="inputbox"', $row->published );

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
	global $mainframe;

	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	if (count( $cid ) < 1) {
		$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
		JError::raiseError(500, JText::_( 'Select a plugin to '.$action ) );
	}

	$cids = implode( ',', $cid );

	$query = 'UPDATE #__plugins SET published = "'. intval( $publish ) .'"'
	. ' WHERE id IN ( '.$cids.' )'
	. ' AND ( checked_out = 0 OR ( checked_out = ' .$user->get( 'id' ). ' ))'
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		JError::raiseError(500, $db->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('plugin');
		$row->checkin( $cid[0] );
	}

	$mainframe->redirect( 'index.php?option='. $option .'&amp;client='. $client );
}

/**
* Cancels an edit operation
*/
function cancelPlugin( $option, $client )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('plugin');
	$row->bind(JRequest::get('post'));
	$row->checkin();

	$mainframe->redirect( 'index.php?option='. $option .'&amp;client='. $client );
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderPlugin( $uid, $inc, $option, $client )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	// Currently Unsupported
	if ($client == 'admin') {
		$where = "client_id = 1";
	} else {
		$where = "client_id = 0";
	}
	$row =& JTable::getInstance('plugin');
	$row->load( $uid );
	$row->move( $inc, "folder='$row->folder' AND ordering > -10000 AND ordering < 10000 AND ($where)"  );

	$mainframe->redirect( 'index.php?option='. $option );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option, $client )
{
	global $mainframe;

	$db =& JFactory::getDBO();
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

	$row =& JTable::getInstance('plugin');
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	$mainframe->redirect( 'index.php?option='. $option );
}

function saveOrder( &$cid )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$row 		=& JTable::getInstance('plugin');
	$conditions = array();

	// update ordering values
	for ( $i=0; $i < $total; $i++ ) {
		$row->load( (int) $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				JError::raiseError(500, $db->getErrorMsg() );
			}
			// remember to updateOrder this group
			$condition = 'folder = "'.$row->folder.'" AND ordering > -10000 AND ordering < 10000 AND client_id = $row->client_id';
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
	$mainframe->redirect( 'index.php?option=com_plugins', $msg );
} // saveOrder
?>
