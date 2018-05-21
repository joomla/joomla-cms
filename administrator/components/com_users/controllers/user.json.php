<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User controller class.
 *
 * @since  1.6
 */
class UsersControllerUser extends JControllerForm
{
	/**
	 * Method to terminate an existing user's session.
	 *
	 * @return  void  True if the record can be added, an error object if not.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function endSession()
	{
		JLog::add('my debug message' . $this->input->getInt('user_id'), JLog::DEBUG, 'my-debug-category');

		$userId = $this->input->getInt('user_id'); // is getting the User id not the Edit id
		$data   = array();

		// var_dump($userId);
		// exit();


		// exit

		if ($userId !== 0)
		{
			/** @var UsersModelUser $model */
			$model = $this->getModel('User');
			$model->destroyUsersSessions($userId);
			$data['success'] = JText::_('COM_USERS_LOGGED_OUT_SUCCESS');
		}
		else
		{
			$data['error'] = JText::_('COM_MESSAGES_ERR_INVALID_USER');
		}


		echo new JResponseJson($data);
		jexit();
	}

}
