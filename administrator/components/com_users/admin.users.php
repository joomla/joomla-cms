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
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_users', 'manage' ))
{
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

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
		removeUsers( $cid );
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
	global $database, $mainframe, $my, $acl;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'a.name' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_type		= $mainframe->getUserStateFromRequest( "$option.filter_type", 		'filter_type', 		0 );
	$filter_logged		= $mainframe->getUserStateFromRequest( "$option.filter_logged", 	'filter_logged', 	0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( strtolower( $search ) ) );
	$where 				= array();

	if (isset( $search ) && $search!= '') {
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
	$pgids = $acl->get_group_children( $my->gid, 'ARO', 'RECURSE' );

	if (is_array( $pgids ) && count( $pgids ) > 0) {
		$where[] = "(a.gid NOT IN (" . implode( ',', $pgids ) . "))";
	}
	$filter = '';
	if ($filter_logged == 1 || $filter_logged == 2) {
		$filter = "\n INNER JOIN #__session AS s ON s.userid = a.id";
	}
		
	$orderby = "\n ORDER BY $filter_order $filter_order_Dir";	
	$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );		

	$query = "SELECT COUNT(a.id)"
	. "\n FROM #__users AS a"
	. $filter
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = "SELECT a.*, g.name AS groupname"
	. "\n FROM #__users AS a"
	. "\n INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id"	
	. "\n INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id"	
	. "\n INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id"
	. $filter
	. $where
	. "\n GROUP BY a.id"
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

	$n = count( $rows );
	$template = "SELECT COUNT(s.userid)"
	. "\n FROM #__session AS s"
	. "\n WHERE s.userid = %d"
	;
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
	$database->setQuery( $query );
	$types[] 		= mosHTML::makeOption( '0', '- '. JText::_( 'Select Group' ) .' -' );
	$types 			= array_merge( $types, $database->loadObjectList() );
	$lists['type'] 	= mosHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type" );

	// get list of Log Status for dropdown filter
	$logged[] = mosHTML::makeOption( 0, '- '. JText::_( 'Select Log Status' ) .' -');
	$logged[] = mosHTML::makeOption( 1, JText::_( 'Logged In' ) );
	$lists['logged'] = mosHTML::selectList( $logged, 'filter_logged', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_logged" );
	
	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;	
	
	// search filter
	$lists['search']= $search;	
	
	HTML_users::showUsers( $rows, $pageNav, $option, $lists );
}

/**
 * Edit the user
 * @param int The user ID
 * @param string The URL option
 */
function editUser( $id, $option='users' ) 
{
	global $mainframe;
	
	$database =& $mainframe->getDBO();
	$user 	  =& JUser::getInstance($id);
	$acl      =& JFactory::getACL();

	if ( $user->get('id') ) {
		$query = "SELECT *"
		. "\n FROM #__contact_details"
		. "\n WHERE user_id =". $user->get('id')
		;
		$database->setQuery( $query );
		$contact = $database->loadObjectList();
	} else {
		$contact 	= NULL;
		$row->block = 0;
	}

	$userObjectID 	= $acl->get_object_id( 'users', $user->get('id'), 'ARO' );
	$userGroups 	= $acl->get_object_groups( $userObjectID, 'ARO' );
	$userGroupName 	= strtolower( $acl->get_group_name( $userGroups[0], 'ARO' ) );

	$myObjectID 	= $acl->get_object_id( 'users', $user->get('id'), 'ARO' );
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
		mosRedirect( 'index2.php?option=com_users', JText::_('NOT_AUTH') );
	}

	//if ( $userGroupName == 'super administrator' ) {
		// super administrators can't change
	// 	$lists['gid'] = '<input type="hidden" name="gid" value="'. $my->gid .'" /><strong>'. JText::_( 'Super Administrator' ) .'</strong>';
	//} else if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
	if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
		// administrators can't change each other
		$lists['gid'] = '<input type="hidden" name="gid" value="'. $user->get('gid') .'" /><strong>'. JText::_( 'Administrator' ) .'</strong>';
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

		$lists['gid'] 	= mosHTML::selectList( $gtree, 'gid', 'size="10"', 'value', 'text', $user->get('gid') );
	}

	// build the html select list
	$lists['block'] 	= mosHTML::yesnoRadioList( 'block', 'class="inputbox" size="1"', $user->get('block') );
	// build the html select list
	$lists['sendEmail'] = mosHTML::yesnoRadioList( 'sendEmail', 'class="inputbox" size="1"', $user->get('sendEmail') );

	HTML_users::edituser( $user, $contact, $lists, $option );
}

function saveUser( $option, $task ) 
{
	global $mainframe;

	/*
	 * Initialize some variables
	 */
	$db			= & $mainframe->getDBO();
	$me			= & $mainframe->getUser();
	$MailFrom	= $mainframe->getCfg('mailfrom');
	$FromName	= $mainframe->getCfg('fromname');
	$SiteName	= $mainframe->getCfg('sitename');

	/*
	 * Lets create a new JUser object
	 */
	$user = new JUser();

	if (!$user->bind( $_POST )) {
		josRedirect( 'index2.php?option=com_users', $user->getError() );
		return false;
	}

	/*
	 * Are we dealing with a new user which we need to create?
	 */
	$isNew 	= !$user->get('id');

	/*
	 * Lets save the JUser object
	 */
	if (!$user->save()) {
		josRedirect( 'index2.php?option=com_users', $user->getError() );
		return false;
	}

/*
 * TODO
 * @todo Remove this section if we don't need it... shouldn't need it as it is
 * taken care of in the JUserModel class inside of the JUser class... don't we
 * just LOVE encapsulation?... i thought so.
 */
//	// update the ACL
//	if ( !$isNew ) {
//		$query = "SELECT id"
//		. "\n FROM #__core_acl_aro"
//		. "\n WHERE value = '$row->id'"
//		;
//		$database->setQuery( $query );
//		$aro_id = $database->loadResult();
//
//		$query = "UPDATE #__core_acl_groups_aro_map"
//		. "\n SET group_id = $row->gid"
//		. "\n WHERE aro_id = $aro_id"
//		;
//		$database->setQuery( $query );
//		$database->query() or die( $database->stderr() );
//	}

	/*
	 * Time for the email magic so get ready to sprinkle the magic dust...
	 */
	if ($isNew) {
		$adminEmail = $me->get('email');
		$adminName	= $me->get('name');

		$subject = JText::_('NEW_USER_MESSAGE_SUBJECT');
		$message = sprintf ( JText::_('NEW_USER_MESSAGE'), $user->get('name'), $SiteName, $mainframe->getSiteURL(), $user->get('username'), $user->clearPW );

		if ($MailFrom != "" && $FromName != "") {
			$adminName 	= $FromName;
			$adminEmail = $MailFrom;
		}
		josMail( $adminEmail, $adminName, $user->get('email'), $subject, $message );
	}

	switch ( $task ) {
		case 'apply':
        	$msg = sprintf( JText::_( 'Successfully Saved changes to User' ), $user->get('name') );
			josRedirect( 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $user->get('id'), $msg );
			break;

		case 'save':
		default:
        	$msg = sprintf( JText::_( 'Successfully Saved User' ), $user->get('name') );
			josRedirect( 'index2.php?option=com_users', $msg );
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

function removeUsers( $cid ) 
{
	global $mainframe, $database, $acl, $my;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	if (count( $cid )) 
	{
		//load user plugin group
		JPluginHelper::importPlugin( 'user' );

		$obj =& JModel::getInstance('user', $database );
		foreach ($cid as $id) {
			// check for a super admin ... can't delete them
			$objectID 	= $acl->get_object_id( 'users', $id, 'ARO' );
			$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
			$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );


			//trigger the onBeforeDeleteUser event
			$results = $mainframe->triggerEvent( 'onBeforeDeleteUser', array( array( 'id' => $id ) ) );

			$success = false;
			if ( $this_group == 'super administrator' ) {
				$msg = JText::_( 'You cannot delete a Super Administrator' );
 			} else if ( $id == $my->id ){
 				$msg = JText::_( 'You cannot delete Yourself!' );
 			} else if ( ( $this_group == 'administrator' ) && ( $my->gid == 24 ) ){
 				$msg = JText::_( 'WARNDELETE' );
			} else {
				$obj->delete( $id );
				$msg = $obj->getError();
				$success = true;
			}

			//trigger the onAfterDeleteUser event
			$results = $mainframe->triggerEvent( 'onAfterDeleteUser', array( array('id' => $id), $success, $msg ) );
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
function changeUserBlock( $cid=null, $block=1, $option ) {
	global $database;

	if (count( $cid ) < 1) {
		$action = $block ? 'block' : 'unblock';
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
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
function logoutUser( $cid=null, $option, $task ) 
{
	global $database, $my;
	
	$client = mosGetParam( $_REQUEST, 'client' );

	$cids = $cid;
	if ( is_array( $cid ) ) {
		if ( count( $cid ) < 1 ) {
			mosRedirect( 'index2.php?option=com_users', JText::_( 'Please select a user' ) );
		}
		$cids = implode( ',', $cid );
	}

	$query = "DELETE FROM #__session"
	. "\n WHERE userid IN ( $cids )"
	. "\n AND client_id = $client"
	;
	$database->setQuery( $query );
	$database->query();
	
	$msg = JText::_( 'User Sesssion ended' );
	switch ( $task ) {
		case 'flogout':
			mosRedirect( 'index2.php', $msg );
			break;

		default:
			mosRedirect( 'index2.php?option=com_users', $msg );
			break;
	}
}
?>