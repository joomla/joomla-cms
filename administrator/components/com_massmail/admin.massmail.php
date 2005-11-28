<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Massmail
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
if (!$acl->acl_check( 'com_massmail', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );

switch ($task) {
	case 'send':
		sendMail();
		break;

	case 'cancel':
		mosRedirect( 'index2.php' );
		break;

	default:
		messageForm( $option );
		break;
}

function messageForm( $option ) {
	global $acl;

	$gtree = array(
	mosHTML::makeOption( 0, '- '. JText::_( 'All User Groups' ) .' -' )
	);

	// get list of groups
	$lists = array();
	$gtree = array_merge( $gtree, $acl->get_group_children_tree( null, 'USERS', false ) );
	$lists['gid'] = mosHTML::selectList( $gtree, 'mm_group', 'size="10"', 'value', 'text', 0 );

	HTML_massmail::messageForm( $lists, $option );
}

function sendMail() {
	global $database, $my, $acl;
	global $mosConfig_sitename;
	global $mosConfig_mailfrom, $mosConfig_fromname;

	$mode				= mosGetParam( $_POST, 'mm_mode', 0 );
	$subject			= mosGetParam( $_POST, 'mm_subject', '' );
	$gou				= mosGetParam( $_POST, 'mm_group', NULL );
	$recurse			= mosGetParam( $_POST, 'mm_recurse', 'NO_RECURSE' );
	// pulls message inoformation either in text or html format
	if ( $mode ) {
		$message_body	= $_POST['mm_message'];
	} else {
		// automatically removes html formatting
		$message_body	= mosGetParam( $_POST, 'mm_message', '' );
	}
	$message_body 		= stripslashes( $message_body );

	if (!$message_body || !$subject || $gou === null) {
		mosRedirect( 'index2.php?option=com_massmail&mosmsg='. JText::_( 'Please fill in the form correctly' ) );
	}

	// get users in the group out of the acl
	$to = $acl->get_group_objects( $gou, 'ARO', $recurse );

	$rows = array();
	if ( count( $to['users'] ) || $gou === '0' ) {
		// Get sending email address
		$query = "SELECT email"
		. "\n FROM #__users"
		. "\n WHERE id = $my->id"
		;
		$database->setQuery( $query );
		$my->email = $database->loadResult();

		// Get all users email and group except for senders
		$query = "SELECT email"
		. "\n FROM #__users"
		. "\n WHERE id != $my->id"
		. ( $gou !== '0' ? " AND id IN (" . implode( ',', $to['users'] ) . ")" : '' )
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// Build e-mail message format
		$message_header 	= sprintf( _MASSMAIL_MESSAGE, $mosConfig_sitename );
		$message 			= $message_header . $message_body;
		$subject 			= $mosConfig_sitename. ' / '. stripslashes( $subject);

		//Send email
		foreach ($rows as $row) {
			mosMail( $mosConfig_mailfrom, $mosConfig_fromname, $row->email, $subject, $message, $mode );
		}
	}

	$msg = sprintf( JText::_( 'E-mail sent to' ), count( $rows ) );
	mosRedirect( 'index2.php?option=com_massmail', $msg );
}
?>
