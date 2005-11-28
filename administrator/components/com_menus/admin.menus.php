<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
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

$id 		= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
$type 		= mosGetParam( $_REQUEST, 'type', false );
$menutype 	= mosGetParam( $_REQUEST, 'menutype', 'mainmenu' );
$task 		= mosGetParam( $_REQUEST, 'task', '' );
$access 	= mosGetParam( $_POST, 'access', '' );
$utaccess	= mosGetParam( $_POST, 'utaccess', '' );
$ItemName	= mosGetParam( $_POST, 'ItemName', '' );
$menu 		= mosGetParam( $_POST, 'menu', '' );
$cid 		= mosGetParam( $_POST, 'cid', array(0) );

$path 		= JPATH_ADMINISTRATOR . '/components/com_menus/';

if (!is_array( $cid )) {
	$cid = array(0);
}


switch ($task) {
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
			require_once( $path . $menu->type .'/'. $menu->type .'.menu.php' );
		}
		break;

	case 'save':
	case 'apply':
		require_once( $path . $type .'/'. $type .'.menu.php' );
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

	default:
		$type = mosGetParam( $_REQUEST, 'type' );
		if ($type) {
			// adding a new item - type selection form
			require_once( $path . $type .'/'. $type .'.menu.php' );
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

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart$menutype", 'limitstart', 0 );
	$levellimit = $mainframe->getUserStateFromRequest( "view{$option}limit$menutype", 'levellimit', 10 );
	$search 	= $mainframe->getUserStateFromRequest( "search{$option}$menutype", 'search', '' );
	$search 	= $database->getEscaped( trim( strtolower( $search ) ) );

	// select the records
	// note, since this is a tree we have to do the limits code-side
	if ($search) {
		$query = "SELECT m.id"
		. "\n FROM #__menu AS m"
		//. "\n LEFT JOIN #__content AS c ON c.id = m.componentid AND type='content_typed'"
		. "\n WHERE menutype = '$menutype'"
		. "\n AND LOWER( m.name ) LIKE '%" . strtolower( $search ) . "%'"
		;
		$database->setQuery( $query );
		$search_rows = $database->loadResultArray();
	}

	$query = "SELECT m.*, u.name AS editor, g.name AS groupname, c.publish_up, c.publish_down, com.name AS com_name"
	. "\n FROM #__menu AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = m.access"
	. "\n LEFT JOIN #__content AS c ON c.id = m.componentid AND m.type = 'content_typed'"
	. "\n LEFT JOIN #__components AS com ON com.id = m.componentid AND m.type = 'components'"
	. "\n WHERE m.menutype = '$menutype'"
	. "\n AND m.published != -2"
	. "\n ORDER BY parent, ordering"
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

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$levellist = mosHTML::integerSelectList( 1, 20, 1, 'levellimit', 'size="1" onchange="document.adminForm.submit();"', $levellimit );

	// slice out elements based on limits
	$list = array_slice( $list, $pageNav->limitstart, $pageNav->limit );

	$i = 0;
	foreach ( $list as $mitem ) {
		$edit = '';
		switch ( $mitem->type ) {
			case 'separator':
			case 'component_item_link':
				break;

			case 'url':
				if ( eregi( 'index.php\?', $mitem->link ) ) {
					if ( !eregi( 'Itemid=', $mitem->link ) ) {
						$mitem->link .= '&Itemid='. $mitem->id;
					}
				}
				break;

			case 'newsfeed_link':
				$edit = 'index2.php?option=com_newsfeeds&task=edit&hidemainmenu=1A&id=' . $mitem->componentid;
				$list[$i]->descrip 	= JText::_( 'Edit this Newsfeed' );
				$mitem->link .= '&Itemid='. $mitem->id;
				break;

			case 'contact_item_link':
				$edit = 'index2.php?option=com_contact&task=editA&hidemainmenu=1&id=' . $mitem->componentid;
				$list[$i]->descrip 	= JText::_( 'Edit this Contact' );
				$mitem->link .= '&Itemid='. $mitem->id;
				break;

			case 'content_item_link':
				$edit = 'index2.php?option=com_content&task=edit&hidemainmenu=1&id=' . $mitem->componentid;
				$list[$i]->descrip 	= JText::_( 'Edit this Content' );
				break;

			case 'content_typed':
				$edit = 'index2.php?option=com_typedcontent&task=edit&hidemainmenu=1&id='. $mitem->componentid;
				$list[$i]->descrip 	= JText::_( 'Edit this Static Content' );
				break;

			default:
				$mitem->link .= '&Itemid='. $mitem->id;
				break;
		}
		$list[$i]->link = $mitem->link;
		$list[$i]->edit = $edit;
		$i++;
	}

	$i = 0;
	foreach ( $list as $row ) {
		// pulls name and description from menu type xml
		$row = ReadMenuXML( $row->type, $row->com_name );
		$list[$i]->type 	= $row[0];
		if (!isset($list[$i]->descrip)) $list[$i]->descrip = $row[1];
		$i++;
	}

	HTML_menusections::showMenusections( $list, $pageNav, $search, $levellist, $menutype, $option );
}

/**
* Displays a selection list for menu item types
*/
function addMenuItem( &$cid, $menutype, $option, $task ) {
	$types 	= array();

	// list of directories
	$dirs 	= mosReadDirectory( JPATH_ADMINISTRATOR .'/components/com_menus' );

	// load files for menu types
	foreach ( $dirs as $dir ) {
		// needed within menu type .php files
		$type 	= $dir;
		$dir 	= JPATH_ADMINISTRATOR .'/components/com_menus/'. $dir;
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

	// split into Submit
	$i = 0;
	foreach ( $types as $type ) {
		if ( strstr( $type->group, 'Submit' ) || !$type->group ) {
			$types_submit[] = $types[$i];
		}
		$i++;
	}

	HTML_menusections::addMenuItem( $cid, $menutype, $option, $types_content, $types_component, $types_link, $types_other, $types_submit );
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
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$row->name = ampReplace( $row->name );

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->updateOrder( "menutype = '$row->menutype' AND parent = $row->parent" );

	$msg = JText::_( 'Menu item Saved' );
	switch ( $task ) {
		case 'apply':
			mosRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype .'&task=edit&id='. $row->id . '&hidemainmenu=1' , $msg );
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
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		return JText::_( 'Select an item to' ) . ($publish ? JText::_( 'publish' ) : JText::_( 'unpublish' ) );
	}

	$menu = new mosMenu( $database );
	foreach ($cid as $id) {
		$menu->load( $id );
		$menu->published = $publish;

		if (!$menu->check()) {
			return $menu->getError();
		}
		if (!$menu->store()) {
			return $menu->getError();
		}

		if ($menu->type) {
			$database = &$database;
			$task = $publish ? 'publish' : 'unpublish';
			// $type value is used in *.menu.php
			$type = $menu->type;
			require_once( JPATH_ADMINISTRATOR . '/components/com_menus/' . $type . '/' . $type . '.menu.php' );
		}
	}
	return null;
}

/**
* Trashes a menu record
*/
function TrashMenuSection( $cid=NULL ) {
	global $database;

	$state = "-2";
	//seperate contentids
	$cids = implode( ',', $cid );
	$query = "UPDATE #__menu"
	. "\n SET published = $state, ordering = 0"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$total = count( $cid );

	$msg = sprintf( JText::_( 'Item(s) sent to the Trash' ), $total );
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
/*
	if ( $menu->type == 'content_typed' ) {
		$contentid = mosGetParam( $_POST, 'id', 0 );
		$content = new mosContent( $database );
		$content->load( $contentid );
		$content->checkin();
	}
*/
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
	$row->move( $inc, "menutype = '$row->menutype' AND parent = $row->parent" );

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
		return $menu->getError();
	}
	if (!$menu->store()) {
		return $menu->getError();
	}

	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menutype );
}

/**
* Form for moving item(s) to a specific menu
*/
function moveMenu( $option, $cid, $menutype ) {
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to move', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	## query to list selected menu items
	$cids = implode( ',', $cid );
	$query = "SELECT a.name"
	. "\n FROM #__menu AS a"
	. "\n WHERE a.id IN ( $cids )"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	## query to choose menu
	$query = "SELECT a.params"
	. "\n FROM #__modules AS a"
	. "\n WHERE a.module = 'mod_mainmenu'"
	. "\n ORDER BY a.title"
	;
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
function addDescendants($id, &$cid) {
	global $database;

	$query = "SELECT id"
	. "\n FROM #__menu"
	. "\n WHERE parent = $id"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
		exit();
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
			echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
			exit();
		} // if
	} // foreach

	if ($firstroot) {
		$row->load( $firstroot );
		$row->updateOrder( "menutype = '$row->menutype' AND parent = $row->parent" );
	} // if

	$msg = sprintf( JText::_( 'Menu Items moved to' ), count($cid), $menu );
	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menutype .'&mosmsg='. $msg );
} // moveMenuSave

/**
* Form for copying item(s) to a specific menu
*/
function copyMenu( $option, $cid, $menutype ) {
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to move', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	## query to list selected menu items
	$cids = implode( ',', $cid );
	$query = "SELECT a.name"
	. "\n FROM #__menu AS a"
	. "\n WHERE a.id IN ( $cids )"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	$menuTypes 	= mosAdminMenus::menutypes();

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

	$curr = new mosMenu( $database );
	$cidref = array();
	foreach( $cid as $id ) {
		$curr->load( $id );
		$curr->id = NULL;
		if ( !$curr->store() ) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
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
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$curr->updateOrder( "menutype = '$curr->menutype' AND parent = $curr->parent" );
	} // foreach
	$msg = sprintf( JText::_( 'Menu Items Copied to' ), count( $cid ), $menu );
	mosRedirect( 'index2.php?option='. $option .'&menutype='. $menutype .'&mosmsg='. $msg );
}

function ReadMenuXML( $type, $component=-1 ) {
	// xml file for module
	$xmlfile = JPATH_ADMINISTRATOR .'/components/com_menus/'. $type .'/'. $type .'.xml';
	$xmlDoc =& JFactory::getXMLParser();
	$xmlDoc->resolveErrors( true );

	if ($xmlDoc->loadXML( $xmlfile, false, true )) {
		$root = &$xmlDoc->documentElement;

		if ( $root->getTagName() == 'mosinstall' && ( $root->getAttribute( 'type' ) == 'component' || $root->getAttribute( 'type' ) == 'menu' ) ) {
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
	$row[1] = $descrip;
	$row[2] = $group;

	return $row;
}

function saveOrder( &$cid, $menutype ) {
	global $database;

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
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
				exit();
			}
			// remember to updateOrder this group
			$condition = "menutype = '$menutype' AND parent = $row->parent AND published >= 0";
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

	$msg 	= JText::_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_menus&menutype='. $menutype, $msg );
} // saveOrder
?>
