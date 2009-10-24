<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
 * @version		1.0
 */
class UsersModelRegistration extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		// Get the application object.
		$app	= &JFactory::getApplication();
		$params	= &$app->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get the registration form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @access	public
	 * @return	mixed		JForm object on success, false on failure.
	 * @since	1.0
	 */
	function getForm()
	{
		// Get the form.
		$form = parent::getForm('registration', 'com_users.registration', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Get the dispatcher and load the users plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('users');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareUsersRegistrationForm', array(&$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the registration form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for users plugins to extend the data.
	 *
	 * @access	public
	 * @return	mixed		Data object on success, false on failure.
	 * @since	1.0
	 */
	function &getData()
	{
		$false	= false;
		$data	= new stdClass();
		$app	= &JFactory::getApplication();
		$params	= &JComponentHelper::getParams('com_users');

		// Override the base user data with any data in the session.
		$temp = (array)$app->getUserState('com_users.registration.data', array());
		foreach ($temp as $k => $v) {
			$data->$k = $v;
		}

		// Get the groups the user should be added to after registration.
		$data->groups = isset($data->groups) ? array_unique($data->groups) : array();

		// Get the default new user group, Registered if not specified.
		$system	= $params->get('new_usertype', 2);
		$data->usertype = $system;

		// TODO: Not sure we need all this stuff anymore. Just need to add the group to the list and we are golden.
		// Handle the system default group.
		if (!in_array($system, $data->groups)) {
			// Add the system group to the first position.
			array_unshift($data->groups, $system);
		} else {
			// Make sure the system group is the first item.
			unset($data->groups[array_search($system, $data->groups)]);
			array_unshift($data->groups, $system);
		}

		// Unset the passwords.
		unset($data->password1);
		unset($data->password2);

		// Get the dispatcher and load the users plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('users');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onPrepareUsersRegistrationData', array(&$data));

		// Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @access	public
	 * @param	array		$data		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 * @since	1.0
	 */
	function register($temp)
	{
		$config = &JFactory::getConfig();
		$params = &JComponentHelper::getParams('com_users');

		// Add the table include path and then initialize the table with JUser.
		JTable::addIncludePath(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'jxtended'.DS.'database'.DS.'table');
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
		$data['fromname'] = $config->getValue('fromname');
		$data['mailfrom'] = $config->getValue('mailfrom');
		$data['sitename'] = $config->getValue('sitename');

		// Handle account activation/confirmation e-mails.
		if ($params->get('useractivation'))
		{
			// Set the link to activate the user account.
			$uri = &JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			// Get the registration activation e-mail.
			$message = 'com_users.registration.activate';
		}
		else
		{
			// Get the registration confirmation e-mail.
			$message = 'com_users.registration.confirm';
		}

		// Load the message template and bind the data.
		jimport('joomla.utilities.simpletemplate');
		$template = JxSimpleTemplate::getInstance($message);
		$template->bind($data);

		// Send the registration e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $template->getTitle(), $template->getBody());

		// Check for an error.
		if ($return !== true) {
			$this->setError(JText::_('USERS REGISTRATION SEND MAIL FAILED'));
			return false;
		}

		return $user->id;
	}

	/**
	 * Method to activate a user account.
	 *
	 * @access	public
	 * @param	string		$token		The activation token.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function activate($token)
	{
		$config = &JFactory::getConfig();

		// Get the user id based on the token.
		$this->_db->setQuery(
			'SELECT `id` FROM `#__users`' .
			' WHERE `activation` = '.$this->_db->Quote($token) .
			' AND `block` = 1' .
			' AND `lastvisitDate` = '.$this->_db->Quote($this->_db->getNullDate())
		);
		$userId = (int)$this->_db->loadResult();

		// Check for a valid user id.
		if (!$userId) {
			$this->setError(JText::_('USERS ACTIVATION TOKEN NOT FOUND'));
			return false;
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('users');

		// Activate the user.
		$user = &JFactory::getUser($userId);
		$user->set('activation', '');
		$user->set('block', '0');

		// Store the user object.
		if (!$user->save()) {
			$this->setError($user->getError());
			return false;
		}

		// Compile the notification mail values.
		$data = $user->getProperties();
		$data['fromname'] = $config->getValue('fromname');
		$data['mailfrom'] = $config->getValue('mailfrom');
		$data['sitename'] = $config->getValue('sitename');

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

		return true;
	}
}