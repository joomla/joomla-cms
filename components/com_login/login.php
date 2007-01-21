<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Users
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
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar('task'))
{
	case 'login' :
		LoginController::login();
		break;
	case 'logout' :
		LoginController::logout();
		break;
	default :
		LoginController::display();
		break;
}

/**
 * Static class to hold controller functions for the Login component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Login
 * @since		1.5
 */

class LoginController
{
	function display()
	{
		global $mainframe, $Itemid, $option;

		// Initialize variables
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();
		$pathway	=& $mainframe->getPathway();

		$menu		=& JSiteHelper::getActiveMenuItem();
		$params		=& JSiteHelper::getMenuParams();

		// Set some default page parameters if not set
		$params->def( 'page_title', 				1 );
		$params->def( 'header_login', 				$menu->name );
		$params->def( 'header_logout', 				$menu->name );
		$params->def( 'pageclass_sfx', 				'' );
		$params->def( 'login', 						'index.php' );
		$params->def( 'logout', 					'index.php' );
		$params->def( 'description_login', 			1 );
		$params->def( 'description_logout', 		1 );
		$params->def( 'description_login_text', 	JText::_( 'LOGIN_DESCRIPTION' ) );
		$params->def( 'description_logout_text',	JText::_( 'LOGOUT_DESCRIPTION' ) );
		$params->def( 'image_login', 				'key.jpg' );
		$params->def( 'image_logout', 				'key.jpg' );
		$params->def( 'image_login_align', 			'right' );
		$params->def( 'image_logout_align', 		'right' );
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$params->def( 'registration', 				$usersConfig->get( 'allowUserRegistration' ) );

		if ( !$user->get('guest') )
		{
			$title = JText::_( 'Logout');

			// pathway item
			$pathway->setItemName(1, $title );
			// Set page title
			$document->setTitle( $title );
		}
		else
		{
			$title = JText::_( 'Login');

			// pathway item
			$pathway->setItemName(1, $title );
			// Set page title
			$document->setTitle( $title );
		}

		require_once (JPATH_COMPONENT.DS.'views'.DS.'login'.DS.'view.php');
		$view = new LoginViewLogin();

		$view->assign('type', (!$user->get('guest')) ? 'logout' : 'login');
		$view->assignRef('params', $params);

		$view->display();
	}

	function login()
	{
		global $mainframe;

		$username	= JRequest::getVar( 'username' );
		$password	= JRequest::getVar( 'password' );
		$return		= JRequest::getVar('return', false);

		//check the token before we do anything else
		//$token	= JUtility::getToken();
		//if(!JRequest::getVar( $token, 0, 'post' )) {
		//	JError::raiseError(403, 'Request Forbidden');
		//}

		$error = $mainframe->login($username, $password);

		if(!JError::isError($error))
		{
			/*
			 * checks for the presence of a return url and ensures that this url is not
			 * the registration or login pages
			 */
			if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
				$mainframe->redirect( $return );
			}
		}
		else
		{
			// Facilitate third party login forms
			if ( $return ) {
				$mainframe->redirect( $return );
			} else {
				LoginController::display();
			}
		}
	}

	function logout()
	{
		global $mainframe;

		$error = $mainframe->logout();

		if(!JError::isError($error))
		{
			$return = JRequest::getVar( 'return' );

			/*
			 * checks for the presence of a return url and ensures that this url is not
			 * the registration or login pages
			 */
			if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
				$mainframe->redirect( $return );
			}
		} else {
			LoginController::display();
		}
	}
}
?>