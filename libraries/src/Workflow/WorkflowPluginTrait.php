<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Workflow;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use ReflectionClass;

/**
 * Trait for component workflow plugins.
 *
 * @since  4.0.0
 */
trait WorkflowPluginTrait
{
    /**
     * Add different parameter options to the transition view, we need when executing the transition
     *
     * @param   Form      $form The form
     * @param   \stdClass $data The data
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function enhanceWorkflowTransitionForm(Form $form, $data)
    {
        $workflow_id = (int) ($data->workflow_id ?? $form->getValue('workflow_id'));

        $workflow = $this->getWorkflow($workflow_id);

        if (empty($workflow->id) || !$this->isSupported($workflow->extension)) {
            return false;
        }

        // Load XML file from "parent" plugin
        $path = dirname((new ReflectionClass(static::class))->getFileName());

        if (is_file($path . '/forms/action.xml')) {
            $form->loadFile($path . '/forms/action.xml');
        }

        return $workflow;
    }

    /**
     * Get the workflow for a given ID
     *
     * @param   int|null $workflowId ID of the workflow
     *
     * @return  CMSObject|boolean  Object on success, false on failure.
     *
     * @since   4.0.0
     */
    protected function getWorkflow(int $workflowId = null)
    {
        $workflowId = !empty($workflowId) ? $workflowId : $this->app->input->getInt('workflow_id');

        if (is_array($workflowId)) {
            return false;
        }

        return $this->app->bootComponent('com_workflow')
            ->getMVCFactory()
            ->createModel('Workflow', 'Administrator', ['ignore_request' => true])
            ->getItem($workflowId);
    }

    /**
     * Check if the current plugin should execute workflow related activities
     *
     * @param   string $context Context to check
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function isSupported($context)
    {
        return false;
    }

    /**
     * Check if the context is listed in the allowed of forbidden lists and return the result.
     *
     * @param   string $context Context to check
     *
     * @return  boolean
     */
    protected function checkAllowedAndForbiddenlist($context)
    {
        $allowedlist = \array_filter((array) $this->params->get('allowedlist', []));
        $forbiddenlist = \array_filter((array) $this->params->get('forbiddenlist', []));

        if (!empty($allowedlist)) {
            foreach ($allowedlist as $allowed) {
                if ($context === $allowed) {
                    return true;
                }
            }

            return false;
        }

        foreach ($forbiddenlist as $forbidden) {
            if ($context === $forbidden) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the context supports a specific functionality.
     *
     * @param   string  $context       Context to check
     * @param   string  $functionality The functionality
     *
     * @return  boolean
     */
    protected function checkExtensionSupport($context, $functionality)
    {
        $parts = explode('.', $context);

        $component = $this->app->bootComponent($parts[0]);

        if (
            !$component instanceof WorkflowServiceInterface
            || !$component->isWorkflowActive($context)
            || !$component->supportFunctionality($functionality, $context)
        ) {
            return false;
        }

        return true;
    }
}
