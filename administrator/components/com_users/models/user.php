<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * User model.
 *
 * @since  1.6
 */
class UsersModelUser extends JModelAdmin
{
	/**
	 * An item.
	 *
	 * @var    array
	 */
	protected $_item = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		$config = array_merge(
			array(
				'event_after_delete'  => 'onUserAfterDelete',
				'event_after_save'    => 'onUserAfterSave',
				'event_before_delete' => 'onUserBeforeDelete',
				'event_before_save'   => 'onUserBeforeSave',
				'events_map'          => array('save' => 'user', 'delete' => 'user', 'validate' => 'user')
			), $config
		);

		parent::__construct($config);
	}

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
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('user.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			$this->_item[$pk] = parent::getItem($pk);
		}

		return $this->_item[$pk];
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
		$pluginParams = new Registry;

		if (JPluginHelper::isEnabled('user', 'joomla'))
		{
			$plugin = JPluginHelper::getPlugin('user', 'joomla');
			$pluginParams->loadString($plugin->params);
		}

		// Get the form.
		$form = $this->loadForm('com_users.user', 'user', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$userId = $form->getValue('id');

		// Passwords fields are required when mail to user is set to No in the joomla user plugin
		if ($userId === 0 && $pluginParams->get('mail_to_user', '1') === '0')
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

		// When multilanguage is set, a user's default site language should also be a Content Language
		if (JLanguageMultilang::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'frontend_language', 'params');
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

		$this->preprocessData('com_users.profile', $data, 'user');

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
		$iAmSuperAdmin = $my->authorise('core.admin');

		// User cannot modify own user groups
		if ((int) $user->id == (int) $my->id && !$iAmSuperAdmin && isset($data['groups']))
		{
			// Form was probably tampered with
			JFactory::getApplication()->enqueueMessage(JText::_('COM_USERS_USERS_ERROR_CANNOT_EDIT_OWN_GROUP'), 'warning');

			$data['groups'] = null;
		}

		if ($data['block'] && $pk == $my->id && !$my->block)
		{
			$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'));

			return false;
		}

		// Make sure user groups is selected when add/edit an account
		if (empty($data['groups']) && ((int) $user->id != (int) $my->id || $iAmSuperAdmin))
		{
			$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_SAVE_ACCOUNT_WITHOUT_GROUPS'));

			return false;
		}

		// Make sure that we are not removing ourself from Super Admin group
		if ($iAmSuperAdmin && $my->get('id') == $pk)
		{
			// Check that at least one of our new groups is Super Admin
			$stillSuperAdmin = false;
			$myNewGroups = $data['groups'];

			foreach ($myNewGroups as $group)
			{
				$stillSuperAdmin = $stillSuperAdmin ?: JAccess::checkGroup($group, 'core.admin');
			}

			if (!$stillSuperAdmin)
			{
				$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_DEMOTE_SELF'));

				return false;
			}
		}

		// Handle the two factor authentication setup
		if (array_key_exists('twofactor', $data))
		{
			$twoFactorMethod = $data['twofactor']['method'];

			// Get the current One Time Password (two factor auth) configuration
			$otpConfig = $this->getOtpConfig($pk);

			if ($twoFactorMethod != 'none')
			{
				// Run the plugins
				FOFPlatform::getInstance()->importPlugin('twofactorauth');
				$otpConfigReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorApplyConfiguration', array($twoFactorMethod));

				// Look for a valid reply
				foreach ($otpConfigReplies as $reply)
				{
					if (!is_object($reply) || empty($reply->method) || ($reply->method != $twoFactorMethod))
					{
						continue;
					}

					$otpConfig->method = $reply->method;
					$otpConfig->config = $reply->config;

					break;
				}

				// Save OTP configuration.
				$this->setOtpConfig($pk, $otpConfig);

				// Generate one time emergency passwords if required (depleted or not set)
				if (empty($otpConfig->otep))
				{
					$oteps = $this->generateOteps($pk);
				}
			}
			else
			{
				$otpConfig->method = 'none';
				$otpConfig->config = array();
				$this->setOtpConfig($pk, $otpConfig);
			}

			// Unset the raw data
			unset($data['twofactor']);

			// Reload the user record with the updated OTP configuration
			$user->load($pk);
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

		JPluginHelper::importPlugin($this->events_map['delete']);
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

					// Fire the before delete event.
					$dispatcher->trigger($this->event_before_delete, array($table->getProperties()));

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}
					else
					{
						// Trigger the after delete event.
						$dispatcher->trigger($this->event_after_delete, array($user_to_delete->getProperties(), true, $this->getError()));
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

		JPluginHelper::importPlugin($this->events_map['save']);

		// Prepare the logout options.
		$options = array(
			'clientid' => $app->get('shared_session', '0') ? null : 0,
		);

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

						// Trigger the before save event.
						$result = $dispatcher->trigger($this->event_before_save, array($old, false, $table->getProperties()));

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

						// Trigger the after save event
						$dispatcher->trigger($this->event_after_save, array($table->getProperties(), false, true, null));
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
		$dispatcher = JEventDispatcher::getInstance();
		$user       = JFactory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');
		$table         = $this->getTable();
		$pks           = (array) $pks;

		JPluginHelper::importPlugin($this->events_map['save']);

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				$old   = $table->getProperties();
				$allow = $user->authorise('core.edit.state', 'com_users');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				if (empty($table->activation))
				{
					// Ignore activated accounts.
					unset($pks[$i]);
				}
				elseif ($allow)
				{
					$table->block      = 0;
					$table->activation = '';

					// Allow an exception to be thrown.
					try
					{
						if (!$table->check())
						{
							$this->setError($table->getError());

							return false;
						}

						// Trigger the before save event.
						$result = $dispatcher->trigger($this->event_before_save, array($old, false, $table->getProperties()));

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

						// Fire the after save event
						$dispatcher->trigger($this->event_after_save, array($table->getProperties(), false, true, null));
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
		$pks = ArrayHelper::toInteger($pks);

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
			$cmd = ArrayHelper::getValue($commands, 'group_action', 'add');

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
		$user_ids = ArrayHelper::toInteger($user_ids);

		// Check if I am a Super Admin
		$iAmSuperAdmin = JFactory::getUser()->authorise('core.admin');

		// Non-super super user cannot work with super-admin user.
		if (!$iAmSuperAdmin && JUserHelper::checkSuperUserInUsers($user_ids))
		{
			$this->setError(JText::_('COM_USERS_ERROR_CANNOT_BATCH_SUPERUSER'));

			return false;
		}

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

		if (empty($user_ids))
		{
			$this->setError(JText::_('COM_USERS_USERS_ERROR_CANNOT_REQUIRERESET_SELF'));

			return false;
		}

		// Get the DB object
		$db = $this->getDbo();

		$user_ids = ArrayHelper::toInteger($user_ids);

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
		$user_ids = ArrayHelper::toInteger($user_ids);

		// Check if I am a Super Admin
		$iAmSuperAdmin = JFactory::getUser()->authorise('core.admin');

		// Non-super super user cannot work with super-admin user.
		if (!$iAmSuperAdmin && JUserHelper::checkSuperUserInUsers($user_ids))
		{
			$this->setError(JText::_('COM_USERS_ERROR_CANNOT_BATCH_SUPERUSER'));

			return false;
		}

		// Non-super admin cannot work with super-admin group.
		if ((!$iAmSuperAdmin && JAccess::checkGroup($group_id, 'core.admin')) || $group_id < 1)
		{
			$this->setError(JText::_('COM_USERS_ERROR_INVALID_GROUP'));

			return false;
		}

		// Get the DB object
		$db = $this->getDbo();

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
			$result   = array();
			$form     = $this->getForm();

			if ($form)
			{
				$groupsIDs = $form->getValue('groups');
			}

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
	 */
	public function getOtpConfig($user_id = null)
	{
		$user_id = (!empty($user_id)) ? $user_id : (int) $this->getState('user.id');

		// Initialise
		$otpConfig = (object) array(
			'method' => 'none',
			'config' => array(),
			'otep'   => array()
		);

		/**
		 * Get the raw data, without going through JUser (required in order to
		 * be able to modify the user record before logging in the user).
		 */
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__users'))
			->where($db->qn('id') . ' = ' . (int) $user_id);
		$db->setQuery($query);
		$item = $db->loadObject();

		// Make sure this user does have OTP enabled
		if (empty($item->otpKey))
		{
			return $otpConfig;
		}

		// Get the encrypted data
		list($method, $config) = explode(':', $item->otpKey, 2);
		$encryptedOtep = $item->otep;

		// Get the secret key, yes the thing that is saved in the configuration file
		$key = $this->getOtpConfigEncryptionKey();

		if (strpos($config, '{') === false)
		{
			$openssl         = new FOFEncryptAes($key, 256);
			$mcrypt          = new FOFEncryptAes($key, 256, 'cbc', null, 'mcrypt');

			$decryptedConfig = $mcrypt->decryptString($config);

			if (strpos($decryptedConfig, '{') !== false)
			{
				// Data encrypted with mcrypt
				$decryptedOtep = $mcrypt->decryptString($encryptedOtep);
				$encryptedOtep = $openssl->encryptString($decryptedOtep);
			}
			else
			{
				// Config data seems to be save encrypted, this can happen with 3.6.3 and openssl, lets get the data
				$decryptedConfig = $openssl->decryptString($config);
			}

			$otpKey = $method . ':' . $decryptedConfig;

			$query = $db->getQuery(true)
				->update($db->qn('#__users'))
				->set($db->qn('otep') . '=' . $db->q($encryptedOtep))
				->set($db->qn('otpKey') . '=' . $db->q($otpKey))
				->where($db->qn('id') . ' = ' . $db->q($user_id));
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$decryptedConfig = $config;
		}

		// Create an encryptor class
		$aes = new FOFEncryptAes($key, 256);

		// Decrypt the data
		$decryptedOtep = $aes->decryptString($encryptedOtep);

		// Remove the null padding added during encryption
		$decryptedConfig = rtrim($decryptedConfig, "\0");
		$decryptedOtep = rtrim($decryptedOtep, "\0");

		// Update the configuration object
		$otpConfig->method = $method;
		$otpConfig->config = @json_decode($decryptedConfig);
		$otpConfig->otep = @json_decode($decryptedOtep);

		/*
		 * If the decryption failed for any reason we essentially disable the
		 * two-factor authentication. This prevents impossible to log in sites
		 * if the site admin changes the site secret for any reason.
		 */
		if (is_null($otpConfig->config))
		{
			$otpConfig->config = array();
		}

		if (is_object($otpConfig->config))
		{
			$otpConfig->config = (array) $otpConfig->config;
		}

		if (is_null($otpConfig->otep))
		{
			$otpConfig->otep = array();
		}

		if (is_object($otpConfig->otep))
		{
			$otpConfig->otep = (array) $otpConfig->otep;
		}

		// Return the configuration object
		return $otpConfig;
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
	 */
	public function setOtpConfig($user_id, $otpConfig)
	{
		$user_id = (!empty($user_id)) ? $user_id : (int) $this->getState('user.id');

		$updates = (object) array(
			'id'     => $user_id,
			'otpKey' => '',
			'otep'   => ''
		);

		// Create an encryptor class
		$key = $this->getOtpConfigEncryptionKey();
		$aes = new FOFEncryptAes($key, 256);

		// Create the encrypted option strings
		if (!empty($otpConfig->method) && ($otpConfig->method != 'none'))
		{
			$decryptedConfig = json_encode($otpConfig->config);
			$decryptedOtep = json_encode($otpConfig->otep);
			$updates->otpKey = $otpConfig->method . ':' . $decryptedConfig;
			$updates->otep = $aes->encryptString($decryptedOtep);
		}

		$db = $this->getDbo();
		$result = $db->updateObject('#__users', $updates, 'id');

		return $result;
	}

	/**
	 * Gets the symmetric encryption key for the OTP configuration data. It
	 * currently returns the site's secret.
	 *
	 * @return  string  The encryption key
	 *
	 * @since   3.2
	 */
	public function getOtpConfigEncryptionKey()
	{
		return JFactory::getConfig()->get('secret');
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
	 */
	public function getTwofactorform($user_id = null)
	{
		$user_id = (!empty($user_id)) ? $user_id : (int) $this->getState('user.id');

		$otpConfig = $this->getOtpConfig($user_id);

		FOFPlatform::getInstance()->importPlugin('twofactorauth');

		return FOFPlatform::getInstance()->runPlugins('onUserTwofactorShowConfiguration', array($otpConfig, $user_id));
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
	 */
	public function generateOteps($user_id, $count = 10)
	{
		$user_id = (!empty($user_id)) ? $user_id : (int) $this->getState('user.id');

		// Initialise
		$oteps = array();

		// Get the OTP configuration for the user
		$otpConfig = $this->getOtpConfig($user_id);

		// If two factor authentication is not enabled, abort
		if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
		{
			return $oteps;
		}

		$salt = '0123456789';
		$base = strlen($salt);
		$length = 16;

		for ($i = 0; $i < $count; $i++)
		{
			$makepass = '';
			$random = JCrypt::genRandomBytes($length + 1);
			$shift = ord($random[0]);

			for ($j = 1; $j <= $length; ++$j)
			{
				$makepass .= $salt[($shift + ord($random[$j])) % $base];
				$shift += ord($random[$j]);
			}

			$oteps[] = $makepass;
		}

		$otpConfig->otep = $oteps;

		// Save the now modified OTP configuration
		$this->setOtpConfig($user_id, $otpConfig);

		return $oteps;
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
	 */
	public function isValidSecretKey($user_id, $secretkey, $options = array())
	{
		// Load the user's OTP (one time password, a.k.a. two factor auth) configuration
		if (!array_key_exists('otp_config', $options))
		{
			$otpConfig = $this->getOtpConfig($user_id);
			$options['otp_config'] = $otpConfig;
		}
		else
		{
			$otpConfig = $options['otp_config'];
		}

		// Check if the user has enabled two factor authentication
		if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
		{
			// Load language
			$lang = JFactory::getLanguage();
			$extension = 'com_users';
			$source = JPATH_ADMINISTRATOR . '/components/' . $extension;

			$lang->load($extension, JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load($extension, $source, null, false, true);

			$warn = true;
			$warnMessage = JText::_('COM_USERS_ERROR_SECRET_CODE_WITHOUT_TFA');

			if (array_key_exists('warn_if_not_req', $options))
			{
				$warn = $options['warn_if_not_req'];
			}

			if (array_key_exists('warn_irq_msg', $options))
			{
				$warnMessage = $options['warn_irq_msg'];
			}

			// Warn the user if they are using a secret code but they have not
			// enabled two factor auth in their account.
			if (!empty($secretkey) && $warn)
			{
				try
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage($warnMessage, 'warning');
				}
				catch (Exception $exc)
				{
					// This happens when we are in CLI mode. In this case
					// no warning is issued
					return true;
				}
			}

			return true;
		}

		$credentials = array(
			'secretkey' => $secretkey,
		);

		// Try to validate the OTP
		FOFPlatform::getInstance()->importPlugin('twofactorauth');

		$otpAuthReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorAuthenticate', array($credentials, $options));

		$check = false;

		/*
		 * This looks like noob code but DO NOT TOUCH IT and do not convert
		 * to in_array(). During testing in_array() inexplicably returned
		 * null when the OTEP begins with a zero! o_O
		 */
		if (!empty($otpAuthReplies))
		{
			foreach ($otpAuthReplies as $authReply)
			{
				$check = $check || $authReply;
			}
		}

		// Fall back to one time emergency passwords
		if (!$check)
		{
			$check = $this->isValidOtep($user_id, $secretkey, $otpConfig);
		}

		return $check;
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
	 */
	public function isValidOtep($user_id, $otep, $otpConfig = null)
	{
		if (is_null($otpConfig))
		{
			$otpConfig = $this->getOtpConfig($user_id);
		}

		// Did the user use an OTEP instead?
		if (empty($otpConfig->otep))
		{
			if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
			{
				// Two factor authentication is not enabled on this account.
				// Any string is assumed to be a valid OTEP.
				return true;
			}
			else
			{
				/**
				 * Two factor authentication enabled and no OTEPs defined. The
				 * user has used them all up. Therefore anything they enter is
				 * an invalid OTEP.
				 */
				return false;
			}
		}

		// Clean up the OTEP (remove dashes, spaces and other funny stuff
		// our beloved users may have unwittingly stuffed in it)
		$otep = filter_var($otep, FILTER_SANITIZE_NUMBER_INT);
		$otep = str_replace('-', '', $otep);

		$check = false;

		// Did we find a valid OTEP?
		if (in_array($otep, $otpConfig->otep))
		{
			// Remove the OTEP from the array
			$otpConfig->otep = array_diff($otpConfig->otep, array($otep));

			$this->setOtpConfig($user_id, $otpConfig);

			// Return true; the OTEP was a valid one
			$check = true;
		}

		return $check;
	}
}
