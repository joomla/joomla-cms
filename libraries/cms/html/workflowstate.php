<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class working with workflow states select lists
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlWorkflowState
{

	/**
	 * Get a list of the available workflow states.
	 *
	 * @param   array  $options  An array of options for the control
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function existing($options)
	{
		// Get the database object and a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('ws.id AS workflow_state_id, ws.title AS workflow_state_title, w.title AS workflow_title, w.id AS workflow_id')
			->from('#__workflow_states AS ws')
			->leftJoin($db->quoteName('#__workflows', 'w') . ' ON w.id = ws.workflow_id')
			->order('ws.ordering');

		// Set the query and load the options.
		$db->setQuery($query);
		$states = $db->loadObjectList();

		$workflowStates = array();

		// Grouping the states by workflow
		foreach ($states as $state)
		{
			// Using workflow ID to differentiate workflows having same title
			$workflowStateKey = $state->workflow_title . ' (' . $state->workflow_id . ')';

			if (!array_key_exists($workflowStateKey, $workflowStates))
			{
				$workflowStates[$workflowStateKey] = array();
			}

			$workflowStates[$workflowStateKey][] = \JHtml::_('select.option', $state->workflow_state_id, $state->workflow_state_title);
		}

		$prefix[] = array(
			\JHtml::_('select.option', null, $options['title'])
		);

		return array_merge($prefix, $workflowStates);
	}
}
