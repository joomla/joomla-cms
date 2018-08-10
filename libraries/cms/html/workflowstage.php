<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Utility class working with workflow states select lists
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlWorkflowStage
{

	/**
	 * Get a list of the available workflow stages.
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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select(
					$db->quoteName(
						[
							'ws.id',
							'ws.title',
							'w.id',
							'w.title'
						],
						[
							'workflow_stage_id',
							'workflow_state_title',
							'workflow_id',
							'workflow_title'
						]
					)
				)
			->from('#__workflow_stages AS ws')
			->leftJoin($db->quoteName('#__workflows', 'w') . ' ON w.id = ws.workflow_id')
			->order('ws.ordering');

		// Set the query and load the options.
		$states = $db->setQuery($query)->loadObjectList();

		$workflowStates = array();

		// Grouping the states by workflow
		foreach ($states as $state)
		{
			// Using workflow ID to differentiate workflows having same title
			$workflowStageKey = $state->workflow_title . ' (' . $state->workflow_id . ')';

			if (!array_key_exists($workflowStateKey, $workflowStages))
			{
				$workflowStages[$workflowStageKey] = array();
			}

			$workflowStages[$workflowStageKey][] = HTMLHelper::_('select.option', $state->workflow_stage_id, $state->workflow_stage_title);
		}

		$prefix[] = array(
			HTMLHelper::_('select.option', '', $options['title'])
		);

		return array_merge($prefix, $workflowStates);
	}
}
