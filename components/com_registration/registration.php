<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
		josRedirect( 'index.php' );
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

/**
 * Sends a new password to the email adress
 * @return void
 *
 */
function sendNewPass()
{
	global $mainframe;

	$siteURL 	= $mainframe->getBaseURL();
	$sitename 	= $mainframe->getCfg('sitename');
	$db 		=& $mainframe->getDBO();

	// ensure no malicous sql gets past
	$checkusername	= JRequest::getVar( 'checkusername', '', 'post' );
	$checkusername	= $db->getEscaped( $checkusername );
	$confirmEmail	= JRequest::getVar( 'confirmEmail', '', 'post' );
	$confirmEmail	= $db->getEscaped( $confirmEmail );

	$query = "SELECT id"
	. "\n FROM #__users"
	. "\n WHERE username = '$checkusername'"
	. "\n AND email = '$confirmEmail'"
	;
	$db->setQuery( $query );
	if (!($user_id = $db->loadResult()) || !$checkusername || !$confirmEmail) {
		josRedirect( 'index.php?option=com_registration&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
	}

	$newpass = mosMakePassword();
	$message = sprintf( JText::_( 'NEWPASS_MAIL_MSG' ), $checkusername, JText::_( 'NEWPASS_MSG1' ), $siteURL, JText::_( 'NEWPASS_MSG2' ), $newpass, JText::_( 'NEWPASS_MSG3' ) );

	eval ("\$message = \"$message\";");
	$subject = sprintf( JText::_( 'New password for' ), $sitename, $checkusername );
	eval ("\$subject = \"$subject\";");

	$mailfrom = $mainframe->getCfg( 'mailfrom' );
	$fromname = $mainframe->getCfg( 'fromname' );
	mosMail($mailfrom, $fromname, $confirmEmail, $subject, $message);

	$newpass = md5( $newpass );
	$sql = "UPDATE #__users"
	. "\n SET password = '$newpass'"
	. "\n WHERE id = $user_id"
	;
	$db->setQuery( $sql );
	if (!$db->query()) {
		JError::raiseError( 404, JText::_('SQL error' ) . $db->stderr(true));
	}

	josRedirect( 'index.php?option=com_registration', JText::_( 'New User Password created and sent!' ) );
}

/**
 * Prepares the registration form
 * @return void
 */
function registerForm()
{
	global $mainframe;
	$user = $mainframe->getUser();

	if (!$mainframe->getCfg( 'allowUserRegistration' )) {
		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
		return;
	}

	$breadcrumbs =& $mainframe->getPathWay();

 	// Page Title
 	$mainframe->SetPageTitle( JText::_( 'Registration' ) );
	// Breadcrumb
  	$breadcrumbs->addItem( JText::_( 'New' ));

	HTML_registration::registerForm( $user );
}

/**
 * Save user registration and notify users and admins if required
 * @return void
 */
function saveRegistration()
{
	global $mainframe;

	$db = $mainframe->getDBO();
	$user = $mainframe->getUser();
	$acl = &JFactory::getACL();

	$allowUserRegistration = $mainframe->getCfg( 'allowUserRegistration' );
	$useractivation = $mainframe->getCfg( 'useractivation' );
	$sitename = $mainframe->getCfg( 'sitename' );
	$mailfrom = $mainframe->getCfg( 'mailfrom' );
	$fromname = $mainframe->getCfg( 'fromname' );

	if ($allowUserRegistration=='0') {
		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
		return;
	}

	$siteURL 		= $mainframe->getBaseURL();
	$breadcrumbs 	=& $mainframe->getPathWay();

	$user =& JUser::getInstance();

	if (!$user->bind( $_POST, 'usertype' )) {
		JError::raiseError( 500, $row->getError());
		exit();
	}

	$user->set('id', 0);
	$user->set('usertype', '');
	$user->set('gid', $acl->get_group_id( 'Registered', 'ARO' ));

	if ($useractivation == '1') {
		$user->set('activation', md5( mosMakePassword()) );
		$user->set('block', '1');
	}

	$pwd = $user->get('password');
	$user->set('registerDate', date('Y-m-d H:i:s'));

	if (!$user->save()) {

		$breadcrumbs =& $mainframe->getPathWay();

	 	// Page Title
	 	$mainframe->SetPageTitle( JText::_( 'Registration' ) );
		// Breadcrumb
	  	$breadcrumbs->addItem( JText::_( 'New' ));

		HTML_registration::errorMessage( JText::_( 'REGERROR' ), $user->getError() );
		HTML_registration::registerForm( $user );
		return false;
	}

	$name 		= $user->get('name');
	$email 		= $user->get('email');
	$username 	= $user->get('username');

	$subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
	$subject 	= html_entity_decode($subject, ENT_QUOTES);
	if ( $useractivation == 1 ){
		$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."/index.php?option=com_registration&task=activate&activation=".$user->get('activation'), $siteURL, $username, $pwd);
	} else {
		$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
	}

	$message = html_entity_decode($message, ENT_QUOTES);
	// Send email to user
	if ($mailfrom != "" && $fromname != "") {
		$adminName2 = $fromname;
		$adminEmail2 = $mailfrom;
	} else {
		$query = "SELECT name, email"
		. "\n FROM #__users"
		. "\n WHERE LOWER( usertype ) = 'superadministrator'"
		. "\n OR LOWER( usertype ) = 'super administrator'"
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		$row2 			= $rows[0];
		$adminName2 	= $row2->name;
		$adminEmail2 	= $row2->email;
	}

	mosMail($adminEmail2, $adminName2, $email, $subject, $message);

	// Send notification to all administrators
	$subject2 = sprintf ( JText::_( 'Account details for %s at %s' ), $name, $sitename);
	$message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $adminName2, $sitename, $name, $email, $username);
	$subject2 = html_entity_decode($subject2, ENT_QUOTES);
	$message2 = html_entity_decode($message2, ENT_QUOTES);

	// get superadministrators id
	$admins = $acl->get_group_objects( 25, 'ARO' );

	foreach ( $admins['users'] AS $id ) {
		$query = "SELECT email, sendEmail"
		. "\n FROM #__users"
		."\n WHERE id = $id"
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		$row = $rows[0];

		if ($row->sendEmail) {
			mosMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
		}
	}

	if ( $useractivation == 1 ){
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
	$db						=& $mainframe->getDBO();
	$userActivation			= $mainframe->getCfg('useractivation');
	$allowUserRegistration	= $mainframe->getCfg('allowUserRegistration');
	$breadcrumbs 			=& $mainframe->getPathWay();

	if ($allowUserRegistration == '0' || $userActivation == '0') {
		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
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
	if (JUserHelper::activateUser($activation))
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