<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Joomla.Extensions
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

switch ( JRequest::getCmd('task'))
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
 * @package		Joomla
 * @subpackage	Login
 * @since		1.5
 */
class LoginController
{
	function display()
	{
		$module = & JModuleHelper::getModule('mod_login');
		$module = JModuleHelper::renderModule($module, array('style' => 'rounded', 'id' => 'section-box'));
		echo $module;
	}

	function login()
	{
		global $mainframe;

		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

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