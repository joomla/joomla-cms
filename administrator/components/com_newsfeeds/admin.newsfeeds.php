<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Newsfeeds
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
if (!$user->authorize( 'com_newsfeeds', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// Set the table directory
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_newsfeeds'.DS.'tables');

require_once( JApplicationHelper::getPath( 'admin_html' ) );

$task 	= JRequest::getCmd('task');

switch ($task) {

	case 'add' :
		editNewsFeed(false);
		break;
	case 'edit':
		editNewsFeed(true);
		break;

	case 'save':
	case 'apply':
		saveNewsFeed( );
		break;

	case 'publish':
		publishNewsFeeds( );
		break;

	case 'unpublish':
		unPublishNewsFeeds( );
		break;

	case 'remove':
		removeNewsFeeds( );
		break;

	case 'cancel':
		cancelNewsFeed( );
		break;

	case 'orderup':
		moveUpNewsFeed( );
		break;

	case 'orderdown':
		moveDownNewsFeed( );
		break;

	case 'saveorder':
		saveOrder( );
		break;

	default:
		showNewsFeeds( );
		break;
}

/**
* List the records
*/
function showNewsFeeds(  )
{
	global $mainframe, $option;

	$db					=& JFactory::getDBO();

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order",		'filter_order',		'a.ordering',	'cmd' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'',				'word' );
	$filter_state		= $mainframe->getUserStateFromRequest( "$option.filter_state",		'filter_state',		'',				'word' );
	$filter_catid		= $mainframe->getUserStateFromRequest( "$option.filter_catid",		'filter_catid',		0,				'int' );
	$search				= $mainframe->getUserStateFromRequest( "$option.search",			'search',			'',				'string' );
	$search				= JString::strtolower( $search );

	$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart	= $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

	$where = array();
	if ( $filter_catid ) {
		$where[] = 'a.catid = '.(int) $filter_catid;
	}
	if ($search) {
		$where[] = 'LOWER(a.name) LIKE '.$db->Quote('%'.$search.'%');
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = 'a.published = 1';
		} else if ($filter_state == 'U' ) {
			$where[] = 'a.published = 0';
		}
	}

	$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	if ($filter_order == 'a.ordering'){
		$orderby 	= ' ORDER BY catname, a.ordering';
	} else {
		$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', catname, a.ordering';
	}

	// get the total number of records
	$query = 'SELECT COUNT(*) '
	. ' FROM #__newsfeeds AS a'
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	// get the subset (based on limits) of required records
	$query = 'SELECT a.*, c.title AS catname, u.name AS editor'
	. ' FROM #__newsfeeds AS a'
	. ' LEFT JOIN #__categories AS c ON c.id = a.catid'
	. ' LEFT JOIN #__users AS u ON u.id = a.checked_out'
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
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['catid'] = JHTML::_('list.category',  'filter_catid', 'com_newsfeeds', $filter_catid, $javascript );

	// state filter
	$lists['state']	= JHTML::_('grid.state',  $filter_state );

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_newsfeeds::showNewsFeeds( $rows, $lists, $pageNav, $option );
}

/**
* Creates a new or edits and existing user record
*/
function editNewsFeed($edit)
{
	$db 		=& JFactory::getDBO();
	$user 		=& JFactory::getUser();

	$catid 		= JRequest::getVar( 'catid', 0, '', 'int' );
	$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );
	$option 	= JRequest::getCmd( 'option' );
	JArrayHelper::toInteger($cid, array(0));

	$row =& JTable::getInstance( 'newsfeed', 'Table' );
	// load the row from the db table
	if($edit)
	$row->load( $cid[0] );

	if ($edit) {
		// do stuff for existing records
		$row->checkout( $user->get('id') );
	} else {
		// do stuff for new records
		$row->ordering 		= 0;
		$row->numarticles 	= 5;
		$row->cache_time 	= 3600;
		$row->published 	= 1;
	}

	// build the html select list for ordering
	$query = 'SELECT a.ordering AS value, a.name AS text'
	. ' FROM #__newsfeeds AS a'
	. ' ORDER BY a.ordering'
	;

	if($edit)
		$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, $cid[0], $query, 1 );
	else
		$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, '', $query, 1 );

	// build list of categories
	$lists['category'] 			= JHTML::_('list.category',  'catid', $option, intval( $row->catid ) );
	// build the html select list
	$lists['published'] 		= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );

	HTML_newsfeeds::editNewsFeed( $row, $lists, $option );
}

/**
* Saves the record from an edit form submit
*/
function saveNewsFeed(  )
{
	global $mainframe;

	$db 		=& JFactory::getDBO();
	$task 		= JRequest::getVar( 'task');

	$row 		=& JTable::getInstance( 'newsfeed', 'Table' );
	if (!$row->bind(JRequest::get('post'))) {
		JError::raiseError(500, $row->getError() );
	}

	// Sets rtl value when rtl checkbox ticked
	$isRtl = JRequest::getInt('rtl');
	if ($isRtl) {
		$row->rtl = 1;
	}

	// pre-save checks
	if (!$row->check()) {
		JError::raiseError(500, $row->getError() );
	}

	// if new item, order last in appropriate group
	if (!$row->id) {
		$where = "catid = " . (int) $row->catid;
		$row->ordering = $row->getNextOrder( $where );
	}

	// save the changes
	if (!$row->store()) {
		JError::raiseError(500, $row->getError() );
	}
	$row->checkin();

	switch ($task)
	{
		case 'apply':
			$msg = JText::_( 'Changes to Newsfeed saved' );
			$link = 'index.php?option=com_newsfeeds&task=edit&cid[]='. $row->id ;
			break;

		case 'save':
		default:
			$msg = JText::_( 'Newsfeed saved' );
			$link = 'index.php?option=com_newsfeeds';
			break;
	}

	$mainframe->redirect( $link, $msg );
}

/**
* Publishes one or more modules
*/
function publishNewsFeeds(  ) {
	changePublishNewsFeeds( 1 );
}

/**
* Unpublishes one or more modules
*/
function unPublishNewsFeeds(  ) {
	changePublishNewsFeeds( 0 );
}

/**
* Publishes or Unpublishes one or more modules
* @param integer 0 if unpublishing, 1 if publishing
*/
function changePublishNewsFeeds( $publish )
{
	global $mainframe;

	$db 		=& JFactory::getDBO();
	$user 		=& JFactory::getUser();

	$cid		= JRequest::getVar('cid', array(), '', 'array');
	$option		= JRequest::getCmd('option');
	JArrayHelper::toInteger($cid);

	if (empty( $cid )) {
		JError::raiseWarning( 500, 'No items selected' );
		$mainframe->redirect( 'index.php?option='. $option );
	}

	$cids = implode( ',', $cid );

	$query = 'UPDATE #__newsfeeds'
	. ' SET published = '.(int) $publish
	. ' WHERE id IN ( '. $cids .' )'
	. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id') .' ) )'
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		JError::raiseError(500, $db->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance( 'newsfeed', 'Table' );
		$row->checkin( $cid[0] );
	}

	$mainframe->redirect( 'index.php?option='. $option );
}

/**
* Removes records
*/
function removeNewsFeeds( )
{
	global $mainframe;

	$db 		=& JFactory::getDBO();
	$cid 		= JRequest::getVar('cid', array(), '', 'array');
	$option 	= JRequest::getCmd('option');
	JArrayHelper::toInteger($cid);

	if (count($cid) < 1) {
		JError::raiseWarning(500, JText::_( 'Select an item to delete', true ) );
		$mainframe->redirect( 'index.php?option='. $option );
	}

	$cids = implode( ',', $cid );
	$query = 'DELETE FROM #__newsfeeds'
	. ' WHERE id IN ( '. $cids .' )'
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
	}

	$mainframe->redirect( 'index.php?option='. $option );
}

/**
* Cancels an edit operation
*/
function cancelNewsFeed(  )
{
	global $mainframe;

	$db 	=& JFactory::getDBO();
	$option = JRequest::getCmd('option');

	$row =& JTable::getInstance( 'newsfeed', 'Table' );
	$row->bind(JRequest::get('post'));
	$row->checkin();
	$mainframe->redirect( 'index.php?option='. $option );
}

/**
* Moves the record up one position
*/
function moveUpNewsFeed(  ) {
	orderNewsFeed( -1 );
}

/**
* Moves the record down one position
*/
function moveDownNewsFeed(  ) {
	orderNewsFeed( 1 );
}

/**
* Moves the order of a record
* @param integer The direction to reorder, +1 down, -1 up
*/
function orderNewsFeed( $inc )
{
	global $mainframe;

	$db		=& JFactory::getDBO();
	$cid	= JRequest::getVar('cid', array(0), '', 'array');
	$option = JRequest::getCmd('option');
	JArrayHelper::toInteger($cid, array(0));

	$limit 		= JRequest::getVar( 'limit', 0, '', 'int' );
	$limitstart = JRequest::getVar( 'limitstart', 0, '', 'int' );
	$catid 		= JRequest::getVar( 'catid', 0, '', 'int' );

	$row =& JTable::getInstance( 'newsfeed', 'Table' );
	$row->load( $cid[0] );
	$row->move( $inc, 'catid = '.(int) $row->catid.' AND published != 0' );

	$mainframe->redirect( 'index.php?option='. $option );
}

/**
* Saves user reordering entry
*/
function saveOrder(  )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
	JArrayHelper::toInteger($cid);

	$total		= count( $cid );
	$order		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	JArrayHelper::toInteger($order, array(0));

	$row =& JTable::getInstance( 'newsfeed', 'Table' );
	$groupings = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( (int) $cid[$i] );
		// track categories
		$groupings[] = $row->catid;

		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				JError::raiseError(500, $db->getErrorMsg() );
			}
		}
	}

	// execute updateOrder for each parent group
	$groupings = array_unique( $groupings );
	foreach ($groupings as $group){
		$row->reorder('catid = '.(int) $group);
	}

	$msg 	= 'New ordering saved';
	$mainframe->redirect( 'index.php?option=com_newsfeeds', $msg );
}
?>
