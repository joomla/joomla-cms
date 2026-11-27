<?php

/**
 * @package Joomla.Administrator
 * @subpackage com_workflow
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Event\Workflow\WorkflowConditionFieldsEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Workflow\Administrator\Automation\BuiltinConditionFields;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * Renders one automation condition builder.
 *
 * The mode attribute picks the filter, which offers item checks, or the condition, which offers
 * moment checks. The builder itself is drawn by condition-builder.js from the config output here.
 *
 * @since  __DEPLOY_VERSION__
 */
class ConditionbuilderField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'Conditionbuilder';

    /**
     * Builds the field markup: a hidden input for the JSON value, plus a config
     * blob (scoped to this field's mode) that the client-side builder reads.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        $document = Factory::getApplication()->getDocument();
        $document->getWebAssetManager()
            ->useStyle('com_workflow.condition-builder')
            ->useScript('com_workflow.condition-builder')
            ->usePreset('choicesjs')
            ->useScript('webcomponent.field-fancy-select')
            ->useScript('joomla.dialog-autocreate');

        Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
        Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

        $storedValue = $this->value;

        // The value round trips as a JSON string; normalise anything else back to one.
        if (\is_array($storedValue) || \is_object($storedValue)) {
            $storedValue = json_encode($storedValue);
        }

        $availableFields = $this->getAvailableFields();

        $configuration = json_encode([
            'fields'       => $this->getFieldChoices($availableFields),
            'operators'    => $this->getOperatorChoices($availableFields),
            'valueTypes'   => $this->getValueTypes($availableFields),
            'valueOptions' => $this->getValueChoices($availableFields),
            'text'         => $this->getInterfaceText(),
            'preview'      => $this->getPreviewConfig(),
            'expert'       => $this->isExpertMode(),
            'expertDialog' => $this->expertModeDialog(),
        ]);

        return '<div class="condition-builder" data-condition-builder data-config="'
            . htmlspecialchars($configuration, ENT_QUOTES, 'UTF-8') . '">'
            . '<input type="hidden" name="' . $this->name . '" id="' . $this->id
            . '" value="' . htmlspecialchars((string) $storedValue, ENT_QUOTES, 'UTF-8') . '">'
            . '</div>';
    }

    /**
     * The checks that may be offered here.
     *
     * @return array<string, array> Check definitions keyed by name.
     *
     * @since __DEPLOY_VERSION__
     */
    private function getAvailableFields(): array
    {
        $app       = Factory::getApplication();
        $extension = $this->resolveExtension($app);
        $database  = Factory::getContainer()->get(DatabaseInterface::class);

        $builtInFields = (new BuiltinConditionFields($database))->declarations($extension);

        PluginHelper::importPlugin('workflow');

        $event = new WorkflowConditionFieldsEvent(
            'onWorkflowListConditionFields',
            ['extension' => $extension]
        );

        Factory::getContainer()->get(DispatcherInterface::class)->dispatch($event->getName(), $event);

        // Union, not array_merge, so a plugin cannot replace a built-in check of the same name.
        $fields = $builtInFields + $event->getFields();

        $wantedScope = (string) $this->element['mode'] === 'filter'
            ? WorkflowConditionFieldsEvent::SCOPE_ITEM
            : WorkflowConditionFieldsEvent::SCOPE_MOMENT;

        return array_filter(
            $fields,
            static fn (array $field): bool => $field['scope'] === $wantedScope
        );
    }

    /**
     * Works out which workflow extension the builder is being drawn for.
     *
     * @param   \Joomla\CMS\Application\CMSApplicationInterface  $app  The application.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resolveExtension($app): string
    {
        // Prefer the workflow row: it stores com_content.article, while the request may only carry
        // com_content.
        $workflowId = (int) $app->getInput()->getInt('workflow_id');

        if ($workflowId > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('extension'))
                ->from($db->quoteName('#__workflows'))
                ->where($db->quoteName('id') . ' = :workflowId')
                ->bind(':workflowId', $workflowId, ParameterType::INTEGER);

            $extension = (string) $db->setQuery($query)->loadResult();

            if ($extension !== '') {
                return $extension;
            }
        }

        $requestedExtension = (string) $app->getInput()->getCmd('extension', '');

        return $requestedExtension !== '' ? $requestedExtension : 'com_content.article';
    }

    /**
     * The checks the builder may offer, as value and label pairs.
     *
     * @param   array  $fields  The available check definitions.
     *
     * @return  array<int, array<string, string>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getFieldChoices(array $fields): array
    {
        $choices = [];

        foreach ($fields as $field) {
            $choices[] = ['value' => $field['name'], 'label' => $field['label']];
        }

        return $choices;
    }

    /**
     * The operators each check supports.
     *
     * @param   array  $fields  The available check definitions.
     *
     * @return  array<string, array<int, array<string, string>>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getOperatorChoices(array $fields): array
    {
        $operatorLabels = [
            WorkflowConditionFieldsEvent::OPERATOR_IS           => Text::_('COM_WORKFLOW_AUTOMATION_OP_IS'),
            WorkflowConditionFieldsEvent::OPERATOR_IS_NOT       => Text::_('COM_WORKFLOW_AUTOMATION_OP_IS_NOT'),
            WorkflowConditionFieldsEvent::OPERATOR_IN           => Text::_('COM_WORKFLOW_AUTOMATION_OP_IN'),
            WorkflowConditionFieldsEvent::OPERATOR_NOT_IN       => Text::_('COM_WORKFLOW_AUTOMATION_OP_NOT_IN'),
            WorkflowConditionFieldsEvent::OPERATOR_HAS_ANY      => Text::_('COM_WORKFLOW_AUTOMATION_OP_HAS_ANY'),
            WorkflowConditionFieldsEvent::OPERATOR_HAS_ALL      => Text::_('COM_WORKFLOW_AUTOMATION_OP_HAS_ALL'),
            WorkflowConditionFieldsEvent::OPERATOR_HAS_NONE     => Text::_('COM_WORKFLOW_AUTOMATION_OP_HAS_NONE'),
            WorkflowConditionFieldsEvent::OPERATOR_BEFORE       => Text::_('COM_WORKFLOW_AUTOMATION_OP_BEFORE'),
            WorkflowConditionFieldsEvent::OPERATOR_AFTER        => Text::_('COM_WORKFLOW_AUTOMATION_OP_AFTER'),
            WorkflowConditionFieldsEvent::OPERATOR_ON           => Text::_('COM_WORKFLOW_AUTOMATION_OP_ON'),
            WorkflowConditionFieldsEvent::OPERATOR_NOT_ON       => Text::_('COM_WORKFLOW_AUTOMATION_OP_NOT_ON'),
            WorkflowConditionFieldsEvent::OPERATOR_GREATER_THAN => Text::_('COM_WORKFLOW_AUTOMATION_OP_GREATER_THAN'),
            WorkflowConditionFieldsEvent::OPERATOR_LESS_THAN    => Text::_('COM_WORKFLOW_AUTOMATION_OP_LESS_THAN'),
        ];

        $choices = [];

        foreach ($fields as $field) {
            foreach ($field['operators'] as $operator) {
                // Skipped, so a check written for a newer Joomla does not break the builder.
                if (!isset($operatorLabels[$operator])) {
                    continue;
                }

                $choices[$field['name']][] = ['value' => $operator, 'label' => $operatorLabels[$operator]];
            }
        }

        return $choices;
    }

    /**
     * How each check's value is entered.
     *
     * @param   array  $fields  The available check definitions.
     *
     * @return  array<string, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getValueTypes(array $fields): array
    {
        $types = [];

        foreach ($fields as $field) {
            $types[$field['name']] = $field['valueType'];
        }

        return $types;
    }

    /**
     * The selectable values for each check, resolved once here so the builder needs no
     * further requests.
     *
     * @param   array  $fields  The available check definitions.
     *
     * @return  array<string, array<int, array<string, string>>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getValueChoices(array $fields): array
    {
        $choices = [];

        foreach ($fields as $field) {
            if ($field['options'] !== []) {
                $choices[$field['name']] = $field['options'];
            }
        }

        return $choices;
    }

    /**
     * Translated strings the builder renders in its own markup.
     *
     * @return  array<string, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getInterfaceText(): array
    {
        $emptyKey = (string) $this->element['mode'] === 'filter'
            ? 'COM_WORKFLOW_AUTOMATION_BUILDER_EMPTY_FILTER'
            : 'COM_WORKFLOW_AUTOMATION_BUILDER_EMPTY_CONDITION';
        return [
            'addCheck'           => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_ADD_CHECK'),
            'expertHint'         => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EXPERT_HINT'),
            'expertLink'         => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EXPERT_LINK'),
            'expertOffHint'      => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EXPERT_OFF_HINT'),
            'expertOffLink'      => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EXPERT_OFF_LINK'),
            'expertHeader'       => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EXPERT_HEADER'),
            'addExpression'      => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_ADD_EXPRESSION'),
            'remove'             => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_REMOVE'),
            'check'              => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_CHECK'),
            'expression'         => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EXPRESSION'),
            'negate'             => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_NEGATE'),
            'joinWith'           => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_JOIN_WITH'),
            'opAnd'              => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_OP_AND'),
            'opOr'               => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_OP_OR'),
            'cancel'             => Text::_('JCANCEL'),
            'empty'              => Text::_($emptyKey),
            'placeholder'        => Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'),
            'emptyExpression'    => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_EMPTY_EXPRESSION'),
            'preview'            => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_BUTTON'),
            'previewRunning'     => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_RUNNING'),
            'previewResult'      => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_RESULT'),
            'previewCapped'      => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_CAPPED'),
            'previewEmpty'       => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_EMPTY'),
            'previewShowAll'     => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_SHOW_ALL'),
            'previewListHeader'  => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_LIST_HEADER'),
            'previewListTrimmed' => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_LIST_TRIMMED'),
            'previewListRange'   => Text::_('COM_WORKFLOW_AUTOMATION_PREVIEW_LIST_RANGE'),
            'field'              => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_FIELD'),
            'operator'           => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_OPERATOR'),
            'value'              => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_VALUE'),
            'removeCheck'        => Text::_('COM_WORKFLOW_AUTOMATION_BUILDER_REMOVE_CHECK'),
            'previous'           => Text::_('JPREVIOUS'),
            'next'               => Text::_('JNEXT'),
            'close'              => Text::_('JCLOSE'),
        ];
    }

    /**
     * Whether the Workflow Automation plugin is set to offer OR, sub-expressions and NOT.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isExpertMode(): bool
    {
        $plugin = PluginHelper::getPlugin('workflow', 'automation');

        return \is_object($plugin) && (int) (new Registry($plugin->params))->get('expert_mode', 0) === 1;
    }

    /**
     * Where Expert Mode is switched on, for the hint the simple builder shows.
     *
     * @return  string  The plugin's settings link, or '' when this user may not open it.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function expertModeDialog(): ?array
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_plugins')) {
            return null;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('workflow'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('automation'));

        $extensionId = (int) $db->setQuery($query)->loadResult();

        if ($extensionId < 1) {
            return null;
        }

        return [
            'src' => Route::_(
                'index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $extensionId
                    . '&tmpl=component&layout=modal',
                false
            ),

            // Opening the plugin checks it out, so closing the dialog has to check it back in.
            'checkinUrl' => Route::_(
                'index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $extensionId,
                false
            ),
        ];
    }

    /**
     * What the browser needs to ask the server which items this filter matches.
     *
     * Null for the condition field, which answers when a rule may run rather than which items match.
     *
     * @return array|null
     *
     * @since __DEPLOY_VERSION__
     */
    private function getPreviewConfig(): ?array
    {
        if ((string) $this->element['mode'] !== 'filter') {
            return null;
        }

        $input = Factory::getApplication()->getInput();

        return [
            'url'          => 'index.php?option=com_workflow&task=transition.previewFilter',
            'extension'    => $input->getCmd('extension'),
            'workflowId'   => $input->getInt('workflow_id'),
            'transitionId' => $input->getInt('id'),
            'token'        => Session::getFormToken(),
        ];
    }
}
