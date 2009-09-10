<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Profile controller class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersControllerProfile extends UsersController
{
	/**
	 * Method to check out a member for editing and redirect to the edit form.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function edit()
	{
		$app	= &JFactory::getApplication();
		$user	= &JFactory::getUser();
		$userId	= (int) $user->get('id');

		// Get the previous member id (if any) and the current member id.
		$previousId = (int) $app->getUserState('com_users.edit.profile.id');
		$memberId	= (int) JRequest::getInt('member_id', null, '', 'array');

		// Check if the user is trying to edit another users profile.
		if ($userId != $memberId) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return false;
		}

		// Set the member id for the member to edit in the session.
		$app->setUserState('com_users.edit.profile.id', $memberId);

		// Get the model.
		$model = &$this->getModel('Profile', 'UsersModel');

		// Check out the member.
		if ($memberId) {
			$model->checkout($memberId);
		}

		// Check in the previous member.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&member_id='.$memberId, false));
	}

	/**
	 * Method to save a member's profile data.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Profile', 'UsersModel');
		$user	= &JFactory::getUser();
		$userId	= (int)$user->get('id');

		// Get the member data.
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// Check if the user is trying to edit another users profile.
		if ($userId != $data['id']) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return false;
		}

		// Validate the posted data.
		$data = $model->validate($data);

		// Check for errors.
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
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$memberId = (int)$app->getUserState('com_users.edit.profile.id');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&member_id='.$memberId, false));
			return false;
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$memberId = (int)$app->getUserState('com_users.edit.profile.id');
			$this->setMessage(JText::sprintf('USERS PROFILE SAVE FAILED', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&member_id='.$memberId, false));
			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task)
		{
			case 'apply':
				// Check out the profile.
				$app->setUserState('com_users.edit.profile.id', $return);
				$model->checkout($return);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('USERS PROFILE SAVE SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&hidemainmenu=1', false));
				break;

			default:
				// Check in the profile.
				$memberId = (int)$app->getUserState('com_users.edit.profile.id');
				if ($memberId) {
					$model->checkin($memberId);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_users.edit.profile.id', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('USERS PROFILE SAVE SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&member_id='.$return, false));
				break;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.edit.profile.data', null);
	}
}