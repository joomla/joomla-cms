<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('UsersController', JPATH_COMPONENT . '/controller.php');

/**
 * Reset controller class for Users.
 *
 * @since  1.6
 */
class UsersControllerRemind extends UsersController
{
	/**
	 * Method to request a username reminder.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function remind()
	{
		// Check the request token.
		$this->checkToken('post');

		$model = $this->getModel('Remind', 'UsersModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Submit the password reset request.
		$return	= $model->processRemindRequest($data);

		// Check for a hard error.
		if ($return == false && JDEBUG)
		{
			// The request failed.
			// Go back to the request form.
			$message = JText::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=remind', false), $message, 'notice');

			return false;
		}

		// To not expose if the user exists or not we send a generic message.
		$message = JText::_('COM_USERS_REMIND_REQUEST');
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false), $message, 'notice');

		return true;
	}
}
