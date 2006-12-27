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

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, JText::_( 'Registration' ) );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch( JRequest::getVar('task') )
{
	case 'lostPassword':
		RegistrationController::displayPasswordForm();
		break;

	case 'register':
		RegistrationController::displayRegisterForm();
		break;

	case 'sendreminder':
		RegistrationController::sendReminder();
		break;

	case 'save':
		RegistrationController::save();
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
	/**
	 * Prepares the registration form
	 * @return void
	 */
	function displayRegisterForm()
	{
		global $mainframe;
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if (!$usersConfig->get( 'allowUserRegistration' )) {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		$pathway =& $mainframe->getPathWay();

	 	// Page Title
	 	$mainframe->SetPageTitle( JText::_( 'Registration' ) );
		// Breadcrumb
	  	$pathway->addItem( JText::_( 'New' ));

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.php');
		$view = new RegistrationViewRegister();
		$view->user = JFactory::getUser();

		$view->display();
	}


	function displayPasswordForm()
	{
		global $mainframe;

		$mainframe->SetPageTitle( JText::_( 'Lost your Password?' ) );

		$breadcrumbs =& $mainframe->getPathWay();
		$breadcrumbs->addItem( JText::_( 'Lost your Password?' ));

		require_once (JPATH_COMPONENT.DS.'views'.DS.'lostpass'.DS.'view.php');
		$view = new RegistrationViewLostpass();

		$view->display();
	}

	/**
	 * Sends a new password or username reminder to a verified user
	 *
	 * @return	void
	 * @since	1.5
	 */
	function sendReminder()
	{
		global $mainframe, $Itemid;

		// Initialize variables
		$siteURL 	= JURI::base();
		$config		=& JFactory::getConfig();
		$db 		=& JFactory::getDBO();

		// Get the request variables from the post
		$username	= JRequest::getVar( 'jusername', '', 'post' );
		$email		= JRequest::getVar( 'jemail', '', 'post' );

		if ($username) {
			// We have a username ... send a new password
			$query = "SELECT id, email" .
					"\n FROM #__users" .
					"\n WHERE username = '".$db->getEscaped($username)."'";
			$db->setQuery( $query );
			if (!($user = $db->loadObject()) || !$username) {
				$mainframe->redirect( 'index.php?option=com_registration&task=lostPassword&Itemid='.$Itemid, JText::_( 'Sorry, no corresponding user was found' ) );
			}

			// Generate new password
			jimport('joomla.user.authenticate');
			$newpass = JAuthenticateHelper::genRandomPassword();

			// Set new password for the user
			$query = "UPDATE #__users" .
					"\n SET password = '".md5($newpass)."'" .
					"\n WHERE id = ".$user->id;
			$db->setQuery( $query );
			if (!$db->query()) {
				JError::raiseError( 404, JText::_('SQL error' ) . $db->stderr(true));
			}

			// Build the email body and subject
			$message = JText::sprintf( 'NEWPASS_MAIL_MSG', $username, JText::_( 'NEWPASS_MSG1' ), $siteURL, JText::_( 'NEWPASS_MSG2' ), $newpass, JText::_( 'NEWPASS_MSG3' ) );
			eval ("\$message = \"$message\";");
			$subject = JText::sprintf( 'New password for', $config->getValue('config.sitename'), $username );
			eval ("\$subject = \"$subject\";");

			// Send the new password email
			JUtility::sendMail($config->getValue('config.mailfrom'), $config->getValue('config.fromname'), $user->email, $subject, $message);

			$mainframe->redirect( 'index.php', JText::_( 'New User Password created and sent!' ) );
		} else {
			// No username... do we have an email address?
			if ($email) {
				// We have an email address ... is it a valid one?
				$query = "SELECT username" .
						"\n FROM #__users" .
						"\n WHERE email = '".$db->getEscaped($email)."'";
				$db->setQuery( $query );
				if (!($username = $db->loadResult()) || !$email) {
					$mainframe->redirect( 'index.php?option=com_registration&task=lostPassword&Itemid='.$Itemid, JText::_( 'Sorry, no corresponding user was found' ) );
				}

				// Build the email body and subject
				$message = JText::sprintf( 'RESEND_MAIL_MSG', $username, JText::_( 'RESEND_MSG1' ), $siteURL, JText::_( 'RESEND_MSG2' ), JText::_( 'RESEND_MSG3' ) );
				eval ("\$message = \"$message\";");
				$subject = JText::_( 'Resend username for', $config->getValue('config.sitename') );
				eval ("\$subject = \"$subject\";");

				// Send the username reminder email
				JUtility::sendMail($config->getValue('config.mailfrom'), $config->getValue('config.fromname'), $email, $subject, $message);

				$mainframe->redirect( 'index.php', JText::_( 'Username resent' ) );
			} else {
				// We have nothing ... send fail
				$mainframe->redirect( 'index.php?option=com_registration&task=lostPassword&Itemid='.$Itemid, JText::_( 'Sorry, no corresponding user was found' ) );
			}
		}
	}

	/**
	 * Save user registration and notify users and admins if required
	 * @return void
	 */
	function save()
	{
		global $mainframe;

		// Get required system objects
		$user 		=& JFactory::getUser();
		$pathway 	=& $mainframe->getPathWay();
		$config		=& JFactory::getConfig();
		$authorize	=& JFactory::getACL();

		// If user registration is not allowed, show 403 not authorized.
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		// Initialize new usertype setting
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$newUsertype = $usersConfig->get( 'new_usertype' );
		if (!$newUsertype) {
			$newUsertype = 'Registered';
		}

		// Bind the post array to the user object
		if (!$user->bind( JRequest::get('post'), 'usertype' )) {
			JError::raiseError( 500, $user->getError());
			exit();
		}

		// Set some initial user values
		$user->set('id', 0);
		$user->set('usertype', '');
		$user->set('gid', $authorize->get_group_id( $newUsertype, 'ARO' ));
		$user->set('registerDate', date('Y-m-d H:i:s'));

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get( 'useractivation' );
		if ($useractivation == '1') {
			jimport('joomla.user.authenticate');
			$user->set('activation', md5( JAuthenticateHelper::genRandomPassword()) );
			$user->set('block', '1');
		}

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.php');
		$view = new RegistrationViewRegister();

		$view->assignRef('user', $user);
		$message = new stdClass();

		// If there was an error with registration, set the message and display form
		if (!$user->save()) {
		 	// Page Title
		 	$mainframe->setPageTitle( JText::_( 'Registration' ) );
			// Breadcrumb
		  	$pathway->addItem( JText::_( 'New' ));

			$message->title = JText::_( 'REGERROR' );
			$message->text = $user->getError();
			JError::raiseWarning( 500, JText::_( 'REGERROR' ));
			$view->assign('message', $message);
			$view->display();

			return false;
		}

		// Send registration confirmation mail
		$password = JRequest::getVar( 'password' );
		RegistrationController::_sendMail($user, $password);

		// Everything went fine, set relevant message depending upon user activation state and display message
		if ( $useractivation == 1 ) {
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ));

			$message->title = JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' );
			$message->text = JText::_( 'REG_COMPLETE_ACTIVATE' );
		} else {
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_COMPLETE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_COMPLETE_TITLE' ));

			$message->title = JText::_( 'REG_COMPLETE_TITLE' );
			$message->text = JText::_( 'REG_COMPLETE' );
		}

		$view->assign('message', $message);
		$view->display('message');
	}

	function activate()
	{
		global $mainframe;

		// Initialize some variables
		$db			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$pathway 	=& $mainframe->getPathWay();

		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$userActivation			= $usersConfig->get('useractivation');
		$allowUserRegistration	= $usersConfig->get('allowUserRegistration');

		// Check to see if they're logged in, because they don't need activating!
		if($user->get('id')) {
			// They're already logged in, so redirect them to the home page
			$mainframe->redirect( 'index.php' );
		}

		if ($allowUserRegistration == '0' || $userActivation == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.php');
		$view = new RegistrationViewRegister();

		$message = new stdClass();

		// Do we even have an activation string?
		$activation = JRequest::getVar( 'activation', '' );
		$activation = $db->getEscaped( $activation );

		if (empty( $activation ))
		{
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
			$view->assign('message', $message);
			$view->display('message');
			return;
		}

		// Lets activate this user.
		if (JUserHelper::activateUser($activation))
		{
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_COMPLETE' );
		}
		else
		{
			// Page Title
			$mainframe->SetPageTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
		}

		$view->assign('message', $message);
		$view->display('message');
	}

	function _sendMail(&$user, $password)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();

		$name 		= $user->get('name');
		$email 		= $user->get('email');
		$username 	= $user->get('username');

		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$sitename 		= $mainframe->getCfg( 'sitename' );
		$useractivation = $usersConfig->get( 'useractivation' );
		$mailfrom 		= $mainframe->getCfg( 'mailfrom' );
		$fromname 		= $mainframe->getCfg( 'fromname' );
		$siteURL		= JURI::base();

		$subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		if ( $useractivation == 1 ){
			$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."/index.php?option=com_registration&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
		} else {
			$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
		}

		$message = html_entity_decode($message, ENT_QUOTES);
		// Send email to user
		if ($mailfrom != "" && $fromname != "") {
			$adminName2 = $fromname;
			$adminEmail2 = $mailfrom;
		} else {
			$query = "SELECT name, email" .
					"\n FROM #__users" .
					"\n WHERE LOWER( usertype ) = 'superadministrator'" .
					"\n OR LOWER( usertype ) = 'super administrator'";
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
		$authorize =& JFactory::getACL();
		$admins = $authorize->get_group_objects( 25, 'ARO' );

		foreach ( $admins['users'] AS $id )
		{
			$query = "SELECT email, sendEmail" .
					"\n FROM #__users" .
					"\n WHERE id = $id";
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			$row = $rows[0];

			if ($row->sendEmail) {
				JUtility::sendMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
			}
		}
	}
}
?>