<?php
/**
* @version $Id: admin.menus.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

$type 		= mosGetParam( $_REQUEST, 'type', false );
$menutype 	= mosGetParam( $_REQUEST, 'menutype', '' );
$menutype	= stripslashes( $menutype );
$access 	= mosGetParam( $_POST, 'access', '' );
$utaccess	= mosGetParam( $_POST, 'utaccess', '' );
$ItemName	= mosGetParam( $_POST, 'ItemName', '' );
$menu 		= mosGetParam( $_POST, 'menu', '' );


if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'checkin':
		checkin( $id, $menutype );
		break;

	case 'new':
		addMenuItem( $cid, $menutype, $option, $task );
		break;

	case 'edit':
		$cid[0]	= ( $id ? $id : $cid[0] );
		$menu = new mosMenu( $database );
		if ( $cid[0] ) {
			$menu->load( $cid[0]  );
		} else {
			$menu->type = $type;
		}

		if ( $menu->type ) {
			$type = $menu->type;
			loadMenuFiles( $type );
		} else {
			mosRedirect( 'index2.php?option=com_menus&task=new', $_LANG->_( 'Please select a Menu Type' ) );
		}
		break;

	case 'save':
	case 'apply':
		loadMenuFiles( $type );
		break;

	case 'publish':
	case 'unpublish':
		if ($msg = publishMenuSection( $cid, ($task == 'publish') )) {
			// proceed no further if the menu item can't be published
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype .'&mosmsg= '.$msg );
		} else {
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype );
		}
		break;

	case 'remove':
		if ($msg = TrashMenusection( $cid )) {
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype .'&mosmsg= '.$msg );
		} else {
			mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype );
		}
		break;

	case 'cancel':
		cancelMenu( $option );
		break;

	case 'orderup':
		orderMenu( $cid[0], -1, $option );
		break;

	case 'orderdown':
		orderMenu( $cid[0], 1, $option );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0, $option, $menutype );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1, $option, $menutype );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2, $option, $menutype );
		break;

	case 'movemenu':
		moveMenu( $option, $cid, $menutype );
		break;

	case 'movemenusave':
		moveMenuSave( $option, $cid, $menu, $menutype );
		break;

	case 'copymenu':
		copyMenu( $option, $cid, $menutype );
		break;

	case 'copymenusave':
		copyMenuSave( $option, $cid, $menu, $menutype );
		break;

	case 'cancelcopymenu':
	case 'cancelmovemenu':
		viewMenuItems( $menutype, $option );
		break;

	case 'saveorder':
		saveOrder( $cid, $menutype );
		break;

	case 'trashview':
		trashView();
		break;

	case 'trashrestoreconfirm':
		trashRestoreConfirm();
		break;

	case 'trashrestore':
		trashRestore();
		break;

	case 'trashdeleteconfirm' :
		trashDeleteConfirm();
		break;

	case 'trashdelete':
		trashDelete();
		break;

	case 'canceldelete':
	case 'cancelrestore':
		mosRedirect( 'index2.php?option=com_menus&task=trashview' );
		break;

	default:
		$type = trim( mosGetParam( $_REQUEST, 'type', null ) );
		if ( $type ) {
			// adding a new item - type selection form
			loadMenuFiles( $type );
		} else {
			viewMenuItems( $menutype, $option );
		}
		break;
}

/**
* Shows a list of items for a menu
*/
function viewMenuItems( $menutype, $option ) {
	global $database, $mainframe, $mosConfig_list_limit;
	global $_LANG;

	$filter_state 	= $mainframe->getUserStateFromRequest( "filter_state{$option}", 'filter_state', NULL );
	$filter_access 	= $mainframe->getUserStateFromRequest( "filter_access{$option}", 'filter_access', NULL );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart$menutype", 'limitstart', 0 );
	$levellimit 	= $mainframe->getUserStateFromRequest( "view{$option}limit$menutype", 'levellimit', 10 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}$menutype", 'search', '' );
	$search 		= trim( strtolower( $search ) );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'ordering' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'ordering' );

	$a = addslashes( $menutype );

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
	if ( $filter_state <> NULL ) {
		$filter[] = "\n AND m.published = '$filter_state'";
	}
	if ( $filter_access <> NULL ) {
		$filter[] = "\n AND m.access = '$filter_access'";
	}
	$filter = implode( '', $filter );

	// select the records
	// note, since this is a tree we have to do the limits code-side
	if ( $search ) {
		$query = "SELECT m.id"
		. "\n FROM #__menu AS m"
		. "\n WHERE menutype = '$a'"
		. "\n AND LOWER(m.name) LIKE '%" . strtolower( $search ) . "%'"
		;
		$database->setQuery( $query );
		$search_rows = $database->loadResultArray();
	}

	// table column ordering
	$order = "\n ORDER BY m.$tOrder $tOrderDir, m.parent ASC, m.ordering ASC";

	// main query
	$query = "SELECT m.*, u.name AS editor, g.name AS groupname, c.publish_up, c.publish_down, com.name AS com_name"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = m.access"
	. "\n LEFT JOIN #__content AS c ON c.id = m.componentid AND m.type = 'content_typed'"
	. "\n LEFT JOIN #__components AS com ON com.id = m.componentid AND m.type = 'components'"
	. "\n WHERE m.menutype = '$a'"
	. "\n AND m.published != -2"
	. $filter
	. $order
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	// establish the hierarchy of the menu
	$children = array();
	// first pass - collect children
	foreach ($rows as $v ) {
		$pt = $v->parent;
		$list = @$children[$pt] ? $children[$pt] : array();
		array_push( $list, $v );
		$children[$pt] = $list;
	}
	// second pass - get an indent list of the items
	$list = mosTreeRecurse( 0, '', array(), $children, max( 0, $levellimit-1 ) );
	// eventually only pick out the searched items.
	if ($search) {
		$list1 = array();

		foreach ($search_rows as $sid ) {
			foreach ($list as $item) {
				if ($item->id == $sid) {
					$list1[] = $item;
				}
			}
		}
		// replace full list with found items
		$list = $list1;
	}

	$total = count( $list );

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	// get list of Levels for dropdown filter
	$lists['level'] = mosHTML::integerSelectList( 1, 20, 1, 'levellimit', 'size="1" class="inputbox" onchange="document.adminForm.submit();"', $levellimit );

	// slice out elements based on limits
	$list = array_slice( $list, $pageNav->limitstart, $pageNav->limit );

	$i = 0;
	foreach ( $list as $mitem ) {
		$edit = '#';
		switch ( $mitem->type ) {
			case 'separator':
			case 'component_item_link':
				break;

			case 'url':
				if ( eregi( 'index.php\?', $mitem->link ) ) {
					if ( !eregi( 'Itemid=', $mitem->link ) ) {
						$mitem->link .= '&amp;Itemid='. $mitem->id;
					}
				}
				break;

			case 'newsfeed_link':
				$edit = 'index2.php?option=com_newsfeeds&amp;task=editA&amp;id=' . $mitem->componentid;
				$list[$i]->descrip 	= $_LANG->_( 'Edit this Newsfeed' );
				$mitem->link .= '&Itemid='. $mitem->id;
				break;

			case 'contact_item_link':
				$edit = 'index2.php?option=com_contact&amp;task=editA&amp;id=' . $mitem->componentid;
				$list[$i]->descrip 	= $_LANG->_( 'Edit this Contact' );
				$mitem->link .= '&amp;Itemid='. $mitem->id;
				break;

			case 'content_item_link':
				$edit = 'index2.php?option=com_content&amp;task=edit&amp;id=' . $mitem->componentid;
				$list[$i]->descrip 	= $_LANG->_( 'Edit this Content' );
				break;

			case 'content_typed':
				$edit = 'index2.php?option=com_typedcontent&amp;task=edit&amp;id='. $mitem->componentid;
				$list[$i]->descrip 	= $_LANG->_( 'Edit this Static Content' );
				break;

			default:
				$mitem->link .= '&amp;Itemid='. $mitem->id;
				break;
		}
		$list[$i]->link = $mitem->link;
		$list[$i]->edit = $edit;
		$i++;
	}

	// pulls name and description from menu type xml
	$i = 0;
	foreach ( $list as $row ) {
		$row = ReadMenuXML( $row->type, $row->com_name );
		$list[$i]->type 	= $row[0];
		if ( !isset( $list[$i]->descrip ) ) {
			$list[$i]->descrip = $row[1];
		} else {
			$list[$i]->descrip = $row[1]. '<br/><br/>'. $list[$i]->descrip;
		}
		$i++;
	}

	// get list of State for dropdown filter
	$javascript 	= 'onchange="document.adminForm.submit();"';
	$lists['state']	= mosAdminHTML::stateList( 'filter_state', $filter_state, $javascript );

	// get list of Access for dropdown filter
	$javascript 		= 'onchange="document.adminForm.submit();"';
	$lists['access']	= mosAdminHTML::accessList( 'filter_access', $filter_access, $javascript );

	$search = stripslashes( $search );

	mosFS::load( '@class', 'com_menus' );
	mosMenuFactory::menutreeQueries( $lists );

	mosFS::load( '/administrator/includes/admin.php' );

	HTML_menusections::showMenusections( $list, $pageNav, $search, $menutype, $option, $lists );
}

function trashView() {
	global $database, $mainframe, $mosConfig_list_limit;
	global $option;

	mosFS::load( '@pageNavigationAdmin' );

	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'm.menutype' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'm.menutype' );

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'm.menutype' ) ) {
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

	// table column ordering
	$order = "\n ORDER BY $tOrder $tOrderDir, m.menutype, m.name";

	$query = "SELECT COUNT(*)"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n WHERE m.published = -2"
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// Query menu items
	$query = "SELECT m.*"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n WHERE m.published = -2"
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

	mosFS::load( '@class', 'com_menus' );
	mosMenuFactory::menutreeQueries( $lists );

	HTML_menusections::trashShow( $rows, $lists, $pageNav, $option );
}

/**
 * Compiles a list of the items you have selected to permanently delte
 */
function trashDeleteConfirm() {
	global $database, $mainframe;
	global $_LANG;

	$cid	= mosGetParam( $_POST, 'cid', null );
	mosArrayToInts( $cid, 0 );

	if ( ( !is_array( $cid ) || count( $cid ) < 1 ) ) {
		mosErrorAlert( $_LANG->_( 'Select an item to Delete' ) );
	}

	// seperate contentids
	$cids = array();
	if ( count( $cid ) > 0 ) {
		$cids = implode( ',', $cid );
	}

	if ( $cids ) {
		// Content Items query
		$query = "SELECT a.name"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.id IN ( $cids ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id 	= $cid;
		$type 	= 'menu';
	}

	HTML_menusections::trashDelete( 'com_menus', $id, $items, $type );
}

/**
 * Permanently deletes the selected list of trash items
 */
function trashDelete( ) {
	global $database, $mainframe;
	global $_LANG;

	$cid	= mosGetParam( $_POST, 'cid', null );
	mosArrayToInts( $cid, 0 );
	$type 	= is_string( mosGetParam( $_POST, 'type', array(0) ) );

	$total 	= count( $cid );

	if ( $type == 'menu' ) {
		$obj 	= new mosMenu( $database );
		foreach ( $cid as $id ) {
			$id = intval( $id );
			$obj->delete( $id );
		}
	}

	$msg = $total .' '. $_LANG->_( 'Item(s) successfully Deleted' );
	mosRedirect( 'index2.php?option=com_menus&task=trashview', $msg );
}

/**
 * Compiles a list of the items you have selected to permanently delte
 */
function trashRestoreConfirm( ) {
	global $database, $mainframe;
	global $_LANG;

	$cid	= mosGetParam( $_POST, 'cid', null );
	mosArrayToInts( $cid, 0 );

	if ( ( !is_array( $cid ) || count( $cid ) < 1 ) ) {
		mosErrorAlert( $_LANG->_( 'Select an item to Restore' ) );
	}

	// seperate contentids
	$cids = array();
	if ( count( $cid ) > 0 ) {
		$cids = implode( ',', $cid );
	}


	if ( $cids ) {
		// Content Items query
		$query = 	"SELECT a.name"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.id IN ( $cids ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$id 	= $cid;
		$type 	= 'menu';
	}

	HTML_menusections::trashRestore( 'com_menus', $id, $items, $type );
}

/**
 * Restores items selected to normal - restores to an unpublished state
*/
function trashRestore( ) {
	global $database;
	global $_LANG;

	$cid	= mosGetParam( $_POST, 'cid', null );
	mosArrayToInts( $cid, 0 );
	$type 	= mosGetParam( $_POST, 'type', array(0) );

	$total 	= count( $cid );

	// restores to an unpublished state
	$state 		= 0;
	$ordering 	= 9999;

	//seperate contentids
	$cids 		= implode( ',', $cid );

	if ( $type == 'menu' ) {
		$query = 	"UPDATE #__menu"
		. "\n SET published = '$state', ordering = '9999'"
		. "\n WHERE id IN ( $cids )"
		;
	}
	$database->setQuery( $query );
	if ( !$database->query() ) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	$msg = $total .' '. $_LANG->_( 'Item(s) successfully Restored' );
	mosRedirect( 'index2.php?option=com_menus&task=trashview', $msg );
}

/**
* Displays a selection list for menu item types
*/
function addMenuItem( &$cid, $menutype, $option, $task ) {
	global $mosConfig_absolute_path, $mainframe;

	$mainframe->set('disableMenu', true);

	$types 		= array();

	// list of directories
	$dirs 	= mosReadDirectory( $mosConfig_absolute_path .'/administrator/components/com_menus' );

	// load files for menu types
	foreach ( $dirs as $dir ) {
		// needed within menu type .php files
		$type 	= $dir;
		$dir 	= $mosConfig_absolute_path .'/administrator/components/com_menus/'. $dir;
		if ( is_dir( $dir ) ) {
			$files = mosReadDirectory( $dir, ".\.menu\.php$" );
			foreach ($files as $file) {
				require_once( "$dir/$file" );
				// type of menu type
				$types[]->type = $type;
			}
		}
	}

	$i = 0;
	foreach ( $types as $type ) {
		// pulls name and description from menu type xml
		$row = ReadMenuXML( $type->type );
		$types[$i]->name 	= $row[0];
		$types[$i]->descrip = $row[1];
		$types[$i]->group 	= $row[2];
		$i++;
	}

	// sort array of objects alphabetically by name of menu type
	SortArrayObjects( $types, 'name', 1 );

	// split into Content
	$i = 0;
	foreach ( $types as $type ) {
		if ( strstr( $type->group, 'Content' ) ) {
			$types_content[] = $types[$i];
		}
		$i++;
	}

	// split into Links
	$i = 0;
	foreach ( $types as $type ) {
		if ( strstr( $type->group, 'Link' ) ) {
			$types_link[] = $types[$i];
		}
		$i++;
	}

	// split into Component
	$i = 0;
	foreach ( $types as $type ) {
		if ( strstr( $type->group, 'Component' ) ) {
			$types_component[] = $types[$i];
		}
		$i++;
	}

	// split into Other
	$i = 0;
	foreach ( $types as $type ) {
		if ( strstr( $type->group, 'Other' ) || !$type->group ) {
			$types_other[] = $types[$i];
		}
		$i++;
	}

	HTML_menusections::addMenuItem( $cid, $menutype, $option, $types_content, $types_component, $types_link, $types_other );
}


/**
* Generic function to save the menu
*/
function saveMenu( $option, $task='save' ) {
	global $database;

	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
	    $txt = array();
	    foreach ($params as $k=>$v) {
		   $txt[] = "$k=$v";
		}
		$_POST['params'] = mosParameters::textareaHandling( $txt );
	}

	$row = new mosMenu( $database );

	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

	$row->menutype = stripslashes( $row->menutype );

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	$row->updateOrder( "menutype='$row->menutype' AND parent='$row->parent'" );

	$msg = 'Menu item Saved';
	switch ( $task ) {
		case 'apply':
			mosRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype .'&task=edit&id='. $row->id , $msg );
			break;

		case 'save':
		default:
			mosRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype, $msg );
			break;
	}
}

/**
* Publishes or Unpublishes one or more menu sections
* @param database A database connector object
* @param string The name of the category section
* @param array An array of id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishMenuSection( $cid=null, $publish=1 ) {
	global $database, $mosConfig_absolute_path;
	global $_LANG;

	if ( count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to' ) .' '. ($publish ? $_LANG->_( 'publish' ) : $_LANG->_( 'unpublish' ) ) );
	}

	$menu = new mosMenu( $database );
	foreach ($cid as $id) {
		$menu->load( $id );
		$menu->published = $publish;

		if (!$menu->check()) {
			mosErrorAlert( $menu->getError() );
		}
		if (!$menu->store()) {
			mosErrorAlert( $menu->getError() );
		}

		if ($menu->type) {
			$database = &$database;
			$task = $publish ? $_LANG->_( 'publish' ) : $_LANG->_( 'unpublish' );
			require( $mosConfig_absolute_path . '/administrator/components/com_menus/' . $menu->type . '/' . $menu->type . '.menu.php' );
		}
	}
	return null;
}

/**
* Trashes a menu record
*/
function TrashMenuSection( $cid=NULL ) {
	global $database;
	global $_LANG;

	if ( count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to Trash' ) );
	}

	$state = "-2";
	//seperate contentids
	$cids = implode( ',', $cid );
	$query = 	"UPDATE #__menu"
	. "\n SET published = '$state', ordering = '0'"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	$total = count( $cid );
	$msg = $total ." ". $_LANG->_( 'Item(s) sent to the Trash' );
	return $msg;
}

/**
* Cancels an edit operation
*/
function cancelMenu( $option ) {
	global $database;

	$menu = new mosMenu( $database );
	$menu->bind( $_POST );
	$menuid = mosGetParam( $_POST, 'menuid', 0 );
	if ( $menuid ) {
		$menu->id = $menuid;
	}
	$menu->checkin();

	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menu->menutype );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderMenu( $uid, $inc, $option ) {
	global $database;

	$row = new mosMenu( $database );
	$row->load( $uid );
	$row->move( $inc, 'menutype="'. $row->menutype .'" AND parent="'. $row->parent .'"' );

	mosRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype );
}


/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option, $menutype ) {
	global $database;

	$menu = new mosMenu( $database );
	$menu->load( $uid );
	$menu->access = $access;

	if (!$menu->check()) {
		mosErrorAlert( $menu->getError() );
	}
	if (!$menu->store()) {
		mosErrorAlert( $menu->getError() );
	}

	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menutype );
}

/**
* Form for moving item(s) to a specific menu
*/
function moveMenu( $option, $cid, $menutype ) {
	global $database;
	global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to move' ) );
	}
	if ( !$menutype ) {
		mosErrorAlert( $_LANG->_( 'Select a item to move' ) );
	}

	## query to list selected menu items
	$cids = implode( ',', $cid );
	$query = "SELECT a.name FROM #__menu AS a WHERE a.id IN ( ". $cids ." )";
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	## query to choose menu
	$query = "SELECT a.params FROM #__modules AS a WHERE a.module = 'mod_mainmenu' ORDER BY a.title";
	$database->setQuery( $query );
	$modules = $database->loadObjectList();

	foreach ( $modules as $module) {
		$params = mosParseParams( $module->params );
		// adds menutype to array
		$type = trim( @$params->menutype );
		$menu[] = mosHTML::makeOption( $type, $type );
	}
	// build the html select list
	$MenuList = mosHTML::selectList( $menu, 'menu', 'class="inputbox" size="10"', 'value', 'text', null );

	HTML_menusections::moveMenu( $option, $cid, $MenuList, $items, $menutype );
}

/**
* Add all descendants to list of meni id's
*/
function addDescendants( $id, &$cid ) {
	global $database;

	$query = "SELECT id"
	. "\n FROM #__menu"
	. "\n WHERE parent = $id"
	;
    $database->setQuery( $query );
    $rows = $database->loadObjectList();
    if ($database->getErrorNum()) {
		mosErrorAlert( $database->getErrorMsg() );
    } // if
	foreach ($rows as $row) {
		$found = false;
		foreach ($cid as $idx)
			if ($idx == $row->id) {
				$found = true;
				break;
			} // if
		if (!$found) $cid[] = $row->id;
		addDescendants($row->id, $cid);
	} // foreach
} // addDescendants

/**
* Save the item(s) to the menu selected
*/
function moveMenuSave( $option, $cid, $menu, $menutype ) {
	global $database, $my;
	global $_LANG;

	// add all decendants to the list
	foreach ($cid as $id) addDescendants($id, $cid);

	$row = new mosMenu( $database );
	$ordering = 1000000;
	$firstroot = 0;
	foreach ($cid as $id) {
		$row->load( $id );

		// is it moved together with his parent?
		$found = false;
		if ($row->parent != 0)
		   foreach ($cid as $idx)
			  if ($idx == $row->parent) {
				 $found = true;
				 break;
			  } // if
		if (!$found) {
			$row->parent = 0;
			$row->ordering = $ordering++;
			if (!$firstroot) $firstroot = $row->id;
		} // if

		$row->menutype = $menu;
	    if ( !$row->store() ) {
			mosErrorAlert( $row->getError() );
	    } // if
	} // foreach

	if ($firstroot) {
		$row->load( $firstroot );
		$row->updateOrder( "menutype='". $row->menutype ."' AND parent='". $row->parent ."'" );
	} // if

	$msg = count($cid) .' '. $_LANG->_( 'Menu Items moved to' ) .' '. $menu;
	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menutype .'&mosmsg='. $msg );
} // moveMenuSave

/**
* Form for copying item(s) to a specific menu
*/
function copyMenu( $option, $cid, $menutype ) {
	global $database;
	global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to copy' ) );
	}
	if ( !$menutype ) {
		mosErrorAlert( $_LANG->_( 'Select a item to copy' ) );
	}


	## query to list selected menu items
	$cids = implode( ',', $cid );
	$query = "SELECT a.name FROM #__menu AS a WHERE a.id IN ( ". $cids ." )";
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	$menuTypes = mosMenuFactory::getMenuTypes();

	foreach ( $menuTypes as $menuType ) {
		$menu[] = mosHTML::makeOption( $menuType, $menuType );
	}
	// build the html select list
	$MenuList = mosHTML::selectList( $menu, 'menu', 'class="inputbox" size="10"', 'value', 'text', null );

	HTML_menusections::copyMenu( $option, $cid, $MenuList, $items, $menutype );
}

/**
* Save the item(s) to the menu selected
*/
function copyMenuSave( $option, $cid, $menu, $menutype ) {
	global $database;
	global $_LANG;

	$curr = new mosMenu( $database );
	$cidref = array();
	foreach( $cid as $id ) {
		$curr->load( $id );
		$curr->id = NULL;
		if ( !$curr->store() ) {
			mosErrorAlert( $row->getError() );
		}
		$cidref[] = array($id, $curr->id);
	}
	foreach ( $cidref as $ref ) {
		$curr->load( $ref[1] );
		if ($curr->parent!=0) {
			$found = false;
			foreach ( $cidref as $ref2 )
				if ($curr->parent == $ref2[0]) {
					$curr->parent = $ref2[1];
					$found = true;
					break;
				} // if
			if (!$found && $curr->menutype!=$menu)
				$curr->parent = 0;
		} // if
		$curr->menutype = $menu;
		$curr->ordering = '9999';
		if ( !$curr->store() ) {
			mosErrorAlert( $curr->getError() );
		}
		$curr->updateOrder( "menutype='". $curr->menutype ."' AND parent='". $curr->parent ."'" );
	} // foreach
	$msg = count( $cid ) .' '. $_LANG->_( 'Menu Items Copied to' ) .' '. $menu;
	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menutype .'&mosmsg='. $msg );
}

function ReadMenuXML( $type, $component=-1 ) {
	global $mosConfig_absolute_path;

	// XML library
	require_once( $mosConfig_absolute_path . '/includes/domit/xml_domit_lite_include.php' );
	// xml file for module
	$xmlfile = $mosConfig_absolute_path .'/administrator/components/com_menus/'. $type .'/'. $type .'.xml';
	$xmlDoc = new DOMIT_Lite_Document();
	$xmlDoc->resolveErrors( true );

	if ($xmlDoc->loadXML( $xmlfile, false, true )) {
		$root = &$xmlDoc->documentElement;

		if ( $element->getTagName() == 'mosparams' && ( $element->getAttribute( 'type' ) == 'component' || $element->getAttribute( 'type' ) == 'menu' ) ) {
			// Menu Type Name
			$element 	= &$root->getElementsByPath( 'name', 1 );
			$name 		= $element ? trim( $element->getText() ) : '';
			// Menu Type Description
			$element 	= &$root->getElementsByPath( 'description', 1 );
			$descrip 	= $element ? trim( $element->getText() ) : '';
			// Menu Type Group
			$element 	= &$root->getElementsByPath( 'group', 1 );
			$group 		= $element ? trim( $element->getText() ) : '';
		}
	}

	if ( ( $component <> -1 ) && ( $name == 'Component') ) {
			$name .= ' - '. $component;
	}

	$row[0]	= $name;
	$row[1] = addslashes( $descrip );
	$row[2] = $group;

	return $row;
}

function saveOrder( &$cid, $menutype ) {
	global $database;
	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row		= new mosMenu( $database );
	$conditions = array();

    // update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				mosErrorAlert( $row->getError() );
			}
			// remember to updateOrder this group
			$condition = "menutype = '$menutype' AND parent = '$row->parent' AND published >= 0";
			$found = false;
			foreach ( $conditions as $cond )
				if ($cond[1]==$condition) {
					$found = true;
					break;
				} // if
			if (!$found) $conditions[] = array($row->id, $condition);
		} // for
	} // for

	// execute updateOrder for each group
	foreach ( $conditions as $cond ) {
		$row->load( $cond[0] );
		$row->updateOrder( $cond[1] );
	} // foreach

	$msg 	= $_LANG->_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype, $msg );
} // saveOrder

function loadMenuFiles( $type ) {
	$basePath 	= '/administrator/components/com_menus/';

	mosFS::load( $basePath . $type .'/'. $type .'.menu.html.php' );
	mosFS::load( $basePath . $type .'/'. $type .'.class.php' );
	mosFS::load( $basePath . $type .'/'. $type .'.menu.php' );
}

function checkin( $id, $menutype ) {
	global $database;
	global $_LANG;

	$row = new mosMenu( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype, $msg );
}
?>