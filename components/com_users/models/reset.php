<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
/**
 * Rest model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.5
 */
class UsersModelReset extends JModelForm
{
	/**
	 * Method to get the password reset request form.
	 *
	 * @param	string	The name of the form.
	 * @param	string	The name of the form XML to load (excluding suffix).
	 * @param	array	An array of options for the form.
	 * @return	object	JForm object on success, JException on failure.
	 * @since	1.6
	 */
	public function getForm($formName = 'com_users.reset_request', $formXml = 'reset_request', $options = array('control' => 'jform'))
	{
		// Get the form.
		$form = parent::getForm($formName, $formXml, $options);
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the password reset complete form.
	 *
	 * @return	object	JForm object on success, JException on failure.
	 * @since	1.6
	 */
	public function getResetCompleteForm()
	{
		// Get the form.
		return $this->getForm('com_users.reset_complete', 'reset_complete');
	}

	/**
	 * Method to get the password reset confirm form.
	 *
	 * @return	object	JForm object on success, JException on failure.
	 * @since	1.6
	 */
	public function getResetConfirmForm()
	{
		// Get the form.
		return $this->getForm('com_users.reset_confirm', 'reset_confirm');
	}

	/**
	 * Override preprocessForm to load the user plugin group instead of content.
	 *
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm(JForm $form, $data)
	{
		parent::preprocessForm($form, $data, 'user');
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$app	= JFactory::getApplication();
		$params	= $app->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * @since	1.6
	 */
	function processResetComplete($data)
	{
		// Get the form.
		$form = &$this->getResetCompleteForm();

		// Check for an error.
		if (JError::isError($form)) {
			return $form;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			return $return;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}
			return false;
		}

		// Get the token and user id from the confirmation process.
		$app	= JFactory::getApplication();
		$token	= $app->getUserState('com_users.reset.token', null);
		$userId	= $app->getUserState('com_users.reset.user', null);

		// Check the token and user id.
		if (empty($token) || empty($userId)) {
			return new JException(JText::_('COM_USERS_RESET_COMPLETE_TOKENS_MISSING'), 403);
		}

		// Get the user object.
		$user = JUser::getInstance($userId);

		// Check for a user and that the tokens match.
		if (empty($user) || $user->activation !== $token) {
			$this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
			return false;
		}

		// Make sure the user isn't blocked.
		if ($user->block) {
			$this->setError(JText::_('COM_USERS_USER_BLOCKED'));
			return false;
		}

		// Generate the new password hash.
		jimport('joomla.user.helper');
		$salt		= JUserHelper::genRandomPassword(32);
		$crypted	= JUserHelper::getCryptedPassword($data['password1'], $salt);
		$password	= $crypted.':'.$salt;

		// Update the user object.
		$user->password			= $password;
		$user->activation		= '';
		$user->password_clear	= $data['password1'];

		// Save the user to the database.
		if (!$user->save(true)) {
			return new JException(JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
		}

		// Flush the user data from the session.
		$app->setUserState('com_users.reset.token', null);
		$app->setUserState('com_users.reset.user', null);

		return true;
	}

	/**
	 * @since	1.6
	 */
	function processResetConfirm($data)
	{
		// Get the form.
		$form = $this->getResetConfirmForm();

		// Check for an error.
		if (JError::isError($form)) {
			return $form;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			return $return;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}
			return false;
		}

		// Find the user id for the given token.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('*');
		$query->from('`#__users`');
		$query->where('`activation` = '.$db->Quote($data['token']));

		// Get the user id.
		$db->setQuery((string) $query);
		$user = $db->loadObject();

		// Check for an error.
		if ($db->getErrorNum()) {
			return new JException(JText::sprintf('COM_USERS_DATABASE_ERROR', $db->getErrorMsg()), 500);
		}

		// Check for a user.
		if (empty($user)) {
			$this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
			return false;
		}

		// Make sure the user isn't blocked.
		if ($user->block) {
			$this->setError(JText::_('COM_USERS_USER_BLOCKED'));
			return false;
		}

		// Push the user data into the session.
		$app = JFactory::getApplication();
		$app->setUserState('com_users.reset.token', $data['token']);
		$app->setUserState('com_users.reset.user', $user->id);

		return true;
	}

	/**
	 * Method to start the password reset process.
	 *
	 * @since	1.6
	 */
	public function processResetRequest($data)
	{
		$config	= JFactory::getConfig();

		// Get the form.
		$form = $this->getForm();

		// Check for an error.
		if (JError::isError($form)) {
			return $form;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			return $return;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}
			return false;
		}

		// Get the user id.
		jimport('joomla.user.helper');
		$userId	= JUserHelper::getUserId($data['username']);

		// Make sure the user exists.
		if (empty($userId)) {
			$this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
			return false;
		}

		// Get the user object.
		$user = JUser::getInstance($userId);

		// Make sure the user isn't blocked.
		if ($user->block) {
			$this->setError(JText::_('COM_USERS_USER_BLOCKED'));
			return false;
		}

		// Set the confirmation token.
		$token = JUtility::getHash(JUserHelper::genRandomPassword());
		$user->activation = $token;

		// Save the user to the database.
		if (!$user->save(true)) {
			return new JException(JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
		}

		// Assemble the password reset confirmation link.
		$mode = $config->get('force_ssl', 0) == 2 ? 1 : -1;
		$link = 'index.php?option=com_users&task=reset.confirm&username='.$user->username.'&token='.$token.'&'.JUtility::getToken(true).'=1';

		// Put together the e-mail template data.
		$data = $user->getProperties();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['link_text']	= JRoute::_($link, false, $mode);
		$data['link_html']	= JRoute::_($link, true, $mode);
		$data['token']		= $token;

		$subject = JText::sprintf(
			'COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT',
			$data['sitename']
		);

		$body = JText::sprintf(
			'COM_USERS_EMAIL_PASSWORD_RESET_BODY',
			$data['sitename'],
			$data['token'],
			$data['link_text']
		);

		// Send the password reset request e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $user->email, $subject, $message);

		// Check for an error.
		if ($return !== true) {
			return new JException(JText::_('COM_USERS_MAIL_FAILED'), 500);
		}

		return true;
	}
}