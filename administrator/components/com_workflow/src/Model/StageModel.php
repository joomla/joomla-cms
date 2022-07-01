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
 * Model class for stage
 *
 * @since  4.0.0
 */
class StageModel extends AdminModel
{
    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   4.0.0
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
     * @since   4.0.0
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
     * @return   boolean  True on success.
     *
     * @since  4.0.0
     */
    public function save($data)
    {
        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;
        $app        = Factory::getApplication();
        $user       = $app->getIdentity();
        $input      = $app->input;
        $workflowID = $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');

        if (empty($data['workflow_id'])) {
            $data['workflow_id'] = $workflowID;
        }

        $workflow = $this->getTable('Workflow');

        $workflow->load($data['workflow_id']);

        $parts = explode('.', $workflow->extension);

        if (isset($data['rules']) && !$user->authorise('core.admin', $parts[0])) {
            unset($data['rules']);
        }

        // Make sure we use the correct extension when editing an existing workflow
        $key = $table->getKeyName();
        $pk  = (isset($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        if ($pk > 0) {
            $table->load($pk);

            if ((int) $table->workflow_id) {
                $data['workflow_id'] = (int) $table->workflow_id;
            }
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();

            // Alter the title for save as copy
            if ($origTable->load(['title' => $data['title']])) {
                list($title) = $this->generateNewTitle(0, '', $data['title']);
                $data['title'] = $title;
            }

            $data['published'] = 0;
            $data['default']   = 0;
        }

        return parent::save($data);
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
        $table = $this->getTable('Workflow', 'Administrator');

        $table->load($record->workflow_id);

        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        $app = Factory::getApplication();
        $extension = $app->getUserStateFromRequest('com_workflow.stage.filter.extension', 'extension', null, 'cmd');

        $parts = explode('.', $extension);

        $component = reset($parts);

        if (!Factory::getUser()->authorise('core.delete', $component . '.state.' . (int) $record->id) || $record->default) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

            return false;
        }

        return true;
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
        $app = Factory::getApplication();
        $context = $this->option . '.' . $this->name;
        $extension = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', null, 'cmd');

        if (!\property_exists($record, 'workflow_id')) {
            $workflowID          = $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');
            $record->workflow_id = $workflowID;
        }

        // Check for existing workflow.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $extension . '.state.' . (int) $record->id);
        }

        // Default to component settings if workflow isn't known.
        return $user->authorise('core.edit.state', $extension);
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean A Form object on success, false on failure
     *
     * @since   4.0.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_workflow.state',
            'stage',
            array(
                'control' => 'jform',
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
            'com_workflow.edit.state.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to change the home state of one or more items.
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
            if (!$table->published) {
                $this->setError(Text::_('COM_WORKFLOW_ITEM_MUST_PUBLISHED'));

                return false;
            }
        }

        if (empty($table->id) || !$this->canEditState($table)) {
            Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');

            return false;
        }

        if ($value) {
            // Verify that the home page for this language is unique per client id
            if ($table->load(array('default' => '1', 'workflow_id' => $table->workflow_id))) {
                $table->default = 0;
                $table->store();
            }
        }

        if ($table->load($pk)) {
            $table->default = $value;
            $table->store();
        }

        // Clean the cache
        $this->cleanCache();

        return true;
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
        $app = Factory::getApplication();
        $extension = $app->getUserStateFromRequest('com_workflow.state.filter.extension', 'extension', null, 'cmd');

        // Default item existence checks.
        if ($value != 1) {
            foreach ($pks as $i => $pk) {
                if ($table->load($pk) && $table->default) {
                    // Prune items that you can't change.
                    $app->enqueueMessage(Text::_('COM_WORKFLOW_MSG_DISABLE_DEFAULT'), 'error');

                    unset($pks[$i]);
                }
            }
        }

        return parent::publish($pks, $value);
    }

    /**
     * Method to preprocess the form.
     *
     * @param   Form    $form  A Form object.
     * @param   mixed   $data  The data expected for the form.
     * @param   string  $group The name of the plugin group to import (defaults to "content").
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
}
