<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        $config = array_merge(
            [
                'event_after_delete'  => 'onUserAfterDelete',
                'event_after_save'    => 'onUserAfterSave',
                'event_before_delete' => 'onUserBeforeDelete',
                'event_before_save'   => 'onUserBeforeSave',
                'events_map'          => ['save' => 'user', 'delete' => 'user', 'validate' => 'user'],
            ],
            $config
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
    public function getTable($type = 'User', $prefix = 'Joomla\\CMS\\Table\\', $config = [])
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

        if ($this->_item === null) {
            $this->_item = [];
        }

        if (!isset($this->_item[$pk])) {
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
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.user', 'user', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        $user = $this->getCurrentUser();

        // If the user needs to change their password, mark the password fields as required
        if ($user->requireReset) {
            $form->setFieldAttribute('password', 'required', 'true');
            $form->setFieldAttribute('password2', 'required', 'true');
        }

        // When multilanguage is set, a user's default site language should also be a Content Language
        if (Multilanguage::isEnabled()) {
            $form->setFieldAttribute('language', 'type', 'frontend_language', 'params');
        }

        $userId = (int) $form->getValue('id');

        // The user should not be able to set the requireReset value on their own account
        if ($userId === (int) $user->id) {
            $form->removeField('requireReset');
        }

        /**
         * If users without core.manage permission editing their own account, remove some fields which they should
         * not be allowed to change and prevent them to change user name if configured
         */
        if (!$user->authorise('core.manage', 'com_users') && (int) $user->id === $userId) {
            if (!ComponentHelper::getParams('com_users')->get('change_login_name')) {
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
        $data = Factory::getApplication()->getUserState('com_users.edit.user.data', []);

        if (empty($data)) {
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

        $my            = $this->getCurrentUser();
        $iAmSuperAdmin = $my->authorise('core.admin');

        // User cannot modify own user groups
        if ((int) $user->id == (int) $my->id && !$iAmSuperAdmin && isset($data['groups'])) {
            // Form was probably tampered with
            Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_USERS_ERROR_CANNOT_EDIT_OWN_GROUP'), 'warning');

            $data['groups'] = null;
        }

        if ($data['block'] && $pk == $my->id && !$my->block) {
            $this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'));

            return false;
        }

        // Make sure user groups is selected when add/edit an account
        if (empty($data['groups']) && ((int) $user->id != (int) $my->id || $iAmSuperAdmin)) {
            $this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_SAVE_ACCOUNT_WITHOUT_GROUPS'));

            return false;
        }

        // Make sure that we are not removing ourself from Super Admin group
        if ($iAmSuperAdmin && $my->get('id') == $pk) {
            // Check that at least one of our new groups is Super Admin
            $stillSuperAdmin = false;
            $myNewGroups     = $data['groups'];

            foreach ($myNewGroups as $group) {
                $stillSuperAdmin = $stillSuperAdmin ?: Access::checkGroup($group, 'core.admin');
            }

            if (!$stillSuperAdmin) {
                $this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_DEMOTE_SELF'));

                return false;
            }
        }

        // Unset the username if it should not be overwritten
        if (
            !$my->authorise('core.manage', 'com_users')
            && (int) $user->id === (int) $my->id
            && !ComponentHelper::getParams('com_users')->get('change_login_name')
        ) {
            unset($data['username']);
        }

        // Bind the data.
        if (!$user->bind($data)) {
            $this->setError($user->getError());

            return false;
        }

        // Store the data.
        if (!$user->save()) {
            $this->setError($user->getError());

            return false;
        }

        // Destroy all active sessions for the user after changing the password or blocking him
        if ($data['password2'] || $data['block']) {
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
        $user  = $this->getCurrentUser();
        $table = $this->getTable();
        $pks   = (array) $pks;

        // Check if I am a Super Admin
        $iAmSuperAdmin = $user->authorise('core.admin');

        PluginHelper::importPlugin($this->events_map['delete']);

        if (in_array($user->id, $pks)) {
            $this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_DELETE_SELF'));

            return false;
        }

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                // Access checks.
                $allow = $user->authorise('core.delete', 'com_users');

                // Don't allow non-super-admin to delete a super admin
                $allow = (!$iAmSuperAdmin && Access::check($pk, 'core.admin')) ? false : $allow;

                if ($allow) {
                    // Get users data for the users to delete.
                    $user_to_delete = Factory::getUser($pk);

                    // Fire the before delete event.
                    Factory::getApplication()->triggerEvent($this->event_before_delete, [$table->getProperties()]);

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());

                        return false;
                    } else {
                        // Trigger the after delete event.
                        Factory::getApplication()->triggerEvent($this->event_after_delete, [$user_to_delete->getProperties(), true, $this->getError()]);
                    }
                } else {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    Factory::getApplication()->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'error');
                }
            } else {
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
        $user       = $this->getCurrentUser();

        // Check if I am a Super Admin
        $iAmSuperAdmin = $user->authorise('core.admin');
        $table         = $this->getTable();
        $pks           = (array) $pks;

        PluginHelper::importPlugin($this->events_map['save']);

        // Prepare the logout options.
        $options = [
            'clientid' => $app->get('shared_session', '0') ? null : 0,
        ];

        // Access checks.
        foreach ($pks as $i => $pk) {
            if ($value == 1 && $pk == $user->get('id')) {
                // Cannot block yourself.
                unset($pks[$i]);
                Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'), 'error');
            } elseif ($table->load($pk)) {
                $old   = $table->getProperties();
                $allow = $user->authorise('core.edit.state', 'com_users');

                // Don't allow non-super-admin to delete a super admin
                $allow = (!$iAmSuperAdmin && Access::check($pk, 'core.admin')) ? false : $allow;

                if ($allow) {
                    // Skip changing of same state
                    if ($table->block == $value) {
                        unset($pks[$i]);
                        continue;
                    }

                    $table->block = (int) $value;

                    // If unblocking, also change password reset count to zero to unblock reset
                    if ($table->block === 0) {
                        $table->resetCount = 0;
                    }

                    // Allow an exception to be thrown.
                    try {
                        if (!$table->check()) {
                            $this->setError($table->getError());

                            return false;
                        }

                        // Trigger the before save event.
                        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$old, false, $table->getProperties()]);

                        if (in_array(false, $result, true)) {
                            // Plugin will have to raise its own error or throw an exception.
                            return false;
                        }

                        // Store the table.
                        if (!$table->store()) {
                            $this->setError($table->getError());

                            return false;
                        }

                        if ($table->block) {
                            UserHelper::destroyUserSessions($table->id);
                        }

                        // Trigger the after save event
                        Factory::getApplication()->triggerEvent($this->event_after_save, [$table->getProperties(), false, true, null]);
                    } catch (\Exception $e) {
                        $this->setError($e->getMessage());

                        return false;
                    }

                    // Log the user out.
                    if ($value) {
                        $app->logout($table->id, $options);
                    }
                } else {
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
        $user = $this->getCurrentUser();

        // Check if I am a Super Admin
        $iAmSuperAdmin = $user->authorise('core.admin');
        $table         = $this->getTable();
        $pks           = (array) $pks;

        PluginHelper::importPlugin($this->events_map['save']);

        // Access checks.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                $old   = $table->getProperties();
                $allow = $user->authorise('core.edit.state', 'com_users');

                // Don't allow non-super-admin to delete a super admin
                $allow = (!$iAmSuperAdmin && Access::check($pk, 'core.admin')) ? false : $allow;

                if (empty($table->activation)) {
                    // Ignore activated accounts.
                    unset($pks[$i]);
                } elseif ($allow) {
                    $table->block      = 0;
                    $table->activation = '';

                    // Allow an exception to be thrown.
                    try {
                        if (!$table->check()) {
                            $this->setError($table->getError());

                            return false;
                        }

                        // Trigger the before save event.
                        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$old, false, $table->getProperties()]);

                        if (in_array(false, $result, true)) {
                            // Plugin will have to raise it's own error or throw an exception.
                            return false;
                        }

                        // Store the table.
                        if (!$table->store()) {
                            $this->setError($table->getError());

                            return false;
                        }

                        // Fire the after save event
                        Factory::getApplication()->triggerEvent($this->event_after_save, [$table->getProperties(), false, true, null]);
                    } catch (\Exception $e) {
                        $this->setError($e->getMessage());

                        return false;
                    }
                } else {
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
        if (array_search(0, $pks, true)) {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks)) {
            $this->setError(Text::_('COM_USERS_USERS_NO_ITEM_SELECTED'));

            return false;
        }

        $done = false;

        if (!empty($commands['group_id'])) {
            $cmd = ArrayHelper::getValue($commands, 'group_action', 'add');

            if (!$this->batchUser((int) $commands['group_id'], $pks, $cmd)) {
                return false;
            }

            $done = true;
        }

        if (!empty($commands['reset_id'])) {
            if (!$this->batchReset($pks, $commands['reset_id'])) {
                return false;
            }

            $done = true;
        }

        if (!$done) {
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
        $iAmSuperAdmin = $this->getCurrentUser()->authorise('core.admin');

        // Non-super super user cannot work with super-admin user.
        if (!$iAmSuperAdmin && UserHelper::checkSuperUserInUsers($userIds)) {
            $this->setError(Text::_('COM_USERS_ERROR_CANNOT_BATCH_SUPERUSER'));

            return false;
        }

        // Set the action to perform
        if ($action === 'yes') {
            $value = 1;
        } else {
            $value = 0;
        }

        // Prune out the current user if they are in the supplied user ID array
        $userIds = array_diff($userIds, [$this->getCurrentUser()->id]);

        if (empty($userIds)) {
            $this->setError(Text::_('COM_USERS_USERS_ERROR_CANNOT_REQUIRERESET_SELF'));

            return false;
        }

        // Get the DB object
        $db = $this->getDatabase();

        $userIds = ArrayHelper::toInteger($userIds);

        $query = $db->getQuery(true);

        // Update the reset flag
        $query->update($db->quoteName('#__users'))
            ->set($db->quoteName('requireReset') . ' = :requireReset')
            ->whereIn($db->quoteName('id'), $userIds)
            ->bind(':requireReset', $value, ParameterType::INTEGER);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
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
        $iAmSuperAdmin = $this->getCurrentUser()->authorise('core.admin');

        // Non-super super user cannot work with super-admin user.
        if (!$iAmSuperAdmin && UserHelper::checkSuperUserInUsers($userIds)) {
            $this->setError(Text::_('COM_USERS_ERROR_CANNOT_BATCH_SUPERUSER'));

            return false;
        }

        // Non-super admin cannot work with super-admin group.
        if ((!$iAmSuperAdmin && Access::checkGroup($groupId, 'core.admin')) || $groupId < 1) {
            $this->setError(Text::_('COM_USERS_ERROR_INVALID_GROUP'));

            return false;
        }

        // Get the DB object
        $db = $this->getDatabase();

        switch ($action) {
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
        if (isset($doDelete)) {
            /*
            * First we need to check that the user is part of more than one group
            * otherwise we will end up with a user that is not part of any group
            * unless we are moving the user to a new group.
            */
            if ($doDelete === 'group') {
                $query = $db->getQuery(true);
                $query->select($db->quoteName('user_id'))
                    ->from($db->quoteName('#__user_usergroup_map'))
                    ->whereIn($db->quoteName('user_id'), $userIds);

                // Add the group by clause to remove users who are only in one group
                $query->group($db->quoteName('user_id'))
                    ->having('COUNT(user_id) > 1');
                $db->setQuery($query);
                $users = $db->loadColumn();

                // If we have no users to process, throw an error to notify the user
                if (empty($users)) {
                    $this->setError(Text::_('COM_USERS_ERROR_ONLY_ONE_GROUP'));

                    return false;
                }

                // Check to see if the users are in the group to be removed
                $query->clear()
                    ->select($db->quoteName('user_id'))
                    ->from($db->quoteName('#__user_usergroup_map'))
                    ->whereIn($db->quoteName('user_id'), $users)
                    ->where($db->quoteName('group_id') . ' = :group_id')
                    ->bind(':group_id', $groupId, ParameterType::INTEGER);
                $db->setQuery($query);
                $users = $db->loadColumn();

                // If we have no users to process, throw an error to notify the user
                if (empty($users)) {
                    $this->setError(Text::_('COM_USERS_ERROR_NOT_IN_GROUP'));

                    return false;
                }

                // Finally remove the users from the group
                $query->clear()
                    ->delete($db->quoteName('#__user_usergroup_map'))
                    ->whereIn($db->quoteName('user_id'), $users)
                    ->where($db->quoteName('group_id') . '= :group_id')
                    ->bind(':group_id', $groupId, ParameterType::INTEGER);
                $db->setQuery($query);
            } elseif ($doDelete === 'all') {
                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__user_usergroup_map'))
                    ->whereIn($db->quoteName('user_id'), $userIds);
            }
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        // Assign the users to the group if requested.
        if (isset($doAssign)) {
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

            foreach ($userIds as $id) {
                if (!in_array($id, $users)) {
                    $query->values($id . ',' . $groupId);
                    $groups = true;
                }
            }

            // If we have no users to process, throw an error to notify the user
            if (!$groups) {
                $this->setError(Text::_('COM_USERS_ERROR_NO_ADDITIONS'));

                return false;
            }

            $query->insert($db->quoteName('#__user_usergroup_map'))
                ->columns([$db->quoteName('user_id'), $db->quoteName('group_id')]);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
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
        $user = $this->getCurrentUser();

        if ($user->authorise('core.edit', 'com_users') && $user->authorise('core.manage', 'com_users')) {
            $model = $this->bootComponent('com_users')
                ->getMVCFactory()->createModel('Groups', 'Administrator', ['ignore_request' => true]);

            return $model->getItems();
        } else {
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

        if (empty($userId)) {
            $result   = [];
            $form     = $this->getForm();

            if ($form) {
                $groupsIDs = $form->getValue('groups');
            }

            if (!empty($groupsIDs)) {
                $result = $groupsIDs;
            } else {
                $params = ComponentHelper::getParams('com_users');

                if ($groupId = $params->get('new_usertype', $params->get('guest_usergroup', 1))) {
                    $result[] = $groupId;
                }
            }
        } else {
            $result = UserHelper::getUserGroups($userId);
        }

        return $result;
    }

    /**
     * No longer used
     *
     * @param   integer  $userId  Ignored
     *
     * @return  \stdClass
     *
     * @since   3.2
     *
     * @deprecated   4.2 will be removed in 6.0.
     *               Will be removed without replacement
     */
    public function getOtpConfig($userId = null)
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. Use \Joomla\Component\Users\Administrator\Helper\Mfa::getUserMfaRecords() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        // Return the configuration object
        return (object) [
            'method' => 'none',
            'config' => [],
            'otep'   => [],
        ];
    }

    /**
     * No longer used
     *
     * @param   integer    $userId     Ignored
     * @param   \stdClass  $otpConfig  Ignored
     *
     * @return  boolean  True on success
     *
     * @since   3.2
     *
     * @deprecated   4.2 will be removed in 5.0.
     *               Will be removed without replacement
     */
    public function setOtpConfig($userId, $otpConfig)
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. Multi-factor Authentication actions are handled by plugins in the multifactorauth folder.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return true;
    }

    /**
     * No longer used
     *
     * @return  string
     *
     * @since   3.2
     *
     * @deprecated   4.2 will be removed in 6.0.
     *               Use \Joomla\CMS\Factory::getApplication()->get('secret') instead'
     */
    public function getOtpConfigEncryptionKey()
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. Use \Joomla\CMS\Factory::getApplication()->get(\'secret\') instead',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return Factory::getApplication()->get('secret');
    }

    /**
     * No longer used
     *
     * @param   integer  $userId  Ignored
     *
     * @return  array  Empty array
     *
     * @since   3.2
     * @throws  \Exception
     *
     * @deprecated   4.2 will be removed in 5.0.
     *               Will be removed without replacement
     */
    public function getTwofactorform($userId = null)
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. Use \Joomla\Component\Users\Administrator\Helper\Mfa::getConfigurationInterface()',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return [];
    }

    /**
     * No longer used
     *
     * @param   integer  $userId  Ignored
     * @param   integer  $count   Ignored
     *
     * @return  array  Empty array
     *
     * @since   3.2
     *
     * @deprecated   4.2 will be removed in 5.0
     *               Will be removed without replacement
     */
    public function generateOteps($userId, $count = 10)
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. See \Joomla\Component\Users\Administrator\Model\BackupcodesModel::saveBackupCodes()',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return [];
    }

    /**
     * No longer used. Always returns true.
     *
     * @param   integer  $userId     Ignored
     * @param   string   $secretKey  Ignored
     * @param   array    $options    Ignored
     *
     * @return  boolean  Always true
     *
     * @since   3.2
     * @throws  \Exception
     *
     * @deprecated   4.2 will be removed in 5.0
     *               Will be removed without replacement
     */
    public function isValidSecretKey($userId, $secretKey, $options = [])
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. Multi-factor Authentication actions are handled by plugins in the multifactorauth folder.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return true;
    }

    /**
     * No longer used
     *
     * @param   integer  $userId     Ignored
     * @param   string   $otep       Ignored
     * @param   object   $otpConfig  Ignored
     *
     * @return  boolean  Always true
     *
     * @since   3.2
     *
     * @deprecated   4.2 will be removed in 5.0
     *               Will be removed without replacement
     */
    public function isValidOtep($userId, $otep, $otpConfig = null)
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. Multi-factor Authentication actions are handled by plugins in the multifactorauth folder.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return true;
    }
}
