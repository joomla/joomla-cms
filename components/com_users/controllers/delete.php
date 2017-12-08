<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('UsersController', JPATH_COMPONENT . '/controller.php');

/**
 * Delete controller class for Users.
 *
 * @since  __DEPLOY_VERSION__
 */
class UsersControllerDelete extends UsersController
{
	/**
	 * Method to request a username self-deletion.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete()
	{
		// Check the request token.
		$this->checkToken('post');

		$model = $this->getModel('Delete', 'UsersModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Submit the user delete request.
		$return	= $model->processDeleteRequest($data);

		// Check for a hard error.
		if ($return == false)
		{
			// The request failed.
			// Go back to the request form.
			$message = JText::sprintf('COM_USERS_DELETE_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=delete', false), $message, 'notice');

			return false;
		}
		else
		{
			// The request succeeded.
			// Proceed to step two.
			$message = JText::_('COM_USERS_DELETE_REQUEST_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false), $message);

			return true;
		}
	}
}
