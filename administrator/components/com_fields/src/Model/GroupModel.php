<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Group Model
 *
 * @since  3.7.0
 */
class GroupModel extends AdminModel
{
    /**
     * @var null|string
     *
     * @since   3.7.0
     */
    public $typeAlias = null;

    /**
     * Allowed batch commands
     *
     * @var array
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
    ];

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   3.7.0
     */
    public function save($data)
    {
        // Alter the title for save as copy
        $input = Factory::getApplication()->getInput();

        // Save new group as unpublished
        if ($input->get('task') == 'save2copy') {
            $data['state'] = 0;
        }

        return parent::save($data);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   3.7.0
     * @throws  \Exception
     */
    public function getTable($name = 'Group', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A Form object on success, false on failure
     *
     * @since   3.7.0
     */
    public function getForm($data = [], $loadData = true)
    {
        $context = $this->getState('filter.context');
        $jinput  = Factory::getApplication()->getInput();

        if (empty($context) && isset($data['context'])) {
            $context = $data['context'];
            $this->setState('filter.context', $context);
        }

        // Get the form.
        $form = $this->loadForm(
            'com_fields.group.' . $context,
            'group',
            [
                'control'   => 'jform',
                'load_data' => $loadData,
            ]
        );

        if (empty($form)) {
            return false;
        }

        // Modify the form based on Edit State access controls.
        if (empty($data['context'])) {
            $data['context'] = $context;
        }

        $user = $this->getCurrentUser();

        if (!$user->authorise('core.edit.state', $context . '.fieldgroup.' . $jinput->get('id'))) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving. The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Don't allow to change the created_by user if not allowed to access com_users.
        if (!$user->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_by', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @since   3.7.0
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->state != -2) {
            return false;
        }

        return $this->getCurrentUser()->authorise('core.delete', $record->context . '.fieldgroup.' . (int) $record->id);
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the
     *                   component.
     *
     * @since   3.7.0
     */
    protected function canEditState($record)
    {
        $user = $this->getCurrentUser();

        // Check for existing fieldgroup.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $record->context . '.fieldgroup.' . (int) $record->id);
        }

        // Default to component settings.
        return $user->authorise('core.edit.state', $record->context);
    }

    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function populateState()
    {
        parent::populateState();

        $context = Factory::getApplication()->getUserStateFromRequest('com_fields.groups.context', 'context', 'com_fields', 'CMD');
        $this->setState('filter.context', $context);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   Table  $table  A Table object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   3.7.0
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('context') . ' = ' . $db->quote($table->context),
        ];
    }

    /**
     * Method to preprocess the form.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @see     \Joomla\CMS\Form\FormField
     * @since   3.7.0
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);

        $parts = FieldsHelper::extract($this->state->get('filter.context'));

        // If we don't have a valid context then return early
        if (!$parts) {
            return;
        }

        // Extract the component name
        $component = $parts[0];

        // Extract the section name
        $section = $parts[1];

        // Set the access control rules field component value.
        $form->setFieldAttribute('rules', 'component', $component);

        // Looking first in the component models/forms folder
        $path = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/models/forms/fieldgroup/' . $section . '.xml');

        if (file_exists($path)) {
            $lang = Factory::getLanguage();
            $lang->load($component, JPATH_BASE);
            $lang->load($component, JPATH_BASE . '/components/' . $component);

            if (!$form->loadFile($path, false)) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }
        }
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
     * @see     JFormRule
     * @see     JFilterInput
     * @since   3.9.23
     */
    public function validate($form, $data, $group = null)
    {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_fields')) {
            if (isset($data['rules'])) {
                unset($data['rules']);
            }
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @since   3.7.0
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $data  = $app->getUserState('com_fields.edit.group.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Language, Access) in edit form if those have been selected in Field Group Manager
            if (!$data->id) {
                // Check for which context the Field Group Manager is used and get selected fields
                $context = substr($app->getUserState('com_fields.groups.filter.context', ''), 4);
                $filters = (array) $app->getUserState('com_fields.groups.' . $context . '.filter');

                $data->set(
                    'state',
                    $input->getInt('state', (!empty($filters['state']) ? $filters['state'] : null))
                );
                $data->set(
                    'language',
                    $input->getString('language', (!empty($filters['language']) ? $filters['language'] : null))
                );
                $data->set(
                    'access',
                    $input->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access')))
                );
            }
        }

        $this->preprocessData('com_fields.group', $data);

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since   3.7.0
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Prime required properties.
            if (empty($item->id)) {
                $item->context = $this->getState('filter.context');
            }

            if (property_exists($item, 'params')) {
                $item->params = new Registry($item->params);
            }
        }

        return $item;
    }

    /**
     * Clean the cache
     *
     * @param   string   $group     The cache group
     * @param   integer  $clientId  No longer used, will be removed without replacement
     *                              @deprecated   4.3 will be removed in 6.0
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        $context = Factory::getApplication()->getInput()->get('context');

        parent::cleanCache($context);
    }
}
