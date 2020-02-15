<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Fields Groups
 *
 * @since  3.7.0
 */
class JFormFieldFieldgroups extends JFormFieldList
{
	public $type = 'Fieldgroups';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$context = (string) $this->element['context'];
		$states    = $this->element['state'] ?: '0,1';
		$states    = ArrayHelper::toInteger(explode(',', $states));

		$user       = JFactory::getUser();
		$viewlevels = ArrayHelper::toInteger($user->getAuthorisedViewLevels());

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title AS text, id AS value, state');
		$query->from('#__fields_groups');
		$query->where('state IN (' . implode(',', $states) . ')');
		$query->where('context = ' . $db->quote($context));
		$query->where('access IN (' . implode(',', $viewlevels) . ')');
		$query->order('ordering asc, id asc');

		$db->setQuery($query);
		$options = $db->loadObjectList();

		foreach ($options AS $option)
		{
			if ($option->state == 0)
			{
				$option->text = '[' . $option->text . ']';
			}

			if ($option->state == 2)
			{
				$option->text = '{' . $option->text . '}';
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
