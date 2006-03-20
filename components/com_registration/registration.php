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
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JApplicationHelper::getPath( 'front_html' ) );

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, JText::_( 'Registration' ) );

switch( $task ) 
{
	case 'lostPassword':
		lostPassForm();
		break;

	case 'sendNewPass':
		sendNewPass();
		break;

	case 'register':
		registerForm();
		break;

	case 'saveRegistration':
		saveRegistration();
		break;

	case 'activate':
		activate();
		break;
		
	case 'cancel':
		mosRedirect( 'index.php' );
		break;	
}

function lostPassForm() 
{
	global $mainframe;

	$mainframe->SetPageTitle( JText::_( 'Lost your Password?' ) );

	$breadcrumbs =& $mainframe->getPathWay();
	$breadcrumbs->addItem( JText::_( 'Lost your Password?' ));
	
	HTML_registration::lostPassForm();
}

function sendNewPass() 
{
	global $database, $mainframe;
	global $mosConfig_mailfrom, $mosConfig_fromname;
	
	$siteURL 	= $mainframe->getBaseURL();
	$_sitename 	= $mainframe->getCfg('sitename');

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
		mosRedirect( 'index.php?option=com_registration&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
	}

	$newpass = mosMakePassword();
	$message = sprintf( JText::_( 'NEWPASS_MAIL_MSG' ), $checkusername, JText::_( 'NEWPASS_MSG1' ), $siteURL, JText::_( 'NEWPASS_MSG2' ), $newpass, JText::_( 'NEWPASS_MSG3' ) );

	eval ("\$message = \"$message\";");
	$subject = sprintf( JText::_( 'New password for' ), $_sitename, $checkusername );
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

	mosRedirect( 'index.php?option=com_registration', JText::_( 'New User Password created and sent!' ) );
}

function registerForm() 
{
	global $mainframe;
	
	if (!$mainframe->getCfg( 'allowUserRegistration' )) {
		mosNotAuth();
		return;
	}
	
	$breadcrumbs =& $mainframe->getPathWay();

 	// Page Title
 	$mainframe->SetPageTitle( JText::_( 'Registration' ) ); 
	// Breadcrumb
  	$breadcrumbs->addItem( JText::_( 'New' ));

	HTML_registration::registerForm();
}

function saveRegistration() 
{
	global $database, $acl, $mainframe;
	global $mosConfig_sitename, $mosConfig_useractivation, $mosConfig_allowUserRegistration;
	global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailfrom, $mosConfig_fromname;
	
	if ($mosConfig_allowUserRegistration=='0') {
		mosNotAuth();
		return;
	}
	
	$siteURL 		= $mainframe->getBaseURL();
	$breadcrumbs 	=& $mainframe->getPathWay();

	$user =& JUser::getInstance( );

	if (!$user->bind( $_POST, 'usertype' )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$user->set('id', 0);
	$user->set('usertype', '');
	$user->set('gid', $acl->get_group_id( '', 'Registered', 'ARO' ));

	if ($mosConfig_useractivation == '1') {
		$user->set('activation', md5( mosMakePassword()) );
		$user->set('block', '1');
	}

	$pwd = $user->get('password');
	$user->set('password', md5( $pwd ));
	$user->set('registerDate', date('Y-m-d H:i:s'));

	if (!$user->store()) {
		echo "<script> alert('".$user->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$name 		= $user->get('name');
	$email 		= $user->get('email');
	$username 	= $user->get('username');

	$subject 	= sprintf ( JText::_( 'Account details for' ), $name, $mosConfig_sitename);
	$subject 	= html_entity_decode($subject, ENT_QUOTES);
	if ( $mosConfig_useractivation == 1 ){
		$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $mosConfig_sitename, $siteURL."/index.php?option=com_registration&task=activate&activation=".$user->get('activation'), $siteURL, $username, $pwd);
	} else {
		$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $mosConfig_sitename, $siteURL);
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
		// Page Title
		$mainframe->SetPageTitle( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ) );
		// Breadcrumb
		$breadcrumbs->addItem( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ));
		
		HTML_registration::message( 'REG_COMPLETE_ACTIVATE_TITLE', 'REG_COMPLETE_ACTIVATE' );
	} else {
		// Page Title
		$mainframe->SetPageTitle( JText::_( 'REG_COMPLETE_TITLE' ) );
		// Breadcrumb
		$breadcrumbs->addItem( JText::_( 'REG_COMPLETE_TITLE' ));
		
		HTML_registration::message( 'REG_COMPLETE_TITLE', 'REG_COMPLETE' );
	}
}

function activate() 
{
	global $mainframe;
	
	/*
	 * Initialize some variables
	 */
	$db						= & $mainframe->getDBO();
	$UserActivation			= $mainframe->getCfg('useractivation');
	$AllowUserRegistration	= $mainframe->getCfg('allowUserRegistration');
	$breadcrumbs 			=& $mainframe->getPathWay();

	if ($AllowUserRegistration == '0' || $UserActivation == '0') {
		mosNotAuth();
		return;
	}

	/*
	 * Do we even have an activation string?
	 */
	$activation = JRequest::getVar( 'activation', '' );
	$activation = $db->getEscaped( $activation );
	
	if (empty( $activation )) 
	{
		// Page Title
		$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
		// Breadcrumb
		$breadcrumbs->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));
		
		HTML_registration::message( 'REG_ACTIVATE_NOT_FOUND_TITLE', 'REG_ACTIVATE_NOT_FOUND' );
		return;
	}
	
	/*
	 * Lets activate this user.
	 */
	if (JUserHelper::activate($activation)) 
	{
		// Page Title
		$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ) );
		// Breadcrumb
		$breadcrumbs->addItem( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ));
		
		HTML_registration::message( 'REG_ACTIVATE_COMPLETE_TITLE', 'REG_ACTIVATE_COMPLETE' );
	} 
	else 
	{
		// Page Title
		$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
		// Breadcrumb
		$breadcrumbs->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));
		
		HTML_registration::message( 'REG_ACTIVATE_NOT_FOUND_TITLE', 'REG_ACTIVATE_NOT_FOUND' );
	}
}
?>