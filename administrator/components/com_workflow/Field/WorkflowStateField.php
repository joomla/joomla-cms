<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('groupedlist');

/**
 * Workflow States field.
 *
 * @since  4.0
 */
class WorkflowStateField extends \JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   4.0.0
	 */
	protected $type = 'WorkflowState';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   4.0.0
	 * @throws  \UnexpectedValueException
	 */
	protected function getGroups()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Select distinct states for existing articles
		$query
			->select('DISTINCT ' . $db->qn('ws.id', 'workflow_state_id'))
			->select($db->qn('ws.title', 'workflow_state_title'))
			->select($db->qn('w.title', 'workflow_title'))
			->select($db->qn('w.id', 'workflow_id'))
			->from($db->qn('#__content', 'c'))
			->join('INNER', $db->qn('#__workflow_states', 'ws') . ' ON(' . $db->qn('c.state') . ' = ' . $db->qn('ws.id') . ')')
			->join('INNER', $db->qn('#__workflows', 'w') . ' ON(' . $db->qn('ws.workflow_id') . ' = ' . $db->qn('w.id') . ')')
			->order('workflow_id');

		$states = $db->setQuery($query)->loadObjectList();

		$workflowStates = array();

		// Grouping the states by workflow
		foreach ($states as $state)
		{
			if (!array_key_exists($state->workflow_title, $workflowStates))
			{
				$workflowStates[$state->workflow_title] = array();
			}

			$obj =(object) array(
				'value'     => $state->workflow_state_id,
				'text'      => $state->workflow_state_title
			);

			array_push($workflowStates[$state->workflow_title], $obj);
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getGroups(), $workflowStates);
	}
}
