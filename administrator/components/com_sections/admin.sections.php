<?php
/**
* @version $Id: admin.sections.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Sections
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

// get parameters from the URL or submitted form
$scope 		= mosGetParam( $_REQUEST, 'scope', '' );
$section 	= mosGetParam( $_REQUEST, 'section', '' );

switch ( $task ) {
	case 'new':
		editSection( 0, $scope, $option );
		break;

	case 'edit':
		editSection( $cid[0], $scope, $option );
		break;

	case 'editA':
		editSection( $id, $scope, $option );
		break;

	case 'go2menu':
	case 'go2menuitem':
	case 'menulink':
	case 'save':
	case 'apply':
		saveSection( $option, $scope, $task );
		break;

	case 'remove':
		removeSections( $cid, $scope );
		break;

	case 'copyselect':
		copySectionSelect( $option, $cid, $section );
		break;

	case 'copysave':
		copySectionSave( $cid );
		break;

	case 'publish':
		publishSections( $scope, $cid, 1 );
		break;

	case 'unpublish':
		publishSections( $scope, $cid, 0 );
		break;

	case 'cancel':
		cancelSection( $scope );
		break;

	case 'orderup':
		orderSection( $cid[0], -1, $scope );
		break;

	case 'orderdown':
		orderSection( $cid[0], 1, $scope );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0 );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1 );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2 );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'checkin':
		checkin( $id );
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
function showSections( $scope, $option ) {
	global $database, $my, $mainframe, $mosConfig_list_limit;

	$filter_state	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$filter_access	= $mainframe->getUserStateFromRequest( "filter_access{$option}", 'filter_access', NULL );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'ordering' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'ordering' );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'published' ) ) {
		$tOrderDir = 'ASC';
	} else {
		$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'ASC' );
	}
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] 		= $tOrder;

	// used by filter
	$filter = array();
	if ( $search ) {
		$filter[] = "\n AND ( LOWER( c.title ) LIKE '%$search%' OR LOWER( c.name ) LIKE '%$search%' )";
	}
	if ( $filter_state <> NULL ) {
		$filter[] = "\n AND c.published = '$filter_state'";
	}
	if ( $filter_access <> NULL ) {
		$filter[] = "\n AND c.access = '$filter_access'";
	}
	$filter = implode( '', $filter );

	// table column ordering
	switch ( $tOrder ) {
		case 'published':
			$order = "\n ORDER BY c.$tOrder $tOrderDir, c.ordering ASC, c.name ASC";
			break;

		default:
			$order = "\n ORDER BY c.$tOrder $tOrderDir, c.name ASC";
			break;
	}

	// get the total number of records
	$query = "SELECT COUNT( * )"
	. "\n FROM #__sections AS c"
	. "\n WHERE c.scope = '$scope'"
	. $filter
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	$query = "SELECT c.*, g.name AS groupname, u.name AS editor"
	. "\n FROM #__sections AS c"
	. "\n LEFT JOIN #__content AS cc ON c.id = cc.sectionid"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = c.access"
	. "\n WHERE c.scope = '$scope'"
	. $filter
	. "\n GROUP BY c.id"
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		mosErrorAlert( $database->stderr() );
	}

	$count = count( $rows );
	// number of Categories
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__categories AS a"
		. "\n WHERE a.section = ". $rows[$i]->id
		. "\n AND a.published = '1'"
		;
		$database->setQuery( $query );
		$cats = $database->loadResult();
		$rows[$i]->categories = ( $cats ? $cats : '-' );
	}
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.sectionid = ". $rows[$i]->id
		. "\n AND a.state != '-2'"
		;
		$database->setQuery( $query );
		$active = $database->loadResult();
		$rows[$i]->active = ( $active ? $active : '-' );
	}
	// number of Trashed Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.sectionid = ". $rows[$i]->id
		. "\n AND a.state = '-2'"
		;
		$database->setQuery( $query );
		$trash = $database->loadResult();
		$rows[$i]->trash = ( $trash ? $trash : '-' );
	}
	// number of Menu Links
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( id )"
		. "\n FROM #__menu"
		. "\n WHERE componentid = ". $rows[$i]->id
		. "\n AND ( type = 'content_archive_section' OR type = 'content_blog_section' OR type = 'content_section' )"
		. "\n AND published <> '-2'"
		;
		$database->setQuery( $query );
		$links = $database->loadResult();
		$rows[$i]->links = ( $links ? $links : '-' );
	}

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	// get list of Access for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['access']	= mosAdminHTML::accessList( 'filter_access', $filter_access, $javascript );

	$lists['search'] 	= stripslashes( $search );

	sections_html::show( $rows, $scope, $my->id, $pageNav, $option, $lists );
}

/**
* Compiles information to add or edit a section
* @param database A database connector object
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
* @param string The name of the current user
*/
function editSection( $uid=0, $scope='', $option ) {
	global $database, $my, $task, $mainframe;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	if ( !$uid && $task <> 'new' ) {
		mosErrorAlert( $_LANG->_( 'Select an item to Edit' ) );
	}

	mosFS::load( '@class', 'com_content' );

	$row = new mosSection( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut()) {
		mosErrorAlert( $_LANG->_( 'The section' ) .' '. $row->title .' '. $_LANG->_( 'descBeingEditted' ) );
	}

	if ( $uid ) {
		$row->checkout( $my->id );
		if ( $row->id > 0 ) {
			$query = "SELECT *"
			. "\n FROM #__menu"
			. "\n WHERE componentid = ". $row->id
			. "\n AND ( type = 'content_archive_section' OR type = 'content_blog_section' OR type = 'content_section' )"
			;
			$database->setQuery( $query );
			$menus = $database->loadObjectList();
			$count = count( $menus );
			for( $i = 0; $i < $count; $i++ ) {
				switch ( $menus[$i]->type ) {
					case 'content_section':
						$menus[$i]->type = $_LANG->_( 'Section Table' );
						break;

					case 'content_blog_section':
						$menus[$i]->type = $_LANG->_( 'Section Blog' );
						break;

					case 'content_archive_section':
						$menus[$i]->type = $_LANG->_( 'Section Blog Archive' );
						break;
				}
			}
		} else {
			$menus = array();
		}
	} else {
		$row->scope 		= $scope;
		$row->published 	= 1;
		$menus 			= array();
	}

	// build the html select list for section types
	$types[] = mosHTML::makeOption( '', $_LANG->_( 'Select Type' ) );
	$types[] = mosHTML::makeOption( 'content_section', $_LANG->_( 'Section List' ) );
	$types[] = mosHTML::makeOption( 'content_blog_section', $_LANG->_( 'Section Blog' ) );
	$types[] = mosHTML::makeOption( 'content_archive_section', $_LANG->_( 'Section Archive Blog' ) );
	$lists['link_type'] 		= mosHTML::selectList( $types, 'link_type', 'class="inputbox" size="1"', 'value', 'text' );;

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__sections"
	. "\n WHERE scope='$row->scope' ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $uid, $query );

	// build the select list for the image positions
	$active =  ( $row->image_position ? $row->image_position : 'left' );
	$lists['image_position'] 	= mosAdminMenus::Positions( 'image_position', $active, NULL, 0 );
	// build the html select list for images
	$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= mosAdminMenus::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );
	// build the html select list for menu selection
	$lists['menuselect']		= mosContentFactory::buildMenuSelect();

	sections_html::edit( $row, $option, $lists, $menus );
}

/**
* Saves the catefory after an edit form submit
* @param database A database connector object
* @param string The name of the category section
*/
function saveSection( $scope, $task ) {
	global $database;
	global $_LANG;

	$menu 		= mosGetParam( $_POST, 'menu', 'mainmenu' );
	$menuid		= mosGetParam( $_POST, 'menuid', 0 );
	$oldtitle 	= mosGetParam( $_POST, 'oldtitle', null );

	$row = new mosSection( $database );
	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getErrorMsg() );
	}
	if (!$row->check()) {
		mosErrorAlert( $row->getErrorMsg() );
	}
	if ( $oldtitle ) {
		if ( $oldtitle <> $row->title ) {
			$query = "UPDATE #__menu"
			. "\n SET name = '$row->title'"
			. "\n WHERE name = '$oldtitle'"
			. "\n AND type = 'content_section'"
			;
			$database->setQuery( $query );
			$database->query();
		}
	}

	if (!$row->store()) {
		mosErrorAlert( $row->getErrorMsg() );
	}
	$row->checkin();
	$row->updateOrder( "scope='$row->scope'" );

	$msg = $_LANG->_( 'Changes to Section saved' );
	switch ( $task ) {
		case 'go2menu':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu, $msg );
			break;

		case 'go2menuitem':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&id='. $menuid, $msg );
			break;

		case 'menulink':
			menuLink( $row->id );
			break;

		case 'apply':
			mosRedirect( 'index2.php?option=com_sections&scope='. $scope .'&task=editA&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Section saved' );

			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer ) {
				mosRedirect( $referer, $msg );
			} else {
				mosRedirect( 'index2.php?option=com_sections&scope='. $scope, $msg );
			}
			break;
	}
}
/**
* Deletes one or more categories from the categories table
* @param database A database connector object
* @param string The name of the category section
* @param array An array of unique category id numbers
*/
function removeSections( $cid, $scope ) {
	global $database;
	global $_LANG;

	if ( count( $cid ) < 1 ) {
		mosErrorAlert( $_LANG->_( 'Select a section to delete' ) );
	}

	$cids = implode( ',', $cid );

	$query = "SELECT s.id, s.name, COUNT( c.id ) AS numcat"
	. "\n FROM #__sections AS s"
	. "\n LEFT JOIN #__categories AS c ON c.section=s.id"
	. "\n WHERE s.id IN ( $cids )"
	. "\n GROUP BY s.id"
	;
	$database->setQuery( $query );
	if (!($rows = $database->loadObjectList())) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	$err = array();
	$cid = array();
	foreach ($rows as $row) {
		if ($row->numcat == 0) {
			$cid[] = $row->id;
			$name[] = $row->name;
		} else {
			$err[] = $row->name;
		}
	}

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__sections"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $database->getErrorMsg() );
		}
	}

	if (count( $err )) {
		$cids = implode( ', ', $err );
		$msg = $_LANG->_( 'Sections(s)' ) .': '. $cids .' '. $_LANG->_( 'DESCCANNOTBEREMOVEDCONTAINCAT' );
		mosRedirect( 'index2.php?option=com_sections&scope='. $scope, $msg );
	}

	$names = implode( ', ', $name );
	$msg = $_LANG->_( 'Section(s)' ) .': '. $names .' '. $_LANG->_( 'successfully deleted' );
	mosRedirect( 'index2.php?option=com_sections&scope='. $scope, $msg );
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
function publishSections( $scope, $cid=null, $publish=1 ) {
	global $database, $my;
	global $_LANG;

	if ( !is_array( $cid ) || count( $cid ) < 1 ) {
		$action = $publish ? 'publish' : 'unpublish';
		mosErrorAlert( $_LANG->_( 'Select a section to' ) .' '. $action );
	}

	$cids = implode( ',', $cid );
	$count = count( $cid );
	if ( $publish ) {
		if ( !$count ){
			mosErrorAlert( $_LANG->_( 'Cannot Publish an Empty Section' ) .' '. $count );
		}
	}

	$query = "UPDATE #__sections"
	. "\n SET published = '$publish'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if ( $count == 1 ) {
		$row = new mosSection( $database );
		$row->checkin( $cid[0] );
	}

	// check if section linked to menu items if unpublishing
	if ( $publish == 0 ) {
		$query = "SELECT id"
		. "\n FROM #__menu"
		. "\n WHERE type = 'content_section'"
		. "\n AND componentid IN ( $cids )"
		;
		$database->setQuery(  );
		$menus = $database->loadObjectList();

		if ($menus) {
			foreach ($menus as $menu) {
				$query = "UPDATE #__menu"
				. "\n SET published = $publish"
				. "\n WHERE id = $menu->id"
				;
				$database->setQuery( $query );
				$database->query();
			}
		}
	}

	mosRedirect( 'index2.php?option=com_sections&scope='. $scope );
}

/**
* Cancels an edit operation
* @param database A database connector object
* @param string The name of the category section
* @param integer A unique category id
*/
function cancelSection( $scope ) {
	global $database;

	$row = new mosSection( $database );
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect( 'index2.php?option=com_sections&scope='. $scope );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderSection( $uid, $inc, $scope ) {
	global $database;

	$row = new mosSection( $database );
	$row->load( $uid );
	$row->move( $inc, "scope='$row->scope'" );

	mosRedirect( 'index2.php?option=com_sections&scope='. $scope );
}


/**
* Form for copying item(s) to a specific menu
*/
function copySectionSelect( $option, $cid, $section ) {
	global $database;
	global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to Copy' ) );
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = "SELECT a.name, a.id"
	. "\n FROM #__categories AS a"
	. "\n WHERE a.section IN ( $cids )"
	;
	$database->setQuery( $query );
	$categories = $database->loadObjectList();

	## query to list items from categories
	$query = "SELECT a.title, a.id"
	. "\n FROM #__content AS a"
	. "\n WHERE a.sectionid IN ( $cids )"
	. "\n ORDER BY a.sectionid, a.catid, a.title"
	;
	$database->setQuery( $query );
	$contents = $database->loadObjectList();

	sections_html::copySectionSelect( $option, $cid, $categories, $contents, $section );
}


/**
* Save the item(s) to the menu selected
*/
function copySectionSave( $sectionid ) {
	global $database;
	global $_LANG;

	$title 		= mosGetParam( $_REQUEST, 'title', '' );
	$contentid 	= mosGetParam( $_REQUEST, 'content', '' );
	$categoryid = mosGetParam( $_REQUEST, 'category', '' );

	// copy section
	$section = new mosSection ( $database );
	foreach( $sectionid as $id ) {
		$section->load( $id );
		$section->id 	= NULL;
		$section->title = $title;
		$section->name 	= $title;
		if ( !$section->check() ) {
			mosErrorAlert( $section->getError() );
		}

		if ( !$section->store() ) {
			mosErrorAlert( $section->getError() );
		}
		$section->checkin();
		$section->updateOrder( "section='$section->id'" );
		// stores original catid
		$newsectids[]['old'] = $id;
		// pulls new catid
		$newsectids[]['new'] = $section->id;
	}
	$sectionMove = $section->id;

	// copy categories
	$category = new mosCategory ( $database );
	foreach( $categoryid as $id ) {
		$category->load( $id );
		$category->id = NULL;
		$category->section = $sectionMove;
		foreach( $newsectids as $newsectid ) {
			if ( $category->section == $newsectid['old'] ) {
				$category->section = $newsectid['new'];
			}
		}
		if (!$category->check()) {
			mosErrorAlert( $category->getError() );
		}

		if (!$category->store()) {
			mosErrorAlert( $category->getError() );
		}
		$category->checkin();
		$category->updateOrder( "section='$category->section'" );
		// stores original catid
		$newcatids[]['old'] = $id;
		// pulls new catid
		$newcatids[]['new'] = $category->id;
	}

	$content = new mosContent ( $database );
	foreach( $contentid as $id) {
		$content->load( $id );
		$content->id = NULL;
		$content->hits = 0;
		foreach( $newsectids as $newsectid ) {
			if ( $content->sectionid == $newsectid['old'] ) {
				$content->sectionid = $newsectid['new'];
			}
		}
		foreach( $newcatids as $newcatid ) {
			if ( $content->catid == $newcatid['old'] ) {
				$content->catid = $newcatid['new'];
			}
		}
		if (!$content->check()) {
			mosErrorAlert( $content->getError() );
		}

		if (!$content->store()) {
			mosErrorAlert( $content->getError() );
		}
		$content->checkin();
	}
	$sectionOld = new mosSection ( $database );
	$sectionOld->load( $sectionMove );

	$msg = $_LANG->_( 'Section' ) .' '. $sectionOld-> name .' '. $_LANG->_( 'DESCALLCATANDITEMSCOPIED' ) .' '. $title;
	mosRedirect( 'index2.php?option=com_sections&scope=content&mosmsg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access ) {
	global $database;

	$row = new mosSection( $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		mosErrorAlert( $row->getError() );
	}
	if ( !$row->store() ) {
		mosErrorAlert( $row->getError() );
	}

	mosRedirect( 'index2.php?option=com_sections&scope='. $row->scope );
}

function menuLink( $id ) {
	global $database;
	global $_LANG;

	$section = new mosSection( $database );
	$section->bind( $_POST );
	$section->checkin();

	$menu 		= mosGetParam( $_POST, 'menuselect', '' );
	$name 		= mosGetParam( $_POST, 'link_name', '' );
	$type 		= mosGetParam( $_POST, 'link_type', '' );

	switch ( $type ) {
		case 'content_section':
			$link 		= 'index.php?option=com_content&task=section&id='. $id;
			$menutype	= 'Section Table';
			break;

		case 'content_blog_section':
			$link 		= 'index.php?option=com_content&task=blogsection&id='. $id;
			$menutype	= 'Section Blog';
			break;

		case 'content_archive_section':
			$link 		= 'index.php?option=com_content&task=archivesection&id='. $id;
			$menutype	= 'Section Blog Archive';
			break;
	}

	$row 				= new mosMenu( $database );
	$row->menutype 		= $menu;
	$row->name 			= $name;
	$row->type 			= $type;
	$row->published		= 1;
	$row->componentid	= $id;
	$row->link			= $link;
	$row->ordering		= 9999;

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	$row->updateOrder( 'scope="'. $row->scope .'"' );

	$msg = $name .' ( '. $menutype .' ) '. $_LANG->_( 'in menu' ) .': '. $menu .' '. $_LANG->_( 'successfully created' );
	mosRedirect( 'index2.php?option=com_sections&scope=content&task=editA&id='. $id,  $msg );
}

function saveOrder( $cid ) {
	global $database;
	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosSection( $database );
	$conditions = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				mosErrorAlert( $row->getError() );
			} // if
			// remember to updateOrder this group
			$condition = "scope='$row->scope'";
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
	mosRedirect( 'index2.php?option=com_sections&scope=content', $msg );
}

function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosSection( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_sections&scope=content', $msg );
}
?>