<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Massmail
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
if (!$user->authorize( 'com_massmail', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );

switch ($task) {
	case 'send':
		sendMail();
		break;

	case 'cancel':
		$mainframe->redirect( 'index.php' );
		break;

	default:
		messageForm( $option );
		break;
}

function messageForm( $option )
{
	$acl =& JFactory::getACL();

	$gtree = array(
		JHTML::_('select.option',  0, '- '. JText::_( 'All User Groups' ) .' -' )
	);

	// get list of groups
	$lists = array();
	$gtree = array_merge( $gtree, $acl->get_group_children_tree( null, 'users', false ) );
	$lists['gid'] = JHTML::_('select.genericlist',   $gtree, 'mm_group', 'size="10"', 'value', 'text', 0 );

	HTML_massmail::messageForm( $lists, $option );
}

function sendMail()
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$user 				=& JFactory::getUser();
	$acl 				=& JFactory::getACL();

	$mode				= JRequest::getVar( 'mm_mode', 0, 'post' );
	$subject			= JRequest::getVar( 'mm_subject', '', 'post' );
	$gou				= JRequest::getVar( 'mm_group', '0', 'post' );
	$recurse			= JRequest::getVar( 'mm_recurse', 'NO_RECURSE', 'post' );
	// pulls message inoformation either in text or html format
	if ( $mode ) {
		$message_body	= $_POST['mm_message'];
	} else {
		// automatically removes html formatting
		$message_body	= JRequest::getVar( 'mm_message', '', 'post' );
	}
	$message_body 		= stripslashes( $message_body );

	if (!$message_body || !$subject) {
		$mainframe->redirect( 'index.php?option=com_massmail', JText::_( 'Please fill in the form correctly' ) );
	}

	// get users in the group out of the acl
	$to = $acl->get_group_objects( $gou, 'ARO', $recurse );

	$rows = array();
	if ( count( $to['users'] ) || $gou === '0' ) {
		// Get sending email address
		$query = 'SELECT email'
		. ' FROM #__users'
		. ' WHERE id = ' .$user->get('id')
		;
		$db->setQuery( $query );
		$user->set( 'email', $db->loadResult() );

		// Get all users email and group except for senders
		$query = 'SELECT email'
		. ' FROM #__users'
		. ' WHERE id != ' .$user->get('id')
		. ( $gou !== '0' ? ' AND id IN (' . implode( ',', $to['users'] ) . ')' : '' )
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		$mailer =& JFactory::getMailer();
		$params =& JComponentHelper::getParams( 'com_massmail' );
		// Build e-mail message format
		$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
		$mailer->setSubject($params->get('mailSubjectPrefix') . stripslashes( $subject));
		$mailer->setBody($message_body . $params->get('mailBodySuffix'));

		foreach ($rows as $row) {
			$mailer->addRecipient($row->email);
		}
		$mailer->Send();
	}

	$msg = JText::sprintf( 'E-mail sent to', count( $rows ) );
	$mainframe->redirect( 'index.php?option=com_massmail', $msg );
}
?>
