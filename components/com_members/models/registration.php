<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');

/**
 * Registration model class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersModelRegistration extends JModelItem
{
	/**
	 */
	protected function _populateState()
	{
		// Get the application object.
		$app	= &JFactory::getApplication();
		$params	= &$app->getParams('com_members');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get the registration form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for members plugins to extend the form with extra fields.
	 *
	 * @return	mixed		JXForm object on success, false on failure.
	 */
	public function &getForm()
	{
		$false = false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		$form = &JForm::getInstance('jxform', 'registration', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('members');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareMembersRegistrationForm', array(&$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $form;
	}

	/**
	 * Method to get the registration form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for members plugins to extend the data.
	 *
	 * @return	mixed		Data object on success, false on failure.
	 */
	public function &getData()
	{
		$false	= false;
		$data	= new stdClass();
		$app	= &JFactory::getApplication();
		$params	= &JComponentHelper::getParams('com_users');

		// Override the base user data with any data in the session.
		$temp = (array)$app->getUserState('com_members.registration.data', array());
		foreach ($temp as $k => $v) {
			$data->$k = $v;
		}

		// Get the groups the user should be added to after registration.
		$data->groups = isset($data->groups) ? array_unique($data->groups) : array();

		// Get the default new user group.
		$system	= $params->get('new_usertype', 'Registered');
		$data->usertype = $system;

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

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('members');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onPrepareMembersRegistrationData', array(&$data));

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
	 * @param	array		$data		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 */
	public function register($temp)
	{
		$config = &JFactory::getConfig();
		$params = &JComponentHelper::getParams('com_users');

		// Add the table include path and then initialize the table with JUser.
		JTable::getInstance('User');
		$user = JUser::getInstance(0);
		$data = (array) $this->getData();

		// Merge in the registration data.
		foreach ($data as $k => $v) {
			$temp[$k] = $v;
		}

		$data = $temp;

		// Prepare the data for the user object.
		$data['email']		= $data['email1'];
		$data['password']	= $data['password1'];

		// Get the system group id.
		$this->_db->setQuery(
			'SELECT id FROM #__core_acl_aro_groups' .
			' WHERE name = '.$this->_db->Quote($data['usertype'])
		);
		$data['gid'] = (int)$this->_db->loadResult();

		// Check if the user needs to activate their account.
		if ($params->get('useractivation')) {
			jimport('joomla.user.helper');
			$data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;
		}

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError(JText::sprintf('MEMBERS REGISTRATION BIND FAILED', $user->getError()));
			return false;
		}

		// Load the members plugin group.
		JPluginHelper::importPlugin('members');

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
			$data['activate'] = $base.JRoute::_('index.php?option=com_members&task=registration.activate&token='.$data['activation'], false);

			// Get the registration activation e-mail.
			$message = 'com_members.registration.activate';
		}
		else
		{
			// Get the registration confirmation e-mail.
			$message = 'com_members.registration.confirm';
		}

		// Load the message template and bind the data.
		jimport('joomla.utilities.simpletemplate');
		$template = JSimpleTemplate::getInstance($message);
		$template->bind($data);

		// Send the registration e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $template->getTitle(), $template->getBody());

		// Check for an error.
		if ($return !== true) {
			$this->setError(JText::_('MEMBERS REGISTRATION SEND MAIL FAILED'));
			return false;
		}

		return $user->id;
	}

	/**
	 * Method to activate a user account.
	 *
	 * @param	string		$token		The activation token.
	 * @return	boolean		True on success, false on failure.
	 */
	public function activate($token)
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
			$this->setError(JText::_('MEMBERS ACTIVATION TOKEN NOT FOUND'));
			return false;
		}

		// Load the members plugin group.
		JPluginHelper::importPlugin('members');

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
		$template = JSimpleTemplate::getInstance('com_members.registration.confirm');
		$template->bind($data);

		// Send the registration e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $template->getTitle(), $template->getBody());

		// Check for an error.
		if ($return !== true) {
			$this->setError(JText::_('MEMBERS ACTIVATION SEND MAIL FAILED'));
			return false;
		}

		return true;
	}
}