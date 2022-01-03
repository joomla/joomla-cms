<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Encrypt\Aes;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * User model.
 *
 * @since  1.6
 */
class UserModel extends AdminModel
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
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
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

		parent::__construct($config, $factory);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'User', $prefix = 'Joomla\\CMS\\Table\\', $config = array())
	{
		$table = Table::getInstance($type, $prefix, $config);

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
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|bool  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.user', 'user', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$user = Factory::getUser();

		// If the user needs to change their password, mark the password fields as required
		if ($user->requireReset)
		{
			$form->setFieldAttribute('password', 'required', 'true');
			$form->setFieldAttribute('password2', 'required', 'true');
		}

		// When multilanguage is set, a user's default site language should also be a Content Language
		if (Multilanguage::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'frontend_language', 'params');
		}

		$userId = (int) $form->getValue('id');

		// The user should not be able to set the requireReset value on their own account
		if ($userId === (int) $user->id)
		{
			$form->removeField('requireReset');
		}

		/**
		 * If users without core.manage permission editing their own account, remove some fields which they should
		 * not be allowed to change and prevent them to change user name if configured
		 */
		if (!$user->authorise('core.manage', 'com_users') && (int) $user->id === $userId)
		{
			if (!ComponentHelper::getParams('com_users')->get('change_login_name'))
			{
				$form->setFieldAttribute('username', 'required', 'false');
				$form->setFieldAttribute('username', 'readonly', 'true');
				$form->setFieldAttribute('username', 'description', 'COM_USERS_USER_FIELD_NOCHANGE_USERNAME_DESC');
			}

			$form->removeField('lastResetTime');
			$form->removeField('resetCount');
			$form->removeField('sendEmail');
			$form->removeField('block');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_users.edit.user.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_users.profile', $data, 'user');

		return $data;
	}

	/**
	 * Override Joomla\CMS\MVC\Model\AdminModel::preprocessForm to ensure the correct plugin group is loaded.
	 *
	 * @param   Form    $form   A Form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 *
	 * @throws  \Exception if there is an error in the form event.
	 */
	protected function preprocessForm(Form $form, $data, $group = 'user')
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
	 * @throws  \Exception
	 */
	public function save($data)
	{
		$pk   = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('user.id');
		$user = User::getInstance($pk);

		$my = Factory::getUser();
		$iAmSuperAdmin = $my->authorise('core.admin');

		// User cannot modify own user groups
		if ((int) $user->id == (int) $my->id && !$iAmSuperAdmin && isset($data['groups']))
		{
			// Form was probably tampered with
			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_USERS_ERROR_CANNOT_EDIT_OWN_GROUP'), 'warning');

			$data['groups'] = null;
		}

		if ($data['block'] && $pk == $my->id && !$my->block)
		{
			$this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'));

			return false;
		}

		// Make sure user groups is selected when add/edit an account
		if (empty($data['groups']) && ((int) $user->id != (int) $my->id || $iAmSuperAdmin))
		{
			$this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_SAVE_ACCOUNT_WITHOUT_GROUPS'));

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
				$stillSuperAdmin = $stillSuperAdmin ?: Access::checkGroup($group, 'core.admin');
			}

			if (!$stillSuperAdmin)
			{
				$this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_DEMOTE_SELF'));

				return false;
			}
		}

		// Handle the two factor authentication setup
		if (isset($data['twofactor']['method']))
		{
			$twoFactorMethod = $data['twofactor']['method'];

			// Get the current One Time Password (two factor auth) configuration
			$otpConfig = $this->getOtpConfig($pk);

			if ($twoFactorMethod != 'none')
			{
				// Run the plugins
				PluginHelper::importPlugin('twofactorauth');
				$otpConfigReplies = Factory::getApplication()->triggerEvent('onUserTwofactorApplyConfiguration', array($twoFactorMethod));

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

		// Destroy all active sessions for the user after changing the password or blocking him
		if ($data['password2'] || $data['block'])
		{
			UserHelper::destroyUserSessions($user->id, true);
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
	 * @throws  \Exception
	 */
	public function delete(&$pks)
	{
		$user  = Factory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');

		PluginHelper::importPlugin($this->events_map['delete']);

		if (in_array($user->id, $pks))
		{
			$this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_DELETE_SELF'));

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
				$allow = (!$iAmSuperAdmin && Access::check($pk, 'core.admin')) ? false : $allow;

				if ($allow)
				{
					// Get users data for the users to delete.
					$user_to_delete = Factory::getUser($pk);

					// Fire the before delete event.
					Factory::getApplication()->triggerEvent($this->event_before_delete, array($table->getProperties()));

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}
					else
					{
						// Trigger the after delete event.
						Factory::getApplication()->triggerEvent($this->event_after_delete, array($user_to_delete->getProperties(), true, $this->getError()));
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					Factory::getApplication()->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'error');
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
	 * @throws  \Exception
	 */
	public function block(&$pks, $value = 1)
	{
		$app        = Factory::getApplication();
		$user       = Factory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');
		$table         = $this->getTable();
		$pks           = (array) $pks;

		PluginHelper::importPlugin($this->events_map['save']);

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
				Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'), 'error');
			}
			elseif ($table->load($pk))
			{
				$old   = $table->getProperties();
				$allow = $user->authorise('core.edit.state', 'com_users');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && Access::check($pk, 'core.admin')) ? false : $allow;

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
						$result = Factory::getApplication()->triggerEvent($this->event_before_save, array($old, false, $table->getProperties()));

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

						if ($table->block)
						{
							UserHelper::destroyUserSessions($table->id);
						}

						// Trigger the after save event
						Factory::getApplication()->triggerEvent($this->event_after_save, [$table->getProperties(), false, true, null]);
					}
					catch (\Exception $e)
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
					Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
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
	 * @throws  \Exception
	 */
	public function activate(&$pks)
	{
		$user = Factory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');
		$table         = $this->getTable();
		$pks           = (array) $pks;

		PluginHelper::importPlugin($this->events_map['save']);

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				$old   = $table->getProperties();
				$allow = $user->authorise('core.edit.state', 'com_users');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && Access::check($pk, 'core.admin')) ? false : $allow;

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
						$result = Factory::getApplication()->triggerEvent($this->event_before_save, array($old, false, $table->getProperties()));

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
						Factory::getApplication()->triggerEvent($this->event_after_save, [$table->getProperties(), false, true, null]);
					}
					catch (\Exception $e)
					{
						$this->setError($e->getMessage());

						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
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
			$this->setError(Text::_('COM_USERS_USERS_NO_ITEM_SELECTED'));

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
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch flag users as being required to reset their passwords
	 *
	 * @param   array   $userIds  An array of user IDs on which to operate
	 * @param   string  $action   The action to perform
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   3.2
	 */
	public function batchReset($userIds, $action)
	{
		$userIds = ArrayHelper::toInteger($userIds);

		// Check if I am a Super Admin
		$iAmSuperAdmin = Factory::getUser()->authorise('core.admin');

		// Non-super super user cannot work with super-admin user.
		if (!$iAmSuperAdmin && UserHelper::checkSuperUserInUsers($userIds))
		{
			$this->setError(Text::_('COM_USERS_ERROR_CANNOT_BATCH_SUPERUSER'));

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
		$userIds = array_diff($userIds, array(Factory::getUser()->id));

		if (empty($userIds))
		{
			$this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_REQUIRERESET_SELF'));

			return false;
		}

		// Get the DB object
		$db = $this->getDbo();

		$userIds = ArrayHelper::toInteger($userIds);

		$query = $db->getQuery(true);

		// Update the reset flag
		$query->update($db->quoteName('#__users'))
			->set($db->quoteName('requireReset') . ' = :requireReset')
			->whereIn($db->quoteName('id'), $userIds)
			->bind(':requireReset', $value, ParameterType::INTEGER);

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Perform batch operations
	 *
	 * @param   integer  $groupId  The group ID which assignments are being edited
	 * @param   array    $userIds  An array of user IDs on which to operate
	 * @param   string   $action   The action to perform
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   1.6
	 */
	public function batchUser($groupId, $userIds, $action)
	{
		$userIds = ArrayHelper::toInteger($userIds);

		// Check if I am a Super Admin
		$iAmSuperAdmin = Factory::getUser()->authorise('core.admin');

		// Non-super super user cannot work with super-admin user.
		if (!$iAmSuperAdmin && UserHelper::checkSuperUserInUsers($userIds))
		{
			$this->setError(Text::_('COM_USERS_ERROR_CANNOT_BATCH_SUPERUSER'));

			return false;
		}

		// Non-super admin cannot work with super-admin group.
		if ((!$iAmSuperAdmin && Access::checkGroup($groupId, 'core.admin')) || $groupId < 1)
		{
			$this->setError(Text::_('COM_USERS_ERROR_INVALID_GROUP'));

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
				->whereIn($db->quoteName('user_id'), $userIds);

			// Only remove users from selected group
			if ($doDelete == 'group')
			{
				$query->where($db->quoteName('group_id') . ' = :group_id')
					->bind(':group_id', $groupId, ParameterType::INTEGER);
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
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
				->where($db->quoteName('group_id') . ' = :group_id')
				->bind(':group_id', $groupId, ParameterType::INTEGER);
			$db->setQuery($query);
			$users = $db->loadColumn();

			// Build the values clause for the assignment query.
			$query->clear();
			$groups = false;

			foreach ($userIds as $id)
			{
				if (!in_array($id, $users))
				{
					$query->values($id . ',' . $groupId);
					$groups = true;
				}
			}

			// If we have no users to process, throw an error to notify the user
			if (!$groups)
			{
				$this->setError(Text::_('COM_USERS_ERROR_NO_ADDITIONS'));

				return false;
			}

			$query->insert($db->quoteName('#__user_usergroup_map'))
				->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
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
		$user = Factory::getUser();

		if ($user->authorise('core.edit', 'com_users') && $user->authorise('core.manage', 'com_users'))
		{
			$model = $this->bootComponent('com_users')
				->getMVCFactory()->createModel('Groups', 'Administrator', ['ignore_request' => true]);

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
				$params = ComponentHelper::getParams('com_users');

				if ($groupId = $params->get('new_usertype', $params->get('guest_usergroup', 1)))
				{
					$result[] = $groupId;
				}
			}
		}
		else
		{
			$result = UserHelper::getUserGroups($userId);
		}

		return $result;
	}

	/**
	 * Returns the one time password (OTP) – a.k.a. two factor authentication –
	 * configuration for a particular user.
	 *
	 * @param   integer  $userId  The numeric ID of the user
	 *
	 * @return  \stdClass  An object holding the OTP configuration for this user
	 *
	 * @since   3.2
	 */
	public function getOtpConfig($userId = null)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		// Initialise
		$otpConfig = (object) array(
			'method' => 'none',
			'config' => array(),
			'otep'   => array()
		);

		/**
		 * Get the raw data, without going through User (required in order to
		 * be able to modify the user record before logging in the user).
		 */
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $userId, ParameterType::INTEGER);
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

		// Cleanup old encryption methods, and convert to using openssl as the adapter to use.
		if (strpos($config, '{') === false)
		{
			/**
			 * This part of the if statement block of code has been reviewed just before 4.0.0 release and determined that it is wrong,
			 * and has never worked.
			 *
			 * The aim is/was to migrate away from mcrypt encrypted data by decrypting the data and then re-encrypting
			 * it with the openssl adapter, but there has been a bug for a long time in the constructing of the
			 * mcrypt Aes class, where the number of parameters passed were wrong, meaning it was actually returning
			 * an openssl adapter not an mcrypt one.
			 *
			 * Rather than fix this just before 4.0.0 release, we will deprecate this block and remove it in 5.0.0
			 *
			 * @deprecated 4.0.0 Will be removed in 5.0.0 - always use the openssl (default) adapter with the Aes class from now on.
			 */

			// We use the openssl adapter by default now.
			$openssl = new Aes($key, 256);

			/**
			 * Deal with legacy mcrypt encrypted data
			 * NOTE THIS NEXT LINE IS WRONG and contains wrong number of params, thus returns the openssl adapter and not the mcrypt adapter.
			 */
			$mcrypt = new Aes($key, 256, 'cbc', null, 'mcrypt');

			// Attempt to decrypt using the mcrypt adapter, under normal circumstances this should fail (We no longer use mcrypt adapter to encrypt).
			$decryptedConfig = $mcrypt->decryptString($config);

			// If we were able to decrypt using the mcrypt adapter, { will be in the config (JSON String), so lets update to openssl adapter use.
			if (strpos($decryptedConfig, '{') !== false)
			{
				// Data encrypted with mcrypt, decrypt it, and then convert to openssl.
				$decryptedOtep = $mcrypt->decryptString($encryptedOtep);
				$encryptedOtep = $openssl->encryptString($decryptedOtep);
			}
			else
			{
				// Config data seems to be save encrypted, this can happen with 3.6.3 and openssl, lets get the data.
				$decryptedConfig = $openssl->decryptString($config);
			}

			$otpKey = $method . ':' . $decryptedConfig;

			$query = $db->getQuery(true)
				->update($db->quoteName('#__users'))
				->set($db->quoteName('otep') . ' = :otep')
				->set($db->quoteName('otpKey') . ' = :otpKey')
				->where($db->quoteName('id') . ' = :id')
				->bind(':otep', $encryptedOtep)
				->bind(':otpKey', $otpKey)
				->bind(':id', $userId, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$decryptedConfig = $config;
		}

		// Create an encryptor class
		$aes = new Aes($key, 256);

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
	 * @param   integer    $userId     The numeric ID of the user
	 * @param   \stdClass  $otpConfig  The OTP configuration object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	public function setOtpConfig($userId, $otpConfig)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		$updates = (object) array(
			'id'     => $userId,
			'otpKey' => '',
			'otep'   => ''
		);

		// Create an encryptor class
		$key = $this->getOtpConfigEncryptionKey();
		$aes = new Aes($key, 256);

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
		return Factory::getApplication()->get('secret');
	}

	/**
	 * Gets the configuration forms for all two-factor authentication methods
	 * in an array.
	 *
	 * @param   integer  $userId  The user ID to load the forms for (optional)
	 *
	 * @return  array
	 *
	 * @since   3.2
	 * @throws  \Exception
	 */
	public function getTwofactorform($userId = null)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		$otpConfig = $this->getOtpConfig($userId);

		PluginHelper::importPlugin('twofactorauth');

		return Factory::getApplication()->triggerEvent('onUserTwofactorShowConfiguration', array($otpConfig, $userId));
	}

	/**
	 * Generates a new set of One Time Emergency Passwords (OTEPs) for a given user.
	 *
	 * @param   integer  $userId  The user ID
	 * @param   integer  $count   How many OTEPs to generate? Default: 10
	 *
	 * @return  array  The generated OTEPs
	 *
	 * @since   3.2
	 */
	public function generateOteps($userId, $count = 10)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		// Initialise
		$oteps = array();

		// Get the OTP configuration for the user
		$otpConfig = $this->getOtpConfig($userId);

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
			$random = Crypt::genRandomBytes($length + 1);
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
		$this->setOtpConfig($userId, $otpConfig);

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
	 * @param   integer  $userId     The user's numeric ID
	 * @param   string   $secretKey  The secret key you want to check
	 * @param   array    $options    Options; see above
	 *
	 * @return  boolean  True if it's a valid secret key for this user.
	 *
	 * @since   3.2
	 * @throws  \Exception
	 */
	public function isValidSecretKey($userId, $secretKey, $options = array())
	{
		// Load the user's OTP (one time password, a.k.a. two factor auth) configuration
		if (!array_key_exists('otp_config', $options))
		{
			$otpConfig = $this->getOtpConfig($userId);
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
			$lang = Factory::getLanguage();
			$extension = 'com_users';
			$source = JPATH_ADMINISTRATOR . '/components/' . $extension;

			$lang->load($extension, JPATH_ADMINISTRATOR)
				|| $lang->load($extension, $source);

			$warn = true;
			$warnMessage = Text::_('COM_USERS_ERROR_SECRET_CODE_WITHOUT_TFA');

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
			if (!empty($secretKey) && $warn)
			{
				try
				{
					$app = Factory::getApplication();
					$app->enqueueMessage($warnMessage, 'warning');
				}
				catch (\Exception $exc)
				{
					// This happens when we are in CLI mode. In this case
					// no warning is issued
					return true;
				}
			}

			return true;
		}

		$credentials = array(
			'secretkey' => $secretKey,
		);

		// Try to validate the OTP
		PluginHelper::importPlugin('twofactorauth');

		$otpAuthReplies = Factory::getApplication()->triggerEvent('onUserTwofactorAuthenticate', array($credentials, $options));

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
			$check = $this->isValidOtep($userId, $secretKey, $otpConfig);
		}

		return $check;
	}

	/**
	 * Checks if the supplied string is a valid one time emergency password
	 * (OTEP) for this user. If it is it will be automatically removed from the
	 * user's list of OTEPs.
	 *
	 * @param   integer  $userId     The user ID against which you are checking
	 * @param   string   $otep       The string you want to test for validity
	 * @param   object   $otpConfig  Optional; the two factor authentication configuration (automatically fetched if not set)
	 *
	 * @return  boolean  True if it's a valid OTEP or if two factor auth is not
	 *                   enabled in this user's account.
	 *
	 * @since   3.2
	 */
	public function isValidOtep($userId, $otep, $otpConfig = null)
	{
		if (is_null($otpConfig))
		{
			$otpConfig = $this->getOtpConfig($userId);
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

			$this->setOtpConfig($userId, $otpConfig);

			// Return true; the OTEP was a valid one
			$check = true;
		}

		return $check;
	}
}
