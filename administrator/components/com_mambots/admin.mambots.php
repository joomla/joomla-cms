<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Mambots
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
if (!$acl->acl_check( 'com_mambots', 'manage', 'users', $my->usertype, 'mambots', 'all' )) {
		mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );

$client = mosGetParam( $_REQUEST, 'client', '' );
$cid 	= mosGetParam( $_POST, 'cid', array(0) );
$id 	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ( $task ) {

	case 'new':
	case 'edit':
		editMambot( $option, $cid[0], $client );
		break;

	case 'editA':
		editMambot( $option, $id, $client );
		break;

	case 'save':
	case 'apply':
		saveMambot( $option, $client, $task );
		break;

	case 'remove':
		removeMambot( $cid, $option, $client );
		break;

	case 'cancel':
		cancelMambot( $option, $client );
		break;

	case 'publish':
	case 'unpublish':
		publishMambot( $cid, ($task == 'publish'), $option, $client );
		break;

	case 'orderup':
	case 'orderdown':
		orderMambot( $cid[0], ($task == 'orderup' ? -1 : 1), $option, $client );
		break;

	case 'accesspublic':
	case 'accessregistered':
	case 'accessspecial':
		accessMenu( $cid[0], $task, $option, $client );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	default:
		viewMambots( $option, $client );
		break;
}

/**
* Compiles a list of installed or defined modules
*/
function viewMambots( $option, $client ) {
	global $database, $mainframe, $mosConfig_list_limit;
	global $mosConfig_absolute_path;
	global $_LANG;

	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$filter_type	= $mainframe->getUserStateFromRequest( "filter_type{$option}{$client}", 'filter_type', 1 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}{$client}", 'search', '' );
	$search 		= $database->getEscaped( trim( strtolower( $search ) ) );

	if ($client == 'admin') {
		$where[] = "m.client_id = '1'";
		$client_id = 1;
	} else {
		$where[] = "m.client_id = '0'";
		$client_id = 0;
	}

	// used by filter
	if ( $filter_type != 1 ) {
		$where[] = "m.folder = '$filter_type'";
	}
	if ( $search ) {
		$where[] = "LOWER( m.name ) LIKE '%$search%'";
	}

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__mambots AS m"
	. ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' )
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( $GLOBALS['mosConfig_absolute_path'] . '/administrator/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT m.*, u.name AS editor, g.name AS groupname"
	. "\n FROM #__mambots AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = m.access"
	. ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' )
	. "\n GROUP BY m.id"
	. "\n ORDER BY m.folder ASC, m.ordering ASC, m.name ASC"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	// get list of Positions for dropdown filter
	$query = "SELECT folder AS value, folder AS text"
	. "\n FROM #__mambots"
	. "\n WHERE client_id = '$client_id'"
	. "\n GROUP BY folder"
	. "\n ORDER BY folder"
	;
	$types[] = mosHTML::makeOption( 1, '- '. $_LANG->_( 'Select Type' ) .' -' );
	$database->setQuery( $query );
	$types 			= array_merge( $types, $database->loadObjectList() );
	$lists['type']	= mosHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_type );

	HTML_modules::showMambots( $rows, $client, $pageNav, $option, $lists, $search );
}

/**
* Saves the module after an edit form submit
*/
function saveMambot( $option, $client, $task ) {
	global $database;
	global $_LANG;

	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ($params as $k=>$v) {
			$txt[] = "$k=$v";
		}

 		$_POST['params'] = mosParameters::textareaHandling( $txt );
	}

	$row = new mosMambot( $database );
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
	if ($client == 'admin') {
		$where = "client_id='1'";
	} else {
		$where = "client_id='0'";
	}
	$row->updateOrder( "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000 AND ( $where )" );

	switch ( $task ) {
		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes to Mambot' ) .': '. $row->name;
			mosRedirect( 'index2.php?option='. $option .'&client='. $client .'&task=editA&hidemainmenu=1&id='. $row->id, $msg );

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved Mambot' ) .': '. $row->name;
			mosRedirect( 'index2.php?option='. $option .'&client='. $client, $msg );
			break;
	}
}

/**
* Compiles information to add or edit a module
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function editMambot( $option, $uid, $client ) {
	global $database, $my, $mainframe;
	global $mosConfig_absolute_path;
	global $_LANG;

	$lists 	= array();
	$row 	= new mosMambot($database);

	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
		mosErrorAlert($_LANG->_( 'The module' ) ." ". $row->title ." ". $_LANG->_( 'DESCBEINGEDITTED' ), "document.location.href='index2.php?option=$option'");
	}

	if ($client == 'admin') {
		$where = "client_id='1'";
	} else {
		$where = "client_id='0'";
	}

	// get list of groups
	if ($row->access == 99 || $row->client_id == 1) {
		$lists['access'] = 'Administrator<input type="hidden" name="access" value="99" />';
	} else {
		// build the html select list for the group access
		$lists['access'] = mosAdminMenus::Access( $row );
	}

	if ($uid) {
		$row->checkout( $my->id );

		if ( $row->ordering > -10000 && $row->ordering < 10000 ) {
			// build the html select list for ordering
			$query = "SELECT ordering AS value, name AS text"
			. "\n FROM #__mambots"
			. "\n WHERE folder = '$row->folder'"
			. "\n AND published > 0"
			. "\n AND $where"
			. "\n AND ordering > -10000"
			. "\n AND ordering < 10000"
			. "\n ORDER BY ordering"
			;
			$order = mosGetOrderingList( $query );
			$lists['ordering'] = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $_LANG->_( 'This mambot cannot be reordered' );
		}
		$lists['folder'] = '<input type="hidden" name="folder" value="'. $row->folder .'" />'. $row->folder;

		// XML library
		require_once( $mosConfig_absolute_path . '/includes/domit/xml_domit_lite_include.php' );
		// xml file for module
		$xmlfile = $mosConfig_absolute_path . '/mambots/' .$row->folder . '/' . $row->element .'.xml';
		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );
		if ($xmlDoc->loadXML( $xmlfile, false, true )) {
			$root = &$xmlDoc->documentElement;
			if ($root->getTagName() == 'mosinstall' && $root->getAttribute( 'type' ) == 'mambot' ) {
				$element = &$root->getElementsByPath( 'description', 1 );
				$row->description = $element ? trim( $element->getText() ) : '';

			}
		}
	} else {
		$row->folder 		= '';
		$row->ordering 		= 999;
		$row->published 	= 1;
		$row->description 	= '';

		$folders = mosReadDirectory( $mosConfig_absolute_path . '/mambots/' );
		$folders2 = array();
		foreach ($folders as $folder) {
			if (is_dir( $mosConfig_absolute_path . '/mambots/' . $folder ) && ( $folder <> 'CVS' ) ) {
				$folders2[] = mosHTML::makeOption( $folder );
			}
		}
		$lists['folder'] = mosHTML::selectList( $folders2, 'folder', 'class="inputbox" size="1"', 'value', 'text', null );
		$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $_LANG->_( 'DESCNEWITEMSDEFAULTLASTPLACE' );
	}

	$lists['published'] = mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

	// get params definitions
	$params = new mosParameters( $row->params, $mainframe->getPath( 'bot_xml', $row->folder.'/'.$row->element ), 'mambot' );

	HTML_modules::editMambot( $row, $lists, $params, $option );
}

/**
* Deletes one or more mambots
*
* Also deletes associated entries in the #__mambots table.
* @param array An array of unique category id numbers
*/
function removeMambot( &$cid, $option, $client ) {
	global $database, $my;
	global $_LANG;

	if (count( $cid ) < 1) {
		echo "<script> alert('". $_LANG->_( 'Select a module to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	mosRedirect( 'index2.php?option=com_installer&element=mambot&client='. $client .'&task=remove&cid[]='. $cid[0] );
}

/**
* Publishes or Unpublishes one or more modules
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishMambot( $cid=null, $publish=1, $option, $client ) {
	global $database, $my;
	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $publish ? $_LANG->_( 'publish' ) : $_LANG->_( 'unpublish' );
		echo "<script> alert('". $_LANG->_( 'Select a mambot to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__mambots SET published = '". intval( $publish ) ."'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ))"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new mosMambot( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( 'index2.php?option='. $option .'&client='. $client );
}

/**
* Cancels an edit operation
*/
function cancelMambot( $option, $client ) {
	global $database;

	$row = new mosMambot( $database );
	$row->bind( $_POST );
	$row->checkin();

	mosRedirect( 'index2.php?option='. $option .'&client='. $client );
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderMambot( $uid, $inc, $option, $client ) {
	global $database;

	// Currently Unsupported
	if ($client == 'admin') {
		$where = "client_id = 1";
	} else {
		$where = "client_id = 0";
	}
	$row = new mosMambot( $database );
	$row->load( $uid );
	$row->move( $inc, "folder='$row->folder' AND ordering > -10000 AND ordering < 10000 AND ($where)"  );

	mosRedirect( 'index2.php?option='. $option );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option, $client ) {
	global $database;

	switch ( $access ) {
		case 'accesspublic':
			$access = 0;
			break;

		case 'accessregistered':
			$access = 1;
			break;

		case 'accessspecial':
			$access = 2;
			break;
	}

	$row = new mosMambot( $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	mosRedirect( 'index2.php?option='. $option );
}

function saveOrder( &$cid ) {
	global $database;
	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosMambot( $database );
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
			$condition = "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000 AND client_id = $row->client_id";
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
	mosRedirect( 'index2.php?option=com_mambots', $msg );
} // saveOrder
?>