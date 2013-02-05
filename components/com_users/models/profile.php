<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Profile model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelProfile extends JModelForm
{
	/**
	 * @var		object	The user profile data.
	 * @since	1.6
	 */
	protected $data;

	/**
	 * Method to check in a user.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkin($userId = null)
	{
		// Get the user id.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('user.id');

		if ($userId) {
			// Initialise the table with JUser.
			$table = JTable::getInstance('User');

			// Attempt to check the row in.
			if (!$table->checkin($userId)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to check out a user for editing.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkout($userId = null)
	{
		// Get the user id.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('user.id');

		if ($userId) {
			// Initialise the table with JUser.
			$table = JTable::getInstance('User');

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $userId)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get the profile form data.
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

			$userId = $this->getState('user.id');

			// Initialise the table with JUser.
			$this->data	= new JUser($userId);

			// Set the base user data.
			$this->data->email1 = $this->data->get('email');
			$this->data->email2 = $this->data->get('email');

			// Override the base user data with any data in the session.
			$temp = (array)JFactory::getApplication()->getUserState('com_users.edit.profile.data', array());
			foreach ($temp as $k => $v) {
				$this->data->$k = $v;
			}

			// Unset the passwords.
			unset($this->data->password1);
			unset($this->data->password2);

			$registry = new JRegistry($this->data->params);
			$this->data->params = $registry->toArray();

			// Get the dispatcher and load the users plugins.
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('user');

			// Trigger the data preparation event.
			$results = $dispatcher->trigger('onContentPrepareData', array('com_users.profile', $this->data));

			// Check for errors encountered while preparing the data.
			if (count($results) && in_array(false, $results, true)) {
				$this->setError($dispatcher->getError());
				$this->data = false;
			}
		}

		return $this->data;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.profile', 'profile', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		if (!JComponentHelper::getParams('com_users')->get('change_login_name'))
		{
			$form->setFieldAttribute('username', 'class', '');
			$form->setFieldAttribute('username', 'filter', '');
			$form->setFieldAttribute('username', 'description', 'COM_USERS_PROFILE_NOCHANGE_USERNAME_DESC');
			$form->setFieldAttribute('username', 'validate', '');
			$form->setFieldAttribute('username', 'message', '');
			$form->setFieldAttribute('username', 'readonly', 'true');
			$form->setFieldAttribute('username', 'required', 'false');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
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
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		if (JComponentHelper::getParams('com_users')->get('frontend_userparams'))
		{
			$form->loadFile('frontend', false);
			if (JFactory::getUser()->authorise('core.login.admin')) {
				$form->loadFile('frontend_admin', false);
			}
		}
		parent::preprocessForm($form, $data, $group);
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
		$params	= JFactory::getApplication()->getParams('com_users');

		// Get the user id.
		$userId = JFactory::getApplication()->getUserState('com_users.edit.profile.id');
		$userId = !empty($userId) ? $userId : (int)JFactory::getUser()->get('id');

		// Set the user id.
		$this->setState('user.id', $userId);

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
	public function save($data)
	{
		$userId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('user.id');

		$user = new JUser($userId);

		// Prepare the data for the user object.
		$data['email']		= $data['email1'];
		$data['password']	= $data['password1'];

		// Unset the username if it should not be overwritten
		if (!JComponentHelper::getParams('com_users')->get('change_login_name'))
		{
			unset($data['username']);
		}

		// Unset the block so it does not get overwritten
		unset($data['block']);

		// Unset the sendEmail so it does not get overwritten
		unset($data['sendEmail']);

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError(JText::sprintf('USERS PROFILE BIND FAILED', $user->getError()));
			return false;
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('user');

		// Null the user groups so they don't get overwritten
		$user->groups = null;

		// Store the data.
		if (!$user->save()) {
			$this->setError($user->getError());
			return false;
		}

		return $user->id;
	}
}
