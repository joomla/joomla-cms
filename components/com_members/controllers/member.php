<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

/**
 * Registration controller class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersControllerMember extends MembersController
{
	/**
	 * Method to login a user.
	 */
	public function login()
	{
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app = &JFactory::getApplication();
		$data = $app->getUserState('members.login.form.data', array());

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return'])) {
			$data['return'] = 'index.php?option=com_members&view=profile';
		}

		// Get the login options.
		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return']	= $data['return'];
		$options['action']	= 'core.site.login';

		// Get the login credentials.
		$credentials = array();
		$credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
		$credentials['password'] = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);

		// Perform the login.
		$error = $app->login($credentials, $options);

		// Check if the login succeeded.
		if(!JError::isError($error)) {
			$app->setUserState('members.login.form.data', array());
			$app->redirect(JRoute::_($data['return'], false));
		} else {
			$data['remember'] = (int)$options['remember'];
			$app->setUserState('members.login.form.data', $data);
			$app->redirect(JRoute::_('index.php?option=com_members&view=login', false));
		}
	}

	/**
	 * Method to register a user.
	 */
	public function register()
	{
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		// Get the form data.
		$data	= JRequest::getVar('user', array(), 'post', 'array');

		// Get the model and validate the data.
		$model	= &$this->getModel('Registration', 'MembersModel');
		$return	= $model->validate($data);

		// Check for errors.
		if ($return === false)
		{
			// Get the validation messages.
			$app	= &JFactory::getApplication();
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('members.registration.form.data', $data);

			// Redirect back to the registration form.
			$this->setRedirect('index.php?option=com_members&view=registration');
			return false;
		}

		// Finish the registration.
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('members.registration.form.data', $data);

			// Redirect back to the registration form.
			$message = JText::sprintf('MEMBERS REGISTRATION FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_members&view=registration', $message, 'error');
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('members.registration.form.data', null);


		var_dump($return);
		exit;
	}

	/**
	 * Method to login a user.
	 */
	public function remind()
	{
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));
	}

	/**
	 * Method to login a user.
	 */
	public function resend()
	{
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));
	}
}