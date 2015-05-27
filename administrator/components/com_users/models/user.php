<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       1.6
 */
class UsersModelUser extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	*/
	public function getTable($type = 'User', $prefix = 'JTable', $config = array())
	{
		$table = JTable::getInstance($type, $prefix, $config);

		return $table;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		$result->tags = new JHelperTags;
		$result->tags->getTagIds($result->id, 'com_users.user');

		// Get the dispatcher and load the users plugins.
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the data preparation event.
		$dispatcher->trigger('onContentPrepareData', array('com_users.user', $result));

		return $result;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$plugin = JPluginHelper::getPlugin('user', 'joomla');
		$pluginParams = new JRegistry($plugin->params);

		// Get the form.
		$form = $this->loadForm('com_users.user', 'user', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Passwords fields are required when mail to user is set to No in joomla user plugin
		$userId = $form->getValue('id');

		if ($userId === 0 && $pluginParams->get('mail_to_user') === "0")
		{
			$form->setFieldAttribute('password', 'required', 'true');
			$form->setFieldAttribute('password2', 'required', 'true');
		}

		// If the user needs to change their password, mark the password fields as required
		if (JFactory::getUser()->requireReset)
		{
			$form->setFieldAttribute('password', 'required', 'true');
			$form->setFieldAttribute('password2', 'required', 'true');
		}

		// The user should not be able to set the requireReset value on their own account
		if ((int) $userId === (int) JFactory::getUser()->id)
		{
			$form->removeField('requireReset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_users.edit.user.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		JPluginHelper::importPlugin('user');

		$this->preprocessData('com_users.profile', $data);

		return $data;
	}

	/**
	 * Override JModelAdmin::preprocessForm to ensure the correct plugin group is loaded.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$pk   = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('user.id');
		$user = JUser::getInstance($pk);

		$my = JFactory::getUser();

		if ($data['block'] && $pk == $my->id && !$my->block)
		{
			$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'));

			return false;
		}

		// Make sure that we are not removing ourself from Super Admin group
		$iAmSuperAdmin = $my->authorise('core.admin');

		if ($iAmSuperAdmin && $my->get('id') == $pk)
		{
			// Check that at least one of our new groups is Super Admin
			$stillSuperAdmin = false;
			$myNewGroups = $data['groups'];

			foreach ($myNewGroups as $group)
			{
				$stillSuperAdmin = ($stillSuperAdmin) ? ($stillSuperAdmin) : JAccess::checkGroup($group, 'core.admin');
			}

			if (!$stillSuperAdmin)
			{
				$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_DEMOTE_SELF'));

				return false;
			}
		}

		// Bind the data.
		if (!$user->bind($data))
		{
			$this->setError($user->getError());

			return false;
		}

		// Store the data.
		if (!$user->save())
		{
			$this->setError($user->getError());

			return false;
		}

		$this->setState('user.id', $user->id);

		return true;
	}

	/**
	 * Method to delete rows.
	 *
	 * @param   array  &$pks  An array of item ids.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function delete(&$pks)
	{
		$user  = JFactory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');

		// Trigger the onUserBeforeSave event.
		JPluginHelper::importPlugin('user');
		$dispatcher = JEventDispatcher::getInstance();

		if (in_array($user->id, $pks))
		{
			$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_DELETE_SELF'));

			return false;
		}

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.delete', 'com_users');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				if ($allow)
				{
					// Get users data for the users to delete.
					$user_to_delete = JFactory::getUser($pk);

					// Fire the onUserBeforeDelete event.
					$dispatcher->trigger('onUserBeforeDelete', array($table->getProperties()));

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}
					else
					{
						// Trigger the onUserAfterDelete event.
						$dispatcher->trigger('onUserAfterDelete', array($user_to_delete->getProperties(), true, $this->getError()));
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to block user records.
	 *
	 * @param   array    &$pks   The ids of the items to publish.
	 * @param   integer  $value  The value of the published state
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function block(&$pks, $value = 1)
	{
		$app        = JFactory::getApplication();
		$dispatcher = JEventDispatcher::getInstance();
		$user       = JFactory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');
		$table         = $this->getTable();
		$pks           = (array) $pks;

		JPluginHelper::importPlugin('user');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($value == 1 && $pk == $user->get('id'))
			{
				// Cannot block yourself.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'));
			}
			elseif ($table->load($pk))
			{
				$old   = $table->getProperties();
				$allow = $user->authorise('core.edit.state', 'com_users');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				// Prepare the logout options.
				$options = array(
					'clientid' => 0
				);

				if ($allow)
				{
					// Skip changing of same state
					if ($table->block == $value)
					{
						unset($pks[$i]);
						continue;
					}

					$table->block = (int) $value;

					// If unblocking, also change password reset count to zero to unblock reset
					if ($table->block === 0)
					{
						$table->resetCount = 0;
					}

					// Allow an exception to be thrown.
					try
					{
						if (!$table->check())
						{
							$this->setError($table->getError());

							return false;
						}

						// Trigger the onUserBeforeSave event.
						$result = $dispatcher->trigger('onUserBeforeSave', array($old, false, $table->getProperties()));

						if (in_array(false, $result, true))
						{
							// Plugin will have to raise its own error or throw an exception.
							return false;
						}

						// Store the table.
						if (!$table->store())
						{
							$this->setError($table->getError());

							return false;
						}

						// Trigger the onAftereStoreUser event
						$dispatcher->trigger('onUserAfterSave', array($table->getProperties(), false, true, null));
					}
					catch (Exception $e)
					{
						$this->setError($e->getMessage());

						return false;
					}

					// Log the user out.
					if ($value)
					{
						$app->logout($table->id, $options);
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		return true;
	}

	/**
	 * Method to activate user records.
	 *
	 * @param   array  &$pks  The ids of the items to activate.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function activate(&$pks)
	{
		$dispatcher	= JEventDispatcher::getInstance();
		$user		= JFactory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');
		$table         = $this->getTable();
		$pks           = (array) $pks;

		JPluginHelper::importPlugin('user');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				$old	= $table->getProperties();
				$allow	= $user->authorise('core.edit.state', 'com_users');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				if (empty($table->activation))
				{
					// Ignore activated accounts.
					unset($pks[$i]);
				}
				elseif ($allow)
				{
					$table->block		= 0;
					$table->activation	= '';

					// Allow an exception to be thrown.
					try
					{
						if (!$table->check())
						{
							$this->setError($table->getError());

							return false;
						}

						// Trigger the onUserBeforeSave event.
						$result = $dispatcher->trigger('onUserBeforeSave', array($old, false, $table->getProperties()));

						if (in_array(false, $result, true))
						{
							// Plugin will have to raise it's own error or throw an exception.
							return false;
						}

						// Store the table.
						if (!$table->store())
						{
							$this->setError($table->getError());

							return false;
						}

						// Fire the onAftereStoreUser event
						$dispatcher->trigger('onUserAfterSave', array($table->getProperties(), false, true, null));
					}
					catch (Exception $e)
					{
						$this->setError($e->getMessage());

						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		return true;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('COM_USERS_USERS_NO_ITEM_SELECTED'));

			return false;
		}

		$done = false;

		if (!empty($commands['group_id']))
		{
			$cmd = JArrayHelper::getValue($commands, 'group_action', 'add');

			if (!$this->batchUser((int) $commands['group_id'], $pks, $cmd))
			{
				return false;
			}

			$done = true;
		}

		if (!empty($commands['reset_id']))
		{
			if (!$this->batchReset($pks, $commands['reset_id']))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch flag users as being required to reset their passwords
	 *
	 * @param   array   $user_ids  An array of user IDs on which to operate
	 * @param   string  $action    The action to perform
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   3.2
	 */
	public function batchReset($user_ids, $action)
	{
		// Set the action to perform
		if ($action === 'yes')
		{
			$value = 1;
		}
		else
		{
			$value = 0;
		}

		// Prune out the current user if they are in the supplied user ID array
		$user_ids = array_diff($user_ids, array(JFactory::getUser()->id));

		// Get the DB object
		$db = $this->getDbo();

		JArrayHelper::toInteger($user_ids);

		$query = $db->getQuery(true);

		// Update the reset flag
		$query->update($db->quoteName('#__users'))
			->set($db->quoteName('requireReset') . ' = ' . $value)
			->where($db->quoteName('id') . ' IN (' . implode(',', $user_ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Perform batch operations
	 *
	 * @param   integer  $group_id  The group ID which assignments are being edited
	 * @param   array    $user_ids  An array of user IDs on which to operate
	 * @param   string   $action    The action to perform
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   1.6
	 */
	public function batchUser($group_id, $user_ids, $action)
	{
		// Get the DB object
		$db = $this->getDbo();

		JArrayHelper::toInteger($user_ids);

		// Non-super admin cannot work with super-admin group
		if ((!JFactory::getUser()->get('isRoot') && JAccess::checkGroup($group_id, 'core.admin')) || $group_id < 1)
		{
			$this->setError(JText::_('COM_USERS_ERROR_INVALID_GROUP'));

			return false;
		}

		switch ($action)
		{
			// Sets users to a selected group
			case 'set':
				$doDelete = 'all';
				$doAssign = true;
				break;

			// Remove users from a selected group
			case 'del':
				$doDelete = 'group';
				break;

			// Add users to a selected group
			case 'add':
			default:
				$doAssign = true;
				break;
		}

		// Remove the users from the group if requested.
		if (isset($doDelete))
		{
			$query = $db->getQuery(true);

			// Remove users from the group
			$query->delete($db->quoteName('#__user_usergroup_map'))
				->where($db->quoteName('user_id') . ' IN (' . implode(',', $user_ids) . ')');

			// Only remove users from selected group
			if ($doDelete == 'group')
			{
				$query->where($db->quoteName('group_id') . ' = ' . (int) $group_id);
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		// Assign the users to the group if requested.
		if (isset($doAssign))
		{
			$query = $db->getQuery(true);

			// First, we need to check if the user is already assigned to a group
			$query->select($db->quoteName('user_id'))
				->from($db->quoteName('#__user_usergroup_map'))
				->where($db->quoteName('group_id') . ' = ' . (int) $group_id);
			$db->setQuery($query);
			$users = $db->loadColumn();

			// Build the values clause for the assignment query.
			$query->clear();
			$groups = false;

			foreach ($user_ids as $id)
			{
				if (!in_array($id, $users))
				{
					$query->values($id . ',' . $group_id);
					$groups = true;
				}
			}

			// If we have no users to process, throw an error to notify the user
			if (!$groups)
			{
				$this->setError(JText::_('COM_USERS_ERROR_NO_ADDITIONS'));

				return false;
			}

			$query->insert($db->quoteName('#__user_usergroup_map'))
				->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Gets the available groups.
	 *
	 * @return  array  An array of groups
	 *
	 * @since   1.6
	 */
	public function getGroups()
	{
		$user = JFactory::getUser();

		if ($user->authorise('core.edit', 'com_users') && $user->authorise('core.manage', 'com_users'))
		{
			$model = JModelLegacy::getInstance('Groups', 'UsersModel', array('ignore_request' => true));

			return $model->getItems();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets the groups this object is assigned to
	 *
	 * @param   integer  $userId  The user ID to retrieve the groups for
	 *
	 * @return  array  An array of assigned groups
	 *
	 * @since   1.6
	 */
	public function getAssignedGroups($userId = null)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		if (empty($userId))
		{
			$result = array();

			$groupsIDs = $this->getForm()->getValue('groups');

			if (!empty($groupsIDs))
			{
				$result = $groupsIDs;
			}
			else
			{
				$config = JComponentHelper::getParams('com_users');

				if ($groupId = $config->get('new_usertype'))
				{
					$result[] = $groupId;
				}
			}
		}
		else
		{
			$result = JUserHelper::getUserGroups($userId);
		}

		return $result;
	}

	/**
	 * Returns the one time password (OTP) – a.k.a. two factor authentication –
	 * configuration for a particular user.
	 *
	 * @param   integer  $user_id  The numeric ID of the user
	 *
	 * @return  stdClass  An object holding the OTP configuration for this user
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function getOtpConfig($user_id = null)
	{
		return null;
	}

	/**
	 * Sets the one time password (OTP) – a.k.a. two factor authentication –
	 * configuration for a particular user. The $otpConfig object is the same as
	 * the one returned by the getOtpConfig method.
	 *
	 * @param   integer   $user_id    The numeric ID of the user
	 * @param   stdClass  $otpConfig  The OTP configuration object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function setOtpConfig($user_id, $otpConfig)
	{
		return false;
	}

	/**
	 * Gets the symmetric encryption key for the OTP configuration data. It
	 * currently returns the site's secret.
	 *
	 * @return  string  The encryption key
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function getOtpConfigEncryptionKey()
	{
		return '';
	}

	/**
	 * Gets the configuration forms for all two-factor authentication methods
	 * in an array.
	 *
	 * @param   integer  $user_id  The user ID to load the forms for (optional)
	 *
	 * @return  array
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function getTwofactorform($user_id = null)
	{
		return array();
	}

	/**
	 * Generates a new set of One Time Emergency Passwords (OTEPs) for a given user.
	 *
	 * @param   integer  $user_id  The user ID
	 * @param   integer  $count    How many OTEPs to generate? Default: 10
	 *
	 * @return  array  The generated OTEPs
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function generateOteps($user_id, $count = 10)
	{
		return array();
	}

	/**
	 * Checks if the provided secret key is a valid two factor authentication
	 * secret key. If not, it will check it against the list of one time
	 * emergency passwords (OTEPs). If it's a valid OTEP it will also remove it
	 * from the user's list of OTEPs.
	 *
	 * This method will return true in the following conditions:
	 * - The two factor authentication is not enabled
	 * - You have provided a valid secret key for
	 * - You have provided a valid OTEP
	 *
	 * You can define the following options in the $options array:
	 * otp_config		The OTP (one time password, a.k.a. two factor auth)
	 *				    configuration object. If not set we'll load it automatically.
	 * warn_if_not_req	Issue a warning if you are checking a secret key against
	 *					a user account which doesn't have any two factor
	 *					authentication method enabled.
	 * warn_irq_msg		The string to use for the warn_if_not_req warning
	 *
	 * @param   integer  $user_id    The user's numeric ID
	 * @param   string   $secretkey  The secret key you want to check
	 * @param   array    $options    Options; see above
	 *
	 * @return  boolean  True if it's a valid secret key for this user.
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function isValidSecretKey($user_id, $secretkey, $options = array())
	{
		return false;
	}

	/**
	 * Checks if the supplied string is a valid one time emergency password
	 * (OTEP) for this user. If it is it will be automatically removed from the
	 * user's list of OTEPs.
	 *
	 * @param   integer  $user_id    The user ID against which you are checking
	 * @param   string   $otep       The string you want to test for validity
	 * @param   object   $otpConfig  Optional; the two factor authentication configuration (automatically fetched if not set)
	 *
	 * @return  boolean  True if it's a valid OTEP or if two factor auth is not
	 *                   enabled in this user's account.
	 *
	 * @since   3.2
     * @deprecated
	 */
	public function isValidOtep($user_id, $otep, $otpConfig = null)
	{
		return false;
	}
}
