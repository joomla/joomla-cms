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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\GroupedlistField;

/**
 * Workflow States field.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowStateField extends GroupedlistField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'WorkflowState';

	/**
	 * The extension where we're
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension = 'com_content';

	/**
	 * Show only the states which has an item attached
	 *
	 * @var     boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $activeonly = false;

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$success = parent::setup($element, $value, $group);

		if ($success)
		{
			if (strlen($element['extension']))
			{
				$this->extension =  (string) $element['extension'];
			}

			if ((string) $element['activeonly'] == '1' || (string) $element['activeonly'] == 'true')
			{
				$this->activeonly =  true;
			}
		}

		return $success;
	}

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
				->from($db->qn('#__workflow_states', 'ws'))
				->from($db->qn('#__workflows', 'w'))
				->where($db->qn('ws.workflow_id') . ' = ' . $db->qn('w.id'))
				->where($db->qn('w.extension') . ' = ' . $db->q($this->extension))
				->order($db->qn('w.ordering'));

		if ($this->activeonly)
		{
			$query
					->from($db->qn('#__workflow_associations', 'wa'))
					->where($db->qn('wa.state_id') . ' = ' . $db->qn('ws.id'))
					->where($db->qn('wa.extension') . ' = ' . $db->q($this->extension));

		}

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

			$workflowStates[$workflowStateKey][] = HTMLHelper::_('select.option', $state->workflow_state_id, $state->workflow_state_title);
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getGroups(), $workflowStates);
	}
}
