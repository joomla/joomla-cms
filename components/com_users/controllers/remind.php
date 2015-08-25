<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

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
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('Remind', 'UsersModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Submit the password reset request.
		$return = $model->processRemindRequest($data);

		// Check for a hard error.
		if ($return == false)
		{
			// The request failed.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route  = 'index.php?option=com_users&view=remind' . $itemid;

			// Go back to the request form.
			$message = JText::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');

			return false;
		}
		else
		{
			// The request succeeded.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route  = 'index.php?option=com_users&view=login' . $itemid;

			// Proceed to step two.
			$message = JText::_('COM_USERS_REMIND_REQUEST_SUCCESS');
			$this->setRedirect(JRoute::_($route, false), $message);

			return true;
		}
	}
}
