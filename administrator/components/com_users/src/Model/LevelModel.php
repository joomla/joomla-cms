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
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User view level model.
 *
 * @since  1.6
 */
class LevelModel extends AdminModel
{
    /**
     * Method to delete one or more access levels.
     *
     * @param   array  $pks  An array of primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   5.2.0
     */
    public function delete(&$pks)
    {
        $pks   = (array) $pks;
        $table = $this->getTable();

        $levelsInUse = [];

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                // Check if the access level is being used.
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select('DISTINCT access');

                // Get all tables that have the access field
                $checkTables = $db->getTableList();
                $prefix      = $db->getPrefix();

                foreach ($checkTables as $checktable) {
                    // Get all of the columns in the table
                    $fields = $db->getTableColumns($checktable);

                    /**
                     * We are looking for the access field. If custom tables are using something other
                     * than the 'access' field they are on their own unfortunately.
                     * Also make sure the table prefix matches the live db prefix (eg, it is not a "bak_" table)
                     */
                    if (strpos($checktable, $prefix) === 0 && isset($fields['access'])) {
                        // Lookup the distinct values of the field.
                        $query->clear('from')
                            ->from($db->quoteName($checktable));
                        $db->setQuery($query);

                        try {
                            $values = $db->loadColumn();
                        } catch (\RuntimeException $e) {
                            $this->setError($e->getMessage());

                            return false;
                        }

                        // Check if the table uses this access level
                        if (\in_array($pk, $values)) {
                            // Add the table to the list of tables that use this access level
                            $levelsInUse[$pk][] = $checktable;

                            // Remove the access level from the list of items to delete
                            unset($pks[$i]);
                        }
                    }
                }
            }
        }

        if (!empty($levelsInUse)) {
            $app = Factory::getApplication();
            $app->enqueueMessage(Text::_('COM_USERS_ERROR_VIEW_LEVEL_IN_USE'), 'error');

            foreach ($levelsInUse as $levelId => $usedIn) {
                $msg = Text::sprintf('COM_USERS_ERROR_VIEW_LEVEL_IN_USE_DETAILS', $levelId, implode(', ', $usedIn));
                $app->enqueueMessage($msg, 'error');
            }
        }

        if (empty($pks)) {
            // Nothing left to delete
            return true;
        }

        return parent::delete($pks);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        $groups = json_decode($record->rules);

        if ($groups === null) {
            throw new \RuntimeException('Invalid rules schema');
        }

        $isAdmin = $this->getCurrentUser()->authorise('core.admin');

        // Check permissions
        foreach ($groups as $group) {
            if (!$isAdmin && Access::checkGroup($group, 'core.admin')) {
                $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

                return false;
            }
        }

        return parent::canDelete($record);
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
    public function getTable($type = 'ViewLevel', $prefix = 'Joomla\\CMS\\Table\\', $config = [])
    {
        $return = Table::getInstance($type, $prefix, $config);

        return $return;
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

        // Convert the params field to an array.
        $result->rules = $result->rules !== null ? json_decode($result->rules) : [];

        return $result;
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
        $form = $this->loadForm('com_users.level', 'level', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  object  The data for the form.
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_users.edit.level.data');

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_users.level', $data);

        return $data;
    }

    /**
     * Method to preprocess the form
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
        // TO DO warning!
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
        if (!isset($data['rules'])) {
            $data['rules'] = [];
        }

        $data['title'] = InputFilter::getInstance()->clean($data['title'], 'TRIM');

        return parent::save($data);
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     \Joomla\CMS\Form\FormRule
     * @see     \Joomla\CMS\Filter\InputFilter
     * @since   3.8.8
     */
    public function validate($form, $data, $group = null)
    {
        $isSuperAdmin = $this->getCurrentUser()->authorise('core.admin');

        // Non Super user should not be able to change the access levels of super user groups
        if (!$isSuperAdmin) {
            if (!isset($data['rules']) || !\is_array($data['rules'])) {
                $data['rules'] = [];
            }

            $groups = array_values(UserGroupsHelper::getInstance()->getAll());

            $rules = [];

            if (!empty($data['id'])) {
                $table = $this->getTable();

                $table->load($data['id']);

                $rules = json_decode($table->rules);
            }

            $rules = ArrayHelper::toInteger($rules);

            foreach ($groups as $value) {
                if (Access::checkGroup((int) $value->id, 'core.admin')) {
                    if (\in_array((int) $value->id, $rules) && !\in_array((int) $value->id, $data['rules'])) {
                        $data['rules'][] = (int) $value->id;
                    } elseif (!\in_array((int) $value->id, $rules) && \in_array((int) $value->id, $data['rules'])) {
                        $this->setError(Text::_('JLIB_USER_ERROR_NOT_SUPERADMIN'));

                        return false;
                    }
                }
            }
        }

        return parent::validate($form, $data, $group);
    }
}
