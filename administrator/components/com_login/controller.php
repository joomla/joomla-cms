<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Login
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Login Controller
 *
 * @package		Joomla
 * @subpackage	Login
 * @since 1.5
 */
class LoginController extends JController
{
	function login()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$credentials = array();

		$credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
		$credentials['password'] = JRequest::getVar('passwd', '', 'post', 'string', JREQUEST_ALLOWRAW);

		$result = $mainframe->login($credentials);

		if (!JError::isError($result)) {
			$mainframe->redirect('index.php');
		}

		LoginController::display();
	}

	function logout()
	{
		global $mainframe;

		$result = $mainframe->logout();

		if (!JError::isError($result)) {
			$mainframe->redirect('index.php?option=com_login');
		}

		LoginController::display();
	}
}