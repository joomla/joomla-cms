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

// ensure user has access to this function
if (!$acl->acl_check( 'com_menumanager', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );

$menu 		= mosGetParam( $_GET, 'menu', '' );
$task 		= mosGetParam( $_REQUEST, 'task', array(0) );
$type 		= mosGetParam( $_POST, 'type', '' );
$cid 		= mosGetParam( $_POST, 'cid', '' );

switch ($task) {
	case 'new':
		editMenu( $option, '' );
		break;

	case 'edit':
		if ( !$menu ) {
			$menu = $cid[0];
		}
		editMenu( $option, $menu );
		break;

	case 'savemenu':
		saveMenu();
		break;

	case 'deleteconfirm':
		deleteconfirm( $option, $cid[0] );
		break;

	case 'deletemenu':
		deleteMenu( $option, $cid, $type );
		break;

	case 'copyconfirm':
		copyConfirm( $option, $cid[0] );
		break;

	case 'copymenu':
		copyMenu( $option, $cid, $type );
		break;

	case 'cancel':
		cancelMenu( $option );
		break;

	default:
		showMenu( $option );
		break;
}


/**
* Compiles a list of menumanager items
*/
function showMenu( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{". $option ."}limitstart", 'limitstart', 0 );

	$menuTypes 	= mosAdminMenus::menutypes();
	$total		= count( $menuTypes );
	$i			= 0;
	foreach ( $menuTypes as $a ) {
		$menus[$i]->type 		= $a;

		// query to get number of modules for menutype
		$query = "SELECT count( id )"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		. "\n AND params LIKE '%$a%'"
		;
		$database->setQuery( $query );
		$modules = $database->loadResult();

		if ( !$modules ) {
			$modules = '-';
		}
		$menus[$i]->modules = $modules;

		$i++;
	}

	// Query to get published menu item counts
	$query = "SELECT a.menutype, count( a.menutype ) as num"
	. "\n FROM #__menu AS a"
	. "\n WHERE a.published = 1"
	. "\n GROUP BY a.menutype"
	. "\n ORDER BY a.menutype"
	;
	$database->setQuery( $query );
	$published = $database->loadObjectList();

	// Query to get unpublished menu item counts
	$query = "SELECT a.menutype, count( a.menutype ) as num"
	. "\n FROM #__menu AS a"
	. "\n WHERE a.published = 0"
	. "\n GROUP BY a.menutype"
	. "\n ORDER BY a.menutype"
	;
	$database->setQuery( $query );
	$unpublished = $database->loadObjectList();

	// Query to get trash menu item counts
	$query = "SELECT a.menutype, count( a.menutype ) as num"
	. "\n FROM #__menu AS a"
	. "\n WHERE a.published = -2"
	. "\n GROUP BY a.menutype"
	. "\n ORDER BY a.menutype"
	;
	$database->setQuery( $query );
	$trash = $database->loadObjectList();

	for( $i = 0; $i < $total; $i++ ) {
		// adds published count
		foreach ( $published as $count ) {
			if ( $menus[$i]->type == $count->menutype ) {
				$menus[$i]->published = $count->num;
			}
		}
		if ( @!$menus[$i]->published ) {
			$menus[$i]->published = '-';
		}
		// adds unpublished count
		foreach ( $unpublished as $count ) {
			if ( $menus[$i]->type == $count->menutype ) {
				$menus[$i]->unpublished = $count->num;
			}
		}
		if ( @!$menus[$i]->unpublished ) {
			$menus[$i]->unpublished = '-';
		}
		// adds trash count
		foreach ( $trash as $count ) {
			if ( $menus[$i]->type == $count->menutype ) {
				$menus[$i]->trash = $count->num;
			}
		}
		if ( @!$menus[$i]->trash ) {
			$menus[$i]->trash = '-';
		}
	}

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	HTML_menumanager::show( $option, $menus, $pageNav );
}


/**
* Edits a mod_mainmenu module
*
* @param option	options for the edit mode
* @param cid	menu id
*/
function editMenu( $option, $menu ) {
	global $database;

	if( $menu ) {
		$row->menutype 	= $menu;
	} else {
		$row = new mosModule( $database );
		// setting default values
		$row->menutype 	= '';
		$row->iscore 	= 0;
		$row->published = 0;
		$row->position 	= 'left';
		$row->module 	= 'mod_mainmenu';
	}

	HTML_menumanager::edit( $row, $option );
}

/**
* Creates a new mod_mainmenu module, which makes the menu visible
* this is a workaround until a new dedicated table for menu management can be created
*/
function saveMenu() {
	global $database;
	;

	$menutype 		= mosGetParam( $_POST, 'menutype', '' );
	$old_menutype 	= mosGetParam( $_POST, 'old_menutype', '' );
	$new			= mosGetParam( $_POST, 'new', 1 );

	// block to stop renaming of 'mainmenu' menutype
	if ( $old_menutype == 'mainmenu' ) {
		if ( $menutype <> 'mainmenu' ) {
			echo "<script> alert('". JText::_( 'WARNMAINMENU' ) ."'); window.history.go(-1); </script>\n";
			exit;
		}
	}

	// check for unique menutype for new menus
	$query = "SELECT params"
	. "\n FROM #__modules"
	. "\n WHERE module = 'mod_mainmenu'"
	;
	$database->setQuery( $query );
	$menus = $database->loadResultArray();
	foreach ( $menus as $menu ) {
		$params = mosParseParams( $menu );
		if ( $params->menutype == $menutype ) {
			echo "<script> alert('". JText::_( 'ERRORMENUNAMEEXISTS' ) ."'); window.history.go(-1); </script>\n";
			exit;
		}
	}

	switch ( $new ) {
		case 1:
		// create a new module for the new menu
			$row = new mosModule( $database );
			$row->bind( $_POST );

			$row->params = 'menutype='. $menutype;

			// check then store data in db
			if (!$row->check()) {
				echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
				exit();
			}
			if (!$row->store()) {
				echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
				exit();
			}

			$row->checkin();
			$row->updateOrder( "position='". $row->position ."'" );

			// module assigned to show on All pages by default
			// ToDO: Changed to become a Joomla! db-object
			$query = "INSERT INTO #__modules_menu VALUES ( $row->id, 0 )";
			$database->setQuery( $query );
			if ( !$database->query() ) {
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
				exit();
			}

			$msg = JText::_( 'New Menu created' ) .' [ '. $menutype .' ]';
			break;

		default:
		// change menutype being of all mod_mainmenu modules calling old menutype
			$query = "SELECT id"
			. "\n FROM #__modules"
			. "\n WHERE module = 'mod_mainmenu'"
			. "\n AND params LIKE '%$old_menutype%'"
			;
			$database->setQuery( $query );
			$modules = $database->loadResultArray();

			foreach ( $modules as $module ) {
				$row = new mosModule( $database );
				$row->load( $module );

				$save = 0;
				$params = mosParseParams( $row->params );
				if ( $params->menutype == $old_menutype ) {
					$params->menutype 	= $menutype;
					$save 				= 1;
				}

				// save changes to module 'menutype' param
				if ( $save ) {
					$txt = array();
					foreach ( $params as $k=>$v) {
						$txt[] = "$k=$v";
					}
					$row->params = implode( "\n", $txt );

					// check then store data in db
					if ( !$row->check() ) {
						echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
						exit();
					}
					if ( !$row->store() ) {
						echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
						exit();
					}

					$row->checkin();
				}
			}

		// change menutype of all menuitems using old menutype
			if ( $menutype <> $old_menutype ) {
				$query = "UPDATE #__menu"
				. "\n SET menutype = '$menutype'"
				. "\n WHERE menutype = '$old_menutype'"
				;
				$database->setQuery( $query );
				$database->query();
			}

			$msg = JText::_( 'Menu Items & Modules updated' );
			break;
	}

	mosRedirect( 'index2.php?option=com_menumanager', $msg );
}

/**
* Compiles a list of the items you have selected to permanently delte
*/
function deleteConfirm( $option, $type ) {
	global $database;
	;

	if ( $type == 'mainmenu' ) {
		echo "<script> alert('". JText::_( 'WARNDELMAINMENU' ) ."'); window.history.go(-1); </script>\n";
		exit();
	}

	// list of menu items to delete
	$query = "SELECT a.name, a.id"
	. "\n FROM #__menu AS a"
	. "\n WHERE ( a.menutype IN ( '$type' ) )"
	. "\n ORDER BY a.name"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	// list of modules to delete
	$query = "SELECT id"
	. "\n FROM #__modules"
	. "\n WHERE module = 'mod_mainmenu'"
	. "\n AND params LIKE '%$type%'"
	;
	$database->setQuery( $query );
	$mods = $database->loadResultArray();

	foreach ( $mods as $module ) {
		$row = new mosModule( $database );
		$row->load( $module );

		$params = mosParseParams( $row->params );
		if ( $params->menutype == $type ) {
			$mid[] = $module;
		}
	}

	@$mids = implode( ',', $mid );
	$query = "SELECT id, title"
	. "\n FROM #__modules"
	. "\n WHERE id IN ( $mids )"
	;
	$database->setQuery( $query );
	@$modules = $database->loadObjectList();

	HTML_menumanager::showDelete( $option, $type, $items, $modules );
}

/**
* Deletes menu items(s) you have selected
*/
function deleteMenu( $option, $cid, $type ) {
	global $database;
	;

	if ( $type == 'mainmenu' ) {
		echo "<script> alert('". JText::_( 'WARNDELMAINMENU' ) ."'); window.history.go(-1); </script>\n";
		exit();
	}


	$mids = mosGetParam( $_POST, 'mids', 0 );
	if ( is_array( $mids ) ) {
		$mids = implode( ',', $mids );
	}
	// delete menu items
	$query = "DELETE FROM #__menu"
	. "\n WHERE ( id IN ( $mids ) )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('". $database->getErrorMsg() ."');</script>\n";
		exit;
	}

	if ( is_array( $cid ) ) {
		$cids = implode( ',', $cid );
	} else {
		$cids = $cid;
	}

	// checks whether any modules to delete
	if ( $cids ) {
		// delete modules
		$query = "DELETE FROM #__modules"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if ( !$database->query() ) {
			echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
			exit;
		}
		// delete all module entires in jos_modules_menu
		$query = "DELETE FROM #__modules_menu"
		. "\n WHERE moduleid IN ( $cids )"
		;
		$database->setQuery( $query );
		if ( !$database->query() ) {
			echo "<script> alert('". $database->getErrorMsg() ."');</script>\n";
			exit;
		}

		// reorder modules after deletion
		$mod = new mosModule( $database );
		$mod->ordering = 0;
		$mod->updateOrder( "position='left'" );
		$mod->updateOrder( "position='right'" );
	}

	$msg = JText::_( 'Menu Deleted' );
	mosRedirect( 'index2.php?option=' . $option, $msg );
}


/**
* Compiles a list of the items you have selected to Copy
*/
function copyConfirm( $option, $type ) {
	global $database;

	// Content Items query
	$query = 	"SELECT a.name, a.id"
	. "\n FROM #__menu AS a"
	. "\n WHERE ( a.menutype IN ( '$type' ) )"
	. "\n ORDER BY a.name"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	HTML_menumanager::showCopy( $option, $type, $items );
}


/**
* Copies a complete menu, all its items and creates a new module, using the name speified
*/
function copyMenu( $option, $cid, $type ) {
	global $database;
	;

	$menu_name 		= mosGetParam( $_POST, 'menu_name', 'New Menu' );
	$module_name 	= mosGetParam( $_POST, 'module_name', 'New Module' );

	// check for unique menutype for new menu copy
	$query = "SELECT params"
	. "\n FROM #__modules"
	. "\n WHERE module = 'mod_mainmenu'"
	;
	$database->setQuery( $query );
	$menus = $database->loadResultArray();
	foreach ( $menus as $menu ) {
		$params = mosParseParams( $menu );
		if ( $params->menutype == $menu_name ) {
			echo "<script> alert('". JText::_( 'ERRORMENUNAMEEXISTS' ) ."'); window.history.go(-1); </script>\n";
			exit;
		}
	}

	// copy the menu items
	$mids 		= mosGetParam( $_POST, 'mids', '' );
	$total 		= count( $mids );
	$copy 		= new mosMenu( $database );
	$original 	= new mosMenu( $database );
	sort( $mids );
	$a_ids 		= array();

	foreach( $mids as $mid ) {
		$original->load( $mid );
		$copy 			= $original;
		$copy->id 		= NULL;
		$copy->parent 	= $a_ids[$original->parent];
		$copy->menutype = $menu_name;

		if ( !$copy->check() ) {
			echo "<script> alert('".$copy->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		if ( !$copy->store() ) {
			echo "<script> alert('".$copy->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$a_ids[$original->id] = $copy->id;
	}

	// create the module copy
	$row = new mosModule( $database );
	$row->load( 0 );
	$row->title 	= $module_name;
	$row->iscore 	= 0;
	$row->published = 1;
	$row->position 	= 'left';
	$row->module 	= 'mod_mainmenu';
	$row->params 	= 'menutype='. $menu_name;

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->updateOrder( "position='$row->position'" );
	// module assigned to show on All pages by default
	// ToDO: Changed to become a Joomla! db-object
	$query = "INSERT INTO #__modules_menu VALUES ( $row->id, 0 )";
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = JText::_( 'Copy of Menu' ) .' `'. $type .'` '. JText::_( 'created, consisting of' ) .' '. $total .' '. JText::_( 'items' );
	mosRedirect( 'index2.php?option=' . $option, $msg );
}

/**
* Cancels an edit operation
* @param option	options for the operation
*/
function cancelMenu( $option ) {
	mosRedirect( 'index2.php?option=' . $option . '&task=view' );
}
?>