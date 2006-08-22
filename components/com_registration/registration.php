<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
define( 'JPATH_COM_REGISTRATION', dirname( __FILE__ ));

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, JText::_( 'Registration' ) );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch( JRequest::getVar( 'task' ) )
{
	case 'lostPassword':
		RegistrationController::lostPassForm();
		break;

	case 'sendNewPass':
		$userunknown	= JRequest::getVar( 'userunkown', 0, 'post', 'integer' );
		if( $userunknown == 1 ) {
			RegistrationController::resendUser();
		} else {
			RegistrationController::sendNewPass();
		}
		break;

	case 'register':
		RegistrationController::registerForm();
		break;

	case 'saveRegistration':
		RegistrationController::saveRegistration();
		break;

	case 'activate':
		RegistrationController::activate();
		break;

	case 'cancel':
		$mainframe->redirect( 'index.php' );
		break;
}

/**
 * Static class to hold controller functions for the Registration component
 *
 * @static
 * @author		David Gal <david.gal@joomla.org>
 * @package		Joomla
 * @subpackage	Search
 * @since		1.5
 */
class RegistrationController
{

	function lostPassForm()
	{
		global $mainframe;
	
		$mainframe->SetPageTitle( JText::_( 'Lost your Password?' ) );
	
		$breadcrumbs =& $mainframe->getPathWay();
		$breadcrumbs->addItem( JText::_( 'Lost your Password?' ));

		require_once (JPATH_COM_REGISTRATION.DS.'views'.DS.'registration'.DS.'registration.php');
		$view = new RegistrationViewRegistration();
	
		$view->lostPassForm();
	}
	
	/**
	 * Sends a new password to the email adress
	 * @return void
	 *
	 */
	function sendNewPass()
	{
		global $mainframe;
	
		// Protect against simple spoofing attacks
		if (!JUtility::spoofCheck()) {
			JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return;
		}
	
		$siteURL 	= $mainframe->getBaseURL();
		$sitename 	= $mainframe->getCfg('sitename');
		$db 		=& JFactory::getDBO();
	
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
			$mainframe->redirect( 'index.php?option=com_registration&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
		}
	
		jimport('joomla.application.user.authenticate');
		$newpass = JAuthenticateHelper::genRandomPassword();
		$message = sprintf( JText::_( 'NEWPASS_MAIL_MSG' ), $checkusername, JText::_( 'NEWPASS_MSG1' ), $siteURL, JText::_( 'NEWPASS_MSG2' ), $newpass, JText::_( 'NEWPASS_MSG3' ) );
	
		eval ("\$message = \"$message\";");
		$subject = sprintf( JText::_( 'New password for' ), $sitename, $checkusername );
		eval ("\$subject = \"$subject\";");
	
		$mailfrom = $mainframe->getCfg( 'mailfrom' );
		$fromname = $mainframe->getCfg( 'fromname' );
		JUtility::sendMail($mailfrom, $fromname, $confirmEmail, $subject, $message);
	
		$newpass = md5( $newpass );
		$sql = "UPDATE #__users"
		. "\n SET password = '$newpass'"
		. "\n WHERE id = $user_id"
		;
		$db->setQuery( $sql );
		if (!$db->query()) {
			JError::raiseError( 404, JText::_('SQL error' ) . $db->stderr(true));
		}
	
		$mainframe->redirect( 'index.php?option=com_registration', JText::_( 'New User Password created and sent!' ) );
	}
	
	/**
	 * Resends the user details if a user with the email adress can be found
	 * @return void
	 */
	function resendUser() {
		global $mainframe;
	
		/*
		 * Protect against simple spoofing attacks
		 */
		if (!JUtility::spoofCheck()) {
			JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return;
		}
	
		$siteURL 	= $mainframe->getBaseURL();
		$sitename 	= $mainframe->getCfg('sitename');
		$db 		=& JFactory::getDBO();
	
		// ensure no malicous sql gets past
		$confirmEmail	= JRequest::getVar( 'confirmEmail', '', 'post' );
		$confirmEmail	= $db->getEscaped( $confirmEmail );
	
		$query = "SELECT username"
		. "\n FROM #__users"
		. "\n WHERE email = '$confirmEmail'"
		;
		$db->setQuery( $query );
		if (!($username = $db->loadResult()) || !$confirmEmail) {
			$mainframe->redirect( 'index.php?option=com_registration&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
		}
	
		$message = sprintf( JText::_( 'RESEND_MAIL_MSG' ), $username, JText::_( 'RESEND_MSG1' ), $siteURL, JText::_( 'RESEND_MSG2' ), JText::_( 'RESEND_MSG3' ) );
	
		eval ("\$message = \"$message\";");
		$subject = sprintf( JText::_( 'Resend username for' ), $sitename );
		eval ("\$subject = \"$subject\";");
	
		$mailfrom = $mainframe->getCfg( 'mailfrom' );
		$fromname = $mainframe->getCfg( 'fromname' );
		JUtility::sendMail($mailfrom, $fromname, $confirmEmail, $subject, $message);
	
		$mainframe->redirect( 'index.php?option=com_registration', JText::_( 'Username resend' ) );
	}
	
	/**
	 * Prepares the registration form
	 * @return void
	 */
	function registerForm()
	{
		global $mainframe;
	
		if (!$mainframe->getCfg( 'allowUserRegistration' )) {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}
	
		$breadcrumbs =& $mainframe->getPathWay();
	
	 	// Page Title
	 	$mainframe->SetPageTitle( JText::_( 'Registration' ) );
		// Breadcrumb
	  	$breadcrumbs->addItem( JText::_( 'New' ));
	
		// create the view
		require_once (JPATH_COM_REGISTRATION.DS.'views'.DS.'registration'.DS.'registration.php');
		$view = new RegistrationViewRegistration();
		$view->user = JFactory::getUser();
		
		$view->registerForm();
	}
	
	/**
	 * Save user registration and notify users and admins if required
	 * @return void
	 */
	function saveRegistration()
	{
		global $mainframe;
	
		// Protect against simple spoofing attacks
		if (!JUtility::spoofCheck()) {
			JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return;
		}
	
		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();
		$acl 	= &JFactory::getACL();
	
		$allowUserRegistration 	= $mainframe->getCfg( 'allowUserRegistration' );
		$useractivation 		= $mainframe->getCfg( 'useractivation' );
		$sitename 				= $mainframe->getCfg( 'sitename' );
		$mailfrom 				= $mainframe->getCfg( 'mailfrom' );
		$fromname 				= $mainframe->getCfg( 'fromname' );
		$new_usertype			= $mainframe->getCfg( 'new_usertype' );
	
		if ($allowUserRegistration=='0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}
	
		if (!JUtility::spoofCheck()) {
			JError::raiseError( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return;
		}
	
		if (!$new_usertype) {
			$new_usertype = 'Registered';
		}
	
		$siteURL 		= $mainframe->getBaseURL();
		$breadcrumbs 	=& $mainframe->getPathWay();
	
		$user =& JUser::getInstance();
		
		// create the view
		require_once (JPATH_COM_REGISTRATION.DS.'views'.DS.'registration'.DS.'registration.php');
		$view = new RegistrationViewRegistration();
		$view->user = $user;
		
	
		if (!$user->bind( $_POST, 'usertype' )) {
			JError::raiseError( 500, $row->getError());
			exit();
		}
	
		// setup new user
		$user->set('id', 0);
		$user->set('usertype', '');
		$user->set('gid', $acl->get_group_id( $new_usertype, 'ARO' ));
	
		if ($useractivation == '1') {
			jimport('joomla.application.user.authenticate');
			$user->set('activation', md5( JAuthenticateHelper::genRandomPassword()) );
			$user->set('block', '1');
		}
	
		// retrieving the uncrypted password for notification
		$pwd = JRequest::getVar( 'password' );
		$user->set('registerDate', date('Y-m-d H:i:s'));
	
		if (!$user->save()) {
			$breadcrumbs =& $mainframe->getPathWay();
	
		 	// Page Title
		 	$mainframe->SetPageTitle( JText::_( 'Registration' ) );
			// Breadcrumb
		  	$breadcrumbs->addItem( JText::_( 'New' ));
	
			$view->messageTitle = JText::_( 'REGERROR' );
			$view->messageText = $user->getError();
			$view->errorMessage();
			$view->registerForm();
			
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
	
		JUtility::sendMail($adminEmail2, $adminName2, $email, $subject, $message);
	
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
				JUtility::sendMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
			}
		}
	
		if ( $useractivation == 1 ){
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ) );
			// Breadcrumb
			$breadcrumbs->addItem( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ));
	
			$view->messageTitle = JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' );
			$view->messageText = JText::_( 'REG_COMPLETE_ACTIVATE' );
			$view->message();
		} else {
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_COMPLETE_TITLE' ) );
			// Breadcrumb
			$breadcrumbs->addItem( JText::_( 'REG_COMPLETE_TITLE' ));
	
			$view->messageTitle = JText::_( 'REG_COMPLETE_TITLE' );
			$view->messageText = JText::_( 'REG_COMPLETE' );
			$view->message();
		}
	}
	
	function activate()
	{
		global $mainframe;
	
		$user =& JFactory::getUser();
		
		// Check to see if they're logged in, because they don't need activating!
		if($user->get('id')) {
			// They're already logged in, so redirect them to the home page
			$mainframe->redirect( 'index.php' );
		}
	
		// Initialize some variables
		$db						=& JFactory::getDBO();
		$userActivation			= $mainframe->getCfg('useractivation');
		$allowUserRegistration	= $mainframe->getCfg('allowUserRegistration');
		$breadcrumbs 			=& $mainframe->getPathWay();
	
		if ($allowUserRegistration == '0' || $userActivation == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}
		
		// create the view
		require_once (JPATH_COM_REGISTRATION.DS.'views'.DS.'registration'.DS.'registration.php');
		$view = new RegistrationViewRegistration();
	
	
		// Do we even have an activation string?
		$activation = JRequest::getVar( 'activation', '' );
		$activation = $db->getEscaped( $activation );
	
		if (empty( $activation ))
		{
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$breadcrumbs->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$view->messageTitle = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$view->messageText = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
			$view->message();
	
			return;
		}
	
		// Lets activate this user.
		if (JUserHelper::activateUser($activation))
		{
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ) );
			// Breadcrumb
			$breadcrumbs->addItem( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ));
	
			$view->messageTitle = JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' );
			$view->messageText = JText::_( 'REG_ACTIVATE_COMPLETE' );
			$view->message();
		}
		else
		{
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$breadcrumbs->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));
	
			$view->messageTitle = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$view->messageText = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
			$view->message();
		}
	}
}
?>
