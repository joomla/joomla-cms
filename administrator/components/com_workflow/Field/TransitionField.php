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

FormHelper::loadFieldClass('list');

/**
 * Components Category field.
 *
 * @since  1.6
 */
class TransitionField extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.7.0
	 */
	protected $type = 'Transition';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		// Let's get the id for the current item, either category or content item.
		$jinput = Factory::getApplication()->input;

		// Initialise variable.
		$db = Factory::getDbo();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('extension', 'com_content');
		$state = $this->element['state'] ? (int) $this->element['state'] : (int) $jinput->get('extension', 0);
		$query = $db->getQuery(true)
			->select($db->qn('id', 'value'))
			->select($db->qn('title', 'text'))
			->from($db->qn('#__workflow_transitions'))
			->where($db->qn('from_state_id') . '=' . $state);

		$items = $db->setQuery($query)->loadObjectList();

		if (count($items))
		{
			$user = Factory::getUser();
			array_filter(
				$items,
				function ($item) use ($user, $extension)
				{
					return $user->authorise('core.run', "$extension.transition.$item->value");
				}
			);

			// Sort by component name
			$items = ArrayHelper::sortObjects($items, 'value', 1, true, true);
		}

		// Get state title
		$query
			->clear()
			->select($db->qn('title', 'text'))
			->from($db->qn('#__workflow_states'))
			->where($db->qn('id') . '=' . $state);

		$state = $db->setQuery($query)->loadObject();

		$default = [
			\JHtml::_('select.option', '', $state->text),
			\JHtml::_('select.option', '-1', '--------', ['disable' => true])
		];

		// Merge with defaults
		$options = array_merge($default, $items);
		return $options;
	}
}
