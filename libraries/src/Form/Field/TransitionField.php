<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Form\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Workflow\Workflow;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

/**
 * Components Category field.
 *
 * @since  __DEPLOY_VERSION__
 */
class TransitionField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Transition';

	protected $extension;

	/**
	 * Method to setup the extension
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result)
		{
			$this->extension = $element['extension'] ?? 'com_content';
		}

		return $result;
	}

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
			->select($db->quoteName(['t.id', 't.title', 's.condition'], ['value', 'text', 'condition']))
			->from($db->quoteName('#__workflow_transitions', 't'))
			->from($db->quoteName('#__workflow_states', 's'))
			->where($db->quoteName('t.from_state_id') . ' = ' . $workflowState)
			->where($db->quoteName('t.to_state_id') . ' = ' . $db->quoteName('s.id'))
			->where($db->quoteName('t.published') . '=1')
			->where($db->quoteName('s.published') . '=1')
			->order($db->quoteName('t.ordering'));

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

			$workflow = new Workflow(['extension' => $this->extension]);

			foreach ($items as $item)
			{
				$conditionName = $workflow->getConditionName($item->condition);

				$item->text .= ' [' . \JText::_($conditionName) . ']';
			}
		}

		// Get state title
		$query
			->clear()
			->select($db->quoteName('title'))
			->from($db->quoteName('#__workflow_states'))
			->where($db->quoteName('id') . '=' . $workflowState);

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
