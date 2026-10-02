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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for transition
 *
 * @since  4.0.0
 */
class TransitionModel extends AdminModel
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

        $app       = Factory::getApplication();
        $extension = $app->getUserStateFromRequest('com_workflow.transition.filter.extension', 'extension', null, 'cmd');

        return $this->getCurrentUser()->authorise('core.delete', $extension . '.transition.' . (int) $record->id);
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
        $user      = $this->getCurrentUser();
        $app       = Factory::getApplication();
        $context   = $this->option . '.' . $this->name;
        $extension = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', null, 'cmd');

        if (!property_exists($record, 'workflow_id')) {
            $workflowID          = $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');
            $record->workflow_id = $workflowID;
        }

        // Check for existing workflow.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $extension . '.transition.' . (int) $record->id);
        }

        // Default to component settings if workflow isn't known.
        return $user->authorise('core.edit.state', $extension);
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  \stdClass|boolean  Object on success, false on failure.
     *
     * @since   4.0.0
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if (property_exists($item, 'options')) {
            $registry      = new Registry($item->options);
            $item->options = $registry->toArray();
        }

        if (!empty($item->id)) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName([
                    'rule_type',
                    'delay_value',
                    'delay_unit',
                    'cron_expression',
                    'run_as_user_id',
                    'item_filter',
                    'fire_condition',
                ]))
                ->from($db->quoteName('#__workflow_automation_rules'))
                ->where($db->quoteName('transition_id') . ' = :id')
                ->bind(':id', $item->id, ParameterType::INTEGER)
                ->setLimit(1);

            $rule = $db->setQuery($query)->loadAssoc();

            if ($rule) {
                $item->automation = [
                    'automation_enabled' => 1,
                    'run_as_user_id'     => (int) ($rule['run_as_user_id'] ?? 0),
                    'automation_rules'   => $rule,
                ];
            } else {
                $item->automation = ['automation_enabled' => 0, 'run_as_user_id' => (int) $this->getCurrentUser()->id, 'automation_rules' => []];
            }
        }

        return $item;
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
        // Switching automation off saves an empty rule, which deletes the stored one.
        $automationData    = $data['automation'] ?? [];
        $automationEnabled = !empty($automationData['automation_enabled']);
        $automationRule    = $automationEnabled ? ($automationData['automation_rules'] ?? []) : [];
        unset($data['automation']);

        if (!empty($automationRule)) {
            $automationRule['run_as_user_id'] = (int) ($automationData['run_as_user_id'] ?? 0);
        }

        $transitionId = (int) ($data['id'] ?? $this->getState($this->getName() . '.id'));

        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;
        $app        = Factory::getApplication();
        $user       = $app->getIdentity();
        $input      = $app->getInput();

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

        // Make sure we use the correct workflow_id when editing an existing transition
        $key = $table->getKeyName();
        $pk  = $data[$key] ?? (int) $this->getState($this->getName() . '.id');

        if ($pk > 0) {
            $table->load($pk);

            if ((int) $table->workflow_id) {
                $data['workflow_id'] = (int) $table->workflow_id;
            }
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = $this->getTable();

            // Alter the title for save as copy
            if ($origTable->load(['title' => $data['title']])) {
                [$title]       = $this->generateNewTitle(0, '', $data['title']);
                $data['title'] = $title;
            }

            $data['published'] = 0;
        }

        if (!parent::save($data)) {
            return false;
        }

        $pk = (int) $this->getState($this->getName() . '.id');

        try {
            $this->saveAutomationRule($pk, $automationRule);
        } catch (\Throwable $error) {
            // The transition itself saved and the old rule survived the rollback, so this reports
            // what failed rather than claiming the whole save did.
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_WORKFLOW_AUTOMATION_RULE_SAVE_FAILED', $error->getMessage()),
                'error'
            );
        }
        if ($automationEnabled && !$this->canExecuteTransition((int) ($automationRule['run_as_user_id'] ?? 0), $pk)) {
            // Only a warning, because the permission is often granted after the rule is written.
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_WORKFLOW_AUTOMATION_WARNING_RUN_AS_CANNOT_EXECUTE'),
                'warning'
            );
        }

        return true;
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

        while ($table->load(['title' => $title])) {
            $title = StringHelper::increment($title);
        }

        return [$title, $alias];
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object
     *
     * @since   4.0.0
     * @throws  \Exception on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_workflow.transition',
            'transition',
            [
                'control'   => 'jform',
                'load_data' => $loadData,
            ]
        );

        $id = $data['id'] ?? $form->getValue('id');

        $item = $this->getItem($id);

        $canEditState = $this->canEditState((object) $item);

        // Modify the form based on access controls.
        if (!$canEditState) {
            $form->setFieldAttribute('published', 'disabled', 'true');
            $form->setFieldAttribute('published', 'required', 'false');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        if (!empty($item->workflow_id)) {
            $data['workflow_id'] = (int) $item->workflow_id;
        }

        if (empty($data['workflow_id'])) {
            $context = $this->option . '.' . $this->name;

            $data['workflow_id'] = (int) Factory::getApplication()->getUserStateFromRequest(
                $context . '.filter.workflow_id',
                'workflow_id',
                0,
                'int'
            );
        }

        $where = $this->getDatabase()->quoteName('workflow_id') . ' = ' . (int) $data['workflow_id'];
        $where .= ' AND ' . $this->getDatabase()->quoteName('published') . ' = 1';

        $form->setFieldAttribute('from_stage_id', 'sql_where', $where);
        $form->setFieldAttribute('to_stage_id', 'sql_where', $where);

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
            'com_workflow.edit.transition.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getWorkflow()
    {
        $app = Factory::getApplication();

        $context = $this->option . '.' . $this->name;

        $workflow_id = (int) $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');

        $workflow = $this->getTable('Workflow');

        $workflow->load($workflow_id);

        return (object) $workflow->getProperties();
    }

    /**
     * Trigger the form preparation for the workflow group
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @see     FormField
     * @since   4.0.0
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $extension = Factory::getApplication()->getInput()->get('extension');

        $parts = explode('.', $extension);

        $extension = array_shift($parts);

        // Set the access control rules field component value.
        $form->setFieldAttribute('rules', 'component', $extension);

        $user = $this->getCurrentUser();

        // Anyone but a super user may only keep the run-as user already saved or pick themselves, so the
        // picker becomes a list of those two. Changing the type in place keeps the field where it is.
        if (!$user->authorise('core.admin')) {
            $choices = array_unique([
                $this->storedRunAsUserId((int) $this->getState($this->getName() . '.id')),
                (int) $user->id,
            ]);

            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'name']))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('id') . ' IN (' . implode(',', $choices) . ')')
                ->order($db->quoteName('name'));

            $form->setFieldAttribute('run_as_user_id', 'type', 'sql', 'automation');
            $form->setFieldAttribute('run_as_user_id', 'key_field', 'id', 'automation');
            $form->setFieldAttribute('run_as_user_id', 'value_field', 'name', 'automation');
            $form->setFieldAttribute('run_as_user_id', 'query', (string) $query, 'automation');
        }

        // The Automation fieldset lives in this form rather than in the plugin that implements it,
        // so it has to be taken out when that plugin is off. Otherwise rules can be written and
        // saved that nothing will ever execute.
        if (!PluginHelper::isEnabled('workflow', 'automation')) {
            $form->removeGroup('automation');
        } elseif (!PluginHelper::isEnabled('task', 'workflowtransition')) {
            // Here the rules are still valid, they simply have nothing to run them, so this warns
            // rather than hiding work the user has already done.
            Factory::getApplication()->enqueueMessage(
                Text::sprintf(
                    'COM_WORKFLOW_AUTOMATION_WARNING_TASK_PLUGIN_DISABLED',
                    $this->taskPluginLink()
                ),
                'warning'
            );
        }

        // Import the appropriate plugin group.
        PluginHelper::importPlugin('workflow');

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * A link that opens the automation task plugin in a dialog, for the warning shown when it is off.
     *
     * Falls back to plain text for a user who may not edit plugins, so the sentence still reads.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function taskPluginLink(): string
    {
        $label = Text::_('COM_WORKFLOW_AUTOMATION_TASK_PLUGIN');
        /** @var \Joomla\CMS\Application\CMSWebApplicationInterface $app */
        $app = Factory::getApplication();

        if (!$app->getIdentity()->authorise('core.manage', 'com_plugins')) {
            return $label;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('task'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('workflowtransition'));

        $extensionId = (int) $db->setQuery($query)->loadResult();

        if ($extensionId < 1) {
            return $label;
        }

        // The dialog script is loaded by the condition builder too, but the warning can appear
        // before any builder renders, so it is asked for here as well.
        $app->getDocument()->getWebAssetManager()->useScript('joomla.dialog-autocreate');

        $popup = [
            'popupType'  => 'iframe',
            'textHeader' => $label,
            'src'        => Route::_(
                'index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $extensionId
                    . '&tmpl=component&layout=modal',
                false
            ),
        ];

        return HTMLHelper::_('link', '#', $label, [
            'data-joomla-dialog'    => htmlspecialchars(
                json_encode($popup, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ENT_QUOTES,
                'UTF-8'
            ),
            'data-checkin-url'      => Route::_('index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $extensionId, false),
            'data-close-on-message' => '',
            'data-reload-on-close'  => '',
        ]);
    }

    /**
     * Persists the automation rule for a transition, replacing whatever was there.
     *
     * @param   integer  $transitionId    The transition id.
     * @param   array    $automationRule  The submitted rule, or empty to clear.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function saveAutomationRule(int $transitionId, array $automationRule): void
    {
        $db   = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now  = Factory::getDate()->toSql();

        try {
            $db->transactionStart();

            $db->setQuery(
                $db->getQuery(true)
                    ->delete($db->quoteName('#__workflow_automation_rules'))
                    ->where($db->quoteName('transition_id') . ' = :id')
                    ->bind(':id', $transitionId, ParameterType::INTEGER)
            )->execute();

            if (!empty($automationRule)) {
                $ruleType = ($automationRule['rule_type'] ?? 'delay') === 'cron' ? 'cron' : 'delay';

                $ruleRow = (object) [
                    'transition_id'   => $transitionId,
                    'published'       => 1,
                    'ordering'        => 0,
                    'rule_type'       => $ruleType,
                    'delay_value'     => $ruleType === 'delay' ? (int) ($automationRule['delay_value'] ?? 0) : 0,
                    'delay_unit'      => $ruleType === 'delay' ? ($automationRule['delay_unit'] ?? 'minutes') : 'minutes',
                    'cron_expression' => $ruleType === 'cron' ? ($automationRule['cron_expression'] ?? '') : '',
                    'run_as_user_id'  => (int) ($automationRule['run_as_user_id'] ?? 0),
                    'item_filter'     => ($automationRule['item_filter'] ?? '') !== '' ? $automationRule['item_filter'] : null,
                    'fire_condition'  => ($automationRule['fire_condition'] ?? '') !== '' ? $automationRule['fire_condition'] : null,
                    'created'         => $now,
                    'created_by'      => $user->id,
                    'modified'        => $now,
                    'modified_by'     => $user->id,
                ];

                $db->insertObject('#__workflow_automation_rules', $ruleRow);
            }

            $db->transactionCommit();
        } catch (\Throwable $error) {
            $db->transactionRollback();

            throw $error;
        }
    }

    /**
     * The run-as user already stored for this transition, or 0 when there is no rule yet.
     *
     * @param   integer  $transitionId  The transition being saved.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function storedRunAsUserId(int $transitionId): int
    {
        if ($transitionId <= 0) {
            return 0;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('run_as_user_id'))
            ->from($db->quoteName('#__workflow_automation_rules'))
            ->where($db->quoteName('transition_id') . ' = :transitionId')
            ->bind(':transitionId', $transitionId, ParameterType::INTEGER);

        return (int) $db->setQuery($query)->loadResult();
    }

    /**
     * Whether an account holds the permission the scheduler will need at run time.
     *
     * Asks the same question that Workflow::getValidTransition() asks at run time.
     *
     * @param   integer  $userId        The run-as account.
     * @param   integer  $transitionId  The transition it would execute.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function canExecuteTransition(int $userId, int $transitionId): bool
    {
        $runAsUser = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

        if ((int) $runAsUser->id !== $userId) {
            return false;
        }

        $parts = explode('.', (string) Factory::getApplication()->getInput()->get('extension'));

        return $runAsUser->authorise('core.execute.transition', array_shift($parts) . '.transition.' . $transitionId);
    }
}
