<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * User Component Controller
 *
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.5
 */
class UserController extends JController
{
	/**
	 * Method to display a view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		parent::display();
	}

	function edit()
	{
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		if ( $user->get('guest')) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		JRequest::setVar('layout', 'form');

		parent::display();
	}

	function save( )
	{
		//preform token check (prevent spoofing)
		$token	= JUtility::getToken();
		if(!JRequest::getVar( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$user	 =& JFactory::getUser();
		$userid = JRequest::getVar( 'id', 0, 'post', 'int' );

		// preform security checks
		if ($user->get('id') == 0 || $userid == 0 || $userid <> $user->get('id')) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		//clean request
		$post = JRequest::get( 'post' );
		$post['password']	= JRequest::getVar('password', '', 'post', 'string');
		$post['verifyPass']	= JRequest::getVar('verifyPass', '', 'post', 'string');

		// do a password safety check
		if(strlen($post['password'])) { // so that "0" can be used as password e.g.
			if($post['password'] != $post['verifyPass']) {
				$msg	= JText::_( 'Passwords do not match');
				$this->setRedirect( $_SERVER['HTTP_REFERER'], $msg );
				return false;
			}
		}

		// store data
		$model = $this->getModel('user');

		if ($model->store($post)) {
			$msg	= JText::_( 'Your settings have been saved.' );
		} else {
			//$msg	= JText::_( 'Error saving your settings.' );
			$msg	= $model->getError();
		}

		$this->setRedirect( $_SERVER['HTTP_REFERER'], $msg );
	}

	function cancel()
	{
		$this->setRedirect( 'index.php' );
	}

	function login()
	{
		global $mainframe;
		
		$username = JRequest::getString('username');
		$password = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);
		$remember = JRequest::getBool('remember', false, 'post');

		if ($return = JRequest::getVar( 'return', false, '' )) {
			$return = base64_decode($return);
		}

		//check the token before we do anything else
		//$token	= JUtility::getToken();
		//if(!JRequest::getVar( $token, 0, 'post' )) {
		//	JError::raiseError(403, 'Request Forbidden');
		//}
		
		//preform the login action
		$error = $mainframe->login($username, $password, $remember);

		if(!JError::isError($error))
		{
			// Redirect if the return url is not registration or login
			if ( $return && !( strpos( $return, 'com_user' ) || strpos( $return, 'com_login' ) ) ) {
				$mainframe->redirect( $return );
			}
		}
		else
		{
			// Facilitate third party login forms
			if ( $return ) {
				$mainframe->redirect( $return );
			} else {
				parent::display();
			}
		}
	}

	function logout()
	{
		global $mainframe;

		//preform the logout action
		$error = $mainframe->logout();

		if(!JError::isError($error))
		{
			$return	= JRequest::getVar( 'return', false, '' );
			
			if ($return) {
				$return = base64_decode($return);
			}

			// Redirect if the return url is not registration or login
			if ( $return && !( strpos( $return, 'com_user' ) || strpos( $return, 'com_login' ) ) ) {
				$mainframe->redirect( $return );
			}
		} else {
			parent::display();
		}
	}

	/**
	 * Prepares the registration form
	 * @return void
	 */
	function register()
	{
		global $mainframe;

		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if (!$usersConfig->get( 'allowUserRegistration' )) {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		JRequest::setVar('view', 'register');

		parent::display();
	}


	function lostpassword()
	{
		JRequest::setVar('view', 'lostpass');
		parent::display();
	}

	/**
	 * Sends a new password or username reminder to a verified user
	 *
	 * @return	void
	 * @since	1.5
	 */
	function sendreminder()
	{
		global $mainframe;

		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getVar( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		}

		// Initialize variables
		$siteURL 	= JURI::base();
		$config		=& JFactory::getConfig();
		$db 		=& JFactory::getDBO();

		// Get the request variables from the post
		$username	= JRequest::getVar( 'jusername', '', 'post' );
		$email		= JRequest::getVar( 'jemail', '', 'post' );

		if ($username)
		{
			// We have a username ... send a new password
			$query = 'SELECT id, email' .
					' FROM #__users' .
					' WHERE username = "'.$db->getEscaped($username).'"';
			$db->setQuery( $query );
			if (!($user = $db->loadObject()) || !$username) {
				$mainframe->redirect( 'index.php?option=com_user&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
			}

			// Generate new password
			jimport('joomla.user.helper');
			$newpass = JUserHelper::genRandomPassword();

			// Set new password for the user
			$query = 'UPDATE #__users' .
					' SET password = "'.md5($newpass).'"' .
					' WHERE id = '.$user->id;
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
		}
		else
		{
			// No username... do we have an email address?
			if ($email)
			{
				// We have an email address ... is it a valid one?
				$query = 'SELECT username' .
						' FROM #__users' .
						' WHERE email = "'.$db->getEscaped($email)."'";
				$db->setQuery( $query );
				if (!($username = $db->loadResult()) || !$email) {
					$mainframe->redirect( 'index.php?option=com_user&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
				}

				// Build the email body and subject
				$message = JText::sprintf( 'RESEND_MAIL_MSG', $username, JText::_( 'RESEND_MSG1' ), $siteURL, JText::_( 'RESEND_MSG2' ), JText::_( 'RESEND_MSG3' ) );
				eval ("\$message = \"$message\";");
				$subject = JText::_( 'Resend username for', $config->getValue('config.sitename') );
				eval ("\$subject = \"$subject\";");

				// Send the username reminder email
				JUtility::sendMail($config->getValue('config.mailfrom'), $config->getValue('config.fromname'), $email, $subject, $message);

				$mainframe->redirect( 'index.php', JText::_( 'Username resent' ) );
			}
			else
			{
				// We have nothing ... send fail
				$mainframe->redirect( 'index.php?option=com_user&task=lostPassword', JText::_( 'Sorry, no corresponding user was found' ) );
			}
		}
	}

	/**
	 * Save user registration and notify users and admins if required
	 * @return void
	 */
	function register_save()
	{
		global $mainframe;

		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getVar( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		}

		// Get required system objects
		$user 		=& JFactory::getUser();
		$pathway 	=& $mainframe->getPathWay();
		$config		=& JFactory::getConfig();
		$authorize	=& JFactory::getACL();
		$document   =& JFactory::getDocument();

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
		}

		// Set some initial user values
		$user->set('id', 0);
		$user->set('usertype', '');
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
		$user->set('registerDate', date('Y-m-d H:i:s'));

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get( 'useractivation' );
		if ($useractivation == '1') {
			jimport('joomla.user.helper');
			$user->set('activation', md5( JUserHelper::genRandomPassword()) );
			$user->set('block', '1');
		}

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.html.php');
		$view = new UserViewRegister();

		$view->assignRef('user', $user);
		$message = new stdClass();

		// If there was an error with registration, set the message and display form
		if ( !$user->save() ) {
		 	// Page Title
		 	$document->setTitle( JText::_( 'Registration' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'New' ) );

			$message->title	= JText::_( 'REGERROR' );
			$message->text	= JText::_( $user->getError() );

			$view->assign('message', $message);
			$view->display();

			return false;
		}

		// Send registration confirmation mail
		$password = JRequest::getVar( 'password' );
		UserController::_sendMail($user, $password);

		// Everything went fine, set relevant message depending upon user activation state and display message
		if ( $useractivation == 1 ) {
			// Page Title
			$document->setTitle( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' ));

			$message->title = JText::_( 'REG_COMPLETE_ACTIVATE_TITLE' );
			$message->text = JText::_( 'REG_COMPLETE_ACTIVATE' );
		} else {
			// Page Title
			$document->setTitle( JText::_( 'REG_COMPLETE_TITLE' ) );
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
		$document   =& JFactory::getDocument();
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
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.html.php');
		$view = new UserViewRegister();

		$message = new stdClass();

		// Do we even have an activation string?
		$activation = JRequest::getVar( 'activation', '' );
		$activation = $db->getEscaped( $activation );

		if (empty( $activation ))
		{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
			$view->assign('message', $message);
			$view->display('message');
			return;
		}

		// Lets activate this user
		jimport('joomla.user.helper');
		if (JUserHelper::activateUser($activation))
		{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_COMPLETE' );
		}
		else
		{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
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
			$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
		} else {
			$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
		}

		$message = html_entity_decode($message, ENT_QUOTES);
		// Send email to user
		if ($mailfrom != "" && $fromname != "") {
			$adminName2 = $fromname;
			$adminEmail2 = $mailfrom;
		} else {
			$query = 'SELECT name, email' .
					' FROM #__users' .
					' WHERE LOWER( usertype ) = "superadministrator"' .
					' OR LOWER( usertype ) = "super administrator"';
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
			$query = 'SELECT email, sendEmail' .
					' FROM #__users' .
					' WHERE id = '. $id;
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
