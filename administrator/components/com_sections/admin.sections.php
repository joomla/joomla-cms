<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Sections
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

require_once( JApplicationHelper::getPath( 'admin_html' ) );

// get parameters from the URL or submitted form
$scope 		= JRequest::getCmd( 'scope' );
$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );
JArrayHelper::toInteger($cid, array(0));

$task = JRequest::getCmd('task');

switch ($task)
{
	case 'add' :
	case 'edit':
		editSection( );
		break;

	case 'go2menu':
	case 'go2menuitem':
	case 'save':
	case 'apply':
		saveSection( $option, $scope, $task );
		break;

	case 'remove':
		removeSections( $cid, $scope, $option );
		break;

	case 'copyselect':
		copySectionSelect( $option, $cid, $scope );
		break;

	case 'copysave':
		copySectionSave( $cid );
		break;

	case 'publish':
		publishSections( $scope, $cid, 1, $option );
		break;

	case 'unpublish':
		publishSections( $scope, $cid, 0, $option );
		break;

	case 'cancel':
		cancelSection( $option, $scope );
		break;

	case 'orderup':
		orderSection( $cid[0], -1, $option, $scope );
		break;

	case 'orderdown':
		orderSection( $cid[0], 1, $option, $scope );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0, $option );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1, $option );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	default:
		showSections( $scope, $option );
		break;
}

/**
* Compiles a list of categories for a section
* @param database A database connector object
* @param string The name of the category section
* @param string The name of the current user
*/
function showSections( $scope, $option )
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$user				=& JFactory::getUser();
	$filter_order		= $mainframe->getUserStateFromRequest( $option.'.filter_order',		'filter_order',		's.ordering',	'cmd' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.filter_order_Dir',	'filter_order_Dir',	'',				'word' );
	$filter_state		= $mainframe->getUserStateFromRequest( $option.'.filter_state',		'filter_state',		'',				'word' );
	$search				= $mainframe->getUserStateFromRequest( $option.'.search',			'search',			'',				'string' );
	$search				= $db->getEscaped( trim( JString::strtolower( $search ) ) );

	$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart	= $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

	$where[] = 's.scope = "'.$scope.'"';

	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = 's.published = 1';
		} else if ($filter_state == 'U' ) {
			$where[] = 's.published = 0';
		}
	}
	if ($search) {
		$where[] = 'LOWER(s.title) LIKE "%'.$search.'%"';
	}

	$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	$orderby 	= ' ORDER BY '.$filter_order.' '. $filter_order_Dir .', s.ordering';

	// get the total number of records
	$query = 'SELECT COUNT(*)'
	. ' FROM #__sections AS s'
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = 'SELECT s.*, g.name AS groupname, u.name AS editor'
	. ' FROM #__sections AS s'
	. ' LEFT JOIN #__content AS cc ON s.id = cc.sectionid'
	. ' LEFT JOIN #__users AS u ON u.id = s.checked_out'
	. ' LEFT JOIN #__groups AS g ON g.id = s.access'
	. $where
	. ' GROUP BY s.id'
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	$count = count( $rows );
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = 'SELECT COUNT( a.id )'
		. ' FROM #__categories AS a'
		. ' WHERE a.section = "'. $rows[$i]->id .'"'
		. ' AND a.published <> -2'
		;
		$db->setQuery( $query );
		$active = $db->loadResult();
		$rows[$i]->categories = $active;
	}
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = 'SELECT COUNT( a.id )'
		. ' FROM #__content AS a'
		. ' WHERE a.sectionid = "'. $rows[$i]->id .'"'
		. ' AND a.state <> -2'
		;
		$db->setQuery( $query );
		$active = $db->loadResult();
		$rows[$i]->active = $active;
	}
	// number of Trashed Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = 'SELECT COUNT( a.id )'
		. ' FROM #__content AS a'
		. ' WHERE a.sectionid = "'. $rows[$i]->id .'"'
		. ' AND a.state = -2'
		;
		$db->setQuery( $query );
		$trash = $db->loadResult();
		$rows[$i]->trash = $trash;
	}

	// state filter
	$lists['state']	= JHTML::_('grid.state',  $filter_state );

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	// search filter
	$lists['search']= $search;

	sections_html::show( $rows, $scope, $user->get('id'), $pageNav, $option, $lists );
}

/**
* Compiles information to add or edit a section
* @param database A database connector object
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
* @param string The name of the current user
*/
function editSection( )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$user 		=& JFactory::getUser();

	$option		= JRequest::getCmd( 'option');
	$scope		= JRequest::getCmd( 'scope' );
	$cid		= JRequest::getVar( 'cid', array(0), '', 'array' );
	JArrayHelper::toInteger($cid, array(0));

	$row =& JTable::getInstance('section');
	// load the row from the db table
	$row->load( $cid[0] );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $user->get('id') )) {
		$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The section' ), $row->title );
		$mainframe->redirect( 'index.php?option='. $option .'&scope='. $row->scope, $msg );
	}

	if ( $cid[0] ) {
		$row->checkout( $user->get('id') );
	} else {
		$row->scope 		= $scope;
		$row->published 	= 1;
	}

	// build the html select list for ordering
	$query = 'SELECT ordering AS value, title AS text'
	. ' FROM #__sections'
	. ' WHERE scope='.$db->Quote($row->scope).' ORDER BY ordering'
	;
	$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, $cid[0], $query );

	// build the select list for the image positions
	$active =  ( $row->image_position ? $row->image_position : 'left' );
	$lists['image_position'] 	= JHTML::_('list.positions',  'image_position', $active, NULL, 0 );
	// build the html select list for images
	$lists['image'] 			= JHTML::_('list.images',  'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= JHTML::_('list.accesslevel',  $row );
	// build the html radio buttons for published
	$lists['published'] 		= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );

	sections_html::edit( $row, $option, $lists );
}

/**
* Saves the catefory after an edit form submit
* @param database A database connector object
* @param string The name of the category section
*/
function saveSection( $option, $scope, $task )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$menu		= JRequest::getVar( 'menu', 'mainmenu', 'post', 'string' );
	$menuid		= JRequest::getVar( 'menuid', 0, 'post', 'int' );
	$oldtitle	= JRequest::getVar( 'oldtitle', '', '', 'post', 'string' );

	$post = JRequest::get('post');

	// fix up special html fields
	$post['description'] = JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWHTML );

	$row =& JTable::getInstance('section');
	if (!$row->bind($post)) {
		JError::raiseError(500, $row->getError() );
	}
	if (!$row->check()) {
		JError::raiseError(500, $row->getError() );
	}
	if ( $oldtitle ) {
		if ( $oldtitle <> $row->title ) {
			$query = 'UPDATE #__menu'
			. ' SET name = "'.$row->title.'"'
			. ' WHERE name = "'.$oldtitle.'"'
			. ' AND type = "content_section"'
			;
			$db->setQuery( $query );
			$db->query();
		}
	}

	// if new item order last in appropriate group
	if (!$row->id) {
		$row->ordering = $row->getNextOrder();
	}

	if (!$row->store()) {
		JError::raiseError(500, $row->getError() );
	}
	$row->checkin();

	switch ( $task )
	{
		case 'go2menu':
			$mainframe->redirect( 'index.php?option=com_menus&menutype='. $menu );
			break;

		case 'go2menuitem':
			$mainframe->redirect( 'index.php?option=com_menus&menutype='. $menu .'&task=edit&id='. $menuid );
			break;

		case 'apply':
			$msg = JText::_( 'Changes to Section saved' );
			$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope .'&task=edit&cid[]='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = JText::_( 'Section saved' );
			$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope, $msg );
			break;
	}
}
/**
* Deletes one or more categories from the categories table
* @param database A database connector object
* @param string The name of the category section
* @param array An array of unique category id numbers
*/
function removeSections( $cid, $scope, $option )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	if (count( $cid ) < 1) {
		JError::raiseError(500, JText::_( 'Select a section to delete', true ) );
	}

	JArrayHelper::toInteger( $cid );
	$cids = implode( ',', $cid );

	$query = 'SELECT s.id, s.name, COUNT(c.id) AS numcat'
	. ' FROM #__sections AS s'
	. ' LEFT JOIN #__categories AS c ON c.section=s.id'
	. ' WHERE s.id IN ( '.$cids.' )'
	. ' GROUP BY s.id'
	;
	$db->setQuery( $query );
	if (!($rows = $db->loadObjectList())) {
		echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
	}

	$err = array();
	$cid = array();
	foreach ($rows as $row) {
		if ($row->numcat == 0) {
			$cid[]	= (int) $row->id;
			$name[]	= $row->name;
		} else {
			$err[]	= $row->name;
		}
	}

	if (count( $cid ))
	{
		$cids = implode( ',', $cid );
		$query = 'DELETE FROM #__sections'
		. ' WHERE id IN ( '.$cids.' )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
		}
	}

	if (count( $err ))
	{
		$cids = implode( ', ', $err );
		$msg = JText::sprintf( 'DESCCANNOTBEREMOVED', $cids );
		$mainframe->redirect( 'index.php?option='. $option .'&amp;scope='. $scope, $msg );
	}

	$names = implode( ', ', $name );
	$msg = JText::sprintf( 'Sections successfully deleted', $names );
	$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope, $msg );
}

/**
* Publishes or Unpublishes one or more categories
* @param database A database connector object
* @param string The name of the category section
* @param integer A unique category id (passed from an edit form)
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The name of the current user
*/
function publishSections( $scope, $cid=null, $publish=1, $option )
{
	global $mainframe;

	$db 	=& JFactory::getDBO();
	$user 	=& JFactory::getUser();

	if ( !is_array( $cid ) || count( $cid ) < 1 ) {
		$action = $publish ? 'publish' : 'unpublish';
		JError::raiseError(500, JText::_( 'Select a section to '.$action, true ) );
	}

	$cids = implode( ',', $cid );
	$count = count( $cid );
	if ( $publish ) {
		if ( !$count ){
			echo "<script> alert('". JText::_( 'Cannot Publish an Empty Section', true ) .": ". $count ."'); window.history.go(-1);</script>\n";
			return;
		}
	}

	$query = 'UPDATE #__sections'
	. ' SET published = ' . intval( $publish )
	. ' WHERE id IN ( '.$cids.' )'
	. ' AND ( checked_out = 0 OR ( checked_out = ' .$user->get('id'). ' ) )'
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		JError::raiseError(500, $db->getErrorMsg() );
	}

	if ( $count == 1 ) {
		$row =& JTable::getInstance('section');
		$row->checkin( $cid[0] );
	}

	// check if section linked to menu items if unpublishing
	if ( $publish == 0 ) {
		$query = 'SELECT id'
		. ' FROM #__menu'
		. ' WHERE type = "content_section"'
		. ' AND componentid IN ( '.$cids.' )'
		;
		$db->setQuery( $query );
		$menus = $db->loadObjectList();

		if ($menus) {
			foreach ($menus as $menu) {
				$query = 'UPDATE #__menu'
				. ' SET published = ' . intval( $publish )
				. ' WHERE id = ' . $menu->id
				;
				$db->setQuery( $query );
				$db->query();
			}
		}
	}

	$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope );
}

/**
* Cancels an edit operation
* @param database A database connector object
* @param string The name of the category section
* @param integer A unique category id
*/
function cancelSection( $option, $scope )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('section');
	$row->bind(JRequest::get('post'));
	$row->checkin();

	$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderSection( $uid, $inc, $option, $scope )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('section');
	$row->load( $uid );
	$row->move( $inc, "scope = '$row->scope'" );

	$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope );
}


/**
* Form for copying item(s) to a specific menu
*/
function copySectionSelect( $option, $cid, $section )
{
	global $mainframe;

	$db =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		JError::raiseError(500, JText::_( 'Select an item to move', true ) );
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = 'SELECT a.name, a.id'
	. ' FROM #__categories AS a'
	. ' WHERE a.section IN ( '.$cids.' )'
	;
	$db->setQuery( $query );
	$categories = $db->loadObjectList();

	## query to list items from categories
	$query = 'SELECT a.title, a.id'
	. ' FROM #__content AS a'
	. ' WHERE a.sectionid IN ( '.$cids.' )'
	. ' ORDER BY a.sectionid, a.catid, a.title'
	;
	$db->setQuery( $query );
	$contents = $db->loadObjectList();

	sections_html::copySectionSelect( $option, $cid, $categories, $contents, $section );
}


/**
* Save the item(s) to the menu selected
*/
function copySectionSave( $sectionid )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$title		= JRequest::getString( 'title' );
	$contentid	= JRequest::getVar( 'content' );
	$categoryid = JRequest::getVar( 'category' );
	JArrayHelper::toInteger($contentid);
	JArrayHelper::toInteger($categoryid);

	// copy section
	$section =& JTable::getInstance('section');
	foreach( $sectionid as $id ) {
		$section->load( $id );
		$section->id 	= NULL;
		$section->title = $title;
		$section->name 	= $title;
		if ( !$section->check() ) {
			JError::raiseError(500, $section->getError() );
		}

		if ( !$section->store() ) {
			JError::raiseError(500, $section->getError() );
		}
		$section->checkin();
		$section->reorder( "section = '$section->id'" );
		// stores original catid
		$newsectids[]["old"] = $id;
		// pulls new catid
		$newsectids[]["new"] = $section->id;
	}
	$sectionMove = $section->id;

	// copy categories
	$category =& JTable::getInstance('category');
	foreach( $categoryid as $id ) {
		$category->load( $id );
		$category->id = NULL;
		$category->section = $sectionMove;
		foreach( $newsectids as $newsectid ) {
			if ( $category->section == $newsectid["old"] ) {
				$category->section = $newsectid["new"];
			}
		}
		if (!$category->check()) {
			JError::raiseError(500, $category->getError() );
		}

		if (!$category->store()) {
			JError::raiseError(500, $category->getError() );
		}
		$category->checkin();
		$category->reorder( "section = '$category->section'" );
		// stores original catid
		$newcatids[]["old"] = $id;
		// pulls new catid
		$newcatids[]["new"] = $category->id;
	}

	$content =& JTable::getInstance('content');
	foreach( $contentid as $id) {
		$content->load( $id );
		$content->id = NULL;
		$content->hits = 0;
		foreach( $newsectids as $newsectid ) {
			if ( $content->sectionid == $newsectid["old"] ) {
				$content->sectionid = $newsectid["new"];
			}
		}
		foreach( $newcatids as $newcatid ) {
			if ( $content->catid == $newcatid["old"] ) {
				$content->catid = $newcatid["new"];
			}
		}
		if (!$content->check()) {
			JError::raiseError(500, $content->getError() );
		}

		if (!$content->store()) {
			JError::raiseError(500, $content->getError() );
		}
		$content->checkin();
	}
	$sectionOld =& JTable::getInstance('section');
	$sectionOld->load( $sectionMove );

	$msg = JText::sprintf( 'DESCCATANDITEMSCOPIED', $sectionOld-> name, $title );
	$mainframe->redirect( 'index.php?option=com_sections&scope=content', $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option )
{
	global $mainframe;

	$db	=& JFactory::getDBO();
	$row =& JTable::getInstance('section');
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	$mainframe->redirect( 'index.php?option='. $option .'&scope='. $row->scope );
}

function saveOrder( &$cid )
{
	global $mainframe;

	$db			=& JFactory::getDBO();
	$row		=& JTable::getInstance('section');

	$total		= count( $cid );
	$order		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	JArrayHelper::toInteger($order, array(0));

	// update ordering values
	for( $i=0; $i < $total; $i++ )
	{
		$row->load( (int) $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				JError::raiseError(500, $db->getErrorMsg() );
			}
		}
	}

	$row->reorder( );

	$msg 	= JText::_( 'New ordering saved' );
	$mainframe->redirect( 'index.php?option=com_sections&scope=content', $msg );
}
?>
