<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which supports state behavior
 *
 * @since  4.0.0
 */
trait WorkflowBehaviorTrait
{
    /**
     * The name of the component.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension = null;

    /**
     * The section of the component.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $section = '';

    /**
     * Is workflow for this component enabled?
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $workflowEnabled = false;

    /**
     * The workflow object
     *
     * @var    Workflow
     * @since  4.0.0
     */
    protected $workflow;

    /**
     * Set Up the workflow
     *
     * @param   string  $extension  The option and section separated by.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setUpWorkflow($extension)
    {
        $parts = explode('.', $extension);

        $this->extension = array_shift($parts);

        if (\count($parts)) {
            $this->section = array_shift($parts);
        }

        if (method_exists($this, 'getDatabase')) {
            $db = $this->getDatabase();
        } else {
            @trigger_error('From 6.0 implementing the getDatabase method will be mandatory.', E_USER_DEPRECATED);
            $db = Factory::getContainer()->get(DatabaseDriver::class);
        }

        $this->workflow = new Workflow($extension, Factory::getApplication(), $db);

        $params = ComponentHelper::getParams($this->extension);

        $this->workflowEnabled = $params->get('workflow_enabled');

        $this->enableWorkflowBatch();
    }

    /**
     * Add the workflow batch to the command list. Can be overwritten by the child class
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function enableWorkflowBatch()
    {
        // Enable batch
        if ($this->workflowEnabled && property_exists($this, 'batch_commands')) {
            $this->batch_commands['workflowstage_id'] = 'batchWorkflowStage';
        }
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   Form   $form  A Form object.
     * @param   mixed  $data  The data expected for the form.
     *
     * @return  void
     *
     * @since   4.0.0
     * @see     FormField
     */
    public function workflowPreprocessForm(Form $form, $data)
    {
        $this->addTransitionField($form, $data);

        if (!$this->workflowEnabled) {
            return;
        }

        // Import the workflow plugin group to allow form manipulation.
        $this->importWorkflowPlugins();
    }

    /**
     * Let plugins access stage change events
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function workflowBeforeStageChange()
    {
        if (!$this->workflowEnabled) {
            return;
        }

        $this->importWorkflowPlugins();
    }

    /**
     * Preparation of workflow data/plugins
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function workflowBeforeSave()
    {
        if (!$this->workflowEnabled) {
            return;
        }

        $this->importWorkflowPlugins();
    }

    /**
     * Executing of relevant workflow methods
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function workflowAfterSave($data)
    {
        // Regardless if workflow is active or not, we have to set the default stage
        // So we can work with the workflow, when the user activates it later
        $id    = $this->getState($this->getName() . '.id');
        $isNew = $this->getState($this->getName() . '.new');

        // We save the first stage
        if ($isNew) {
            // We have to add the paths, because it could be called outside of the extension context
            $path = JPATH_BASE . '/components/' . $this->extension;

            $path = Path::check($path);

            Form::addFormPath($path . '/forms');
            Form::addFormPath($path . '/models/forms');
            Form::addFieldPath($path . '/models/fields');
            Form::addFormPath($path . '/model/form');
            Form::addFieldPath($path . '/model/field');

            $form = $this->getForm();

            $stage_id = $this->getStageForNewItem($form, $data);

            $this->workflow->createAssociation($id, $stage_id);
        }

        if (!$this->workflowEnabled) {
            return;
        }

        // Execute transition
        if (!empty($data['transition'])) {
            $this->executeTransition([$id], $data['transition']);
        }
    }

    /**
     * Batch change workflow stage or current.
     *
     * @param   integer  $value     The workflow stage ID.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since   4.0.0
     */
    public function batchWorkflowStage(int $value, array $pks, array $contexts)
    {
        $user = Factory::getApplication()->getIdentity();

        $workflow = Factory::getApplication()->bootComponent('com_workflow');

        if (!$user->authorise('core.admin', $this->option)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EXECUTE_TRANSITION'));
        }

        // Get workflow stage information
        $stage = $workflow->getMVCFactory()->createTable('Stage', 'Administrator');

        if (empty($value) || !$stage->load($value)) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_BATCH_WORKFLOW_STAGE_ROW_NOT_FOUND'), 'error');

            return false;
        }

        if (empty($pks)) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_BATCH_WORKFLOW_STAGE_ROW_NOT_FOUND'), 'error');

            return false;
        }

        // Update workflow associations
        return $this->workflow->updateAssociations($pks, $value);
    }

    /**
     * Batch change workflow stage or current.
     *
     * @param   integer  $oldId     The ID of the item copied from
     * @param   integer  $newId     The ID of the new item
     *
     * @return  null
     *
     * @since   4.0.0
     */
    public function workflowCleanupBatchMove($oldId, $newId)
    {
        // Trigger workflow plugins only if enable (will be triggered from parent class)
        if ($this->workflowEnabled) {
            $this->importWorkflowPlugins();
        }

        // We always need an association, so create one
        $table = $this->getTable();

        $table->load($newId);

        $catKey = $table->getColumnAlias('catid');

        $stage_id = $this->workflow->getDefaultStageByCategory($table->$catKey);

        if (empty($stage_id)) {
            return;
        }

        $this->workflow->createAssociation((int) $newId, (int) $stage_id);
    }

    /**
     * Runs transition for item.
     *
     * @param   array    $pks           Id of items to execute the transition
     * @param   integer  $transitionId  Id of transition
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function executeTransition(array $pks, int $transitionId)
    {
        $result = $this->workflow->executeTransition($pks, $transitionId);

        if (!$result) {
            $app = Factory::getApplication();

            $app->enqueueMessage(Text::_('COM_CONTENT_ERROR_UPDATE_STAGE', $app::MSG_WARNING));

            return false;
        }

        return true;
    }

    /**
     * Import the Workflow plugins.
     *
     * @param   Form   $form  A Form object.
     * @param   mixed  $data  The data expected for the form.
     *
     * @return  void
     */
    protected function importWorkflowPlugins()
    {
        PluginHelper::importPlugin('workflow');
    }

    /**
     * Adds a transition field to the form. Can be overwritten by the child class if not needed
     *
     * @param   Form   $form  A Form object.
     * @param   mixed  $data  The data expected for the form.
     *
     * @return  void
     * @since   4.0.0
     */
    protected function addTransitionField(Form $form, $data)
    {
        $extension = $this->extension . ($this->section ? '.' . $this->section : '');

        $field = new \SimpleXMLElement('<field></field>');

        $field->addAttribute('name', 'transition');
        $field->addAttribute('type', $this->workflowEnabled ? 'transition' : 'hidden');
        $field->addAttribute('label', 'COM_CONTENT_WORKFLOW_STAGE');
        $field->addAttribute('extension', $extension);

        $form->setField($field);

        $table = $this->getTable();

        $key = $table->getKeyName();

        $id = $data->$key ?? $form->getValue($key);

        if ($id) {
            // Transition field
            $assoc = $this->workflow->getAssociation($id);

            if (!empty($assoc->stage_id)) {
                $form->setFieldAttribute('transition', 'workflow_stage', (int) $assoc->stage_id);
            }
        } else {
            $stage_id = $this->getStageForNewItem($form, $data);

            if (!empty($stage_id)) {
                $form->setFieldAttribute('transition', 'workflow_stage', (int) $stage_id);
            }
        }
    }

    /**
     * Try to load a workflow stage for newly created items
     * which does not have a workflow assigned yet. If the category is not the
     * carrier, overwrite it on your model and deliver your own carrier.
     *
     * @param   Form   $form  A Form object.
     * @param   mixed  $data  The data expected for the form.
     *
     * @return  boolean|integer  An integer, holding the stage ID or false
     * @since   4.0.0
     */
    protected function getStageForNewItem(Form $form, $data)
    {
        $table = $this->getTable();

        $hasKey = $table->hasField('catid');

        if (!$hasKey) {
            return false;
        }

        $catKey = $table->getColumnAlias('catid');

        $field = $form->getField($catKey);

        if (!$field) {
            return false;
        }

        $catId = ((object) $data)->$catKey ?? $form->getValue($catKey);

        // Try to get the category from the html code of the field
        if (empty($catId)) {
            $catId = $field->getAttribute('default', null);

            if (!$catId) {
                // Choose the first category available
                $catOptions = $field->options;

                if ($catOptions && !empty($catOptions[0]->value)) {
                    $catId = (int) $catOptions[0]->value;
                }
            }
        }

        if (empty($catId)) {
            return false;
        }

        return $this->workflow->getDefaultStageByCategory($catId);
    }
}
