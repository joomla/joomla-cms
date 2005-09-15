<?php
/**
* @version $Id: admin.typedcontent.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

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

	case 'toggle_frontpage':
		toggleFrontPage( $cid, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'checkin':
		checkin( $id );
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
	global $database, $mainframe, $mosConfig_list_limit, $_LANG;

	$filter_authorid 	= $mainframe->getUserStateFromRequest( "filter_authorid{$option}", 'filter_authorid', 0 );
	$filter_state	 	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$filter_access	 	= $mainframe->getUserStateFromRequest( "filter_access{$option}", 'filter_access', NULL );
	$limit 				= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 		= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 			= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 			= trim( strtolower( $search ) );
	$tOrder				= mosGetParam( $_POST, 'tOrder', 'c.ordering' );
	$tOrder_old			= mosGetParam( $_POST, 'tOrder_old', 'c.ordering' );

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'published' ) ) {
		$tOrderDir = 'ASC';
	} else {
		$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'DESC' );
	}
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] 		= $tOrder;

	// used by filter
	if ( $search ) {
		$search_query = "\n AND ( LOWER( c.title ) LIKE '%$search%' OR LOWER( c.title_alias ) LIKE '%$search%' )";
	} else {
		$search_query = '';
	}
	$filter = array();
	if ( $filter_authorid > 0 ) {
		$filter[] = "\n AND c.created_by = '$filter_authorid'";
	}
	if ( $filter_state <> NULL ) {
		$filter[] = "\n AND c.state = '$filter_state'";
	}
	if ( $filter_access <> NULL ) {
		$filter[] = "\n AND c.access = '$filter_access'";
	}
	$filter = implode( '', $filter );

	// table column ordering
	$order = "\n ORDER BY $tOrder $tOrderDir, c.ordering DESC";

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

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	$query = "SELECT c.*, g.name AS groupname, u.name AS editor, z.name AS creator, f.content_id AS frontpage"
	. "\n FROM #__content AS c"
	. "\n LEFT JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__users AS z ON z.id = c.created_by"
	. "\n LEFT JOIN #__content_frontpage AS f ON f.content_id = c.id"
	. "\n WHERE c.sectionid = '0'"
	. "\n AND c.catid = '0'"
	. "\n AND c.state <> '-2'"
	. $search_query
	. $filter
	. $order
	;
	$database->setQuery( $query , $pageNav->limitstart, $pageNav->limit);
	$rows = $database->loadObjectList();

	if ($database->getErrorNum()) {
		mosErrorAlert( $database->stderr() );
	}

	$count = count( $rows );
	for( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( id )"
		. "\n FROM #__menu"
		. "\n WHERE componentid = ". $rows[$i]->id
		. "\n AND type = 'content_typed'"
		. "\n AND published <> '-2'"
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
	$authors[] = mosHTML::makeOption( '0', '- ' . $_LANG->_( 'Author' ) . ' -' );
	$database->setQuery( $query );
	$authors = array_merge( $authors, $database->loadObjectList() );
	$lists['authorid']	= mosHTML::selectList( $authors, 'filter_authorid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_authorid );

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	// get list of Access for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['access']	= mosAdminHTML::accessList( 'filter_access', $filter_access, $javascript );

	$search = stripslashes( $search );

	mosFS::load( '@class', 'com_content' );
	mosContentFactory::contenttreeQueries( $lists );

	HTML_typedcontent::showContent( $rows, $pageNav, $option, $search, $lists );
}

/**
* Compiles information to add or edit content
* @param database A database connector object
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
*/
function edit( $uid, $option ) {
	global $database, $my, $mainframe, $task;
	global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_zero_date;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	if ( !$uid && $task <> 'new' ) {
		mosErrorAlert( $_LANG->_( 'Select an item to Edit' ) );
	}

	mosFS::load( '@class', 'com_content' );

	$row = new mosContent( $database );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut()) {
		mosErrorAlert( $_LANG->_( 'The module' ) .' '. $row->title .' '. $_LANG->_( 'descBeingEditted' ) );
	}

	$lists = array();

	if ($uid) {
		// load the row from the db table
		$row->load( $uid );
		// checkout
		$row->checkout( $my->id );

		if (trim( $row->images )) {
			$row->images = htmlentities( $row->images );
			$row->images = explode( "\n", $row->images );
		} else {
			$row->images = array();
		}
		if (trim( $row->publish_down ) == $mosConfig_zero_date) {
			$row->publish_down = 'Never';
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

		$query = "SELECT content_id"
		. "\n FROM #__content_frontpage"
		. "\n WHERE content_id = $row->id"
		;
		$database->setQuery( $query );
		$row->frontpage = $database->loadResult();

		// get list of links to this item
		$and 	= "\n AND componentid = ". $row->id;
		$menus 	= mosContentFactory::buildLinksToMenu( 'content_typed', $and );
	} else {
		// initialise values for a new item
		$row->version 		= 0;
		$row->state 		= 1;
		$row->images 		= array();
		$row->publish_up 	= $mainframe->getDateTime();
		$row->publish_down 	= "Never";
		$row->sectionid 	= 0;
		$row->catid 		= 0;
		$row->creator 		= '';
		$row->modifier 		= '';
		$row->ordering 		= 0;
		$row->frontpage 	= 0;
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
	$lists['created_by'] 	= mosAdminHTML::userSelect( 'created_by', $active );
	// build the select list for the image positions
	$lists['_align'] 		= mosAdminMenus::Positions( '_align' );
	// build the html select list for the group access
	$lists['access'] 		= mosAdminMenus::Access( $row );
	// build the html select list for menu selection
	$lists['menuselect']	= mosContentFactory::buildMenuSelect();
	// build the select list for the image caption alignment
	$lists['_caption_align'] 	= mosAdminMenus::Positions( '_caption_align' );

	// build the select list for the image caption position
	$pos[] = mosHTML::makeOption( 'bottom', $_LANG->_( 'Bottom' ) );
	$pos[] = mosHTML::makeOption( 'top', $_LANG->_( 'Top' ) );
	$lists['_caption_position'] = mosHTML::selectList( $pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text' );

	// build the select list for the link target
	$target[] = mosHTML::makeOption( '_blank', $_LANG->_( 'New Window' ) );
	$target[] = mosHTML::makeOption( '_self', $_LANG->_( 'Parent Window' ) );
	$lists['_link_target'] = mosHTML::selectList( $target, '_link_target', 'class="inputbox" size="1"', 'value', 'text' );

	// make the select list for the frontpage
	$row->frontpage = ( $row->frontpage ? 1 : 0 );
	$lists['frontpage'] 	= mosHTML::yesnoRadioList( 'frontpage', 'class="inputbox" size="1"', $row->frontpage );

	// make the select list for the states
	$lists['state'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox" size="1"', intval( $row->state ) );

	// get params definitions
	$params = new mosParameters( $row->attribs, $mainframe->getPath( 'com_xml', 'com_typedcontent' ), 'component' );

	HTML_typedcontent::edit( $row, $images, $lists, $params, $option, $menus );
}

/**
* Saves the typed content item
*/
function save( $option, $task ) {
	global $database, $my, $mainframe;
	global $_LANG, $mosConfig_zero_date;

	$menu 		= mosGetParam( $_POST, 'menu', 'mainmenu' );
	$menuid		= mosGetParam( $_POST, 'menuid', 0 );

	$row = new mosContent( $database );
	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

	if ( $row->id ) {
		$row->modified 		= $mainframe->getDateTime();
		$row->modified_by 	= $my->id;
	} else {
		$row->created 		= $mainframe->getDateTime();
		$row->created_by 	= $my->id;
	}
	if (trim( $row->publish_down ) == 'Never') {
		$row->publish_down = $mosConfig_zero_date;
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

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();

	// manage frontpage items
	require_once( $mainframe->getPath( 'class', 'com_frontpage' ) );
	$fp = new mosFrontPage( $database );

	if (mosGetParam( $_REQUEST, 'frontpage', 0 )) {

		// toggles go to first place
		if (!$fp->load( $row->id )) {
			// new entry
			$query = "INSERT INTO #__content_frontpage"
			. "\n VALUES ('$row->id','1')"
			;
			$database->setQuery( $query );
			if (!$database->query()) {
				mosErrorAlert( $database->stderr() );
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
	$fp->updateOrder();

	$msg = $_LANG->_( 'Changes to Typed Content Item saved' );
	switch ( $task ) {
		case 'go2menu':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu, $msg );
			break;

		case 'go2menuitem':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&id='. $menuid, $msg );
			break;

		case 'menulink':
			menuLink( $option, $row->id );
			break;

		case 'resethits':
			resethits( $option, $row->id );
			break;

		case 'save':
			$msg 		= $_LANG->_( 'Typed Content Item saved' );

			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer ) {
				mosRedirect( $referer, $msg );
			} else {
				mosRedirect( 'index2.php?option='. $option, $msg );
			}
			break;

		case 'apply':
		default:
			mosRedirect( 'index2.php?option='. $option .'&task=edit&id='. $row->id, $msg );
			break;
	}
}

/**
* Trashes the typed content item
*/
function trash( &$cid, $option ) {
	global $database, $mainframe;
	global $_LANG;

	$total = count( $cid );
	if ( $total == 0 ) {
		mosErrorAlert( $_LANG->_( 'Select an item to delete' ) );
	}

	$state = '-2';
	$ordering = '0';
	//seperate contentids
	$cids = implode( ',', $cid );
	$query = "UPDATE #__content"
	. "\n SET state = '$state', ordering = '$ordering'"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	$msg = $total .' '. $_LANG->_( 'Item(s) sent to the Trash' );
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
		mosErrorAlert( $_LANG->_( 'Select an item to' ) .' '. $action );
	}

	$total = count ( $cid );
	$cids = implode( ',', $cid );

	$query = "UPDATE #__content"
	. "\n SET state = '$state'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out=0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosContent( $database );
		$row->checkin( $cid[0] );
	}

	if ( $state == 1 ) {
		$msg = $total .' '. $_LANG->_( 'Item(s) successfully Published' );
	} else if ( $state == 0 ) {
		$msg = $total .' '. $_LANG->_( 'Item(s) successfully Unpublished' );
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
		mosErrorAlert( $row->getError() );
	}
	if ( !$row->store() ) {
		mosErrorAlert( $row->getError() );
	}

	mosRedirect( 'index2.php?option='. $option );
}


/**
*/
function toggleFrontPage( $cid, $option ) {
	global $database, $my, $mainframe;
	global $_LANG;

	if (count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to toggle' ) );
	}

	$msg = '';
	require_once( $mainframe->getPath( 'class', 'com_frontpage' ) );

	$fp = new mosFrontPage( $database );
	foreach ( $cid as $id ) {
		// toggles go to first place
		if ( $fp->load( $id ) ) {
			if (!$fp->delete( $id )) {
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		} else {
			// new entry
			$query = "INSERT INTO #__content_frontpage VALUES ( '$id', '0' )";
			$database->setQuery( $query );
			if (!$database->query()) {
				mosErrorAlert( $database->stderr() );
			}
			$fp->ordering = 0;
		}
		$fp->updateOrder();
	}

	mosRedirect( 'index2.php?option='. $option, $msg );
}

/**
* Function to reset Hit count of a content item
*/
function resethits( $option, $id ) {
	global $database;
	global $_LANG;

	$row = new mosContent($database);
	$row->Load( $id );
	$row->hits = 0;
	$row->store();
	$row->checkin();

	$msg = $_LANG->_( 'Successfully Reset Hit' );
	mosRedirect( 'index2.php?option='. $option .'&task=edit&id='. $row->id, $msg );
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
		mosErrorAlert( $row->getError() );
	}
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	$row->updateOrder( "menutype='$row->menutype' AND parent='$row->parent'" );

	$msg = $link .' '. $_LANG->_( '(Link - Static Content) in menu:' ) .' '. $menu .' '. $_LANG->_( 'successfully created' );
	mosRedirect( 'index2.php?option='. $option .'&task=edit&id='. $id, $msg );
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
		   		mosErrorAlert( $database->getErrorMsg() );
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

function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosContent( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_typedcontent', $msg );
}
?>