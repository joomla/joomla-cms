<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

require_once( $mainframe->getPath( 'admin_html' ) );

$id 	= mosGetParam( $_REQUEST, 'id', '' );
$cid 	= mosGetParam( $_POST, 'cid', array(0) );
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

	case 'remove':
		trash( $cid, $option );
		break;

	case 'publish':
		changeState( $cid, 1, $option );
		break;

	case 'unpublish':
		changeState( $cid, 0, $option );
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
	global $database, $mainframe, $mosConfig_list_limit;

	$filter_authorid 	= $mainframe->getUserStateFromRequest( "filter_authorid{$option}", 'filter_authorid', 0 );
	$order 				= $mainframe->getUserStateFromRequest( "zorder", 'zorder', 'c.ordering DESC' );
	$limit 				= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 		= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 			= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );

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
	require_once( $GLOBALS['mosConfig_absolute_path'] . '/administrator/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT c.*, g.name AS groupname, u.name AS editor, z.name AS creator"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__users AS z ON z.id = c.created_by"
	. "\n WHERE c.sectionid = 0"
	. "\n AND c.catid = 0"
	. "\n AND c.state <> -2"
	. $search_query
	. $filter
	. "\n ORDER BY ". $order
	. "\n LIMIT $pageNav->limitstart,$pageNav->limit"
	;
	$database->setQuery( $query );
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

	$ordering[] = mosHTML::makeOption( 'c.ordering ASC', $_LANG->_( 'Ordering asc' ) );
	$ordering[] = mosHTML::makeOption( 'c.ordering DESC', $_LANG->_( 'Ordering desc' ) );
	$ordering[] = mosHTML::makeOption( 'c.id ASC', $_LANG->_( 'ID asc' ) );
	$ordering[] = mosHTML::makeOption( 'c.id DESC', $_LANG->_( 'ID desc' ) );
	$ordering[] = mosHTML::makeOption( 'c.title ASC', $_LANG->_( 'Title asc' ) );
	$ordering[] = mosHTML::makeOption( 'c.title DESC', $_LANG->_( 'Title desc' ) );
	$ordering[] = mosHTML::makeOption( 'c.created ASC', $_LANG->_( 'Date asc' ) );
	$ordering[] = mosHTML::makeOption( 'c.created DESC', $_LANG->_( 'Date desc' ) );
	$ordering[] = mosHTML::makeOption( 'z.name ASC', $_LANG->_( 'Author asc' ) );
	$ordering[] = mosHTML::makeOption( 'z.name DESC', $_LANG->_( 'Author desc' ) );
	$ordering[] = mosHTML::makeOption( 'c.state ASC', $_LANG->_( 'Published asc' ) );
	$ordering[] = mosHTML::makeOption( 'c.state DESC', $_LANG->_( 'Published desc' ) );
	$ordering[] = mosHTML::makeOption( 'c.access ASC', $_LANG->_( 'Access asc' ) );
	$ordering[] = mosHTML::makeOption( 'c.access DESC', $_LANG->_( 'Access desc' ) );
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['order'] = mosHTML::selectList( $ordering, 'zorder', 'class="inputbox" size="1"'. $javascript, 'value', 'text', $order );

	// get list of Authors for dropdown filter
	$query = "SELECT c.created_by AS value, u.name AS text"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__users AS u ON u.id = c.created_by"
	. "\n WHERE c.sectionid = 0"
	. "\n GROUP BY u.name"
	. "\n ORDER BY u.name"
	;
	$authors[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select Author' ) .' -' );
	$database->setQuery( $query );
	$authors = array_merge( $authors, $database->loadObjectList() );
	$lists['authorid']	= mosHTML::selectList( $authors, 'filter_authorid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_authorid );

	HTML_typedcontent::showContent( $rows, $pageNav, $option, $search, $lists );
}

/**
* Compiles information to add or edit content
* @param database A database connector object
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
*/
function edit( $uid, $option ) {
	global $database, $my, $mainframe;
	global $mosConfig_absolute_path, $mosConfig_live_site;
	global $_LANG;

	$nullDate = $database->getNullDate();
	$row = new mosContent( $database );

	$lists = array();

	if ($uid) {
		// load the row from the db table
		$row->load( $uid );

		// fail if checked out not by 'me'
		if ($row->isCheckedOut( $my->id )) {
			$alert = $_LANG->_( 'The module' ) .' '. $row->title .' '. $_LANG->_( 'DESCBEINGEDITTED' );
			echo "<script>alert('$alert'); document.location.href='index2.php?option=$option'</script>\n";
			exit(0);
		}

		$row->checkout( $my->id );
		if (trim( $row->images )) {
			$row->images = explode( "\n", $row->images );
		} else {
			$row->images = array();
		}
		if (trim( $row->publish_down ) == $nullDate) {
			$row->publish_down = "Never";
		}

		$query = "SELECT name"
		. "\n FROM #__users"
		. "\n WHERE id = $row->created_by"
		;
		$database->setQuery( $query );
		$row->creator = $database->loadResult();

		$query = "SELECT name"
		. "\n FROM #__users"
		. "\n WHERE id = $row->modified_by"
		;
		$database->setQuery( $query );
		$row->modifier = $database->loadResult();

		// get list of links to this item
		$and 	= "\n AND componentid = ". $row->id;
		$menus 	= mosAdminMenus::Links2Menu( 'content_typed', $and );
	} else {
		// initialise values for a new item
		$row->version 		= 0;
		$row->state 		= 1;
		$row->images 		= array();
		$row->publish_up 	= date( "Y-m-d", time() );
		$row->publish_down 	= "Never";
		$row->sectionid 	= 0;
		$row->catid 		= 0;
		$row->creator 		= '';
		$row->modifier 		= '';
		$row->ordering 		= 0;
		$menus = array();
	}

	// calls function to read image from directory
	$pathA 		= $mosConfig_absolute_path .'/images/stories';
	$pathL 		= $mosConfig_live_site .'/images/stories';
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

	// build list of users
	$active = ( intval( $row->created_by ) ? intval( $row->created_by ) : $my->id );
	$lists['created_by'] 	= mosAdminMenus::UserSelect( 'created_by', $active );
	// build the html select list for the group access
	$lists['access'] 		= mosAdminMenus::Access( $row );
	// build the html select list for menu selection
	$lists['menuselect']	= mosAdminMenus::MenuSelect( );
	// build the select list for the image positions
	$lists['_align'] 		= mosAdminMenus::Positions( '_align' );
	// build the select list for the image caption alignment
	$lists['_caption_align'] 	= mosAdminMenus::Positions( '_caption_align' );
	// build the select list for the image caption position
	$pos[] = mosHTML::makeOption( 'bottom', $_LANG->_( 'Bottom' ) );
	$pos[] = mosHTML::makeOption( 'top', $_LANG->_( 'Top' ) );
	$lists['_caption_position'] = mosHTML::selectList( $pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text' );

	// get params definitions
	$params = new mosParameters( $row->attribs, $mainframe->getPath( 'com_xml', 'com_typedcontent' ), 'component' );

	HTML_typedcontent::edit( $row, $images, $lists, $params, $option, $menus );
}

/**
* Saves the typed content item
*/
function save( $option, $task ) {
	global $database, $my;
	global $_LANG;

	$nullDate = $database->getNullDate();
	$menu 		= mosGetParam( $_POST, 'menu', 'mainmenu' );
	$menuid		= mosGetParam( $_POST, 'menuid', 0 );

	$row = new mosContent( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( $row->id ) {
		$row->modified = date( 'Y-m-d H:i:s' );
		$row->modified_by = $my->id;
	} else {
		$row->created = date( 'Y-m-d H:i:s' );
		$row->created_by 	= $row->created_by ? $row->created_by : $my->id;
	}
	if (trim( $row->publish_down ) == 'Never') {
		$row->publish_down = $nullDate;
	}

	// Save Parameters
	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->attribs = implode( "\n", $txt );
	}

	// code cleaner for xhtml transitional compliance
	$row->introtext = str_replace( '<br>', '<br />', $row->introtext );

	$row->state = mosGetParam( $_REQUEST, 'published', 0 );

	$row->title = ampReplace( $row->title );
	
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	switch ( $task ) {
		case 'go2menu':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu );
			break;

		case 'go2menuitem':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&hidemainmenu=1&id='. $menuid );
			break;

		case 'menulink':
			menuLink( $option, $row->id );
			break;

		case 'resethits':
			resethits( $option, $row->id );
			break;

		case 'save':
			$msg = $_LANG->_( 'Typed Content Item saved' );
			mosRedirect( 'index2.php?option='. $option, $msg );
			break;

		case 'apply':
		default:
			$msg = $_LANG->_( 'Changes to Typed Content Item saved' );
			mosRedirect( 'index2.php?option='. $option .'&task=edit&hidemainmenu=1&id='. $row->id, $msg );
			break;
	}
}

/**
* Trashes the typed content item
*/
function trash( &$cid, $option ) {
	global $database;
	global $_LANG;

	$total = count( $cid );
	if ( $total < 1) {
		echo "<script> alert('". $_LANG->_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$state = '-2';
	$ordering = '0';
	//seperate contentids
	$cids = implode( ',', $cid );
	$query = "UPDATE #__content"
	. "\n SET state = $state, ordering = $ordering"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = $total ." ". $_LANG->_( 'Item(s) sent to the Trash' );
	mosRedirect( 'index2.php?option='. $option, $msg );
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
	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $state == 1 ? 'publish' : ($state == -1 ? 'archive' : 'unpublish');
		echo "<script> alert('". $_LANG->_( 'Select an item to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
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
		$row = new mosContent( $database );
		$row->checkin( $cid[0] );
	}

	if ( $state == "1" ) {
		$msg = $total ." ". $_LANG->_( 'Item(s) successfully Published' );
	} else if ( $state == "0" ) {
		$msg = $total ." ". $_LANG->_( 'Item(s) successfully Unpublished' );
	}
	mosRedirect( 'index2.php?option='. $option .'&msg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function changeAccess( $id, $access, $option  ) {
	global $database;

	$row = new mosContent( $database );
	$row->load( $id );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	mosRedirect( 'index2.php?option='. $option );
}


/**
* Function to reset Hit count of a content item
*/
function resethits( $option, $id ) {
	global $database;
	global $_LANG;

	$row = new mosContent($database);
	$row->Load( $id );
	$row->hits = "0";
	$row->store();
	$row->checkin();

	$msg = $_LANG->_( 'Successfully Reset Hit' );
	mosRedirect( 'index2.php?option='. $option .'&task=edit&hidemainmenu=1&id='. $row->id, $msg );
}

/**
* Cancels an edit operation
* @param database A database connector object
*/
function cancel( $option ) {
	global $database;

	$row = new mosContent( $database );
	$row->bind( $_POST );
	$row->checkin();
	mosRedirect( 'index2.php?option='. $option );
}

function menuLink( $option, $id ) {
	global $database;
	global $_LANG;

	$menu 	= mosGetParam( $_POST, 'menuselect', '' );
	$link 	= mosGetParam( $_POST, 'link_name', '' );

	$row 				= new mosMenu( $database );
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
	$row->updateOrder( "menutype='$row->menutype' AND parent='$row->parent'" );

	$msg = $link ." ". $_LANG->_( '(Link - Static Content) in menu' ) .": ". $menu ." ". $_LANG->_( 'successfully created' );
	mosRedirect( 'index2.php?option='. $option .'&task=edit&hidemainmenu=1&id='. $id, $msg );
}

function go2menu() {
	global $database;

	// checkin content
	$row = new mosContent( $database );
	$row->bind( $_POST );
	$row->checkin();

	$menu = mosGetParam( $_POST, 'menu', 'mainmenu' );

	mosRedirect( 'index2.php?option=com_menus&menutype='. $menu );
}

function go2menuitem() {
	global $database;

	// checkin content
	$row = new mosContent( $database );
	$row->bind( $_POST );
	$row->checkin();

	$menu 	= mosGetParam( $_POST, 'menu', 'mainmenu' );
	$id		= mosGetParam( $_POST, 'menuid', 0 );

	mosRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&hidemainmenu=1&id='. $id );
}

function saveOrder( &$cid ) {
	global $database;
	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosContent( $database );
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
		$row->updateOrder( $cond[1] );
	} // foreach

	$msg 	= $_LANG->_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_typedcontent', $msg );
} // saveOrder
?>