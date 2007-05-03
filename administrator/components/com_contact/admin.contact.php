<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Contact
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
if (!$user->authorize( 'com_contact', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
// Set the table directory
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_contact'.DS.'tables');

$id 	= JRequest::getVar(  'id', 0, 'get', 'int' );
$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task)
{
	case 'add' :
	case 'edit':
		editContact( );
		break;

	case 'apply':
	case 'save':
	case 'save2new':
	case 'save2copy':
		saveContact( $task );
		break;

	case 'remove':
		removeContacts( $cid );
		break;

	case 'publish':
		changeContact( $cid, 1 );
		break;

	case 'unpublish':
		changeContact( $cid, 0 );
		break;

	case 'orderup':
		orderContacts( $cid[0], -1 );
		break;

	case 'orderdown':
		orderContacts( $cid[0], 1 );
		break;

	case 'accesspublic':
		changeAccess( $cid[0], 0 );
		break;

	case 'accessregistered':
		changeAccess( $cid[0], 1 );
		break;

	case 'accessspecial':
		changeAccess( $cid[0], 2 );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'cancel':
		cancelContact();
		break;

	default:
		showContacts( $option );
		break;
}

/**
* List the records
* @param string The current GET/POST option
*/
function showContacts( $option )
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order', 		'filter_order', 	'cd.ordering' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( $option.'filter_state', 		'filter_state', 	'*' );
	$filter_catid 		= $mainframe->getUserStateFromRequest( $option.'filter_catid', 		'filter_catid',		0 );
	$search 			= $mainframe->getUserStateFromRequest( $option.'search', 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

	$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 0);
	$limitstart	= (int) $mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0);

	$where = array();

	if ( $search ) {
		$where[] = 'cd.name LIKE "%'.$search.'%"';
	}
	if ( $filter_catid ) {
		$where[] = 'cd.catid = "'.$filter_catid.'"';
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = 'cd.published = 1';
		} else if ($filter_state == 'U' ) {
			$where[] = 'cd.published = 0';
		}
	}

	$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	if ($filter_order == 'cd.ordering'){
		$orderby 	= ' ORDER BY category, cd.ordering';
	} else {
		$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', category, cd.ordering';
	}

	// get the total number of records
	$query = 'SELECT COUNT(*)'
	. ' FROM #__contact_details AS cd'
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	// get the subset (based on limits) of required records
	$query = 'SELECT cd.*, cc.title AS category, u.name AS user, v.name as editor, g.name AS groupname'
	. ' FROM #__contact_details AS cd'
	. ' LEFT JOIN #__groups AS g ON g.id = cd.access'
	. ' LEFT JOIN #__categories AS cc ON cc.id = cd.catid'
	. ' LEFT JOIN #__users AS u ON u.id = cd.user_id'
	. ' LEFT JOIN #__users AS v ON v.id = cd.checked_out'
	. $where
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();

	// build list of categories
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['catid'] = JAdministratorHelper::ComponentCategory( 'filter_catid', 'com_contact_details', intval( $filter_catid ), $javascript );

	// state filter
	$lists['state']	= JHTML::_('grid.state',  $filter_state );

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_contact::showcontacts( $rows, $pageNav, $option, $lists );
}

/**
* Creates a new or edits and existing user record
* @param int The id of the record, 0 if a new entry
* @param string The current GET/POST option
*/
function editContact( )
{
	$db		=& JFactory::getDBO();
	$user 	=& JFactory::getUser();

	$cid 	= JRequest::getVar( 'cid', array(0));
	$option = JRequest::getVar( 'option');

	if (!is_array( $cid )) {
		$cid = array(0);
	}

	$row =& JTable::getInstance('contact', 'Table');
	// load the row from the db table
	$row->load( $cid[0] );

	if ($cid[0]) {
		// do stuff for existing records
		$row->checkout($user->get('id'));
	} else {
		// do stuff for new records
		$row->imagepos 	= 'top';
		$row->ordering 	= 0;
		$row->published = 1;
	}
	$lists = array();

	// build the html select list for ordering
	$query = 'SELECT ordering AS value, name AS text'
	. ' FROM #__contact_details'
	. ' WHERE published >= 0'
	. ' AND catid = "'.$row->catid.'"'
	. ' ORDER BY ordering'
	;
	$lists['ordering'] 			= JAdministratorHelper::SpecificOrdering( $row, $cid[0], $query, 1 );

	// build list of users
	$lists['user_id'] 			= JAdministratorHelper::UserSelect( 'user_id', $row->user_id, 1, NULL, 'name', 0 );
	// build list of categories
	$lists['catid'] 			= JAdministratorHelper::ComponentCategory( 'catid', 'com_contact_details', intval( $row->catid ) );
	// build the html select list for images
	$lists['image'] 			= JAdministratorHelper::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= JAdministratorHelper::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= JHTML::_('select.booleanlist',  'published', '', $row->published );
	// build the html radio buttons for default
	$lists['default_con'] 		= JHTML::_('select.booleanlist',  'default_con', '', $row->default_con );

	// get params definitions
	$file 	= JPATH_ADMINISTRATOR .'/components/com_contact/contact_items.xml';
	$params = new JParameter( $row->params, $file, 'component' );

	HTML_contact::editcontact( $row, $lists, $option, $params );
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveContact( $task )
{
	global $mainframe;

	// Initialize variables
	$db		=& JFactory::getDBO();
	$row	=& JTable::getInstance('contact', 'Table');
	$post = JRequest::get( 'post' );
	$post['misc'] = JRequest::getVar('misc', '', 'POST', 'string', JREQUEST_ALLOWHTML);
	if (!$row->bind( $post )) {
		JError::raiseError(500, $row->getError() );
	}
	// save params
	$params = JRequest::getVar( 'params', array(), 'post', 'array' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->params = implode( "\n", $txt );
	}

	// save to a copy, reset the primary key
	if ($task == 'save2copy') {
		$row->id = 0;
	}

	// pre-save checks
	if (!$row->check()) {
		JError::raiseError(500, $row->getError() );
	}

	// if new item, order last in appropriate group
	if (!$row->id) {
		$where = "catid = " . $row->catid ;
		$row->ordering = $row->getNextOrder ( $where );
	}

	// save the changes
	if (!$row->store()) {
		JError::raiseError(500, $row->getError() );
	}
	$row->checkin();
	if ($row->default_con) {
		$query = 'UPDATE #__contact_details'
		. ' SET default_con = 0'
		. ' WHERE id <> '. $row->id
		. ' AND default_con = 1'
		;
		$db->setQuery( $query );
		$db->query();
	}

	switch ($task)
	{
		case 'apply':
		case 'save2copy':
			$msg	= JText::sprintf( 'Changes to X saved', 'Contact' );
			$link	= 'index.php?option=com_contact&task=edit&cid[]='. $row->id .'';
			break;

		case 'save2new':
			$msg	= JText::sprintf( 'Changes to X saved', 'Contact' );
			$link	= 'index.php?option=com_contact&task=edit';
			break;

		case 'save':
		default:
			$msg	= JText::_( 'Contact saved' );
			$link	= 'index.php?option=com_contact';
			break;
	}

	$mainframe->redirect( $link, $msg );
}

/**
* Removes records
* @param array An array of id keys to remove
* @param string The current GET/POST option
*/
function removeContacts( &$cid )
{
	global $mainframe;

	// Initialize variables
	$db =& JFactory::getDBO();
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = 'DELETE FROM #__contact_details'
		. ' WHERE id IN ( '. $cids .' )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
		}
	}

	$mainframe->redirect( "index.php?option=com_contact" );
}

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current option
*/
function changeContact( $cid=null, $state=0 )
{
	global $mainframe;

	// Initialize variables
	$db 	=& JFactory::getDBO();
	$user 	=& JFactory::getUser();

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $state ? 'publish' : 'unpublish';
		JError::raiseError(500, JText::_( 'Select an item to '.$action, true ) );
	}

	$cids = implode( ',', $cid );

	$query = 'UPDATE #__contact_details'
	. ' SET published = ' . intval( $state )
	. ' WHERE id IN ( '. $cids .' )'
	. ' AND ( checked_out = 0 OR ( checked_out = '. $user->get('id') .' ) )'
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		JError::raiseError(500, $db->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('contact', 'Table');
		$row->checkin( intval( $cid[0] ) );
	}

	$mainframe->redirect( 'index.php?option=com_contact' );
}

/** JJC
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderContacts( $uid, $inc )
{
	global $mainframe;

	// Initialize variables
	$db =& JFactory::getDBO();

	$row =& JTable::getInstance('contact', 'Table');
	$row->load( $uid );
	$row->move( $inc, 'catid = '. $row->catid .' AND published != 0' );

	$mainframe->redirect( 'index.php?option=com_contact' );
}

/** PT
* Cancels editing and checks in the record
*/
function cancelContact()
{
	global $mainframe;

	// Initialize variables
	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('contact', 'Table');
	$row->bind( JRequest::get( 'post' ));
	$row->checkin();

	$mainframe->redirect('index.php?option=com_contact');
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function changeAccess( $id, $access  )
{
	global $mainframe;

	// Initialize variables
	$db =& JFactory::getDBO();

	$row =& JTable::getInstance('contact', 'Table');
	$row->load( $id );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	$mainframe->redirect( 'index.php?option=com_contact' );
}

function saveOrder( &$cid )
{
	global $mainframe;

	// Initialize variables
	$db			=& JFactory::getDBO();
	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$row =& JTable::getInstance('contact', 'Table');
	$groupings = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( (int) $cid[$i] );
		// track categories
		$groupings[] = $row->catid;

		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				//TODO - convert to JError
				JError::raiseError(500, $db->getErrorMsg() );
			}
		}
	}

	// execute updateOrder for each parent group
	$groupings = array_unique( $groupings );
	foreach ($groupings as $group){
		$row->reorder("catid = $group");
	}

	$msg 	= 'New ordering saved';
	$mainframe->redirect( 'index.php?option=com_contact', $msg );
}
?>
