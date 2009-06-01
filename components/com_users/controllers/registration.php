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
 * Registration controller class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersControllerRegistration extends UsersController
{
	/**
	 * Method to activate a user.
	 *
	 * @access	public
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function activate()
	{
		$user		= &JFactory::getUser();
		$uParams	= &JComponentHelper::getParams('com_users');

		// If the user is logged in, return them back to the homepage.
		if ($user->get('id')) {
			$this->setRedirect('index.php');
			return true;
		}

		// If user registration or account activation is disabled, throw a 403.
		if ($uParams->get('useractivation', 1) == 0 || $uParams->get('allowUserRegistration', 1) == 0) {
			JError::raiseError(403, JText::_('ACCESS FORBIDDEN'));
			return false;
		}

		$model = &$this->getModel('Registration', 'UsersModel');
		$token = JRequest::getVar('token', null, 'request', 'alnum');

		// Check that the token is in a valid format.
		if ($token === null || strlen($token) !== 32) {
			JError::raiseError(403, JText::_('USERS ACTIVATION INVALID TOKEN'));
			return false;
		}

		// Attempt to activate the user.
		$return = $model->activate($token);

		// Check for errors.
		if ($return === false)
		{
			// Redirect back to the homepage.
			$this->setMessage(JText::sprintf('USERS ACTIVATION SAVE FAILED', $model->getError()), 'notice');
			$this->setRedirect('index.php');
			return false;
		}

		// Redirect to the login screen.
		$this->setMessage(JText::_('USERS ACTIVATION SAVE SUCCESS'));
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		return true;
	}

	/**
	 * Method to register a member.
	 *
	 * @access	public
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function register()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('USERS INVALID TOKEN'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Registration', 'UsersModel');

		// Get the member data.
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// Validate the posted data.
		$return = $model->validate($data);

		// Check for errors.
		if ($return === false)
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
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the registration screen.
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration', false));
			return false;
		}

		// Attempt to save the data.
		$data	= $return;
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('USERS REGISTRATION SAVE FAILED', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration', false));
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.registration.data', null);

		// Redirect to the profile screen.
		$this->setMessage(JText::_('USERS REGISTRATION SAVE SUCCESS'));
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));

		return true;
	}
}