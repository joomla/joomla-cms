<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Workflow States field.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowStateField extends \JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'WorkflowState';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException
	 */
	protected function getGroups()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Select distinct states for existing articles
		$query
			->select('DISTINCT ' . $db->qn('ws.id', 'workflow_state_id'))
			->select($db->qn(['ws.title', 'w.title', 'w.id'], ['workflow_state_title', 'workflow_title', 'workflow_id']))
			->from($db->qn('#__workflow_associations', 'wa'))
			->innerJoin($db->qn('#__workflow_states', 'ws') . ' ON (' . $db->qn('wa.state_id') . ' = ' . $db->qn('ws.id') . ')')
			->innerJoin($db->qn('#__workflows', 'w') . ' ON (' . $db->qn('ws.workflow_id') . ' = ' . $db->qn('w.id') . ')')
			->order($db->qn('workflow_id'));

		$states = $db->setQuery($query)->loadObjectList();

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

		// Merge any additional options in the XML definition.
		return array_merge(parent::getGroups(), $workflowStates);
	}
}
