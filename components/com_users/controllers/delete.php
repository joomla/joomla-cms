<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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

		// Check for errors.
		if ($return === false)
		{
			$message = JText::sprintf('COM_USERS_DELETE_REQUEST_FAILED', $model->getError());

			// The request failed. Go back to the request form.
			$return = base64_encode(JUri::getInstance());
			$delete_url_with_return = JRoute::_('index.php?option=com_users&return=' . $return);
			$this->setRedirect($delete_url_with_return, $message, 'warning');

			return false;
		}
		
		// The request succeeded.
		return true;
	}

}
