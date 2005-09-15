<?php
/**
* @version $Id: admin.massmail.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Massmail
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

/**
 * @package Mambo
 * @subpackage massmail
 */
class massmailTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function massmailTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'messageForm' );

		// set task level access control
		$this->setAccessControl( 'com_massmail', 'manage' );
	}

	/**
	 * messageForm
	 */
	function messageForm() {
		global $acl, $mainframe;

		$mainframe->set('disableMenu', true);

		// get list of groups
		$lists = array();
		$lists['gid'] = $acl->get_group_children_tree( null, 'USERS', false, false );

		massmailScreens::messageForm( $lists );
	}

	/**
	 * Cancel
	 */
	function cancel() {
		$this->setRedirect( 'index2.php' );
	}

	/**
	 * Send mail
	 */
	function send() {
		global $database, $my, $acl, $mainframe;
		global $_LANG;

		$sitename = $mainframe->getCfg( 'sitename' );
		$mailfrom = $mainframe->getCfg( 'mailfrom' );
		$fromname = $mainframe->getCfg( 'fromname' );

		$mode		= mosGetParam( $_POST, 'mm_mode', 0 );
		$subject	= mosGetParam( $_POST, 'mm_subject', '' );
		$group		= mosGetParam( $_POST, 'mm_group', NULL );
		$recurse	= mosGetParam( $_POST, 'mm_recurse', 'NO_RECURSE' );

		// pulls message inoformation either in text or html format
		if ( $mode ) {
			$message_body = $_POST['mm_message'];
		} else {
			// automatically removes html formatting
			$message_body = mosGetParam( $_POST, 'mm_message', '' );
		}
		$message_body = stripslashes( $message_body );

		if (!$message_body || !$subject || $group === null) {
			$msg = $_LANG->_( 'Please fill in the form correctly' );
			$this->setRedirect( 'index2.php?option=com_massmail', $msg );
			return;
		}

		// get users in the group out of the acl
		$to = $acl->get_group_objects( $group, 'ARO', $recurse );

		$rows = array();
		if ( count( $to['users'] ) || $group === '0' ) {
			// Get sending email address
			$query = "SELECT email FROM #__users WHERE id='$my->id'";
			$database->setQuery( $query );
			$my->email = $database->loadResult();

			// Get all users email and group except for senders
			$query = "SELECT email FROM #__users"
			. "\n WHERE id != '$my->id'"
			. ( $group !== '0' ? " AND id IN (" . implode( ',', $to['users'] ) . ")" : '' )
			;
			$database->setQuery( $query );
			$rows = $database->loadObjectList();

			// Build e-mail message format
			$message_header 	= $_LANG->sprintf( '_MASSMAIL_MESSAGE', $sitename );
			$message 			= $message_header . $message_body;
			$subject 			= $sitename. ' / '. stripslashes( $subject);

			//Send email
			foreach ($rows as $row) {
				mosMail( $mailfrom, $fromname, $row->email, $subject, $message, $mode );
			}
		}

		$msg = $_LANG->sprintf( 'Email sent to X users', count( $rows ) );
		$this->setRedirect( 'index2.php?option=com_massmail', $msg );
	}
}

$tasker = new massmailTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>