<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

namespace Joomla\Component\Workflow\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\String\StringHelper;

/**
 * Model class for workflow
 *
 * @since  4.0.0
 */
class WorkflowModel extends AdminModel
{
    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function populateState()
    {
        parent::populateState();

        $app       = Factory::getApplication();
        $context   = $this->option . '.' . $this->name;
        $extension = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', null, 'cmd');

        $this->setState('filter.extension', $extension);
    }

    /**
     * Method to change the title
     *
     * @param   integer  $categoryId  The id of the category.
     * @param   string   $alias       The alias.
     * @param   string   $title       The title.
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since  4.0.0
     */
    protected function generateNewTitle($categoryId, $alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('title' => $title))) {
            $title = StringHelper::increment($title);
        }

        return array($title, $alias);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean True on success.
     *
     * @since  4.0.0
     */
    public function save($data)
    {
        $table             = $this->getTable();
        $app               = Factory::getApplication();
        $user              = $app->getIdentity();
        $input             = $app->input;
        $context           = $this->option . '.' . $this->name;
        $extension         = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', null, 'cmd');
        $data['extension'] = !empty($data['extension']) ? $data['extension'] : $extension;

        // Make sure we use the correct extension when editing an existing workflow
        $key = $table->getKeyName();
        $pk  = (isset($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        if ($pk > 0) {
            $table->load($pk);

            $data['extension'] = $table->extension;
        }

        if (isset($data['rules']) && !$user->authorise('core.admin', $data['extension'])) {
            unset($data['rules']);
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();

            // Alter the title for save as copy
            if ($origTable->load(['title' => $data['title']])) {
                list($title) = $this->generateNewTitle(0, '', $data['title']);
                $data['title'] = $title;
            }

            // Unpublish new copy
            $data['published'] = 0;
            $data['default'] = 0;
        }

        $result = parent::save($data);

        // Create default stage for new workflow
        if ($result && $input->getCmd('task') !== 'save2copy' && $this->getState($this->getName() . '.new')) {
            $workflow_id = (int) $this->getState($this->getName() . '.id');

            $table = $this->getTable('Stage');

            $table->id = 0;
            $table->title = 'COM_WORKFLOW_BASIC_STAGE';
            $table->description = '';
            $table->workflow_id = $workflow_id;
            $table->published = 1;
            $table->default = 1;

            $table->store();
        }

        return $result;
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|boolean A Form object on success, false on failure
     *
     * @since   4.0.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_workflow.workflow',
            'workflow',
            array(
                'control'   => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form)) {
            return false;
        }

        $id = $data['id'] ?? $form->getValue('id');

        $item = $this->getItem($id);

        $canEditState = $this->canEditState((object) $item);

        // Modify the form based on access controls.
        if (!$canEditState || !empty($item->default)) {
            if (!$canEditState) {
                $form->setFieldAttribute('published', 'disabled', 'true');
                $form->setFieldAttribute('published', 'required', 'false');
                $form->setFieldAttribute('published', 'filter', 'unset');
            }

            $form->setFieldAttribute('default', 'disabled', 'true');
            $form->setFieldAttribute('default', 'required', 'false');
            $form->setFieldAttribute('default', 'filter', 'unset');
        }

        $form->setFieldAttribute('created', 'default', Factory::getDate()->format('Y-m-d H:i:s'));
        $form->setFieldAttribute('modified', 'default', Factory::getDate()->format('Y-m-d H:i:s'));

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     *
     * @since  4.0.0
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState(
            'com_workflow.edit.workflow.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to preprocess the form.
     *
     * @param   Form    $form   Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $extension = Factory::getApplication()->input->get('extension');

        $parts = explode('.', $extension);

        $extension = array_shift($parts);

        // Set the access control rules field component value.
        $form->setFieldAttribute('rules', 'component', $extension);

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   4.0.0
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('extension') . ' = ' . $db->quote($table->extension),
        ];
    }

    /**
     * Method to change the default state of one item.
     *
     * @param   array    $pk     A list of the primary keys to change.
     * @param   integer  $value  The value of the home state.
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     */
    public function setDefault($pk, $value = 1)
    {
        $table = $this->getTable();

        if ($table->load($pk)) {
            if ($table->published !== 1) {
                $this->setError(Text::_('COM_WORKFLOW_ITEM_MUST_PUBLISHED'));

                return false;
            }
        }

        if (empty($table->id) || !$this->canEditState($table)) {
            Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');

            return false;
        }

        $date = Factory::getDate()->toSql();

        if ($value) {
            // Unset other default item
            if (
                $table->load(
                    [
                    'default' => '1',
                    'extension' => $table->get('extension')
                    ]
                )
            ) {
                $table->default = 0;
                $table->modified = $date;
                $table->store();
            }
        }

        if ($table->load($pk)) {
            $table->modified = $date;
            $table->default  = $value;
            $table->store();
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @since  4.0.0
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return Factory::getUser()->authorise('core.delete', $record->extension . '.workflow.' . (int) $record->id);
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   4.0.0
     */
    protected function canEditState($record)
    {
        $user = Factory::getUser();

        // Check for existing workflow.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $record->extension . '.workflow.' . (int) $record->id);
        }

        // Default to component settings if workflow isn't known.
        return $user->authorise('core.edit.state', $record->extension);
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     */
    public function publish(&$pks, $value = 1)
    {
        $table = $this->getTable();
        $pks   = (array) $pks;

        $date = Factory::getDate()->toSql();

        // Default workflow item check.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk) && $value != 1 && $table->default) {
                // Prune items that you can't change.
                Factory::getApplication()->enqueueMessage(Text::_('COM_WORKFLOW_UNPUBLISH_DEFAULT_ERROR'), 'error');
                unset($pks[$i]);
                break;
            }
        }

        // Clean the cache.
        $this->cleanCache();

        // Ensure that previous checks don't empty the array.
        if (empty($pks)) {
            return true;
        }

        $table->load($pk);
        $table->modified = $date;
        $table->store();

        return parent::publish($pks, $value);
    }
}
