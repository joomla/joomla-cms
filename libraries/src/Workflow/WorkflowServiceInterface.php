<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

\defined('JPATH_PLATFORM') or die;

/**
 * The workflow service.
 *
 * @since  4.0.0
 */
interface WorkflowServiceInterface
{
	/**
	 * Method to filter transitions by given id of state.
	 *
	 * @param   array  $transitions  Array of transitions to filter for
	 * @param   int    $pk           Id of the state on which the transitions are performed
	 *
	 * @return  array
	 *
	 * @since  4.0.0
	 */
	public function filterTransitions($transitions, $pk): array;

	/**
	 * Method to change state of multiple ids
	 *
	 * @param   array  $pks        Array of IDs
	 * @param   int    $condition  Condition of the workflow state
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public static function updateContentState($pks, $condition): bool;

	/**
	 * Returns an array of possible conditions for the component.
	 *
	 * @param   string  $extension  Full extension string
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getConditions($extension): array;

	/**
	 * Returns a table name for the state association
	 *
	 * @param   string  $section  An optional section to differ different areas in the component
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getWorkflowTableBySection(string $section = null) : string;
}
