<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

jimport( 'joomla.application.component.controller' );

/**
 * Login Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @since		1.5
 */
class LoginController extends JController
{
	/**
	 * Method to log in a user.
	 * 
	 * @return	void
	 */
	public function login()
	{
		// Check for request forgeries.
		JRequest::checkToken('request') or jExit(JText::_('JInvalid_Token'));

		$app = &JFactory::getApplication();

		$model = &$this->getModel('login');
		$credentials = $model->getState('credentials');

		$result = $app->login($credentials, array('action' => 'core.administrator.login'));

		if (!JError::isError($result)) {
			$app->redirect('index.php');
		}

		parent::display();
	}

	/**
	 * Method to log out a user.
	 * 
	 * @return	void
	 */
	public function logout()
	{
		$app = &JFactory::getApplication();

		$result = $app->logout();

		if (!JError::isError($result)) {
			$app->redirect('index.php?option=com_login');
		}

		parent::display();
	}
}