<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Users
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
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
$user = & JFactory::getUser();
if (!$user->authorize( 'com_users', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

require_once( JPATH_COMPONENT.DS.'admin.users.html.php' );
require_once( JPATH_COMPONENT.DS.'users.class.php' );

switch (JRequest::getVar('task'))
{
	case 'add' :
	case 'edit':
		editUser( );
		break;

	case 'save':
	case 'apply':
 		saveUser( );
		break;

	case 'remove':
		removeUsers( );
		break;

	case 'block':
		changeUserBlock( 1 );
		break;

	case 'unblock':
		changeUserBlock( 0 );
		break;

	case 'logout':
		logoutUser( );
		break;

	case 'flogout':
		logoutUser( );
		break;

	case 'cancel':
		cancelUser( );
		break;

	case 'contact':
		$contact_id = JRequest::getVar( 'contact_id', '', 'post', 'int' );
		$mainframe->redirect( 'index.php?option=com_contact&atask=edit&cid[]='. $contact_id );
		break;

	default:
		showUsers( );
		break;
}

/**
 * Display users in list form
 */
function showUsers( )
{
	global $mainframe, $option;

	$db				=& JFactory::getDBO();
	$currentUser	=& JFactory::getUser();
	$acl			=& JFactory::getACL();

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'a.name' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_type		= $mainframe->getUserStateFromRequest( "$option.filter_type", 		'filter_type', 		0 );
	$filter_logged		= $mainframe->getUserStateFromRequest( "$option.filter_logged", 	'filter_logged', 	0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );
	$where 				= array();

	$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 0);
	$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0 );

	if (isset( $search ) && $search!= '')
	{
		$where[] = '(a.username LIKE \'%'.$search.'%\' OR a.email LIKE \'%'.$search.'%\' OR a.name LIKE \'%'.$search.'%\')';
	}
	if ( $filter_type )
	{
		if ( $filter_type == 'Public Frontend' )
		{
			$where[] = ' a.usertype = \'Registered\' OR a.usertype = \'Author\' OR a.usertype = \'Editor\' OR a.usertype = \'Publisher\' ';
		}
		else if ( $filter_type == 'Public Backend' )
		{
			$where[] = 'a.usertype = \'Manager\' OR a.usertype = \'Administrator\' OR a.usertype = \'Super Administrator\' ';
		}
		else
		{
			$where[] = 'a.usertype = LOWER( \''.$filter_type.'\' ) ';
		}
	}
	if ( $filter_logged == 1 )
	{
		$where[] = 's.userid = a.id';
	}
	else if ($filter_logged == 2)
	{
		$where[] = 's.userid IS NULL';
	}

	// exclude any child group id's for this user
	$pgids = $acl->get_group_children( $currentUser->get('gid'), 'ARO', 'RECURSE' );

	if (is_array( $pgids ) && count( $pgids ) > 0)
	{
		$where[] = '(a.gid NOT IN (' . implode( ',', $pgids ) . '))';
	}
	$filter = '';
	if ($filter_logged == 1 || $filter_logged == 2)
	{
		$filter = ' INNER JOIN #__session AS s ON s.userid = a.id';
	}

	$orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;
	$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

	$query = 'SELECT COUNT(a.id)'
	. ' FROM #__users AS a'
	. $filter
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = 'SELECT a.*, g.name AS groupname'
	. ' FROM #__users AS a'
	. ' INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id'
	. ' INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id'
	. ' INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id'
	. $filter
	. $where
	. ' GROUP BY a.id'
	. $orderby
	;	
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();

	$n = count( $rows );
	$template = 'SELECT COUNT(s.userid)'
	. ' FROM #__session AS s'
	. ' WHERE s.userid = %d'
	;
	for ($i = 0; $i < $n; $i++)
	{
		$row = &$rows[$i];
		$query = sprintf( $template, intval( $row->id ) );
		$db->setQuery( $query );
		$row->loggedin = $db->loadResult();
	}

	// get list of Groups for dropdown filter
	$query = 'SELECT name AS value, name AS text'
	. ' FROM #__core_acl_aro_groups'
	. ' WHERE name != "ROOT"'
	. ' AND name != "USERS"'
	;
	$db->setQuery( $query );
	$types[] 		= JHTMLSelect::option( '0', '- '. JText::_( 'Select Group' ) .' -' );
	foreach( $db->loadObjectList() as $obj )
	{
		$types[] = JHTMLSelect::option( $obj->value, JText::_( $obj->text ) );
	}
	$lists['type'] 	= JHTMLSelect::genericList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type" );

	// get list of Log Status for dropdown filter
	$logged[] = JHTMLSelect::option( 0, '- '. JText::_( 'Select Log Status' ) .' -');
	$logged[] = JHTMLSelect::option( 1, JText::_( 'Logged In' ) );
	$lists['logged'] = JHTMLSelect::genericList( $logged, 'filter_logged', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_logged" );

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_users::showUsers( $rows, $pageNav, $option, $lists );
}

/**
 * Edit the user
 */
function editUser( )
{
	
	$option 	= JRequest::getVar( 'option');
	$cid 		= JRequest::getVar( 'cid', array(), '', 'array' );
	$userId		= (int) @$cid[0];

	$db 		=& JFactory::getDBO();
	$user 		=& JUser::getInstance( $userId );
	$myuser		=& JFactory::getUser();
	$acl		=& JFactory::getACL();

	// Check for post data in the event that we are returning
	// from a unsuccessful attempt to save data
	$post = JRequest::get('post');
	if ( $post )
	{
		$user->bind($post);
	}
	
	if ( $user->get('id') )
	{
		$query = 'SELECT *'
		. ' FROM #__contact_details'
		. ' WHERE user_id ='. $userId
		;
		$db->setQuery( $query );
		$contact = $db->loadObjectList();
	}
	else
	{
		$contact 	= NULL;
		$row->block = 0;
	}

	$userObjectID 	= $acl->get_object_id( 'users', $user->get('id'), 'ARO' );
	$userGroups 	= $acl->get_object_groups( $userObjectID, 'ARO' );
	$userGroupName 	= strtolower( $acl->get_group_name( $userGroups[0], 'ARO' ) );

	$myObjectID 	= $acl->get_object_id( 'users', $myuser->get('id'), 'ARO' );
	$myGroups 		= $acl->get_object_groups( $myObjectID, 'ARO' );
	$myGroupName 	= strtolower( $acl->get_group_name( $myGroups[0], 'ARO' ) );;

	// ensure user can't add/edit group higher than themselves
	/* NOTE : This check doesn't work commented out for the time being
	if ( is_array( $myGroups ) && count( $myGroups ) > 0 )
	{
		$excludeGroups = (array) $acl->get_group_children( $myGroups[0], 'ARO', 'RECURSE' );
	}
	else
	{
		$excludeGroups = array();
	}

	if ( in_array( $userGroups[0], $excludeGroups ) )
	{
		echo 'not auth';
		$mainframe->redirect( 'index.php?option=com_users', JText::_('NOT_AUTH') );
	}
	*/

	/*
	if ( $userGroupName == 'super administrator' )
	{
		// super administrators can't change
	 	$lists['gid'] = '<input type="hidden" name="gid" value="'. $currentUser->gid .'" /><strong>'. JText::_( 'Super Administrator' ) .'</strong>';
	}
	else if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
	*/

	if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' )
	{
		// administrators can't change each other
		$lists['gid'] = '<input type="hidden" name="gid" value="'. $user->get('gid') .'" /><strong>'. JText::_( 'Administrator' ) .'</strong>';
	}
	else
	{
		$gtree = $acl->get_group_children_tree( null, 'USERS', false );

		// remove users 'above' me
		//$i = 0;
		//while ($i < count( $gtree )) {
		//	if ( in_array( $gtree[$i]->value, (array)$excludeGroups ) ) {
		//		array_splice( $gtree, $i, 1 );
		//	} else {
		//		$i++;
		//	}
		//}

		$lists['gid'] 	= JHTMLSelect::genericList( $gtree, 'gid', 'size="10"', 'value', 'text', $user->get('gid') );
	}

	// build the html select list
	$lists['block'] 	= JHTMLSelect::yesnoList( 'block', 'class="inputbox" size="1"', $user->get('block') );
	// build the html select list
	$lists['sendEmail'] = JHTMLSelect::yesnoList( 'sendEmail', 'class="inputbox" size="1"', $user->get('sendEmail') );

	HTML_users::edituser( $user, $contact, $lists, $option );
}

/**
 * Save current edit or addition
 */
function saveUser(  )
{
	global $mainframe;

	$task 	= JRequest::getVar( 'task' );
	$option = JRequest::getVar( 'option');

	// Initialize some variables
	$db			= & JFactory::getDBO();
	$me			= & JFactory::getUser();
	$MailFrom	= $mainframe->getCfg('mailfrom');
	$FromName	= $mainframe->getCfg('fromname');
	$SiteName	= $mainframe->getCfg('sitename');

 	// Create a new JUser object
	$user = new JUser(JRequest::getVar( 'id', 0, 'post', 'int'));
	$original_gid = $user->get('gid');

	$post = JRequest::get('post');
	if (!$user->bind($post))
	{
		$mainframe->enqueueMessage('Cannot save the user information', 'message');
		$mainframe->enqueueMessage($user->getError(), 'error');
		//$mainframe->redirect( 'index.php?option=com_users', $user->getError() );
		//return false;
		JRequest::setVar( 'task', 'edit');
		return editUser();
	}

	// Are we dealing with a new user which we need to create?
	$isNew 	= ($user->get('id') < 1);
	if (!$isNew)
	{
		// if group has been changed and where original group was a Super Admin
		if ( $user->get('gid') != $original_gid && $original_gid == 25 )
		{
			// count number of active super admins
			$query = 'SELECT COUNT( id )'
			. ' FROM #__users'
			. ' WHERE gid = 25'
			. ' AND block = 0'
			;
			$db->setQuery( $query );
			$count = $db->loadResult();

			if ( $count <= 1 )
			{
				// disallow change if only one Super Admin exists
				$mainframe->redirect( 'index.php?option=com_users', JText::_('WARN_ONLY_SUPER') );
				return false;
			}
		}
	}

	/*
	 * Lets save the JUser object
	 */
	if (!$user->save())
	{
		$mainframe->enqueueMessage('Cannot save the user information', 'message');
		$mainframe->enqueueMessage($user->getError(), 'error');
		//$mainframe->redirect( 'index.php?option=com_users', $user->getError() );
		//return false;
		JRequest::setVar( 'task', 'edit');
		return editUser();
	}
	
	/*
	 * Change the user object in the session
	 */
	if ( $me->get('id') == $user->get('id') )
	{
		$session	= JFactory::getSession();
		$user->_bind($me);
		$session->set('user', $user);
	} 


	/*
	 * Time for the email magic so get ready to sprinkle the magic dust...
	 */
	if ($isNew)
	{
		$adminEmail = $me->get('email');
		$adminName	= $me->get('name');

		$subject = JText::_('NEW_USER_MESSAGE_SUBJECT');
		$message = sprintf ( JText::_('NEW_USER_MESSAGE'), $user->get('name'), $SiteName, $mainframe->getSiteURL(), $user->get('username'), $user->clearPW );

		if ($MailFrom != '' && $FromName != '')
		{
			$adminName 	= $FromName;
			$adminEmail = $MailFrom;
		}
		JUtility::sendMail( $adminEmail, $adminName, $user->get('email'), $subject, $message );
	}

	switch ( $task ) {
		case 'apply':
			$msg = JText::sprintf( 'Successfully Saved changes to User %s', $user->get('name') );
			$mainframe->redirect( 'index.php?option=com_users&task=edit&cid[]='. $user->get('id'), $msg );
			break;

		case 'save':
		default:
			$msg = JText::sprintf( 'Successfully Saved User %s', $user->get('name') );
			$mainframe->redirect( 'index.php?option=com_users', $msg );
			break;
	}
}

/**
* Cancels an edit operation
*/
function cancelUser( )
{
	global $mainframe;

	$option = JRequest::getVar( 'option');
	$mainframe->redirect( 'index.php?option='. $option .'&task=view' );
}

/**
* Delete selected users
*/
function removeUsers(  )
{
	global $mainframe;

	$db 			=& JFactory::getDBO();
	$currentUser 	=& JFactory::getUser();
	$acl			=& JFactory::getACL();
	$cid 			= JRequest::getVar( 'cid', array( 0 ), '', 'array' );

	JArrayHelper::toInteger( $cid );

	if (count( $cid ) < 1) {
		JError::raiseError(500, JText::_( 'Select an item to delete', true ) );
	}

	foreach ($cid as $id)
	{
		// check for a super admin ... can't delete them
		$objectID 	= $acl->get_object_id( 'users', $id, 'ARO' );
		$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
		$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );

		$success = false;
		if ( $this_group == 'super administrator' )
		{
			$msg = JText::_( 'You cannot delete a Super Administrator' );
		}
		else if ( $id == $currentUser->get( 'id' ) )
		{
			$msg = JText::_( 'You cannot delete Yourself!' );
		}
		else if ( ( $this_group == 'administrator' ) && ( $currentUser->get( 'gid' ) == 24 ) )
		{
			$msg = JText::_( 'WARNDELETE' );
		}
		else
		{
			$user =& JUser::getInstance((int)$id);
			$count = 2;

			if ( $user->get( 'gid' ) == 25 )
			{
				// count number of active super admins
				$query = 'SELECT COUNT( id )'
				. ' FROM #__users'
				. ' WHERE gid = 25'
				. ' AND block = 0'
				;
				$db->setQuery( $query );
				$count = $db->loadResult();
			}

			if ( $count <= 1 && $user->get( 'gid' ) == 25 )
			{
			// cannot delete Super Admin where it is the only one that exists
				$msg = "You cannot delete this Super Administrator as it is the only active Super Administrator for your site";
			}
			else
			{
				// delete user
				$user->delete();
				$msg = '';

				JRequest::setVar( 'task', 'remove' );
				JRequest::setVar( 'cid', $id );

				// delete user acounts active sessions
				logoutUser();
			}
		}
	}

	$mainframe->redirect( 'index.php?option=com_users', $msg);
}

/**
* Blocks or Unblocks one or more user records
* @param integer 0 if unblock, 1 if blocking
*/
function changeUserBlock( $block=1 )
{
	global $mainframe;

	$db =& JFactory::getDBO();

	$option = JRequest::getVar( 'option');
	$cid 	= JRequest::getVar( 'cid', array(), '', 'array' );

	JArrayHelper::toInteger( $cid );

	if (count( $cid ) < 1)
	{
		$action = $block ? 'block' : 'unblock';
		JError::raiseError(500, JText::_( 'Select an item to '.$action, true ) );
	}

	$cids = implode( ',', $cid );

	$query = 'UPDATE #__users'
	. ' SET block = '. $block
	. ' WHERE id IN ( '. $cids .' )'
	;
	$db->setQuery( $query );

	if (!$db->query())
	{
		JError::raiseError(500, $db->getErrorMsg() );
	}

	// if action is to block a user
	if ( $block == 1 )
	{
		foreach( $cid as $id )
		{
			JRequest::setVar( 'task', 'block' );
			JRequest::setVar( 'cid', $id );

			// delete user acounts active sessions
			logoutUser();
		}
	}

	$mainframe->redirect( 'index.php?option='. $option );
}

/**
 * logout selected users
*/
function logoutUser( )
{
	global $currentUser, $mainframe;

	$db		=& JFactory::getDBO();
	$task 	= JRequest::getVar( 'task' );
	$cids 	= JRequest::getVar( 'cid', array(), '', 'array' );
	$client = JRequest::getVar( 'client', 0, '', 'int' );
	$id 	= JRequest::getVar( 'id', 0, '', 'int' );

	if ( count( $cids ) < 1 )
	{
		$mainframe->redirect( 'index.php?option=com_users', JText::_( 'Please select a user' ) );
	}
	$cids = implode( ',', $cids );

	if ($task == 'logout')
	{
		$query = 'DELETE FROM #__session'
		. ' WHERE userid IN ( '.$cids.' )'
		;
	}
	else if ($task == 'flogout')
	{
		$query = 'DELETE FROM #__session'
		. ' WHERE userid = '. $cids
		. ' AND client_id = '. $client
		;
	}

	if (isset( $query ))
	{
		$db->setQuery( $query );
		$db->query();
	}

	$msg = JText::_( 'User Sesssion ended' );
	switch ( $task )
	{
		case 'flogout':
			$mainframe->redirect( 'index.php', $msg );
			break;

		case 'remove':
		case 'block':
			return;
			break;

		default:
			$mainframe->redirect( 'index.php?option=com_users', $msg );
			break;
	}
}
?>
