<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

defined('JPATH_PLATFORM') or die;

/**
 * The workflow service.
 *
 * @since  __DEPLOY_VERSION__
 */
interface WorkflowServiceInterface
{
	/**
	 * Method to filter transitions by given id of state.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function updateContentState($pks, $condition): bool;
}
