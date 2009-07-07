<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * User model for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelUser extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app		= &JFactory::getApplication('administrator');
		$params		= &JComponentHelper::getParams('com_users');

		// Load the User state.
		if (JRequest::getWord('layout') === 'edit') {
			$user_id = (int)$app->getUserState('com_users.edit.user.id');
			$this->setState('user.id', $user_id);
		} else {
			$user_id = (int)JRequest::getInt('user_id');
			$this->setState('user.id', $user_id);
		}

		// Add the User id to the context to preserve sanity.
		$context	= 'com_users.user.'.$user_id.'.';

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a user item.
	 *
	 * @param	integer	The id of the user to get.
	 * @return	mixed	User data object on success, false on failure.
	 */
	public function &getItem($userId = null)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('user.id');
		$false	= false;

		// Get a user row instance.
		$table = &$this->getTable('User', 'JTable');

		// Attempt to load the row.
		$return = $table->load($userId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		$value->profile = new JObject;

		// Get the dispatcher and load the users plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfileData', array($userId, &$value));

		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadJSON($value->params);
		$value->params = $registry->toArray();

		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('user', 'com_users.user', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Get the dispatcher and load the users plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfileForm', array($this->getState('user.id'), &$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_users.edit.user.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Gets the available groups.
	 *
	 * @return	array
	 */
	public function getGroups()
	{
		$model = JModel::getInstance('Groups', 'UsersModel');
		return $model->getItems();
	}

	/**
	 * Gets the groups this object is assigned to
	 *
	 * @return	array
	 */
	public function getAssignedGroups($userId = null)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('user.id');

		jimport('joomla.user.helper');
		$result = JUserHelper::getUserGroups($userId);

		return $result;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	$id		The numeric id of a row
	 * @return	boolean	True on success/false on failure
	 */
	public function checkin($userId = null)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('user.id');
		$user		= &JFactory::getUser();
		$user_id	= (int) $user->get('id');

		if (!$userId) {
			return true;
		}

		// Get a JTableUser instance.
		$user = &JTable::getInstance('User', 'JTable');

		// Attempt to check-in the row.
		$return = $user->checkin($user_id, $userId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($user->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to check-out a User for editing.
	 *
	 * @param	int		$user_id	The numeric id of the User to check-out.
	 * @return	bool	False on failure or error, success otherwise.
	 */
	public function checkout($userId)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('user.id');
		$user		= &JFactory::getUser();
		$user_id	= (int) $user->get('id');

		// Check for a new User id.
		if ($userId === -1) {
			return true;
		}

		// Get a JTableUser instance.
		$user = &JTable::getInstance('User', 'JTable');

		// Attempt to check-out the row.
		$return = $user->checkout($user_id, $userId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($user->getError());
			return false;
		}

		// Check if the row is checked-out by someone else.
		if ($return === null) {
			$this->setError(JText::_('USERS_USER_CHECKED_OUT'));
			return false;
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		$userId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('user.id');
		$isNew	= true;

		// Get a JTableUser instance.
		$user = &JTable::getInstance('User', 'JTable');

		// Load the row if saving an existing item.
		if ($userId > 0) {
			$user->load($userId);
			$isNew = false;
		}

		// The password field is a special case.
		if (!empty($data['password']))
		{
			// If a password was sent, ensure that it was verified.
			if ($data['password'] != $data['password2']) {
				$this->setError('USERS_PASSWORD_MISMATCH');
				return false;
			}

			// Generate a password hash.
			jimport('joomla.user.helper');
			$salt  = JUserHelper::genRandomPassword(32);
			$crypt = JUserHelper::getCryptedPassword($data['password'], $salt);
			$data['password'] = $crypt.':'.$salt;
		}
		else {
			// Do nothing to the password field.
			unset($data['password']);
		}

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError($user->getError());
			return false;
		}

		if (!$result = $user->check()) {
			$this->setError($user->getErrorMsg());
			return false;
		}

		// Get the old user
		$old = JUser::getInstance($userId);

		// Fire the onBeforeStoreUser event.
		JPluginHelper::importPlugin('user');
		$dispatcher = &JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeStoreUser', array($old->getProperties(), $isNew));

		// Store the data.
		if (!$result = $user->store()) {
			$this->setError($user->getErrorMsg());
			return false;
		}

		// Fire the onAftereStoreUser event
		$dispatcher->trigger('onAfterStoreUser', array($user->getProperties(), $isNew, $result, $this->getError()));

		$this->setState('user.id', $user->id);

		return true;
	}

	/**
	 * Method to delete a user or list of users.
	 *
	 * @access	public
	 * @param	array	An array of user ids.
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.0
	 */
	function delete($userIds)
	{
		// Sanitize the ids.
		$userIds = (array) $userIds;
		JArrayHelper::toInteger($userIds);

		// Get a JTableUser instance.
		$table = & JTable::getInstance('User', 'JTable');

		// Fire the onBeforeStoreUser event.
		JPluginHelper::importPlugin('user');
		$dispatcher = &JDispatcher::getInstance();

		// Iterate the items to delete each one.
		foreach ($userIds as $userId)
		{
			// Get user data for the user to delete.
			$user = & JFactory::getUser($userId);

			// Fire the onBeforeDeleteUser event.
			$dispatcher->trigger('onBeforeDeleteUser', array($user->getProperties()));

			// Attempt to delete the user.
			if (!$result = $table->delete($userId)) {
				$this->setError($table->getErrorMsg());
				return false;
			}

			// Fire the onAfterDeleteUser event.
			$dispatcher->trigger('onAfterDeleteUser', array($user->getProperties(), $result, $this->getError()));
		}

		return true;
	}

	/**
	 * Perform batch operations
	 *
	 * @param	array	An array of variable for the batch operation
	 * @param	array	An array of IDs on which to operate
	 */
	public function batch($config, $user_ids)
	{
		// Ensure there are selected users to operate on.
		if (empty($user_ids))
		{
			$this->setError(JText::_('USERS_USERS_NOT_SELECTED'));
			return false;
		}
		// Only run operations if a config array is present.
		else if (!empty($config))
		{
			// Ensure there is a valid group.
			$group_id = JArrayHelper::getValue($config, 'group_id', 0, 'int');
			if ($group_id < 1)
			{
				$this->setError(JText::_('USERS_INVALID_GROUP'));
				return false;
			}

			// Get the system ACL object and set the mode to database driven.
			$acl = &JFactory::getACL();
			$oldAclMode = $acl->setCheckMode(1);

			$groupLogic	= JArrayHelper::getValue($config, 'group_logic');
			switch ($groupLogic)
			{
				case 'set':
					$doDelete 		= 2;
					$doAssign 		= true;
					break;

				case 'del':
					$doDelete		= true;
					$doAssign		= false;
					break;

				case 'add':
				default:
					$doDelete		= false;
					$doAssign		= true;
					break;
			}

			// Remove the users from the group(s) if requested.
			if ($doDelete)
			{
				// Purge operation, remove the users from all groups.
				if ($doDelete === 2)
				{
					$this->_db->setQuery(
						'DELETE FROM `#__core_acl_groups_aro_map`' .
						' WHERE `aro_id` IN ('.implode(',', $user_ids).')'
					);
				}
				// Remove the users from the group.
				else
				{
					$this->_db->setQuery(
						'DELETE FROM `#__core_acl_groups_aro_map`' .
						' WHERE `aro_id` IN ('.implode(',', $user_ids).')' .
						' AND `group_id` = '.$group_id
					);
				}

				// Check for database errors.
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			// Assign the users to the group if requested.
			if ($doAssign)
			{
				// Build the tuples array for the assignment query.
				$tuples = array();
				foreach ($user_ids as $id)
				{
					$tuples[] = '('.$id.','.$group_id.')';
				}

				$this->_db->setQuery(
					'INSERT IGNORE INTO `#__core_acl_groups_aro_map` (`aro_id`, `group_id`)' .
					' VALUES '.implode(',', $tuples)
				);

				// Check for database errors.
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			// Set the ACL mode back to it's previous state.
			$acl->setCheckMode($oldAclMode);
		}

		return true;
	}
}