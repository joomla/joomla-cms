<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Event\User\UserGroupAfterDeleteEvent;
use Joomla\CMS\Event\User\UserGroupBeforeDeleteEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User group model.
 *
 * @since  1.6
 */
class GroupModel extends AdminModel
{
    /**
     * Override parent constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config = array_merge(
            [
                'event_after_delete'  => 'onUserAfterDeleteGroup',
                'event_after_save'    => 'onUserAfterSaveGroup',
                'event_before_delete' => 'onUserBeforeDeleteGroup',
                'event_before_save'   => 'onUserBeforeSaveGroup',
                'events_map'          => ['delete' => 'user', 'save' => 'user'],
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
     * @return  Table   A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Usergroup', $prefix = 'Joomla\\CMS\\Table\\', $config = [])
    {
        $return = Table::getInstance($type, $prefix, $config);

        return $return;
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
        $form = $this->loadForm('com_users.group', 'group', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
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
        $data = Factory::getApplication()->getUserState('com_users.edit.group.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_users.group', $data);

        return $data;
    }

    /**
     * Override preprocessForm to load the user plugin group instead of content.
     *
     * @param   Form    $form   A form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception if there is an error loading the form.
     */
    protected function preprocessForm(Form $form, $data, $group = '')
    {
        $obj = \is_array($data) ? ArrayHelper::toObject($data) : $data;

        if (isset($obj->parent_id) && $obj->parent_id == 0 && $obj->id > 0) {
            $form->setFieldAttribute('parent_id', 'type', 'hidden');
            $form->setFieldAttribute('parent_id', 'hidden', 'true');
        }

        parent::preprocessForm($form, $data, 'user');
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
        // Include the user plugins for events.
        PluginHelper::importPlugin($this->events_map['save']);

        /**
         * Check the super admin permissions for group
         * We get the parent group permissions and then check the group permissions manually
         * We have to calculate the group permissions manually because we haven't saved the group yet
         */
        $parentSuperAdmin = Access::checkGroup($data['parent_id'], 'core.admin');

        // Get core.admin rules from the root asset
        $rules = Access::getAssetRules('root.1')->getData();

        // Get the value for the current group (will be true (allowed), false (denied), or null (inherit)
        $groupSuperAdmin = $rules['core.admin']->allow($data['id']);

        // We only need to change the $groupSuperAdmin if the parent is true or false. Otherwise, the value set in the rule takes effect.
        if ($parentSuperAdmin === false) {
            // If parent is false (Denied), effective value will always be false
            $groupSuperAdmin = false;
        } elseif ($parentSuperAdmin === true) {
            // If parent is true (allowed), group is true unless explicitly set to false
            $groupSuperAdmin = ($groupSuperAdmin === false) ? false : true;
        }

        // Check for non-super admin trying to save with super admin group
        $iAmSuperAdmin = $this->getCurrentUser()->authorise('core.admin');

        if (!$iAmSuperAdmin && $groupSuperAdmin) {
            $this->setError(Text::_('JLIB_USER_ERROR_NOT_SUPERADMIN'));

            return false;
        }

        /**
         * Check for super-admin changing self to be non-super-admin
         * First, are we a super admin
         */
        if ($iAmSuperAdmin) {
            // Next, are we a member of the current group?
            $myGroups = Access::getGroupsByUser($this->getCurrentUser()->id, false);

            if (\in_array($data['id'], $myGroups)) {
                // Now, would we have super admin permissions without the current group?
                $otherGroups     = array_diff($myGroups, [$data['id']]);
                $otherSuperAdmin = false;

                foreach ($otherGroups as $otherGroup) {
                    $otherSuperAdmin = $otherSuperAdmin ?: Access::checkGroup($otherGroup, 'core.admin');
                }

                /**
                 * If we would not otherwise have super admin permissions
                 * and the current group does not have super admin permissions, throw an exception
                 */
                if ((!$otherSuperAdmin) && (!$groupSuperAdmin)) {
                    $this->setError(Text::_('JLIB_USER_ERROR_CANNOT_DEMOTE_SELF'));

                    return false;
                }
            }
        }

        if (Factory::getApplication()->getInput()->get('task') == 'save2copy') {
            $data['title'] = $this->generateGroupTitle($data['parent_id'], $data['title']);
        }

        // Proceed with the save
        return parent::save($data);
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
        // Typecast variable.
        $pks        = (array) $pks;
        $user       = $this->getCurrentUser();
        $groups     = Access::getGroupsByUser($user->id);
        $context    = $this->option . '.' . $this->name;
        $dispatcher = $this->getDispatcher();

        // Get a row instance.
        $table = $this->getTable();

        // Load plugins.
        PluginHelper::importPlugin($this->events_map['delete'], null, true, $dispatcher);

        // Check if I am a Super Admin
        $iAmSuperAdmin = $user->authorise('core.admin');

        foreach ($pks as $pk) {
            // Do not allow to delete groups to which the current user belongs
            if (\in_array($pk, $groups)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_DELETE_ERROR_INVALID_GROUP'), 'error');

                return false;
            }

            if (!$table->load($pk)) {
                // Item is not in the table.
                $this->setError($table->getError());

                return false;
            }
        }

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                // Access checks.
                $allow = $user->authorise('core.edit.state', 'com_users');

                // Don't allow non-super-admin to delete a super admin
                $allow = (!$iAmSuperAdmin && Access::checkGroup($pk, 'core.admin')) ? false : $allow;

                if ($allow) {
                    // Fire the before delete event.
                    $beforeDeleteEvent = new UserGroupBeforeDeleteEvent($this->event_before_delete, [
                        'data'    => $table->getProperties(), // @TODO: Remove data argument in Joomla 6, see UserGroupBeforeDeleteEvent
                        'context' => $context,
                        'subject' => $table,
                    ]);
                    $result = $dispatcher->dispatch($this->event_before_delete, $beforeDeleteEvent)->getArgument('result', []);

                    if (\in_array(false, $result, true)) {
                        $this->setError($table->getError());

                        return false;
                    }

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());

                        return false;
                    }

                    // Trigger the after delete event.
                    $dispatcher->dispatch($this->event_after_delete, new UserGroupAfterDeleteEvent($this->event_after_delete, [
                        'data'           => $table->getProperties(), // @TODO: Remove data argument in Joomla 6, see UserGroupAfterDeleteEvent
                        'deletingResult' => true, // @TODO: Remove deletingResult argument in Joomla 6, see UserGroupAfterDeleteEvent
                        'errorMessage'   => $this->getError(), // @TODO: Remove errorMessage argument in Joomla 6, see UserGroupAfterDeleteEvent
                        'context'        => $context,
                        'subject'        => $table,
                    ]));
                } else {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    Factory::getApplication()->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'error');
                }
            }
        }

        return true;
    }

    /**
     * Method to generate the title of group on Save as Copy action
     *
     * @param   integer  $parentId  The id of the parent.
     * @param   string   $title     The title of group
     *
     * @return  string  Contains the modified title.
     *
     * @since   3.3.7
     */
    protected function generateGroupTitle($parentId, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(['title' => $title, 'parent_id' => $parentId])) {
            if ($title == $table->title) {
                $title = StringHelper::increment($title);
            }
        }

        return $title;
    }
}
