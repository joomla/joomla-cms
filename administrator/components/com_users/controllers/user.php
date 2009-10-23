<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * The Users User Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerUser extends JController
{
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		// Map the save tasks.
		$this->registerTask('save2new',		'save');
		$this->registerTask('apply',		'save');
	}

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
	 * Method to add a new user.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialize variables.
		$app = JFactory::getApplication();

		// Clear the level edit information from the session.
		$app->setUserState('com_users.edit.user.id', null);
		$app->setUserState('com_users.edit.user.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
	}

	/**
	 * Method to edit an existing user.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app	= JFactory::getApplication();
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the user to edit.
		$userId = (int) (count($cid) ? $cid[0] : JRequest::getInt('user_id'));

		// Set the id for the user to edit in the session.
		$app->setUserState('com_users.edit.user.id', $userId);
		$app->setUserState('com_users.edit.user.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
	}

	/**
	 * Method to cancel an edit
	 *
	 * @return	void
	 */
	public function cancel()
	{
		// Initialize variables.
		$app = JFactory::getApplication();

		// Clear the user edit information from the session.
		$app->setUserState('com_users.edit.user.id', null);
		$app->setUserState('com_users.edit.user.data', null);

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false));
	}

	/**
	 * Method to save a user.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
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

	/**
	 * Method to run batch opterations.
	 *
	 * @return	void
	 */
	function batch()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= JFactory::getApplication();
		$model	= &$this->getModel('User');
		$vars	= JRequest::getVar('batch', array(), 'post', 'array');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Sanitize user ids.
		$cid = array_unique($cid);
		JArrayHelper::toInteger($cid);

		// Remove any values of zero.
		if (array_search(0, $cid, true)) {
			unset($cid[array_search(0, $cid, true)]);
		}

		// Attempt to run the batch operation.
		if (!$model->batch($vars, $cid))
		{
			// Batch operation failed, go back to the users list and display a notice.
			$message = JText::sprintf('USERS_USERS_BATCH_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=users', $message, 'error');
			return false;
		}

		$message = JText::_('USERS_USERS_BATCH_SUCCESS');
		$this->setRedirect('index.php?option=com_users&view=users', $message);
		return true;
	}

	/**
	 * Method to delete users.
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
		$model = &$this->getModel('User');

		// Attempt to delete the item(s).
		if (!$model->delete($cid)) {
			$this->setMessage(JText::sprintf('USERS_USER_DELETE_FAILED', $model->getError()), 'notice');
		}
		else {
			$this->setMessage(JText::sprintf('USERS_USER_DELETE_SUCCESS', count($cid)));
		}

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false));
	}
}