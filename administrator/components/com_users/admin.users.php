<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
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

if (!$acl->acl_check( 'com_users', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );
require_once( $mainframe->getPath( 'class' ) );

$task 	= mosGetParam( $_REQUEST, 'task' );
$cid 	= mosGetParam( $_REQUEST, 'cid', array( 0 ) );
$id 	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
if (!is_array( $cid )) {
	$cid = array ( 0 );
}

switch ($task) {
	case 'new':
		editUser( 0, $option);
		break;

	case 'edit':
		editUser( intval( $cid[0] ), $option );
		break;

	case 'editA':
		editUser( $id, $option );
		break;

	case 'save':
	case 'apply':
 		saveUser( $option, $task );
		break;

	case 'remove':
		removeUsers( $cid, $option );
		break;

	case 'block':
		changeUserBlock( $cid, 1, $option );
		break;

	case 'unblock':
		changeUserBlock( $cid, 0, $option );
		break;

	case 'logout':
		logoutUser( $cid, $option, $task );
		break;

	case 'flogout':
		logoutUser( $id, $option, $task );
		break;

	case 'cancel':
		cancelUser( $option );
		break;

	case 'contact':
		$contact_id = mosGetParam( $_POST, 'contact_id', '' );
		mosRedirect( 'index2.php?option=com_contact&task=editA&id='. $contact_id );
		break;

	default:
		showUsers( $option );
		break;
}

function showUsers( $option ) {
	global $database, $mainframe, $my, $acl, $mosConfig_list_limit;
	global $_LANG;

	$filter_type	= $mainframe->getUserStateFromRequest( "filter_type{$option}", 'filter_type', 0 );
	$filter_logged	= $mainframe->getUserStateFromRequest( "filter_logged{$option}", 'filter_logged', 0 );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 		= $database->getEscaped( trim( strtolower( $search ) ) );
	$where 			= array();

	if (isset( $search ) && $search!= "") {
		$where[] = "(a.username LIKE '%$search%' OR a.email LIKE '%$search%' OR a.name LIKE '%$search%')";
	}
	if ( $filter_type ) {
		if ( $filter_type == 'Public Frontend' ) {
			$where[] = "a.usertype = 'Registered' OR a.usertype = 'Author' OR a.usertype = 'Editor' OR a.usertype = 'Publisher'";
		} else if ( $filter_type == 'Public Backend' ) {
			$where[] = "a.usertype = 'Manager' OR a.usertype = 'Administrator' OR a.usertype = 'Super Administrator'";
		} else {
			$where[] = "a.usertype = LOWER( '$filter_type' )";
		}
	}
	if ( $filter_logged == 1 ) {
		$where[] = "s.userid = a.id";
	} else if ($filter_logged == 2) {
		$where[] = "s.userid IS NULL";
	}

	// exclude any child group id's for this user
	//$acl->_debug = true;
	$pgids = $acl->get_group_children( $my->gid, 'ARO', 'RECURSE' );

	if (is_array( $pgids ) && count( $pgids ) > 0) {
		$where[] = "(a.gid NOT IN (" . implode( ',', $pgids ) . "))";
	}

	$query = "SELECT COUNT(a.id)"
	. "\n FROM #__users AS a";

	if ($filter_logged == 1 || $filter_logged == 2) {
		$query .= "\n INNER JOIN #__session AS s ON s.userid = a.id";
	}

	$query .= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' )
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = "SELECT a.*, g.name AS groupname"
	. "\n FROM #__users AS a"
	. "\n INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id"	// map user to aro
	. "\n INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.aro_id"	// map aro to group
	. "\n INNER JOIN #__core_acl_aro_groups AS g ON g.group_id = gm.group_id";

	if ($filter_logged == 1 || $filter_logged == 2) {
		$query .= "\n INNER JOIN #__session AS s ON s.userid = a.id";
	}

	$query .= (count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : "")
	. "\n GROUP BY a.id"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	$template = 'SELECT COUNT(s.userid) FROM #__session AS s WHERE s.userid = %d';
	$n = count( $rows );
	for ($i = 0; $i < $n; $i++) {
		$row = &$rows[$i];
		$query = sprintf( $template, intval( $row->id ) );
		$database->setQuery( $query );
		$row->loggedin = $database->loadResult();
	}

	// get list of Groups for dropdown filter
	$query = "SELECT name AS value, name AS text"
	. "\n FROM #__core_acl_aro_groups"
	. "\n WHERE name != 'ROOT'"
	. "\n AND name != 'USERS'"
	;
	$types[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select Group' ) .' -' );
	$database->setQuery( $query );
	$types = array_merge( $types, $database->loadObjectList() );
	$lists['type'] = mosHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type" );

	// get list of Log Status for dropdown filter
	$logged[] = mosHTML::makeOption( 0, '- '. $_LANG->_( 'Select Log Status' ) .' -');
	$logged[] = mosHTML::makeOption( 1, $_LANG->_( 'Logged In' ) );
	$lists['logged'] = mosHTML::selectList( $logged, 'filter_logged', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_logged" );

	HTML_users::showUsers( $rows, $pageNav, $search, $option, $lists );
}

/**
 * Edit the user
 * @param int The user ID
 * @param string The URL option
 */
function editUser( $uid='0', $option='users' ) {
	global $database, $my, $acl, $mainframe;
	global $_LANG;

	$row = new mosUser( $database );
	// load the row from the db table
	$row->load( $uid );

	if ( $uid ) {
		$query = "SELECT *"
		. "\n FROM #__contact_details"
		. "\n WHERE user_id = $row->id"
		;
		$database->setQuery( $query );
		$contact = $database->loadObjectList();
	} else {
		$contact 	= NULL;
		$row->block = 0;
	}

	$userObjectID 	= $acl->get_object_id( 'users', $row->id, 'ARO' );
	$userGroups 	= $acl->get_object_groups( $userObjectID, 'ARO' );
	$userGroupName 	= strtolower( $acl->get_group_name( $userGroups[0], 'ARO' ) );

	$myObjectID 	= $acl->get_object_id( 'users', $my->id, 'ARO' );
	$myGroups 		= $acl->get_object_groups( $myObjectID, 'ARO' );
	$myGroupName 	= strtolower( $acl->get_group_name( $myGroups[0], 'ARO' ) );;

	// ensure user can't add/edit group higher than themselves
	if ( is_array( $myGroups ) && count( $myGroups ) > 0 ) {
		$excludeGroups = (array) $acl->get_group_children( $myGroups[0], 'ARO', 'RECURSE' );
	} else {
		$excludeGroups = array();
	}
	
	if ( in_array( $userGroups[0], $excludeGroups ) ) {
		echo 'not auth';
		mosRedirect( 'index2.php?option=com_users', $_LANG->_('NOT_AUTH') );
	}

	//if ( $userGroupName == 'super administrator' ) {
		// super administrators can't change
	// 	$lists['gid'] = '<input type="hidden" name="gid" value="'. $my->gid .'" /><strong>'. $_LANG->_( 'Super Administrator' ) .'</strong>';
	//} else if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
	if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
		// administrators can't change each other
		$lists['gid'] = '<input type="hidden" name="gid" value="'. $my->gid .'" /><strong>'. $_LANG->_( 'Administrator' ) .'</strong>';
	} else {
		$gtree = $acl->get_group_children_tree( null, 'USERS', false );

		// remove users 'above' me
		$i = 0;
		while ($i < count( $gtree )) {
			if ( in_array( $gtree[$i]->value, $excludeGroups ) ) {
				array_splice( $gtree, $i, 1 );
			} else {
				$i++;
			}
		}

		$lists['gid'] 	= mosHTML::selectList( $gtree, 'gid', 'size="10"', 'value', 'text', $row->gid );
	}

	// build the html select list
	$lists['block'] 	= mosHTML::yesnoRadioList( 'block', 'class="inputbox" size="1"', $row->block );
	// build the html select list
	$lists['sendEmail'] = mosHTML::yesnoRadioList( 'sendEmail', 'class="inputbox" size="1"', $row->sendEmail );

	$file 	= $mainframe->getPath( 'com_xml', 'com_users' );
	$params =& new mosUserParameters( $row->params, $file, 'component' );

	HTML_users::edituser( $row, $contact, $lists, $option, $uid, $params );
}

function saveUser( $option, $task ) {
	global $database, $my;
	global $mosConfig_live_site, $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_sitename;
	global $_LANG, $_MAMBOTS;;

	$row = new mosUser( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$isNew 	= !$row->id;
	$pwd 	= '';

	// MD5 hash convert passwords
	if ($isNew) {
		// new user stuff
		if ($row->password == '') {
			$pwd = mosMakePassword();
			$row->password = md5( $pwd );
		} else {
			$pwd = $row->password;
			$row->password = md5( $row->password );
		}
		$row->registerDate = date( 'Y-m-d H:i:s' );
	} else {
		// existing user stuff
		if ($row->password == '') {
			// password set to null if empty
			$row->password = null;
		} else {
			$row->password = md5( $row->password );
		}
	}

	// save usertype to usetype column
	$query = "SELECT name"
	. "\n FROM #__core_acl_aro_groups"
	. "\n WHERE group_id = $row->gid"
	;
	$database->setQuery( $query );
	$usertype = $database->loadResult();
	$row->usertype = $usertype;

	//load user bot group
	$_MAMBOTS->loadBotGroup( 'user' );

	// save params
	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->params = implode( "\n", $txt );
	}

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	//trigger the onBeforeStoreUser event
	$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array( get_object_vars( $row ), $row->id ) );

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	session_start();
	$_SESSION['session_user_params']= $row->params;
	session_write_close();

	// update the ACL
	if ( !$isNew ) {
		$query = "SELECT aro_id"
		. "\n FROM #__core_acl_aro"
		. "\n WHERE value = '$row->id'"
		;
		$database->setQuery( $query );
		$aro_id = $database->loadResult();

		$query = "UPDATE #__core_acl_groups_aro_map"
		. "\n SET group_id = $row->gid"
		. "\n WHERE aro_id = $aro_id"
		;
		$database->setQuery( $query );
		$database->query() or die( $database->stderr() );
	}

	// for new users, email username and password
	if ($isNew) {
		$query = "SELECT email"
		. "\n FROM #__users"
		. "\n WHERE id = $my->id"
		;
		$database->setQuery( $query );
		$adminEmail = $database->loadResult();

		$subject = _NEW_USER_MESSAGE_SUBJECT;
		$message = sprintf ( _NEW_USER_MESSAGE, $row->name, $mosConfig_sitename, $mosConfig_live_site, $row->username, $pwd );

		if ($mosConfig_mailfrom != "" && $mosConfig_fromname != "") {
			$adminName 	= $mosConfig_fromname;
			$adminEmail = $mosConfig_mailfrom;
		} else {
			$query = "SELECT name, email"
			. "\n FROM #__users"
			// administrator
			. "\n WHERE gid = 25"
			;
			$database->setQuery( $query );
			$admins = $database->loadObjectList();
			$admin 		= $admins[0];
			$adminName 	= $admin->name;
			$adminEmail = $admin->email;
		}
		mosMail( $adminEmail, $adminName, $row->email, $subject, $message );
	}

	//trigger the onAfterStoreUser event
	$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array( get_object_vars( $row ), $row->id, true, null ) );


	switch ( $task ) {
		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes to User' ) .': '. $row->name;
			mosRedirect( 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved User' ) .': '. $row->name;
			mosRedirect( 'index2.php?option=com_users', $msg );
			break;
	}
}

/**
* Cancels an edit operation
* @param option component option to call
*/
function cancelUser( $option ) {
	mosRedirect( 'index2.php?option='. $option .'&task=view' );
}

function removeUsers( $cid, $option ) {
	global $database, $acl, $my;
	global $_LANG, $_MAMBOTS;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". $_LANG->_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	if ( count( $cid ) ) {

		//load user bot group
		$_MAMBOTS->loadBotGroup( 'user' );

		$obj = new mosUser( $database );
		foreach ($cid as $id) {
			// check for a super admin ... can't delete them
			$groups 	= $acl->get_object_groups( 'users', $id, 'ARO' );
			$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );

			//trigger the onBeforeDeleteUser event
			$results = $_MAMBOTS->trigger( 'onBeforeDeleteUser', array( array( 'id' => $id ) ) );

			$success = false;
			if ( $this_group == 'super administrator' ) {
				$msg = $_LANG->_( 'You cannot delete a Super Administrator' );
 			} else if ( $id == $my->id ){
 				$msg = $_LANG->_( 'You cannot delete Yourself!' );
 			} else if ( ( $this_group == 'administrator' ) && ( $my->gid == 24 ) ){
 				$msg = $_LANG->_( 'WARNDELETE' );
			} else {
				$obj->delete( $id );
				$msg = $obj->getError();
				$success = true;
			}

			//trigger the onAfterDeleteUser event
			$results = $_MAMBOTS->trigger( 'onAfterDeleteUser', array( array('id' => $id), $success, $msg ) );
		}
	}

	mosRedirect( 'index2.php?option='. $option, $msg );
}

/**
* Blocks or Unblocks one or more user records
* @param array An array of unique category id numbers
* @param integer 0 if unblock, 1 if blocking
* @param string The current url option
*/
function changeUserBlock( $cid=null, $block=1, $option ) {
	global $database;
	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $block ? 'block' : 'unblock';
		echo "<script> alert('". $_LANG->_( 'Select an item to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__users"
	. "\n SET block = $block"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	mosRedirect( 'index2.php?option='. $option );
}

/**
* @param array An array of unique user id numbers
* @param string The current url option
*/
function logoutUser( $cid=null, $option, $task ) {
	global $database, $my;
	global $_LANG;

	$cids = $cid;
	if ( is_array( $cid ) ) {
		if ( count( $cid ) < 1 ) {
			mosRedirect( 'index2.php?option=com_users', $_LANG->_( 'Please select a user' ) );
		}
		$cids = implode( ',', $cid );
	}

	$query = "DELETE FROM #__session"
	. "\n WHERE userid IN ( $cids )"
	;
	$database->setQuery( $query );
	$database->query();

	$msg = $_LANG->_( 'User Sesssion ended' );
	switch ( $task ) {
		case 'flogout':			
			mosRedirect( 'index2.php', $msg );
			break;
	
		default:
			mosRedirect( 'index2.php?option=com_users', $msg );
			break;
	}
}

function is_email($email){
	$rBool=false;

	if(preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email)){
		$rBool=true;
	}
	return $rBool;
}
?>