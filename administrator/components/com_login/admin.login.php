<?php
/**
 * @version		$Id: admin.login.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Joomla.Extensions
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

switch (JRequest::getCmd('task'))
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
 * @package		Joomla.Administrator
 * @subpackage	Login
 * @since		1.5
 */
class LoginController
{
	function display()
	{
		jimport('joomla.application.module.helper');
		$module = & JModuleHelper::getModule('mod_login');
		$module = JModuleHelper::renderModule($module, array('style' => 'rounded', 'id' => 'section-box'));
		echo $module;
	}

	function login()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken('request') or jexit('Invalid Token');

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
