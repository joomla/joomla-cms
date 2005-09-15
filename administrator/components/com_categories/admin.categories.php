<?php
/**
* @version $Id: admin.categories.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Categories
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

$section 	= mosGetParam( $_REQUEST, 'section', 'content' );

switch ( $task ) {
	case 'new':
		editCategory( 0, $section );
		break;

	case 'edit':
		editCategory( $cid[0], $section );
		break;

	case 'editA':
		editCategory( $id, $section );
		break;

	case 'moveselect':
		moveCategorySelect( $option, $cid, $section );
		break;

	case 'movesave':
		moveCategorySave( $cid, $section );
		break;

	case 'copyselect':
		copyCategorySelect( $option, $cid, $section );
		break;

	case 'copysave':
		copyCategorySave( $cid, $section );
		break;

	case 'go2menu':
	case 'go2menuitem':
	case 'menulink':
	case 'save':
	case 'apply':
		saveCategory( $task );
		break;

	case 'remove':
		removeCategories( $section, $cid );
		break;

	case 'publish':
		publishCategories( $section, $id, $cid, 1 );
		break;

	case 'unpublish':
		publishCategories( $section, $id, $cid, 0 );
		break;

	case 'cancel':
		cancelCategory();
		break;

	case 'orderup':
		orderCategory( $cid[0], -1 );
		break;

	case 'orderdown':
		orderCategory( $cid[0], 1 );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0, $section );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1, $section );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2, $section );
		break;

	case 'saveorder':
		saveOrder( $cid, $section );
		break;

	case 'checkin':
		checkin( $id, $section );
		break;

	default:
		showCategories( $section, $option );
		break;
}

/**
* Compiles a list of categories for a section
* @param string The name of the category section
*/
function showCategories( $section, $option ) {
	global $database, $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path;
    global $_LANG;

	$filter_state	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$filter_access	= $mainframe->getUserStateFromRequest( "filter_access{$option}", 'filter_access', NULL );
	$sectionid 		= $mainframe->getUserStateFromRequest( "sectionid{$option}{$section}", 'sectionid', 0 );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$section}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );

	mosFS::load( '@class', 'com_content' );

	$section_name 	= '';
	$content_add 	= '';
	$content_join 	= '';

	// allows for viewing of all content categories
	if ( $section == 'content' ) {
		$table 			= 'content';
		$content_add 	= "\n , z.title AS section_name";
		$content_join 	= "\n LEFT JOIN #__sections AS z ON z.id = c.section";
		$where 			= "\n WHERE c.section NOT LIKE '%com_%'";

		// get the total number of records
		$query = "SELECT COUNT( * )"
		. "\n FROM #__categories"
		. "\n INNER JOIN #__sections AS s ON s.id = section"
		;
		$database->setQuery( $query );
		$total = $database->loadResult();
		$type 	= 'content';

		// table column ordering values
		$tOrder				= mosGetParam( $_POST, 'tOrder', 'c.section' );
		$tOrder_old			= mosGetParam( $_POST, 'tOrder_old', 'c.section' );
		if ( $tOrder_old <> $tOrder && ( $tOrder <> 'c.section' ) ) {
			$tOrderDir = 'ASC';
		} else {
			$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'ASC' );
		}
		$order 	= "\n ORDER BY $tOrder $tOrderDir, c.section, c.ordering, c.name";

		$lists['title'] = 'CONTENT';
	} else {
		if ( strpos( $section, 'com_' ) === 0 ) {
			$table = substr( $section, 4 );

			$query = "SELECT name"
			. "\n FROM #__components"
			. "\n WHERE link = 'option=$section'"
			;
			$database->setQuery( $query );
			$section_name = $database->loadResult();

			$where 	= "\n WHERE c.section='$section'";
			$type 	= 'other';

			// special handling for contact component
			if ( $section == 'com_contact_details' ) {
				$section_name 	= $_LANG->_( 'Contact' );
			}

			$lists['title'] = strtoupper( $section_name );
		} else {
			$table 	= $section;
			$where 	= "\n WHERE c.section='$section'";
			$type 	= 'other';

			$lists['title'] = $section;
		}

		// get the total number of records
		$query = "SELECT COUNT( * )"
		. "\n FROM #__categories AS c"
		. "\n WHERE c.section = '$section'";
		$database->setQuery( $query );
		$total = $database->loadResult();

		// table column ordering values
		$tOrder		= mosGetParam( $_POST, 'tOrder', 'c.ordering' );
		$tOrder_old	= mosGetParam( $_POST, 'tOrder_old', 'c.ordering' );
		if ( $tOrder_old <> $tOrder && ( $tOrder <> 'c.ordering' ) ) {
			$tOrderDir = 'ASC';
		} else {
			$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'ASC' );
		}
		$order 	= "\n ORDER BY $tOrder $tOrderDir, c.ordering, c.name";
	}

	// table column ordering values
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] = $tOrder;

	// used by filter
	if ( $sectionid > 0 ) {
		$filter = "\n AND c.section = '$sectionid'";
	} else {
		$filter = '';
	}
	if ( $filter_state <> NULL ) {
		$filter .= "\n AND c.published = '$filter_state'";
	}
	if ( $filter_access <> NULL ) {
		$filter .= "\n AND c.access = '$filter_access'";
	}
	if ( $search ) {
		$filter .= "\n AND ( LOWER( c.title ) LIKE '%$search%' OR LOWER( c.name ) LIKE '%$search%' )";
	}

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// main query
	$query = "SELECT  c.*, g.name AS groupname, u.name AS editor, COUNT( DISTINCT s2.checked_out ) AS checked_out_num"
	. $content_add
	. "\n FROM #__categories AS c"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = c.access"
	. "\n LEFT JOIN #__$table AS s2 ON s2.catid = c.id AND s2.checked_out > 0"
	. $content_join
	. $where
	. $filter
	. "\n AND c.published != -2"
	. "\n GROUP BY c.id"
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		mosErrorAlert( $database->stderr() );
	}

	$count = count( $rows );
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $section == 'content' ) {
		// content table
			$query = "SELECT COUNT( a.id )"
			. "\n FROM #__content AS a"
			. "\n WHERE a.catid = ". $rows[$i]->id
			. "\n AND a.state <> '-2'"
			;
		} else {
		// all other component tables
			$query = "SELECT COUNT( a.id )"
			. "\n FROM #__$table AS a"
			. "\n WHERE a.catid = ". $rows[$i]->id
			. "\n AND a.published = '1'"
			;
		}
		$database->setQuery( $query );
		$active = $database->loadResult();
		$rows[$i]->active = ( $active ? $active : '-' );
	}
	// number of Menu Links
	for ( $i = 0; $i < $count; $i++ ) {
		switch ( $section ) {
			case 'content':
				$and 	= "\n AND ( type = 'content_archive_category' OR type = 'content_blog_category' OR type = 'content_category' )";
				break;

			case 'com_weblinks':
				$and 	= "\n AND type = 'weblink_category_table'";
				break;

			case 'com_newsfeeds':
				$and 	= "\n AND type = 'newsfeed_category_table'";
				break;

			case 'com_contact_details':
				$and 	= "\n AND type = 'contact_category_table'";
				break;
		}
		$query = "SELECT COUNT( id )"
		. "\n FROM #__menu"
		. "\n WHERE componentid = ". $rows[$i]->id
		. $and
		. "\n AND published <> '-2'"
		;
		$database->setQuery( $query );
		$links = $database->loadResult();
		$rows[$i]->links = ( $links ? $links : '-' );
	}
	if ( $section == 'content' ) {
		// number of Trashed Items - content only
		for ( $i = 0; $i < $count; $i++ ) {
			$query = "SELECT COUNT( a.id )"
			. "\n FROM #__content AS a"
			. "\n WHERE a.catid = ". $rows[$i]->id
			. "\n AND a.state = '-2'"
			;
			$database->setQuery( $query );
			$trash = $database->loadResult();
			$rows[$i]->trash = ( $trash ? $trash : '-' );
		}
	}

	// get list of sections for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['sectionid']	= mosContentFactory::buildSelectSection( 'sectionid', $sectionid, $javascript );

	// get list of State for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['state']		= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	// get list of Access for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['access']	= mosAdminHTML::accessList( 'filter_access', $filter_access, $javascript );

	$lists['search'] 	= stripslashes( $search );

	// Backlink from components using category table
	if ( ( $section > 0 ) || ( $section == 'com_weblinks' )  || ( $section == 'com_newsfeeds' ) || ( $section == 'com_contact_details' ) ) {

			$sectionbacklink	= trim( $section );

			if ( $sectionbacklink == 'com_contact_details' ) {
				$sectionbacklink	= str_replace( "com_", "", $sectionbacklink );
				$sectionbacklink	= str_replace( "_details", "", $sectionbacklink );
			}
			else {
				$sectionbacklink	= str_replace( "com_", "", $sectionbacklink );
			}
	}
	else {
		$sectionbacklink 	= '';
	}

	categories_html::show( $rows, $section, $pageNav, $lists, $type, $sectionbacklink );
}

/**
* Compiles information to add or edit a category
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
* @param string The name of the current user
*/
function editCategory( $uid=0, $section='' ) {
	global $database, $my, $task, $mainframe;
  	global $_LANG;

  	$mainframe->set('disableMenu', true);

	if ( !$uid && $task <> 'new' ) {
		mosErrorAlert( $_LANG->_( 'Select an item to Edit' ) );
	}

	mosFS::load( '@class', 'com_content' );

	$type 		= mosGetParam( $_REQUEST, 'type', '' );
	$redirect 	= mosGetParam( $_REQUEST, 'section', 'content' );

	$row = new mosCategory( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut()) {
		mosErrorAlert( $_LANG->_( 'The category' ) .' '. $row->title .' '. $_LANG->_( 'descBeingEditted' ) );
	}

	$lists['links']	= 0;
	$menus 			= NULL;
	if ( $uid ) {
		// existing record
		$row->checkout( $my->id );
		// code for Link Menu
		if ( $row->section > 0 ) {
		// content
			$query = "SELECT *"
			. "\n FROM #__menu"
			. "\n WHERE componentid = ". $row->id
			. "\n AND ( type = 'content_archive_category' OR type = 'content_blog_category' OR type = 'content_category' )"
			;
			$database->setQuery( $query );
			$menus = $database->loadObjectList();

			$count = count( $menus );
			for( $i = 0; $i < $count; $i++ ) {
				switch ( $menus[$i]->type ) {
					case 'content_category':
						$menus[$i]->type = $_LANG->_( 'Table - Content Category' );
						break;

					case 'content_blog_category':
						$menus[$i]->type = $_LANG->_( 'Blog - Content Category' );
						break;

					case 'content_archive_category':
						$menus[$i]->type = $_LANG->_( 'Blog - Content Category Archive' );
						break;
				}
			}
			$lists['links']	= 1;
		} else if ( ( $section == 'com_weblinks' )  || ( $section == 'com_newsfeeds' ) || ( $section == 'com_contact_details' )  ) {
			switch ( $section ) {
				case 'com_weblinks':
					$and 	= "\n AND type = 'weblink_category_table'";
					$link 	= $_LANG->_( 'Table - Weblink Category' );
					break;

				case 'com_newsfeeds':
					$and 	= "\n AND type = 'newsfeed_category_table'";
					$link 	= $_LANG->_( 'Table - Newsfeeds Category' );
					break;

				case 'com_contact_details':
					$and 	= "\n AND type = 'contact_category_table'";
					$link 	= $_LANG->_( 'Table - Contacts Category' );
					break;
			}
			$query = "SELECT *"
			. "\n FROM #__menu"
			. "\n WHERE componentid = ". $row->id
			. $and
			;
			$database->setQuery( $query );
			$menus = $database->loadObjectList();

			$count = count( $menus );
			for( $i = 0; $i < $count; $i++ ) {
				$menus[$i]->type = $link;
			}

			$lists['links']	= 1;
		}
	} else {
		// new record
		$row->section 	= $section;
		$row->published = 1;
		$menus 			= NULL;

		if ( ( $section > 0 ) || ( $section == 'com_weblinks' )  || ( $section == 'com_newsfeeds' ) || ( $section == 'com_contact_details' ) ) {
			$lists['links']	= 1;
		}
	}

	// make order list
	$order = array();
	$query = "SELECT COUNT( * )"
	. "\n FROM #__categories"
	. "\n WHERE section = '$row->section'"
	;
	$database->setQuery( $query );
	$max = intval( $database->loadResult() ) + 1;

	for ($i=1; $i < $max; $i++) {
		$order[] = mosHTML::makeOption( $i );
	}

	// build the html select list for sections
	if ( $section == 'content' ) {
		$query = "SELECT s.id AS value, s.title AS text"
		. "\n FROM #__sections AS s"
		. "\n ORDER BY s.ordering"
		;
		$database->setQuery( $query );
		$sections = $database->loadObjectList();
		$lists['section'] = mosHTML::selectList( $sections, 'section', 'class="inputbox" size="1"', 'value', 'text' );;
	} else {
		if ( $type == 'other' ) {
			$section_name = 'N/A';
		} else {
			$temp = new mosSection( $database );
			$temp->load( $row->section );
			$section_name = $temp->name;
		}
		$lists['section'] = '<input type="hidden" name="section" value="'. $row->section .'" />'. $section_name;
	}

	// build the html select list for category types
	if ($row->section == 'com_contact_details') {
		$types[] = mosHTML::makeOption( 'contact_category_table', $_LANG->_( 'Table - Contact Category' ) );
	} else
	if ($row->section == 'com_newsfeeds') {
		$types[] = mosHTML::makeOption( 'newsfeed_category_table', $_LANG->_( 'Table - Newsfeed Category' ) );
	} else
	if ($row->section == 'com_weblinks') {
		$types[] = mosHTML::makeOption( 'weblink_category_table', $_LANG->_( 'Table - Weblink Category' ) );
	} else {
		$types[] = mosHTML::makeOption( '', $_LANG->_( 'Select Type' ) );
		$types[] = mosHTML::makeOption( 'content_category', $_LANG->_( 'Table - Content Category' ) );
		$types[] = mosHTML::makeOption( 'content_blog_category', $_LANG->_( 'Blog - Content Category' ) );
		$types[] = mosHTML::makeOption( 'content_archive_category', $_LANG->_( 'Blog - Content Category Archive' ) );
	} // if
	$lists['link_type'] 		= mosHTML::selectList( $types, 'link_type', 'class="inputbox" size="1"', 'value', 'text' );;

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__categories"
	. "\n WHERE section = '$row->section'"
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $uid, $query );

	// build the select list for the image positions
	$active =  ( $row->image_position ? $row->image_position : 'left' );
	$lists['image_position'] 	= mosAdminMenus::Positions( 'image_position', $active, NULL, 0, 0 );
	// Imagelist
	$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= mosAdminMenus::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );
	// build the html select list for menu selection
	$lists['menuselect']		= mosContentFactory::buildMenuSelect();

	categories_html::edit( $row, $lists, $redirect, $menus );
}

/**
* Saves the catefory after an edit form submit
* @param string The name of the category section
*/
function saveCategory( $task ) {
	global $database;
  	global $_LANG;

	$menu 		= mosGetParam( $_POST, 'menu', 'mainmenu' );
	$menuid		= mosGetParam( $_POST, 'menuid', 0 );
	$redirect 	= mosGetParam( $_POST, 'redirect', '' );
	$oldtitle 	= mosGetParam( $_POST, 'oldtitle', null );

	$row = new mosCategory( $database );
	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

 	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}

	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	$row->updateOrder( "section='$row->section'" );

	if ( $oldtitle ) {
		if ($oldtitle != $row->title) {
			$query = "UPDATE #__menu"
			. "\n SET name = '$row->title'"
			. "\n WHERE name = '$oldtitle'"
			. "\n AND type = 'content_category'"
			;
			$database->setQuery( $query );
			$database->query();
		}
	}

	// Update Section Count
	if ( $row->section != 'com_contact_details' && $row->section != 'com_newsfeeds' && $row->section != 'com_weblinks') {
		$query = "UPDATE #__sections"
		. "\n SET count = count + 1"
		. "\n WHERE id = '$row->section'"
		;
		$database->setQuery( $query );
	}

	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	$msg = $_LANG->_( 'Changes to Category saved' );
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
			mosRedirect( 'index2.php?option=com_categories&section='. $redirect .'&task=editA&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Category saved' );

			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer ) {
				mosRedirect( $referer, $msg );
			} else {
				mosRedirect( 'index2.php?option=com_categories&section='. $redirect, $msg );
			}
			break;
	}
}

/**
* Deletes one or more categories from the categories table
* @param string The name of the category section
* @param array An array of unique category id numbers
*/
function removeCategories( $section, $cid ) {
	global $database;
  	global $_LANG;

	if ( count( $cid ) < 1 ) {
		mosErrorAlert( $_LANG->_( 'Select a category to delete' ) );
	}

	$cids = implode( ',', $cid );

	//Get Section ID prior to removing Category, in order to update counts
	if (intval( $section ) > 0) {
		$table = 'content';
	} else if (strpos( $section, 'com_' ) === 0) {
		$table = substr( $section, 4 );
	} else {
		$table = $section;
	}

	$query = "SELECT c.id, c.name, COUNT( s.catid ) AS numcat"
	. "\n FROM #__categories AS c"
	. "\n LEFT JOIN #__$table AS s ON s.catid = c.id"
	. "\n WHERE c.id IN ( $cids )"
	. "\n GROUP BY c.id"
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
		} else {
			$err[] = $row->name;
		}
	}

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__categories"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $database->getErrorMsg() );
		}
	}

	if (count( $err )) {
		$cids = implode( "\', \'", $err );
		$msg = $_LANG->_( 'Category(s)' ) .': '. $cids .' '. $_LANG->_( 'WARNNOTREMOVEDRECORDS' );
		mosRedirect( 'index2.php?option=com_categories&section='. $section .'&mosmsg='. $msg );
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

/**
* Publishes or Unpublishes one or more categories
* @param string The name of the category section
* @param integer A unique category id (passed from an edit form)
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The name of the current user
*/
function publishCategories( $section, $categoryid=null, $cid=null, $publish=1 ) {
	global $database, $my;
  	global $_LANG;

	if (!is_array( $cid )) {
		$cid = array();
	}
	if ($categoryid) {
		$cid[] = $categoryid;
	}

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		mosErrorAlert( $_LANG->_( SELECT_CATEG ." ". $action ) );
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__categories"
	. "\n SET published = '$publish'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosCategory( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

/**
* Cancels an edit operation
* @param string The name of the category section
* @param integer A unique category id
*/
function cancelCategory() {
	global $database;

	$redirect = mosGetParam( $_POST, 'redirect', '' );

	$row = new mosCategory( $database );
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect( 'index2.php?option=com_categories&section='. $redirect );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderCategory( $uid, $inc ) {
	global $database;

	$row = new mosCategory( $database );
	$row->load( $uid );
	$row->move( $inc, "section='$row->section'" );

	mosRedirect( 'index2.php?option=com_categories&section='. $row->section );
}

/**
* Form for moving item(s) to a specific menu
*/
function moveCategorySelect( $option, $cid, $sectionOld ) {
	global $database;
  	global $_LANG;

	$redirect = mosGetParam( $_POST, 'section', 'content' );;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'SELECT_ITEM_TO_MOVE' ) );
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = "SELECT a.name, a.section"
	. "\n FROM #__categories AS a"
	. "\n WHERE a.id IN ( $cids )"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	## query to list items from categories
	$query = "SELECT a.title"
	. "\n FROM #__content AS a"
	. "\n WHERE a.catid IN ( $cids )"
	. "\n ORDER BY a.catid, a.title"
	;
	$database->setQuery( $query );
	$contents = $database->loadObjectList();

	## query to choose section to move to
	$query = "SELECT a.name AS `text`, a.id AS `value`"
	. "\n FROM #__sections AS a"
	. "\n WHERE a.published = '1'"
	. "\n ORDER BY a.name"
	;
	$database->setQuery( $query );
	$sections = $database->loadObjectList();

	// build the html select list
	$SectionList = mosHTML::selectList( $sections, 'sectionmove', 'class="inputbox" size="10"', 'value', 'text', null );

	categories_html::moveCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect );
}


/**
* Save the item(s) to the menu selected
*/
function moveCategorySave( $cid, $sectionOld ) {
	global $database;
  	global $_LANG;

	$sectionMove = mosGetParam( $_REQUEST, 'sectionmove', '' );

	$cids = implode( ',', $cid );
	$total = count( $cid );

	$query =  "UPDATE #__categories"
	. "\n SET section = '$sectionMove'"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		mosErrorAlert( $database->getErrorMsg() );
	}
	$query = 	"UPDATE #__content"
	. "\n SET sectionid = '$sectionMove'"
	. "\n WHERE catid IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		mosErrorAlert( $database->getErrorMsg() );
	}
	$sectionNew = new mosSection ( $database );
	$sectionNew->load( $sectionMove );

	$msg = $total .' '. $_LANG->_( 'Categories moved to' ) .' '. $sectionNew->name;
	mosRedirect( 'index2.php?option=com_categories&section='. $sectionOld .'&mosmsg='. $msg );
}

/**
* Form for copying item(s) to a specific menu
*/
function copyCategorySelect( $option, $cid, $sectionOld ) {
	global $database;
    global $_LANG;

	$redirect = mosGetParam( $_POST, 'section', 'content' );;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'SELECT_ITEM_TO_MOVE' ) );
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = "SELECT a.name, a.section"
	. "\n FROM #__categories AS a"
	. "\n WHERE a.id IN ( $cids )"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	## query to list items from categories
	$query = "SELECT a.title, a.id"
	. "\n FROM #__content AS a"
	. "\n WHERE a.catid IN ( $cids )"
	. "\n ORDER BY a.catid, a.title"
	;
	$database->setQuery( $query );
	$contents = $database->loadObjectList();

	## query to choose section to move to
	$query = "SELECT a.name AS `text`, a.id AS `value`"
	. "\n FROM #__sections AS a"
	. "\n WHERE a.published = '1'"
	. "\n ORDER BY a.name"
	;
	$database->setQuery( $query );
	$sections = $database->loadObjectList();

	// build the html select list
	$SectionList = mosHTML::selectList( $sections, 'sectionmove', 'class="inputbox" size="10"', 'value', 'text', null );

	categories_html::copyCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect );
}


/**
* Save the item(s) to the menu selected
*/
function copyCategorySave( $cid, $sectionOld ) {
	global $database;
  	global $_LANG;

	$sectionMove 	= mosGetParam( $_REQUEST, 'sectionmove', '' );
	$contentid 		= mosGetParam( $_REQUEST, 'item', '' );
	$total 			= count( $contentid  );

	$category = new mosCategory ( $database );
	foreach( $cid as $id ) {
		$category->load( $id );
		$category->id 		= NULL;
		$category->title 	= $_LANG->_( 'Copy of' ) .' '. $category->title;
		$category->name 	= $_LANG->_( 'Copy of' ) .' '. $category->name;
		$category->section 	= $sectionMove;
		if (!$category->check()) {
			mosErrorAlert( $category->getErrorMsg() );
		}

		if (!$category->store()) {
			mosErrorAlert( $category->getErrorMsg() );
		}
		$category->checkin();
		// stores original catid
		$newcatids[]['old'] = $id;
		// pulls new catid
		$newcatids[]['new'] = $category->id;
	}

	$content = new mosContent ( $database );
	foreach( $contentid as $id) {
		$content->load( $id );
		$content->id = NULL;
		$content->sectionid = $sectionMove;
		$content->hits = 0;
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

	$sectionNew = new mosSection ( $database );
	$sectionNew->load( $sectionMove );

	$msg = $total .' '. $_LANG->_( 'Categories copied to' ) .' '. $sectionNew->name;
	mosRedirect( 'index2.php?option=com_categories&section='. $sectionOld .'&mosmsg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $section ) {
	global $database;

	$row = new mosCategory( $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		mosErrorAlert( $row->getError() );
	}
	if ( !$row->store() ) {
		mosErrorAlert( $row->getError() );
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

function menuLink( $id ) {
	global $database;
    global $_LANG;

	$category = new mosCategory( $database );
	$category->bind( $_POST );
	$category->checkin();

	$redirect	= mosGetParam( $_POST, 'redirect', '' );
	$menu 		= mosGetParam( $_POST, 'menuselect', '' );
	$name 		= mosGetParam( $_POST, 'link_name', '' );
	$sectionid	= mosGetParam( $_POST, 'sectionid', '' );
	$type 		= mosGetParam( $_POST, 'link_type', '' );

	switch ( $type ) {
		case 'content_category':
			$link 		= 'index.php?option=com_content&task=category&sectionid='. $sectionid .'&id='. $id;
			$menutype	= $_LANG->_( 'Content Category Table' );
			break;

		case 'content_blog_category':
			$link 		= 'index.php?option=com_content&task=blogcategory&id='. $id;
			$menutype	= $_LANG->_( 'Content Category Blog' );
			break;

		case 'content_archive_category':
			$link 		= 'index.php?option=com_content&task=archivecategory&id='. $id;
			$menutype	= $_LANG->_( 'Content Category Blog Archive' );
			break;

		case 'contact_category_table':
			$link 		= 'index.php?option=com_contact&catid='. $id;
			$menutype	= $_LANG->_( 'Contact Category Table' );
			break;

		case 'newsfeed_category_table':
			$link 		= 'index.php?option=com_newsfeeds&catid='. $id;
			$menutype	= $_LANG->_( 'Newsfeed Category Table' );
			break;

		case 'weblink_category_table':
			$link 		= 'index.php?option=com_weblinks&catid='. $id;
			$menutype	= $_LANG->_( 'Weblink Category Table' );
			break;

		default:;
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
	$row->updateOrder( "menutype='$menu'" );

	$msg = $name .' ( '. $menutype .' ) '. $_LANG->_( 'in menu' ) .': '. $menu .' '. $_LANG->_( 'successfully created' );
	mosRedirect( 'index2.php?option=com_categories&section='. $redirect .'&task=editA&id='. $id, $msg );
}

function saveOrder( &$cid, $section ) {
	global $database;
    global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row		= new mosCategory( $database );
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
			$condition = "section='$row->section'";
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
	mosRedirect( 'index2.php?option=com_categories&section='. $section, $msg );
} // saveOrder

function checkin( $id, $section ) {
	global $database;
	global $_LANG;

	$row = new mosCategory( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_categories&section='. $section, $msg );
}
?>