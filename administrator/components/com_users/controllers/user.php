<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * User controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerUser extends JControllerForm
{
	/**
	 * Overrides parent save method to check the submitted passwords match.
	 */
	public function save()
	{
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// TODO: JForm should really have a validation handler for this.
		if (isset($data['password']) && isset($data['password2']))
		{
			// Check the passwords match.
			if ($data['password'] != $data['password2'])
			{
				$this->setError('Users_Error_Password_mismatch');
				return false;
			}
			unset($data['password2']);
		}

		return parent::save();
	}


	/**
	 * Method to save a user.
	 *
	 * @return	void
	 */
	public function ___save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= &$this->getModel('User');

		// Get the posted values from the request.
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_users.edit.user.id');

		// Validate the posted data.
		$form	= &$model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_users.edit.user.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
			return false;
		}

		// Get and sanitize the group data.
		$data['groups'] = JRequest::getVar('groups', array(), 'post', 'array');
		$data['groups'] = array_unique($data['groups']);
		JArrayHelper::toInteger($data['groups']);

		// Remove any values of zero.
		if (array_search(0, $data['groups'], true)) {
			unset($data['groups'][array_search(0, $data['groups'], true)]);
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.user.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('USERS_USER_SAVE_FAILED', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task)
		{
			case 'apply':
				// Set the row data in the session.
				$app->setUserState('com_users.edit.user.id',	$model->getState('user.id'));
				$app->setUserState('com_users.edit.user.data',	null);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('USERS_USER_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
				break;

			case 'save2new':
				// Clear the user id and data from the session.
				$app->setUserState('com_users.edit.user.id', null);
				$app->setUserState('com_users.edit.user.data', null);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('USERS_USER_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
				break;

			default:
				// Clear the user id and data from the session.
				$app->setUserState('com_users.edit.user.id', null);
				$app->setUserState('com_users.edit.user.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('USERS_USER_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false));
				break;
		}
	}

}