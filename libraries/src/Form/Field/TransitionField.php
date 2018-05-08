<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

FormHelper::loadFieldClass('list');

/**
 * Components Category field.
 *
 * @since  __DEPLOY_VERSION__
 */
class TransitionField extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Transition';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		// Let's get the id for the current item, either category or content item.
		$jinput = Factory::getApplication()->input;

		// Initialise variable.
		$db = Factory::getDbo();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('extension', 'com_content');
		$workflowState = $this->element['workflow_state'] ? (int) $this->element['workflow_state'] : (int) $jinput->getInt('extension', 0);

		$query = $db->getQuery(true)
			->select($db->qn(['t.id', 't.title', 's.condition'], ['value', 'text', 'condition']))
			->from($db->qn('#__workflow_transitions', 't'))
			->from($db->qn('#__workflow_states', 's'))
			->where($db->qn('t.from_state_id') . ' = ' . $workflowState)
			->where($db->qn('t.to_state_id') . ' = ' . $db->qn('s.id'))
			->where($db->qn('t.published') . '=1')
			->where($db->qn('s.published') . '=1')
			->order($db->qn('t.ordering'));

		$items = $db->setQuery($query)->loadObjectList();

		if (count($items))
		{
			$user = Factory::getUser();

			$items = array_filter(
				$items,
				function ($item) use ($user, $extension)
				{
					return $user->authorise('core.execute.transition', $extension . '.transition.' . $item->value);
				}
			);

			// Sort by transition name
			$items = ArrayHelper::sortObjects($items, 'value', 1, true, true);

			Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

			foreach ($items as $item)
			{
				$item->text .= ' [' . \JText::_(WorkflowHelper::getConditionName($item->condition)) . ']';
			}
		}

		// Get state title
		$query
			->clear()
			->select($db->qn('title'))
			->from($db->qn('#__workflow_states'))
			->where($db->qn('id') . '=' . $workflowState);

		$workflowName = $db->setQuery($query)->loadResult();

		$default = [\JHtml::_('select.option', '', $workflowName)];

		$options = array_merge(parent::getOptions(), $items);

		if (count($options))
		{
			$default[] = \JHtml::_('select.option', '-1', '--------', ['disable' => true]);
		}

		// Merge with defaults
		return array_merge($default, $options);
	}
}
