<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * The Users Group Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerGroups extends JController
{
	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_users', false));
	}

	/**
	 * Method to add a new group.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialize variables.
		$app = JFactory::getApplication();

		// Clear the group edit information from the session.
		$app->setUserState('com_users.edit.group.id', null);
		$app->setUserState('com_users.edit.group.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=group&layout=edit', false));
	}

	/**
	 * Method to edit an existing group.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app	= JFactory::getApplication();
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$groupId = (int) (count($cid) ? $cid[0] : JRequest::getInt('group_id'));

		// Set the group id for the group to edit in the session.
		$app->setUserState('com_users.edit.group.id', $groupId);
		$app->setUserState('com_users.edit.group.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=group&layout=edit', false));
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @return	void
	 */
	public function cancel()
	{
		// Initialize variables.
		$app = JFactory::getApplication();

		// Clear the group edit information from the session.
		$app->setUserState('com_users.edit.group.id', null);
		$app->setUserState('com_users.edit.group.data', null);

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=groups', false));
	}

	/**
	 * Method to save a group.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= JFactory::getApplication();
		$model	= &$this->getModel('Group');

		// Get the posted values from the request.
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_users.edit.group.id');

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
			$app->setUserState('com_users.edit.group.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=group&layout=edit', false));
			return false;
		}

		// Fudge the access section for usergroups for now.
		$data['section_id']	= 1;
		$data['section']	= 'core';

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.group.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('USERS_GROUP_SAVE_FAILED', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=group&layout=edit', false));
			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task)
		{
			case 'apply':
				// Set the row data in the session.
				$app->setUserState('com_users.edit.group.id',	$model->getState('group.id'));
				$app->setUserState('com_users.edit.group.data',	null);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('USERS_GROUP_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=group&layout=edit', false));
				break;

			case 'save2new':
				// Clear the group id and data from the session.
				$app->setUserState('com_users.edit.group.id', null);
				$app->setUserState('com_users.edit.group.data', null);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('USERS_GROUP_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=group&layout=edit', false));
				break;

			default:
				// Clear the group id and data from the session.
				$app->setUserState('com_users.edit.group.id', null);
				$app->setUserState('com_users.edit.group.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('USERS_GROUP_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=groups', false));
				break;
		}
	}

	/**
	 * Method to delete groups.
	 *
	 * @return	void
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get and sanitize the items to delete.
		$cid = JRequest::getVar('cid', null, 'post', 'array');
		JArrayHelper::toInteger($cid);

		// Get the model.
		$model = &$this->getModel('Group');

		// Attempt to delete the item(s).
		if (!$model->delete($cid)) {
			$this->setMessage(JText::sprintf('USERS_GROUP_DELETE_FAILED', $model->getError()), 'notice');
		}
		else {
			$this->setMessage(JText::sprintf('USERS_GROUP_DELETE_SUCCESS', count($cid)));
		}

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=groups', false));
	}
}
