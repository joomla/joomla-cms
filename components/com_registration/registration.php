<?php
/**
* @version $Id$
* @package Joomla
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

require_once( $mainframe->getPath( 'front_html' ) );

switch( $task ) {
	case 'lostPassword':
		lostPassForm( $option );
		break;

	case 'sendNewPass':
		sendNewPass( $option );
		break;

	case 'register':
		registerForm( $option, $mosConfig_useractivation );
		break;

	case 'saveRegistration':
		saveRegistration( $option );
		break;

	case 'activate':
		activate( $option );
		break;
}

function lostPassForm( $option ) {
	global $mainframe;
	;

	$mainframe->SetPageTitle( JText::_( 'Lost your Password?' ) );

	HTML_registration::lostPassForm($option);
}

function sendNewPass( $option ) {
	global $database, $Itemid;
	global $mosConfig_live_site, $mosConfig_sitename;
	global $mosConfig_mailfrom, $mosConfig_fromname;
	;

	$_live_site = $mosConfig_live_site;
	$_sitename 	= $mosConfig_sitename;

	// ensure no malicous sql gets past
	$checkusername	= mosGetParam( $_POST, 'checkusername', '' );
	$checkusername	= $database->getEscaped( $checkusername );
	$confirmEmail	= mosGetParam( $_POST, 'confirmEmail', '');
	$confirmEmail	= $database->getEscaped( $confirmEmail );

	$query = "SELECT id"
	. "\n FROM #__users"
	. "\n WHERE username = '$checkusername'"
	. "\n AND email = '$confirmEmail'"
	;
	$database->setQuery( $query );
	if (!($user_id = $database->loadResult()) || !$checkusername || !$confirmEmail) {
		mosRedirect( "index.php?option=$option&task=lostPassword&mosmsg=". JText::_( 'Sorry, no corresponding user was found' ) );
	}

	$newpass = mosMakePassword();
	$message = JText::_( 'The user account' ) ." ". $checkusername ." ". JText::_( 'NEWPASS_MSG1' ) 
                ." ". $mosConfig_live_site ." ". JText::_( 'NEWPASS_MSG2' )
                ." ". $newpass . JText::_( 'NEWPASS_MSG3' );

	eval ("\$message = \"$message\";");
	$subject = $_sitename ." :: ". JText::_( 'New password for -' ) ." ". $checkusername;
	eval ("\$subject = \"$subject\";");

	mosMail($mosConfig_mailfrom, $mosConfig_fromname, $confirmEmail, $subject, $message);

	$newpass = md5( $newpass );
	$sql = "UPDATE #__users"
	. "\n SET password = '$newpass'"
	. "\n WHERE id = $user_id"
	;
	$database->setQuery( $sql );
	if (!$database->query()) {
		die("SQL error" . $database->stderr(true));
	}

	mosRedirect( "index.php?Itemid=$Itemid&mosmsg=". JText::_( 'New User Password created and sent!' ) );
}

function registerForm( $option, $useractivation ) {
	global $mainframe;
	;

	if (!$mainframe->getCfg( 'allowUserRegistration' )) {
		mosNotAuth();
		return;
	}

  	$mainframe->SetPageTitle( JText::_( 'Registration' ) );

	HTML_registration::registerForm($option, $useractivation);
}

function saveRegistration( $option ) {
	global $database, $acl, $_MAMBOTS;
	global $mosConfig_sitename, $mosConfig_live_site, $mosConfig_useractivation, $mosConfig_allowUserRegistration;
	global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailfrom, $mosConfig_fromname;
	;

	if ($mosConfig_allowUserRegistration=='0') {
		mosNotAuth();
		return;
	}

	$row = new mosUser( $database );

	mosMakeHtmlSafe($row);

	if (!$row->bind( $_POST, 'usertype' )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	//load user bot group
	$_MAMBOTS->loadBotGroup( 'user' );

	$row->id = 0;
	$row->usertype = '';
	$row->gid = $acl->get_group_id( 'Registered', 'ARO' );

	if ($mosConfig_useractivation == '1') {
		$row->activation = md5( mosMakePassword() );
		$row->block = '1';
	}

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$pwd 				= $row->password;
	$row->password 		= md5( $row->password );
	$row->registerDate 	= date('Y-m-d H:i:s');

	//trigger the onBeforeStoreUser event
	$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array( get_object_vars( $row ), true ) );

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

		//trigger the onAfterStoreUser event
		$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array( get_object_vars( $row ), true, true, null ) );

	$name 		= $row->name;
	$email 		= $row->email;
	$username 	= $row->username;

	$subject 	= sprintf ( JText::_( 'Account details for %s at %s' ), $name, $mosConfig_sitename);
	$subject 	= html_entity_decode($subject, ENT_QUOTES);
	if ($mosConfig_useractivation=="1"){
		$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $mosConfig_sitename, $mosConfig_live_site."/index.php?option=com_registration&task=activate&activation=".$row->activation, $mosConfig_live_site, $username, $pwd);
	} else {
		$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $mosConfig_sitename, $mosConfig_live_site);
	}

	$message = html_entity_decode($message, ENT_QUOTES);
	// Send email to user
	if ($mosConfig_mailfrom != "" && $mosConfig_fromname != "") {
		$adminName2 = $mosConfig_fromname;
		$adminEmail2 = $mosConfig_mailfrom;
	} else {
		$query = "SELECT name, email"
		. "\n FROM #__users"
		. "\n WHERE LOWER( usertype ) = 'superadministrator'"
		. "\n OR LOWER( usertype ) = 'super administrator'"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$row2 			= $rows[0];
		$adminName2 	= $row2->name;
		$adminEmail2 	= $row2->email;
	}

	mosMail($adminEmail2, $adminName2, $email, $subject, $message);

	// Send notification to all administrators
	$subject2 = sprintf ( JText::_( 'Account details for %s at %s' ), $name, $mosConfig_sitename);
	$message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $adminName2, $mosConfig_sitename, $row->name, $email, $username);
	$subject2 = html_entity_decode($subject2, ENT_QUOTES);
	$message2 = html_entity_decode($message2, ENT_QUOTES);

	// get superadministrators id
	$admins = $acl->get_group_objects( 25, 'ARO' );

	foreach ( $admins['users'] AS $id ) {
		$query = "SELECT email, sendEmail"
		. "\n FROM #__users"
		."\n WHERE id = $id"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		$row = $rows[0];

		if ($row->sendEmail) {
			mosMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
		}
	}

	if ( $mosConfig_useractivation == 1 ){
		echo JText::_( 'REG_COMPLETE_ACTIVATE' );
	} else {
		echo JText::_( 'REG_COMPLETE' );
	}
}

function activate( $option ) {
	global $database;
	global $mosConfig_useractivation, $mosConfig_allowUserRegistration;
	;

	if ($mosConfig_allowUserRegistration == '0' || $mosConfig_useractivation == '0') {
		mosNotAuth();
		return;
	}

	$activation = mosGetParam( $_REQUEST, 'activation', '' );
	$activation = $database->getEscaped( $activation );

	if (empty( $activation )) {
		echo JText::_( 'REG_ACTIVATE_NOT_FOUND' );
		return;
	}

	$query = "SELECT id"
	. "\n FROM #__users"
	. "\n WHERE activation = '$activation'"
	. "\n AND block = 1"
	;
	$database->setQuery( $query );
	$result = $database->loadResult();

	if ($result) {
		$query = "UPDATE #__users"
		. "\n SET block = 0, activation = ''"
		. "\n WHERE activation = '$activation'"
		. "\n AND block = 1"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "SQL error" . $database->stderr(true);
		}
		echo JText::_( 'REG_ACTIVATE_COMPLETE' );
	} else {
		echo JText::_( 'REG_ACTIVATE_NOT_FOUND' );
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
