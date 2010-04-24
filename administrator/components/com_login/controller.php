<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		JRequest::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app = &JFactory::getApplication();

		$model = &$this->getModel('login');
		$credentials = $model->getState('credentials');
		$return = $model->getState('return');

		$result = $app->login($credentials, array('action' => 'core.login.admin'));

		if (!JError::isError($result)) {
			$app->redirect($return);
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

		$userid = JRequest::getInt('uid', null);

		$options = array(
			'clientid' => ($userid) ? 0 : 1
		);

		$result = $app->logout($userid, $options);

		if (!JError::isError($result)) {
			$model 	= &$this->getModel('login');
			$return = $model->getState('return');
			$app->redirect($return);
		}

		parent::display();
	}
}