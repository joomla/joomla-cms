<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Categories
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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

// get parameters from the URL or submitted form
$section 	= mosGetParam( $_REQUEST, 'section', 'content' );
$cid 		= mosGetParam( $_REQUEST, 'cid', array(0) );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'new':
		editCategory( 0, $section );
		break;

	case 'edit':
		editCategory( intval( $cid[0] ) );
		break;

	case 'editA':
		editCategory( intval( $id ) );
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

	default:
		showCategories( $section, $option );
		break;
}

/**
* Compiles a list of categories for a section
* @param string The name of the category section
*/
function showCategories( $section, $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$sectionid 		= $mainframe->getUserStateFromRequest( "sectionid{$option}{$section}", 'sectionid', 0 );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$section}limitstart", 'limitstart', 0 );

	$section_name 	= '';
	$content_add 	= '';
	$content_join 	= '';
	$order 			= "\n ORDER BY c.ordering, c.name";
	if (intval( $section ) > 0) {
		$table = 'content';

		$query = "SELECT name"
		. "\n FROM #__sections"
		. "\n WHERE id = $section";
		$database->setQuery( $query );
		$section_name = $database->loadResult();
		$section_name = sprintf( JText::_( 'Content:' ), JText::_( $section_name ) );
		$where 	= "\n WHERE c.section = '$section'";
		$type 	= 'content';
	} else if (strpos( $section, 'com_' ) === 0) {
		$table = substr( $section, 4 );

		$query = "SELECT name"
		. "\n FROM #__components"
		. "\n WHERE link = 'option=$section'"
		;
		$database->setQuery( $query );
		$section_name = $database->loadResult();
		$where 	= "\n WHERE c.section = '$section'";
		$type 	= 'other';
		// special handling for contact component
		if ( $section == 'com_contact_details' ) {
			$section_name 	= JText::_( 'Contact' );
		}
		$section_name = sprintf( JText::_( 'Component:' ), $section_name );
	} else {
		$table 	= $section;
		$where 	= "\n WHERE c.section = '$section'";
		$type 	= 'other';
	}

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__categories"
	. "\n WHERE section = '$section'"
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// allows for viweing of all content categories
	if ( $section == 'content' ) {
		$table 			= 'content';
		$content_add 	= "\n , z.title AS section_name";
		$content_join 	= "\n LEFT JOIN #__sections AS z ON z.id = c.section";
		//$where = "\n WHERE s1.catid = c.id";
		$where 			= "\n WHERE c.section NOT LIKE '%com_%'";
		$order 			= "\n ORDER BY c.section, c.ordering, c.name";
		$section_name 	= JText::_( 'All Content:' );
		// get the total number of records
		// get the total number of records
		$query = "SELECT COUNT(*)"
		. "\n FROM #__categories"
		. "\n INNER JOIN #__sections AS s ON s.id = section";
		if ( $sectionid > 0 ) {
			$query .= "\n WHERE section = '$sectionid'";
		}
		$database->setQuery( $query );
		$total = $database->loadResult();
		$type 			= 'content';
	}

	// used by filter
	if ( $sectionid > 0 ) {
		$filter = "\n AND c.section = '$sectionid'";
	} else {
		$filter = '';
	}

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT  c.*, c.checked_out as checked_out_contact_category, g.name AS groupname, u.name AS editor,"
	. "COUNT( DISTINCT s2.checked_out ) AS checked_out"
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
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return;
	}

	$count = count( $rows );
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.catid = ". $rows[$i]->id
		. "\n AND a.state <> -2"
		;
		$database->setQuery( $query );
		$active = $database->loadResult();
		$rows[$i]->active = $active;
	}
	// number of Trashed Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.catid = ". $rows[$i]->id
		. "\n AND a.state = -2"
		;
		$database->setQuery( $query );
		$trash = $database->loadResult();
		$rows[$i]->trash = $trash;
	}

	// get list of sections for dropdown filter
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['sectionid']	= mosAdminMenus::SelectSection( 'sectionid', $sectionid, $javascript );

	categories_html::show( $rows, $section, $section_name, $pageNav, $lists, $type );
}

/**
* Compiles information to add or edit a category
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
* @param string The name of the current user
*/
function editCategory( $uid=0, $section='' ) {
	global $database, $my;
	;

	$type 		= mosGetParam( $_REQUEST, 'type', '' );
	$redirect 	= mosGetParam( $_REQUEST, 'section', 'content' );

	// check for existance of any sections
	$query = "SELECT COUNT( id )"
	. "\n FROM #__sections"
	. "\n WHERE scope = 'content'"
	;
	$database->setQuery( $query );
	$sections = $database->loadResult();
	if (!$sections && $type != 'other') {
		echo "<script> alert('". JText::_( 'WARNSECTION', true ) ."'); window.history.go(-1); </script>\n";
		exit();
	}

	$row = new JCategoryModel( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->checked_out && $row->checked_out <> $my->id) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The category' ), $row->title );
		mosRedirect( 'index2.php?option=categories&section='. $row->section, $msg );
	}

	$lists['links']	= 0;
	$menus 			= NULL;
	if ( $uid ) {
		// existing record
		$row->checkout( $my->id );
		// code for Link Menu

		switch ( $row->section ) {
			case 'com_weblinks':
				$and 	= "\n AND type = 'weblink_category_table'";
				$link 	= JText::_( 'Table - Weblink Category' );
				break;

			case 'com_newsfeeds':
				$and 	= "\n AND type = 'newsfeed_category_table'";
				$link 	= JText::_( 'Table - Newsfeeds Category' );
				break;

			case 'com_contact_details':
				$and 	= "\n AND type = 'contact_category_table'";
				$link 	= JText::_( 'Table - Contacts Category' );
				break;
		}

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
					$menus[$i]->type = JText::_( 'Table - Content Category' );
					break;

					case 'content_blog_category':
					$menus[$i]->type = JText::_( 'Blog - Content Category' );
					break;

					case 'content_archive_category':
					$menus[$i]->type = JText::_( 'Blog - Content Category Archive' );
					break;
				}
			}
			$lists['links']	= 1;
		} else {
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
	}

	// make order list
	$order = array();
	$query = "SELECT COUNT(*)"
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
			$section_name = JText::_( 'N/A' );
		} else {
			$temp = new JSectionModel( $database );
			$temp->load( $row->section );
			$section_name = $temp->name;
		}
		if(!$section_name) $section_name = JText::_( 'N/A' );
		$lists['section'] = '<input type="hidden" name="section" value="'. $row->section .'" />'. $section_name;
	}

	// build the html select list for category types
	$types[] = mosHTML::makeOption( '', JText::_( 'Select Type' ) );
	if ($row->section == 'com_contact_details') {
		$types[] = mosHTML::makeOption( 'contact_category_table', JText::_( 'Contact Category Table' ) );
	} else
	if ($row->section == 'com_newsfeeds') {
		$types[] = mosHTML::makeOption( 'newsfeed_category_table', JText::_( 'Newsfeed Category Table' ) );
	} else
	if ($row->section == 'com_weblinks') {
		$types[] = mosHTML::makeOption( 'weblink_category_table', JText::_( 'Weblink Category Table' ) );
	} else {
		$types[] = mosHTML::makeOption( 'content_category', JText::_( 'Content Category Table' ) );
		$types[] = mosHTML::makeOption( 'content_blog_category', JText::_( 'Content Category Blog' ) );
		$types[] = mosHTML::makeOption( 'content_archive_category', JText::_( 'Content Category Archive Blog' ) );
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
	$lists['menuselect']		= mosAdminMenus::MenuSelect( );

 	categories_html::edit( $row, $lists, $redirect, $menus );
}

/**
* Saves the catefory after an edit form submit
* @param string The name of the category section
*/
function saveCategory( $task ) {
	global $database;
	;

	$menu 		= mosGetParam( $_POST, 'menu', 'mainmenu' );
	$menuid		= mosGetParam( $_POST, 'menuid', 0 );
	$redirect 	= mosGetParam( $_POST, 'redirect', '' );
	$oldtitle 	= mosGetParam( $_POST, 'oldtitle', null );

	$row = new JCategoryModel( $database );
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
	$row->updateOrder( "section = '$row->section'" );

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
	if ($row->section != 'com_contact_details' &&
		$row->section != 'com_newsfeeds' &&
		$row->section != 'com_weblinks') {
		$query = "UPDATE #__sections SET count=count+1"
		. "\n WHERE id = '$row->section'"
		;
		$database->setQuery( $query );
	}

	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	switch ( $task ) {
		case 'go2menu':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu );
			break;

		case 'go2menuitem':
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&hidemainmenu=1&id='. $menuid );
			break;

		case 'menulink':
			menuLink( $row->id );
			break;

		case 'apply':
        	$msg = JText::_( 'Changes to Category saved' );
			mosRedirect( 'index2.php?option=com_categories&section='. $redirect .'&task=editA&hidemainmenu=1&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = JText::_( 'Category saved' );
			mosRedirect( 'index2.php?option=com_categories&section='. $redirect, $msg );
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
	;

	if (count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select a category to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

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
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
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
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	if (count( $err )) {
		$cids = implode( "\', \'", $err );
    	$msg = sprintf( JText::_( 'WARNNOTREMOVEDRECORDS' ), $cids );
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
	;

	if (!is_array( $cid )) {
		$cid = array();
	}
	if ($categoryid) {
		$cid[] = $categoryid;
	}

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select a category to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__categories"
	. "\n SET published = " . intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new JCategoryModel( $database );
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

	$row = new JCategoryModel( $database );
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

	$row = new JCategoryModel( $database );
	$row->load( $uid );
	$row->move( $inc, "section = '$row->section'" );

	mosRedirect( 'index2.php?option=com_categories&section='. $row->section );
}

/**
* Form for moving item(s) to a specific menu
*/
function moveCategorySelect( $option, $cid, $sectionOld ) {
	global $database;

	$redirect = mosGetParam( $_POST, 'section', 'content' );;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to move' ) ."'); window.history.go(-1);</script>\n";
		exit;
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
	$query = "SELECT a.name AS text, a.id AS value"
	. "\n FROM #__sections AS a"
	. "\n WHERE a.published = 1"
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

	$sectionMove = mosGetParam( $_REQUEST, 'sectionmove', '' );

	$cids = implode( ',', $cid );
	$total = count( $cid );

	$query = "UPDATE #__categories"
	. "\n SET section = '$sectionMove'"
	. "WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
		exit();
	}
	$query = "UPDATE #__content"
	. "\n SET sectionid = '$sectionMove'"
	. "\n WHERE catid IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
		exit();
	}
	$sectionNew = new JSectionModel ( $database );
	$sectionNew->load( $sectionMove );

	$msg = sprintf( JText::_( 'Categories moved to' ), $sectionNew->name );
	mosRedirect( 'index2.php?option=com_categories&section='. $sectionOld .'&mosmsg='. $msg );
}

/**
* Form for copying item(s) to a specific menu
*/
function copyCategorySelect( $option, $cid, $sectionOld ) {
	global $database;

	$redirect = mosGetParam( $_POST, 'section', 'content' );;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to move' ) ."'); window.history.go(-1);</script>\n";
		exit;
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
	. "\n WHERE a.published = 1"
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

	$sectionMove 	= mosGetParam( $_REQUEST, 'sectionmove', '' );
	$contentid 		= mosGetParam( $_REQUEST, 'item', '' );
	$total 			= count( $contentid  );

	$category = new JCategoryModel ( $database );
	foreach( $cid as $id ) {
		$category->load( $id );
		$category->id 		= NULL;
		$category->title 	= sprintf( JText::_( 'Copy of' ), $category->title );
		$category->name 	= sprintf( JText::_( 'Copy of' ), $category->name );
		$category->section 	= $sectionMove;
		if (!$category->check()) {
			echo "<script> alert('".$category->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (!$category->store()) {
			echo "<script> alert('".$category->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$category->checkin();
		// stores original catid
		$newcatids[]["old"] = $id;
		// pulls new catid
		$newcatids[]["new"] = $category->id;
	}

	$content = new JContentModel ( $database );
	foreach( $contentid as $id) {
		$content->load( $id );
		$content->id 		= NULL;
		$content->sectionid = $sectionMove;
		$content->hits 		= 0;
		foreach( $newcatids as $newcatid ) {
			if ( $content->catid == $newcatid["old"] ) {
				$content->catid = $newcatid["new"];
			}
		}
		if (!$content->check()) {
			echo "<script> alert('".$content->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (!$content->store()) {
			echo "<script> alert('".$content->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$content->checkin();
	}

	$sectionNew = new JSectionModel ( $database );
	$sectionNew->load( $sectionMove );

	$msg = sprintf( JText::_( 'Categories copied to' ), $total, $sectionNew->name );
	mosRedirect( 'index2.php?option=com_categories&section='. $sectionOld .'&mosmsg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $section ) {
	global $database;

	$row = new JCategoryModel( $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

function menuLink( $id ) {
	global $database;

	$category = new JCategoryModel( $database );
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
			$menutype	= JText::_( 'Content Category Table' );
			break;

		case 'content_blog_category':
			$link 		= 'index.php?option=com_content&task=blogcategory&id='. $id;
			$menutype	= JText::_( 'Content Category Blog' );
			break;

		case 'content_archive_category':
			$link 		= 'index.php?option=com_content&task=archivecategory&id='. $id;
			$menutype	= JText::_( 'Content Category Blog Archive' );
			break;

		case 'contact_category_table':
			$link 		= 'index.php?option=com_contact&catid='. $id;
			$menutype	= JText::_( 'Contact Category Table' );
			break;

		case 'newsfeed_category_table':
			$link 		= 'index.php?option=com_newsfeeds&catid='. $id;
			$menutype	= JText::_( 'Newsfeed Category Table' );
			break;

		case 'weblink_category_table':
			$link 		= 'index.php?option=com_weblinks&catid='. $id;
			$menutype	= JText::_( 'Weblink Category Table' );
			break;
	}

	$row 				= new JMenuModel( $database );
	$row->menutype 		= $menu;
	$row->name 			= $name;
	$row->type 			= $type;
	$row->published		= 1;
	$row->componentid	= $id;
	$row->link			= $link;
	$row->ordering		= 9999;

	if ( $type == 'content_blog_category' ) {
		$row->params = 'categoryid='. $id;
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
	$row->updateOrder( "menutype = '$menu'" );

	$msg = sprintf( JText::_( 'CATSUCCESSCREATED' ), $name, $menutype, $menu );
	mosRedirect( 'index2.php?option=com_categories&section='. $redirect .'&task=editA&hidemainmenu=1&id='. $id, $msg );
}

function saveOrder( &$cid, $section ) {
	global $database;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row		= new JCategoryModel( $database );
	$conditions = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
				exit();
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

	$msg 	= JText::_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_categories&section='. $section, $msg );
} // saveOrder
?>
