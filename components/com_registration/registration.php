<?php
/**
* @version $Id: registration.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Registration
 * @subpackage Registration
 */
class registrationTasks_front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function registrationTasks_front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'register' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	/**
	 * Loads `Registration` HTML Output
	 */
	function register() {
		global $mainframe;
		global $_LANG;

		if ( !$mainframe->getCfg( 'allowUserRegistration' ) ) {
			mosNotAuth();
			return;
		}

		$mainframe->SetPageTitle( $_LANG->_( 'REGISTER_TITLE' ) );

		mosFS::load( '@front_html' );

		registrationScreens_front::register();
	}

	/**
	 * Loads `Lost Password` HTML Output
	 */
	function lostPassword() {
	  	global $_LANG, $mainframe;

		$mainframe->SetPageTitle( $_LANG->_( 'PROMPT_PASSWORD' ) );

		mosFS::load( '@front_html' );

		registrationScreens_front::lostPass();
	}

	/**
	 * Used to send a new password to a User, if they have forgotten their old password
	 */
	function sendNewPass() {
		global $database;
		global $mosConfig_sitename, $mosConfig_mailfrom;
	  	global $_LANG;

		// ensure no malicous sql gets past
		$userName 	= trim( mosGetParam( $_POST, 'checkusername', '') );
		$userName 	= $database->getEscaped( $userName );
		$userEmail 	= trim( mosGetParam( $_POST, 'confirmEmail', '') );
		$userEmail 	= $database->getEscaped( $userEmail );

		$query = "SELECT id"
		. "\n FROM #__users"
		. "\n WHERE username = '$userName'"
		. "\n AND email = '$userEmail'"
		;
		$database->setQuery( $query );
		// validate new password request
		if ( !( $user_id = $database->loadResult() ) || !$userName || !$userEmail ) {
			mosErrorAlert( $_LANG->_( 'ERROR_PASS' ) );
		}

		// generate a new password
		$newpass 		= mosMakePassword();
		// convert password to md5
		$newpass_md5 	= md5( $newpass );

		// Change password
		$query = "UPDATE #__users"
		. "\n SET password = '$newpass_md5'"
		. "\n WHERE id = '$user_id'"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $database->stderr() );
		}

		// email password to user
		$message = $_LANG->_( 'NEWPASS_MSG' );
		eval ( "\$message = \"$message\";" );

		$subject = $_LANG->_( 'NEWPASS_SUB' );
		eval ( "\$subject = \"$subject\";" );

		mosMail( $mosConfig_mailfrom, $mosConfig_sitename, $userEmail, $subject, $message );

		mosRedirect( 'index.php', $_LANG->_( 'NEWPASS_SENT' ) );
	}

	/**
	 * Save submit of registration form
	 */
	function saveRegistration() {
		global $database, $acl, $_MAMBOTS, $mainframe;
		global $mosConfig_sitename, $mosConfig_live_site, $mosConfig_useractivation, $mosConfig_allowUserRegistration;
		global $mosConfig_mailfrom, $mosConfig_fromname;
		global $mosConfig_new_usertype;
		global $_LANG;

		// Check whether User Registration is enabled
		if ( !$mosConfig_allowUserRegistration ) {
			mosNotAuth();
			return;
		}

		$row = new mosUser( $database );

		mosMakeHtmlSafe( $row );

		if ( !$row->bind( $_POST, 'usertype' ) ) {
			mosErrorAlert( $row->getError() );
		}

		//load user bot group
		$_MAMBOTS->loadBotGroup( 'user' );

		$row->id 		= 0;
		// new user Usertype as set in Global Configuration
		$row->usertype 	= $mosConfig_new_usertype;
		// Get GID for usertype set in Global Configuration
		$row->gid 		= $acl->get_group_id( null, $mosConfig_new_usertype, 'ARO' );

		// Account activation email check
		if ( $mosConfig_useractivation ) {
			$row->activation 	= md5( mosMakePassword() );
			$row->block 		= 1;
		}

		if ( !$row->check() ) {
			mosErrorAlert( $row->getError() );
		}

		// md5 hash password
		$pwd 				= $row->password;
		$row->password 		= md5( $row->password );
		// register date
		$row->registerDate 	= $mainframe->getDateTime();

		//trigger the onBeforeStoreUser event
		$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array( get_object_vars( $row ), false ) );

		if ( !$row->store() ) {
			mosErrorAlert( $row->getError() );
		}
		$row->checkin();

		//trigger the onAfterStoreUser event
		$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array( get_object_vars( $row ), false, true, null ) );

		// List of Super Administrators
		$admins = $mainframe->getAdmins();

		// email registration information handling
		$user_name 		= $row->name;
		$user_email 	= $row->email;
		$user_username 	= $row->username;

		$user_subject 	= $_LANG->sprintf ( 'SEND_SUB', $user_name, $mosConfig_sitename );
		$user_subject 	= html_entity_decode( $user_subject, ENT_QUOTES);

		if ( $mosConfig_useractivation ) {
			$user_message = $_LANG->sprintf ( 'USEND_MSG_ACTIVATE', $user_name, $mosConfig_sitename, $mosConfig_live_site .'/index.php?option=com_registration&task=activate&activation='. $row->activation, $mosConfig_live_site, $user_username, $pwd );
		} else {
			$user_message = $_LANG->sprintf ( 'USEND_MSG', $user_name, $mosConfig_sitename, $mosConfig_live_site );
		}
		$user_message = html_entity_decode( $user_message, ENT_QUOTES );

		if ( $mosConfig_mailfrom != '' && $mosConfig_fromname != '' ) {
			$site_Name 	= $mosConfig_fromname;
			$site_Email = $mosConfig_mailfrom;
		} else {
		// If no `From Name` and `From Email` set in GC, use first Super Administrator
			$site_Name 	= $admins[0]->name;
			$site_Email = $admins[0]->email;
		}

		// send email notification/activation to new user
		mosMail( $site_Email, $site_Name, $user_email, $user_subject, $user_message );

		////////////////////////////////////

		$admin_subject = $_LANG->sprintf ( 'SEND_SUB', $user_name, $mosConfig_sitename );
		$admin_subject = html_entity_decode( $admin_subject, ENT_QUOTES );
		$admin_message = $_LANG->sprintf ( 'ASEND_MSG', $site_Name, $mosConfig_sitename, $user_name, $user_email, $user_username );
		$admin_message = html_entity_decode( $admin_message, ENT_QUOTES );

		// send email notification to administrators
		foreach ( $admins as $admin ) {
			mosMail( $site_Email, $site_Name, $admin->email, $admin_subject, $admin_message );
		}

		if ( $mosConfig_useractivation ) {
			$msg = $_LANG->_( 'REG_COMPLETE_ACTIVATE' );
		} else {
			$msg = $_LANG->_( 'REG_COMPLETE' );
		}

		mosRedirect( 'index.php', $msg );
	}

	/**
	 * Activates a user that has applied for registration
	 */
	function activate() {
		global $database;
		global $mosConfig_useractivation, $mosConfig_allowUserRegistration;
		global $_LANG;

		if ($mosConfig_allowUserRegistration == '0' || $mosConfig_useractivation == '0') {
			mosNotAuth();
			return;
		}

		$activation = mosGetParam( $_REQUEST, 'activation', '' );
		$activation = $database->getEscaped( $activation );

		if (empty( $activation )) {
			$msg = $_LANG->_( 'REG_ACTIVATE_NOT_FOUND' );
			mosRedirect( 'index.php', $msg );
		}

		$query = "SELECT id"
		. "\n FROM #__users"
		. "\n WHERE activation = '$activation'"
		. "\n AND block = '1'"
		;
		$database->setQuery( $query );
		$result = $database->loadResult();

		if ( $result ) {
			// Activate User
			$query = "UPDATE #__users"
			. "\n SET block = '0', activation = ''"
			. "\n WHERE activation = '$activation'"
			. "\n AND block = '1'"
			;
			$database->setQuery( $query );
			if ( !$database->query() ) {
				mosErrorAlert( 'SQL error ' . $database->stderr() );
			}

			$msg = $_LANG->_( 'REG_ACTIVATE_COMPLETE' );
		} else {
			$msg = $_LANG->_( 'REG_ACTIVATE_NOT_FOUND' );
		}

		mosRedirect( 'index.php', $msg );
	}
}
$tasker = new registrationTasks_front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();

/**
 * Checks for correct email syntax
 * @param string
 * @return bool
 */
function is_email( $email ){
	$rBool = false;

	if ( preg_match( "/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email ) ) {
		$rBool=true;
	}

	return $rBool;
}
?>