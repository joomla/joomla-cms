<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Fields Forms
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldFieldgroupforms extends JFormFieldList
{
	public $type = 'Fieldgroupforms';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$context = (string) $this->element['context'];
		$states  = $this->element['state'] ?: '0,1';
		$states  = ArrayHelper::toInteger(explode(',', $states));

		$user       = JFactory::getUser();
		$viewLevels = ArrayHelper::toInteger($user->getAuthorisedViewLevels());

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title AS text, id AS value, state');
		$query->from('#__fields_forms');
		$query->where('state IN (' . implode(',', $states) . ')');
		$query->where('context = ' . $db->quote($context));
		$query->where('is_subform = ' . $db->quote(0));
		$query->where('access IN (' . implode(',', $viewLevels) . ')');

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
