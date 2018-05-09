<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Activation controller class for manage the ajax request
 *
 * @since  __DEPLOY_VERSION__
 */
class UsersControllerActivation extends JControllerForm
{
	/**
	 * Method to Active the user and send an email notification
	 *
	 * @param   string   &$message  The message that will be returned
	 * @param   boolean  &$error    If there was an error obtaining the data
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function checkFormData(&$message, &$error)
	{
		$model = $this->getModel('User', 'UsersModel');
		$jinput = JFactory::getApplication()->input->json;
		$userID = $jinput->get('user_id', '', 'string');

		if (empty($userID))
		{
			$message = JText::_('COM_USERS_USERS_NO_ITEM_SELECTED');
			$error = true;

			return false;
		}
		else
		{
			$table = $model->getTable();
			$table->load($userID);
			$actived = $table->activation;

			// Change the state of the records.
			if (!$model->activate($userID))
			{
				$message = JError::raiseWarning(500, $model->getError());
				$error = true;

				return false;
			}
			elseif (!empty($actived))
			{
				// Active the user and send an email notification
				$message = JText::sprintf('COM_USERS_USER_ACTIVATED_NOTIFIED', $table->name);
			}
			else
			{
				// Resend the email notification
				$message = JText::sprintf('COM_USERS_USER_NOTIFIED', $table->name);
			}

			return true;
		}
	}

	/**
	 * Method for generating JSON output
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function send()
	{
		$message = JText::_('COM_USERS_USER_NOTIFIED');
		$error = false;
		$checkResult = $this->checkFormData($message, $error);

		try
		{
			echo new JResponseJson($checkResult, $message, $error);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}
}
