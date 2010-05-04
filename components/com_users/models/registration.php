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
jimport('joomla.plugin.helper');

/**
 * Registration model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelRegistration extends JModelForm
{
	/**
	 * @var		object	The user registration data.
	 * @since	1.6
	 */
	protected $data;

	/**
	 * Method to activate a user account.
	 *
	 * @param	string		The activation token.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function activate($token)
	{
		$config	= JFactory::getConfig();
		$db		= $this->getDbo();

		// Get the user id based on the token.
		$db->setQuery(
			'SELECT `id` FROM `#__users`' .
			' WHERE `activation` = '.$db->Quote($token) .
			' AND `block` = 1' .
			' AND `lastvisitDate` = '.$db->Quote($db->getNullDate())
		);
		$userId = (int) $db->loadResult();

		// Check for a valid user id.
		if (!$userId) {
			$this->setError(JText::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'));
			return false;
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('users');

		// Activate the user.
		$user = JFactory::getUser($userId);
		$user->set('activation', '');
		$user->set('block', '0');

		// Store the user object.
		if (!$user->save()) {
			$this->setError($user->getError());
			return false;
		}

		/*
		// TODO: Should we send a confirming email for activation? We don't in 1.5.
		// Compile the notification mail values.
		$data = $user->getProperties();
		$data['fromname'] = $config->get('fromname');
		$data['mailfrom'] = $config->get('mailfrom');
		$data['sitename'] = $config->get('sitename');

		// Load the message template and bind the data.
		jimport('joomla.utilities.simpletemplate');
		$template = JxSimpleTemplate::getInstance('com_users.registration.confirm');
		$template->bind($data);

		// Send the registration e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $template->getTitle(), $template->getBody());

		// Check for an error.
		if ($return !== true) {
			$this->setError(JText::_('USERS ACTIVATION SEND MAIL FAILED'));
			return false;
		}
		*/

		return true;
	}

	/**
	 * Method to get the registration form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for users plugins to extend the data.
	 *
	 * @return	mixed		Data object on success, false on failure.
	 * @since	1.6
	 */
	public function getData()
	{
		if ($this->data === null) {

			$this->data	= new stdClass();
			$app	= JFactory::getApplication();
			$params	= JComponentHelper::getParams('com_users');

			// Override the base user data with any data in the session.
			$temp = (array)$app->getUserState('com_users.registration.data', array());
			foreach ($temp as $k => $v) {
				$this->data->$k = $v;
			}

			// Get the groups the user should be added to after registration.
			$this->data->groups = isset($this->data->groups) ? array_unique($this->data->groups) : array();

			// Get the default new user group, Registered if not specified.
			$system	= $params->get('new_usertype', 2);
			$this->data->usertype = $system;

			// TODO: Not sure we need all this stuff anymore. Just need to add the group to the list and we are golden.
			// Handle the system default group.
			if (!in_array($system, $this->data->groups)) {
				// Add the system group to the first position.
				array_unshift($this->data->groups, $system);
			} else {
				// Make sure the system group is the first item.
				unset($this->data->groups[array_search($system, $this->data->groups)]);
				array_unshift($this->data->groups, $system);
			}

			// Unset the passwords.
			unset($this->data->password1);
			unset($this->data->password2);

			// Get the dispatcher and load the users plugins.
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('users');

			// Trigger the data preparation event.
			$results = $dispatcher->trigger('onContentPrepareData', array('com_users.registration', $this->data));

			// Check for errors encountered while preparing the data.
			if (count($results) && in_array(false, $results, true)) {
				$this->setError($dispatcher->getError());
				$this->data = false;
			}
		}

		return $this->data;
	}

	/**
	 * Method to get the registration form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @return	mixed		JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm()
	{
		// Get the form.
		$form = parent::getForm('com_users.registration', 'registration', array('control' => 'jform'));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function getFormData()
	{
		return $this->getData();
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
	 * Method to save the form data.
	 *
	 * @param	array		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 * @since	1.6
	 */
	public function register($temp)
	{
		$config = JFactory::getConfig();
		$params = JComponentHelper::getParams('com_users');

		// Initialise the table with JUser.
		JUser::getTable('User', 'JTable');
		$user = new JUser();
		$data = (array)$this->getData();

		// Merge in the registration data.
		foreach ($data as $k => $v) {
			$temp[$k] = $v;
		}

		$data = $temp;

		// Prepare the data for the user object.
		$data['email']		= $data['email1'];
		$data['password']	= $data['password1'];

		// Check if the user needs to activate their account.
		if ($params->get('useractivation')) {
			jimport('joomla.user.helper');
			$data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;
		}

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError(JText::sprintf('USERS REGISTRATION BIND FAILED', $user->getError()));
			return false;
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('users');

		// Store the data.
		if (!$user->save()) {
			$this->setError($user->getError());
			return false;
		}

		// Compile the notification mail values.
		$data = $user->getProperties();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::base();

		// Handle account activation/confirmation e-mails.
		if ($params->get('useractivation'))
		{
			// Set the link to activate the user account.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_SUBJECT',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_user&task=activate&activation='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);
		} else {

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_SUBJECT',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}

		$subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);


		// Send the registration e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

		// Check for an error.
		if ($return !== true) {
			$this->setError(JText::_('USERS REGISTRATION SEND MAIL FAILED'));
			return false;
		}

		return $user->id;
	}

}