<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
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

require_once( JApplicationHelper::getPath( 'admin_html' ) );

$id 	= JRequest::getVar( 'id', '', '', 'int' );
$cid 	= JRequest::getVar( 'cid', array(0), 'post' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ( $task ) {
	case 'cancel':
		cancel( $option );
		break;

	case 'new':
		edit( 0, $option );
		break;

	case 'edit':
		edit( $id, $option );
		break;

	case 'editA':
		edit( $cid[0], $option );
		break;

	case 'go2menu':
	case 'go2menuitem':
	case 'resethits':
	case 'menulink':
	case 'save':
	case 'apply':
		save( $option, $task );
		break;

	case 'move':
		move( $cid );
		break;

	case 'movesave':
		moveSave( $cid );
		break;

	case 'copy':
		copyItem( $cid );
		break;

	case 'remove':
		trash( $cid, $option );
		break;

	case 'publish':
		changeState( $cid, 1, $option );
		break;

	case 'unpublish':
		changeState( $cid, 0, $option );
		break;

	case 'toggle_frontpage':
		toggleFrontPage( $cid );
		break;

	case 'accesspublic':
		changeAccess( $cid[0], 0, $option );
		break;

	case 'accessregistered':
		changeAccess( $cid[0], 1, $option );
		break;

	case 'accessspecial':
		changeAccess( $cid[0], 2, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	default:
		view( $option );
		break;
}

/**
* Compiles a list of installed or defined modules
* @param database A database connector object
*/
function view( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'c.ordering' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$filter_authorid 	= $mainframe->getUserStateFromRequest( "$option.filter_authorid", 	'filter_authorid', 	0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( JString::strtolower( $search ) ) );

	// used by filter
	if ( $search ) {
		$search_query = "\n AND ( LOWER( c.title ) LIKE '%$search%' OR LOWER( c.title_alias ) LIKE '%$search%' )";
	} else {
		$search_query = '';
	}

	$filter = '';
	if ( $filter_authorid > 0 ) {
		$filter = "\n AND c.created_by = '$filter_authorid'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$filter .= "\n AND c.state = 1";
		} else if ($filter_state == 'U' ) {
			$filter .= "\n AND c.state = 0";
		}
	}

	$orderby = "\n ORDER BY $filter_order $filter_order_Dir";

	// get the total number of records
	$query = "SELECT count(*)"
	. "\n FROM #__content AS c"
	. "\n WHERE c.sectionid = '0'"
	. "\n AND c.catid = '0'"
	. "\n AND c.state <> '-2'"
	. $filter
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT c.*, g.name AS groupname, u.name AS editor, z.name AS creator, f.content_id AS frontpage"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__users AS z ON z.id = c.created_by"
	. "\n LEFT JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. "\n WHERE c.sectionid = 0"
	. "\n AND c.catid = 0"
	. "\n AND c.state <> -2"
	. $search_query
	. $filter
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	$count = count( $rows );
	for( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( id )"
		. "\n FROM #__menu"
		. "\n WHERE componentid = ". $rows[$i]->id
		. "\n AND type = 'content_typed'"
		. "\n AND published <> -2"
		;
		$database->setQuery( $query );
		$rows[$i]->links = $database->loadResult();
	}

	// get list of Authors for dropdown filter
	$query = "SELECT c.created_by AS value, u.name AS text"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__users AS u ON u.id = c.created_by"
	. "\n WHERE c.sectionid = 0"
	. "\n GROUP BY u.name"
	. "\n ORDER BY u.name"
	;
	$authors[] = mosHTML::makeOption( '0', '- '. JText::_( 'Select Author' ) .' -' );
	$database->setQuery( $query );
	$authors = array_merge( $authors, $database->loadObjectList() );
	$lists['authorid']	= mosHTML::selectList( $authors, 'filter_authorid', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter_authorid );

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

	HTML_typedcontent::showContent( $rows, $pageNav, $option, $lists );
}

/**
* Compiles information to add or edit content
* @param database A database connector object
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
*/
function edit( $uid, $option ) {
	global $database, $my, $mainframe;

	$row =& JTable::getInstance('content', $database );

	$row->load( $uid );

	$nullDate 	= $database->getNullDate();
	$lists 		= array();

	if ($uid) {
		// fail if checked out not by 'me'
		if ($row->isCheckedOut( $my->id )) {
        	$alert = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
			$action = "document.location.href='index2.php?option=$option'";
			mosErrorAlert( $alert, $action );
		}

		$row->checkout( $my->id );

		if (trim( $row->images )) {
			$row->images = explode( "\n", $row->images );
		} else {
			$row->images = array();
		}

		$row->created 		= mosFormatDate( $row->created, '%Y-%m-%d %H:%M:%S' );
		$row->modified 		= $row->modified == $nullDate ? '' : mosFormatDate( $row->modified, '%Y-%m-%d %H:%M:%S' );
		$row->publish_up 	= mosFormatDate( $row->publish_up, '%Y-%m-%d %H:%M:%S' );
		$row->publish_down	= mosFormatDate( $row->publish_down, '%Y-%m-%d %H:%M:%S' );

		if (trim( $row->publish_down ) == $nullDate) {
			$row->publish_down = JText::_( 'Never' );
		}

		$query = "SELECT name"
		. "\n FROM #__users"
		. "\n WHERE id = $row->created_by"
		;
		$database->setQuery( $query );
		$row->creator = $database->loadResult();

		// test to reduce unneeded query
		if ( $row->created_by == $row->modified_by ) {
			$row->modifier = $row->creator;
		} else {
			$query = "SELECT name"
			. "\n FROM #__users"
			. "\n WHERE id = $row->modified_by"
			;
			$database->setQuery( $query );
			$row->modifier = $database->loadResult();
		}

		$query = "SELECT COUNT(content_id)"
		. "\n FROM #__content_frontpage"
		. "\n WHERE content_id = $row->id"
		;
		$database->setQuery( $query );
		$row->frontpage = $database->loadResult();
		if (!$row->frontpage) {
			$row->frontpage = 0;
		}

		// get list of links to this item
		$and 	= "\n AND componentid = ". $row->id;
		$menus 	= mosAdminMenus::Links2Menu( 'content_typed', $and );
	} else {
		// initialise values for a new item
		$row->version 		= 0;
		$row->state 		= 1;
		$row->images 		= array();
		$row->publish_up 	= date( 'Y-m-d', time() + $mainframe->getCfg('offset') * 60 * 60 );
		$row->publish_down 	= JText::_( 'Never' );
		$row->sectionid 	= 0;
		$row->catid 		= 0;
		$row->creator 		= '';
		$row->modifier 		= '';
		$row->modified 		= $nullDate;
		$row->ordering 		= 0;
		$row->frontpage 	= 0;
		$menus = array();
	}

	// calls function to read image from directory
	$pathA 		= JPATH_SITE .'/images/stories';
	$pathL 		= $mainframe->getSiteURL() .'/images/stories';
	$images 	= array();
	$folders 	= array();
	$folders[] 	= mosHTML::makeOption( '/' );
	mosAdminMenus::ReadImages( $pathA, '/', $folders, $images );
	// list of folders in images/stories/
	$lists['folders'] 		= mosAdminMenus::GetImageFolders( $folders, $pathL );
	// list of images in specfic folder in images/stories/
	$lists['imagefiles']	= mosAdminMenus::GetImages( $images, $pathL );
	// list of saved images
	$lists['imagelist'] 	= mosAdminMenus::GetSavedImages( $row, $pathL );

	// build the html radio buttons for frontpage
	$lists['frontpage']		= mosHTML::yesnoradioList( 'frontpage', '', $row->frontpage );
	// build the html radio buttons for published
	$lists['state'] 		= mosHTML::yesnoradioList( 'state', '', $row->state );
	// build list of users
	$active = ( intval( $row->created_by ) ? intval( $row->created_by ) : $my->id );
	$lists['created_by'] 	= mosAdminMenus::UserSelect( 'created_by', $active );
	// build the html select list for the group access
	$lists['access'] 		= mosAdminMenus::Access( $row );
	// build the html select list for menu selection
	$lists['menuselect']	= mosAdminMenus::MenuSelect( );
	// build the select list for the image positions
	$lists['_align'] 		= mosAdminMenus::Positions( '_align', '', '', 1, 1, 1, 1, 'Ialign' );
	// build the select list for the image caption alignment
	$lists['_caption_align'] 	= mosAdminMenus::Positions( '_caption_align', '', '', 1, 1, 1, 1, 'Icaption_align' );
	// build the select list for the image caption position
	$pos[] = mosHTML::makeOption( 'bottom', JText::_( 'Bottom' ) );
	$pos[] = mosHTML::makeOption( 'top', 	JText::_( 'Top' ) );
	$lists['_caption_position'] = mosHTML::selectList( $pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text', '', 'Icaption_position' );

	// get params definitions
	$params = new JParameter( $row->attribs, JApplicationHelper::getPath( 'com_xml', 'com_typedcontent' ), 'component' );

	HTML_typedcontent::edit( $row, $images, $lists, $params, $option, $menus );
}

/**
* Saves the typed content item
*/
function save( $option, $task )
{
	global $mainframe, $database, $my;

	$nullDate 	= $database->getNullDate();
	$menu 		= JRequest::getVar( 'menu', 'mainmenu', 'post' );
	$menuid		= JRequest::getVar( 'menuid', 0, 'post', 'int' );

	$row =& JTable::getInstance('content', $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ($row->id) {
		$row->modified 		= date( 'Y-m-d H:i:s' );
		$row->modified_by 	= $my->id;
		$row->created 		= $row->created ? mosFormatDate( $row->created, '%Y-%m-%d %H:%M:%S', -$mainframe->getCfg('offset')) : date( 'Y-m-d H:i:s' );
		$row->created_by 	= $row->created_by ? $row->created_by : $my->id;
	} else {
		$row->created 		= $row->created ? mosFormatDate( $row->created, '%Y-%m-%d %H:%M:%S', -$mainframe->getCfg('offset') ) : date( 'Y-m-d H:i:s' );
		$row->created_by 	= $row->created_by ? $row->created_by : $my->id;
	}

	if (strlen(trim( $row->publish_up )) <= 10) {
		$row->publish_up .= ' 00:00:00';
	}
	$row->publish_up 	= mosFormatDate($row->publish_up, '%Y-%m-%d %H:%M:%S', -$mainframe->getCfg('offset') );

	if (trim( $row->publish_down ) == JText::_( 'Never' )) {
		$row->publish_down = $nullDate;
	} else {
		$row->publish_down 	= mosFormatDate($row->publish_down, '%Y-%m-%d %H:%M:%S', -$mainframe->getCfg('offset') );
	}

	$row->state = JRequest::getVar( 'state', 0 );

	// Save Parameters
	$params = JRequest::getVar( 'params', '', 'post' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->attribs = implode( "\n", $txt );
	}

	// code cleaner for xhtml transitional compliance
	$row->introtext = str_replace( '<br>', '<br />', $row->introtext );

	$row->title = ampReplace( $row->title );

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// manage frontpage items
	require_once( JApplicationHelper::getPath( 'class', 'com_frontpage' ) );
	$fp = new JTableFrontPage( $database );

	$frontpage = JRequest::getVar( 'frontpage', 0, 'post' );
	if ($frontpage) {
		// toggles go to first place
		if (!$fp->load( $row->id )) {
			// new entry
			$query = "INSERT INTO #__content_frontpage"
			. "\n VALUES ( $row->id, 1 )"
			;
			$database->setQuery( $query );
			if (!$database->query()) {
				echo "<script> alert('".$database->stderr()."');</script>\n";
				exit();
			}
			$fp->ordering = 1;
		}
	} else {
		// no frontpage mask
		if (!$fp->delete( $row->id )) {
			$msg .= $fp->stderr();
		}
		$fp->ordering = 0;
	}
	$fp->reorder();
	$msg = $frontpage;

	$row->checkin();
	$row->reorder( "state >= 0" );

	switch ( $task ) {
		case 'go2menu':
			josRedirect( 'index2.php?option=com_menus&menutype='. $menu );
			break;

		case 'go2menuitem':
			josRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&hidemainmenu=1&id='. $menuid );
			break;

		case 'menulink':
			menuLink( $option, $row->id );
			break;

		case 'resethits':
			resethits( $option, $row->id );
			break;

		case 'save':
			$msg = JText::_( 'Typed Content Item saved' );
			josRedirect( 'index2.php?option='. $option, $msg );
			break;

		case 'apply':
		default:
			$msg = JText::_( 'Changes to Typed Content Item saved' );
			josRedirect( 'index2.php?option='. $option .'&task=edit&hidemainmenu=1&id='. $row->id, $msg );
			break;
	}
}

/**
* Form for moving item(s) to a different section and category
*/
function move( &$cid ) {
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to move', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	//seperate contentids
	$cids = implode( ',', $cid );
	// Content Items query
	$query = "SELECT a.title"
	. "\n FROM #__content AS a"
	. "\n WHERE ( a.id IN ( $cids ) )"
	. "\n ORDER BY a.title"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	$database->setQuery(
	$query = 	"SELECT CONCAT_WS( ', ', s.id, c.id ) AS `value`, CONCAT_WS( '/', s.name, c.name ) AS `text`"
	. "\n FROM #__sections AS s"
	. "\n INNER JOIN #__categories AS c ON c.section = s.id"
	. "\n WHERE s.scope = 'content'"
	. "\n ORDER BY s.name, c.name"
	);
	$rows = $database->loadObjectList();
	// build the html select list
	$sectCatList = mosHTML::selectList( $rows, 'sectcat', 'class="inputbox" size="8"', 'value', 'text', null );

	HTML_typedcontent::move( $cid, $sectCatList, $items );
}

/**
* Save the changes to move item(s) to a different section and category
*/
function moveSave( &$cid ) {
	global $database, $my;

	$sectcat = JRequest::getVar( 'sectcat', '', 'post' );
	list( $newsect, $newcat ) = explode( ',', $sectcat );

	if (!$newsect && !$newcat ) {
		josRedirect( "index.php?option=com_content&sectionid=0&josmsg=". JText::_( 'An error has occurred' ) );
	}

	// find section name
	$query = "SELECT a.name"
	. "\n FROM #__sections AS a"
	. "\n WHERE a.id = $newsect"
	;
	$database->setQuery( $query );
	$section = $database->loadResult();

	// find category name
	$query = "SELECT  a.name"
	. "\n FROM #__categories AS a"
	. "\n WHERE a.id = $newcat"
	;
	$database->setQuery( $query );
	$category = $database->loadResult();

	$total = count( $cid );
	$cids = implode( ',', $cid );

	$row =& JTable::getInstance('content', $database );
	// update old orders - put existing items in last place
	foreach ($cid as $id) {
		$row->load( intval( $id ) );
		$row->ordering = 0;
		$row->store();
		$row->reorder( "catid = $row->catid AND state >= 0" );
	}

	$query = "UPDATE #__content SET sectionid = $newsect, catid = $newcat"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// update new orders - put items in last place
	foreach ($cid as $id) {
		$row->load( intval( $id ) );
		$row->ordering = 0;
		$row->store();
		$row->reorder( "catid = $row->catid AND state >= 0" );
	}

	$msg = sprintf( JText::_( 'Item(s) successfully moved to Section' ), $total, $section, $category );
	josRedirect( 'index2.php?option=com_typedcontent', $msg );
}

/**
* saves Copies of items
**/
function copyItem( $cid ) {
	global $database;

	$total = count( $cid );
	for ( $i = 0; $i < $total; $i++ ) {
		$row =& JTable::getInstance('content', $database );

		// main query
		$query = "SELECT a.*"
		. "\n FROM #__content AS a"
		. "\n WHERE a.id = ". $cid[$i] .""
		;
		$database->setQuery( $query );
		$item = $database->loadObjectList();

		// values loaded into array set for store
		$row->id 				= NULL;
		$row->sectionid 		= $newsect;
		$row->catid 			= $newcat;
		$row->hits 				= '0';
		$row->ordering			= '0';
		$row->title 			= $item[0]->title;
		$row->title_alias 		= $item[0]->title_alias;
		$row->introtext 		= $item[0]->introtext;
		$row->fulltext 			= $item[0]->fulltext;
		$row->state 			= $item[0]->state;
		$row->mask 				= $item[0]->mask;
		$row->created 			= $item[0]->created;
		$row->created_by 		= $item[0]->created_by;
		$row->created_by_alias 	= $item[0]->created_by_alias;
		$row->modified 			= $item[0]->modified;
		$row->modified_by 		= $item[0]->modified_by;
		$row->checked_out 		= $item[0]->checked_out;
		$row->checked_out_time 	= $item[0]->checked_out_time;
		$row->publish_up 		= $item[0]->publish_up;
		$row->publish_down 		= $item[0]->publish_down;
		$row->images 			= $item[0]->images;
		$row->attribs 			= $item[0]->attribs;
		$row->version 			= $item[0]->parentid;
		$row->parentid 			= $item[0]->parentid;
		$row->metakey 			= $item[0]->metakey;
		$row->metadesc 			= $item[0]->metadesc;
		$row->access 			= $item[0]->access;

		if (!$row->check()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		if (!$row->store()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$row->reorder( "catid='". $row->catid ."' AND state >= 0" );
	}

	$msg = JText::_( 'Item(s) successfully copied' );
	josRedirect( 'index2.php?option=com_typedcontent', $msg );
}


/**
* Changes the state of one or more content pages
* @param string The name of the category section
* @param integer A unique category id (passed from an edit form)
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The name of the current user
*/
function toggleFrontPage( $cid ) {
	global $database;

	if (count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to toggle' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$msg = '';
	require_once( JApplicationHelper::getPath( 'class', 'com_frontpage' ) );

	$fp = new JTableFrontPage( $database );
	foreach ($cid as $id) {
		// toggles go to first place
		if ($fp->load( $id )) {
			if (!$fp->delete( $id )) {
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		} else {
			// new entry
			$query = "INSERT INTO #__content_frontpage"
			. "\n VALUES ( $id, 0 )"
			;
			$database->setQuery( $query );
			if (!$database->query()) {
				echo "<script> alert('".$database->stderr()."');</script>\n";
				exit();
			}
			$fp->ordering = 0;
		}
		$fp->reorder();
	}

	josRedirect( 'index2.php?option=com_typedcontent', $msg );
}

/**
* Trashes the typed content item
*/
function trash( &$cid, $option ) {
	global $database;

	$total = count( $cid );
	if ( $total < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$nullDate 	= $database->getNullDate();
	$state 		= -2;
	$ordering 	= 0;
	//seperate contentids
	$cids = implode( ',', $cid );
	$query = "UPDATE #__content"
	. "\n SET state = $state, ordering = $ordering, checked_out = 0, checked_out_time = '$nullDate'"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = sprintf( JText::_( 'Item(s) sent to the Trash' ), $total );
	josRedirect( 'index2.php?option='. $option, $msg );
}

/**
* Changes the state of one or more content pages
* @param string The name of the category section
* @param integer A unique category id (passed from an edit form)
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The name of the current user
*/
function changeState( $cid=null, $state=0, $option ) {
	global $database, $my;

	if (count( $cid ) < 1) {
		$action = $state == 1 ? 'publish' : ($state == -1 ? 'archive' : 'unpublish');
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$total 	= count ( $cid );
	$cids 	= implode( ',', $cid );

	$query = "UPDATE #__content"
	. "\n SET state = $state"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('content', $database );
		$row->checkin( $cid[0] );
	}

	if ( $state == "1" ) {
    	$msg = sprintf( JText::_( 'Item(s) successfully Published' ), $total );
	} else if ( $state == "0" ) {
    	$msg = sprintf( JText::_( 'Item(s) successfully Unpublished' ), $total );
	}
	josRedirect( 'index2.php?option='. $option .'&msg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function changeAccess( $id, $access, $option  ) {
	global $database;

	$row =& JTable::getInstance('content', $database );
	$row->load( $id );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	josRedirect( 'index2.php?option='. $option );
}


/**
* Function to reset Hit count of a content item
*/
function resethits( $option, $id ) {
	global $database;

	$row =& JTable::getInstance('content', $database );
	$row->Load( $id );
	$row->hits = "0";
	$row->store();
	$row->checkin();

	$msg = JText::_( 'Successfully Reset Hit' );
	josRedirect( 'index2.php?option='. $option .'&task=edit&hidemainmenu=1&id='. $row->id, $msg );
}

/**
* Cancels an edit operation
* @param database A database connector object
*/
function cancel( $option ) {
	global $database;

	$row =& JTable::getInstance('content', $database );
	$row->bind( $_POST );
	$row->checkin();
	josRedirect( 'index2.php?option='. $option );
}

function menuLink( $option, $id ) {
	global $database;

	$menu 	= JRequest::getVar( 'menuselect', '', 'post' );
	$link 	= JRequest::getVar( 'link_name', '', 'post' );

	$link	= stripslashes( ampReplace($link) );

	$row 				=& JTable::getInstance( 'menu', $database );
	$row->menutype 		= $menu;
	$row->name 			= $link;
	$row->type 			= 'content_typed';
	$row->published		= 1;
	$row->componentid	= $id;
	$row->link			= 'index.php?option=com_content&task=view&id='. $id;
	$row->ordering		= 9999;

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->reorder( "menutype='$row->menutype' AND parent='$row->parent'" );

	$msg = sprintf( JText::_( '(Link - Static Content) in menu' ), $link, $menu );
	josRedirect( 'index2.php?option='. $option .'&task=edit&hidemainmenu=1&id='. $id, $msg );
}

function go2menu() {
	global $database;

	// checkin content
	$row =& JTable::getInstance('content', $database );
	$row->bind( $_POST );
	$row->checkin();

	$menu = JRequest::getVar( 'menu', 'mainmenu', 'post' );

	josRedirect( 'index2.php?option=com_menus&menutype='. $menu );
}

function go2menuitem() {
	global $database;

	// checkin content
	$row =& JTable::getInstance('content', $database );
	$row->bind( $_POST );
	$row->checkin();

	$menu 	= JRequest::getVar( 'menu', 'mainmenu', 'post' );
	$id		= JRequest::getVar( 'menuid', 0, 'post', 'int' );

	josRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&hidemainmenu=1&id='. $id );
}

function saveOrder( &$cid ) {
	global $database;

	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$row 		=& JTable::getInstance('content', $database );
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
			$condition = "catid='$row->catid' AND state >= 0";
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
	josRedirect( 'index2.php?option=com_typedcontent', $msg );
} // saveOrder
?>