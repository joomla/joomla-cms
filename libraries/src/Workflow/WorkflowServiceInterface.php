<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

/**
 * The workflow service.
 *
 * @since  4.0.0
 */
interface WorkflowServiceInterface
{
    /**
     * Check if the functionality is supported by the context
     *
     * @param   string  $functionality  The functionality
     * @param   string  $context        The context of the functionality
     *
     * @return boolean
     *
     * @since  4.0.0
     */
    public function supportFunctionality($functionality, $context): bool;

    /**
     * Returns the model name, based on the context
     *
     * @param   string  $context  The context of the workflow
     *
     * @return boolean
     */
    public function getModelName($context): string;

    /**
     * Check if the workflow is active
     *
     * @param   string  $context  The context of the workflow
     *
     * @return boolean
     */
    public function isWorkflowActive($context): bool;

    /**
     * Method to filter transitions by given id of state.
     *
     * @param   integer[]  $transitions  Array of transitions to filter for
     * @param   integer    $pk           Id of the state on which the transitions are performed
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function filterTransitions(array $transitions, int $pk): array;

    /**
     * Returns an array of possible conditions for the component.
     *
     * @param   string  $extension  Full extension string
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getConditions(string $extension): array;

    /**
     * Returns a table name for the state association
     *
     * @param   string  $section  An optional section to differ different areas in the component
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getWorkflowTableBySection(?string $section = null): string;

    /**
     * Returns valid contexts.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getWorkflowContexts(): array;

    /**
     * Returns the workflow context based on the given category section
     *
     * @param   string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    public function getCategoryWorkflowContext(?string $section = null): string;
}
