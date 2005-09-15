<?php
/**
* @version $Id: admin.users.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Users
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

if (!$acl->acl_check( 'com_users', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

switch ($task) {
	case 'new':
		editUser( 0, $option);
		break;

	case 'edit':
		editUser( $cid[0], $option );
		break;

	case 'editA':
		editUser( $id, $option );
		break;

	case 'save':
	case 'apply':
 		saveUser( $task );
		break;

	case 'remove':
		removeUsers( $cid );
		break;

	case 'block':
		changeUserBlock( $cid, 1 );
		break;

	case 'unblock':
		changeUserBlock( $cid, 0 );
		break;

	case 'logout':
		logoutUser( $cid, $task );
		break;

	case 'flogout':
		logoutUser( $id, $task );
		break;

	case 'cancel':
		mosRedirect( 'index2.php?option=com_users&task=view' );
		break;

	case 'contact':
		$contact_id = mosGetParam( $_POST, 'contact_id', '' );
		mosRedirect( 'index2.php?option=com_contact&task=editA&id='. $contact_id );
		break;

	case 'masscreate':
	   massCreate();
	   break;

	case 'savemasscreate':
	   savemassCreate();
	   break;

	default:
		showUsers( $option );
		break;
}

function showUsers( $option ) {
	global $database, $mainframe, $my, $acl, $mosConfig_list_limit, $_LANG;

	$filter_type	= $mainframe->getUserStateFromRequest( "filter_type{$option}", 'filter_type', 0 );
	$filter_logged	= $mainframe->getUserStateFromRequest( "filter_logged{$option}", 'filter_logged', 0 );
	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 		= trim( strtolower( $search ) );
	$where 			= array();
	$group 			= mosGetParam( $_REQUEST, 'group', '' );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'a.name' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'a.name' );

	// table column ordering values
	if ( $tOrder_old <> $tOrder && ( $tOrder <> 'loggedin' ) ) {
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
	if (isset( $search ) && $search!= "") {
		$where[] = "(a.username LIKE '%$search%' OR a.email LIKE '%$search%' OR a.name LIKE '%$search%')";
	}
	if ( $group ) {
		$filter_type = $group;
	}
	if ( $filter_type ) {
		// TODO: unsafe to use hard-coded values
		if ( $filter_type == 29 ) {
			$where[] = "a.gid = '18' OR a.gid = '19' OR a.gid = '20' OR a.gid = '21'";
		} else if ( $filter_type == 30 ) {
			$where[] = "a.gid = '23' OR a.gid = '24' OR a.gid = '25'";
		} else {
			$where[] = "a.gid = '$filter_type'";
		}
	}
	if ( $filter_logged == 1 ) {
		$where[] = "s.userid = a.id";
	} else if ($filter_logged == 2) {
		$where[] = "s.userid IS NULL";
	}

	// exclude any child group id's for this user
	$myObjectID = $acl->get_object_id( 'users', $my->id, 'ARO' );
	$myGroups 	= $acl->get_object_groups( $myObjectID, 'ARO' );
	$pgids 		= $acl->get_group_children( $myGroups[0], 'ARO', 'RECURSE' );

	if (is_array( $pgids ) && count( $pgids ) > 0) {
		$where[] = "(a.gid NOT IN (" . implode( ',', $pgids ) . "))";
	}

	$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

	// table column ordering
	$order = "\n ORDER BY $tOrder $tOrderDir, a.name ASC";

	$query = "SELECT COUNT(a.id)"
	. "\n FROM #__users AS a";

	if ($filter_logged == 1 || $filter_logged == 2) {
		// this join is a resource hog, hence it is separated
		$query .= "\n INNER JOIN #__session AS s ON s.userid = a.id";
	}

	$query .= $where;

	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	// main query
	$query = "SELECT a.*, g.name AS groupname"
	. "\n FROM #__users AS a"
	. "\n INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id"				// map user to aro
	. "\n INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id"	// map aro to group
	. "\n INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id";

	if ($filter_logged == 1 || $filter_logged == 2) {
		$query .= "\n INNER JOIN #__session AS s ON s.userid = a.id";
	}

	$query .= $where
	. "\n GROUP BY a.id"
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
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
	$query = "SELECT id AS value, name AS text"
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

	$search = stripslashes( $search );

	HTML_users::showUsers( $rows, $pageNav, $search, $option, $lists );
}

function editUser( $uid=0, $option='users' ) {
	global $database, $my, $acl, $mainframe;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	$row = new mosUser( $database );
	// load the row from the db table
	$row->load( $uid );

	if ( $uid ) {
		$query = "SELECT *"
		. "\n FROM #__contact_details"
		. "\n WHERE user_id = '$row->id'"
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
	$params = new mosUserParameters( $row->params, $file, 'component' );

	HTML_users::edituser( $row, $contact, $lists, $option, $uid, $params );
}

function saveUser( $task ) {
	global $database, $my, $mainframe;
	global $_LANG, $_MAMBOTS;

	$user_id 	= intval( mosGetParam( $_POST, 'id', 0 ));
	$isOld		= $user_id;

	// number of Super Administrators
	$query = "SELECT COUNT( id  )"
	. "\n FROM #__users"
	. "\n WHERE gid = '25'"
	. "\n AND block = 0"
	;
	$database->setQuery( $query );
	$super_count = $database->loadResult();
	// check if only one Super Administrator exists
	if ( $super_count == 1 && $_POST['block'] ) {
		mosErrorAlert( $_LANG->_( 'SUPERBLOCK' ) );
	}

	$row = new mosUser( $database );
	$row->load( $user_id );
	$orig_password = $row->password;

	if ( !$row->bind( $_POST ) ) {
		mosErrorAlert( $row->getError() );
	}

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

	if ( isset( $_POST['password'] ) && isset( $_POST['password2'] ) && $_POST['password'] != '' ) {
		if ( $_POST['password2'] == $_POST['password'] ) {
			$row->password = md5( $_POST['password'] );
		} else {
			mosErrorAlert( $_LANG->_( 'errorPasswordMatch' ) );
		}
	} else {
		// Restore 'original password'
		$row->password = $orig_password;
	}

	// usertype for usetype column
	$row->usertype = GIDusertype( $row->gid);

	// save register date for new users
	if ( !$isOld ) {
		$row->registerDate = $mainframe->getDateTime();
	}

	if ( !$row->check() ) {
		mosErrorAlert( $row->getError() );
	}

	//trigger the onBeforeStoreUser event
	$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array( get_object_vars( $row ), $user_id ) );

	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}

	$row->checkin();

	// update the ACL
	if ( !$isOld ) {
		newUser( $row );
	}

	//trigger the onAfterStoreUser event
	$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array( get_object_vars( $row ), $user_id, true, null ) );

	switch ( $task ) {
		case 'apply':
			$msg = $_LANG->_( 'Successfully Saved changes to User' ) .': '. $row->name;
			mosRedirect( 'index2.php?option=com_users&task=editA&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = $_LANG->_( 'Successfully Saved User' ) .': '. $row->name;
			mosRedirect( 'index2.php?option=com_users', $msg );
			break;
	}
}

function massCreate() {
	global $mosConfig_new_usertype;
	global $mainframe, $my, $acl;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	$gid = usertypeGID( $mosConfig_new_usertype );

	$userObjectID 	= $acl->get_object_id( 'users', 0, 'ARO' );
	$userGroups 	= $acl->get_object_groups( $userObjectID, 'ARO' );

	$myObjectID 	= $acl->get_object_id( 'users', $my->id, 'ARO' );
	$myGroups 		= $acl->get_object_groups( $myObjectID, 'ARO' );

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

	$lists['gid'] 	= mosHTML::selectList( $gtree, 'gid', 'size="10"', 'value', 'text', $gid );

	usersScreens::massCreate( $lists );
}

function savemassCreate() {
	global $mosConfig_password_length, $mosConfig_new_usertype;
	global $mainframe, $database, $acl, $_MAMBOTS;
	global $_LANG;

	$default_gid   = GIDusertype( $mosConfig_new_usertype );
	$gid		   = mosGetParam( $_POST, 'gid', $default_gid );
	$usertype	  = GIDusertype( $gid );

	$names		 = mosGetParam( $_POST, 'names', array(0) );
	$emails		= mosGetParam( $_POST, 'emails', array(0) );

	$z = 0;
	for ( $i=0; $i < 10; $i++ ) {
		if ( $names[$i] && ( $emails[$i] && strchr( $emails[$i], '@' ) ) ) {
		// filter entries for only data
			$users[$z]->name   = $names[$i];
			$users[$z]->email  = $emails[$i];
			$z++;
		}
	}
	$count = count( $users );

	for ( $i=0; $i < $count; $i++ ) {
	// creates each individual user
		//load user bot group
		$_MAMBOTS->loadBotGroup( 'user' );

		$row = new mosUser( $database );
		$row->load( 0 );

		$row->name		  = $users[$i]->name;
		$row->username	  = $users[$i]->name;
		$row->email		 = $users[$i]->email;
		$row->password	  = mosMakePassword( $mosConfig_password_length );
		$row->usertype	  = $usertype;
		$row->gid		   = $gid;
		$row->registerDate  = $mainframe->getDateTime();

	 	if ( !$row->check() ) {
			mosErrorAlert( $row->getError() );
		}

	   	//trigger the onBeforeStoreUser event
		$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array( get_object_vars( $row ), $row->id ) );


		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}

		newUser( $row );

		//trigger the onAfterStoreUser event
		$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array( get_object_vars( $row ), $row->id, true, null ) );
	}

	$msg = $count .' '. $_LANG->_( 'Users created' );
	mosRedirect( 'index2.php?option=com_users', $msg );
}

function removeUsers( $cid ) {
	global $database, $acl, $my;
	global $_LANG, $_MAMBOTS;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to delete' ) );
	}

	if ( count( $cid ) ) {

		//load user bot group
		$_MAMBOTS->loadBotGroup( 'user' );

		$obj = new mosUser( $database );
		foreach ( $cid as $id ) {
			// check for a super admin ... can't delete them
			$objectID 	= $acl->get_object_id( 'users', $id, 'ARO' );
			$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
			$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );

			//trigger the onBeforeDeleteUser event
			$results = $_MAMBOTS->trigger( 'onBeforeDeleteUser', array( array( 'id' => $id ) ) );

			$success = false;
			if ( ( $this_group == 'super administrator' ) && ( $my->gid != 25 ) ) {
				$msg = $_LANG->_( 'You cannot delete a Super Administrator' );
 			} else if ( $id == $my->id ){
 				$msg = $_LANG->_( 'You cannot delete Yourself!' );
 			} else if ( ( $this_group == 'administrator' ) && ( $my->gid == 24 ) ) {
 				$msg = $_LANG->_( 'You cannot delete another `Administrator` only `Super Administrators` have this power' );
			} else {
				$obj->delete( $id );
				$msg = $obj->getError();
				$success = true;
			}

			//trigger the onAfterDeleteUser event
			$results = $_MAMBOTS->trigger( 'onAfterDeleteUser', array( array('id' => $id), $success, $msg ) );
		}
	}

	mosRedirect( 'index2.php?option=com_users', $msg );
}

/**
* Blocks or Unblocks one or more user records
* @param array An array of unique category id numbers
* @param integer 0 if unblock, 1 if blocking
* @param string The current url option
*/
function changeUserBlock( $cid=null, $block=1 ) {
	global $database, $my;
	global $_LANG, $_MAMBOTS;

	if (count( $cid ) < 1) {
		$action = $block ? 'block' : 'unblock';
		mosErrorAlert( $_LANG->_( 'Select an item to' ) .' '. $action );
	}

	//load user bot group
	$_MAMBOTS->loadBotGroup( 'user' );

	foreach( $cid as $id ) {

		$row = new mosUser( $database );
		$row->load( $id );

		echo $id;

		//trigger the onBeforeStoreUser event
		$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array( get_object_vars( $row ), false ) );
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__users"
	. "\n SET block = '$block'"
	. "\n WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	foreach( $cid as $id ) {
		$row = new mosUser( $database );
		$row->load( $id );

		//trigger the onAfterStoreUser event
		$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array( get_object_vars( $row ), !$block, true, null ) );
	}

	mosRedirect( 'index2.php?option=com_users' );
}

/**
* @param array An array of unique user id numbers
* @param string The current url option
*/
function logoutUser( $cid=null, $task ) {
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

function is_email( $email ){
	$rBool=false;

	if( preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email ) ){
		$rBool=true;
	}
	return $rBool;
}

/*
* Complete entry into proper tables to create a user
*/
function newUser( &$row ) {
	global $database;

	$query = "SELECT id"
	. "\n FROM #__core_acl_aro"
	. "\n WHERE value = '$row->id'"
	;
	$database->setQuery( $query );
	$aro_id = $database->loadResult();

	$query = "UPDATE #__core_acl_groups_aro_map"
	. "\n SET group_id = '$row->gid'"
	. "\n WHERE aro_id = '$aro_id'"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		mosErrorAlert( $database->stderr() );
	}

	mailUserInfo( $row );
}

/*
* Email User their registration details
*/
function mailUserInfo( &$row ) {
	global $mainframe;
	global $mosConfig_live_site, $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_sitename;
	global $_LANG;

	$subject = $_LANG->_( 'emailNewUserSubject' );
	$message = $_LANG->sprintf ( 'emailNewUserBody', $row->name, $mosConfig_sitename, $mosConfig_live_site, $row->username, $row->password );

	if ( $mosConfig_mailfrom != '' && $mosConfig_fromname != '' ) {
		$site_Name 	= $mosConfig_fromname;
		$site_Email = $mosConfig_mailfrom;
	} else {
	// If no `From Name` and `From Email` set in GC, use first Super Administrator
		// List of Super Administrators
		$admins 	= $mainframe->getAdmins();

		$site_Name 	= $admins[0]->name;
		$site_Email = $admins[0]->email;
	}

	// email user registration information to user
	mosMail( $site_Email, $site_Name, $row->email, $subject, $message );
}

/*
* Give GID integer value from a Usertype string value
*/
function usertypeGID ( &$usertype ) {
	global $database;

	$query = "SELECT id"
	. "\n FROM #__core_acl_aro_groups"
	. "\n WHERE LOWER( name ) = '". strtolower( $usertype ) ."'"
	;
	$database->setQuery( $query );
	$gid = $database->loadResult();

	return $gid;
}

/*
* Give Usertype string value from a GID integer value
*/
function GIDusertype ( &$gid ) {
	global $database;

	$query = "SELECT name"
	. "\n FROM #__core_acl_aro_groups"
	. "\n WHERE id = $gid"
	;
	$database->setQuery( $query );
	$usertype = $database->loadResult();

	return $usertype;
}
?>