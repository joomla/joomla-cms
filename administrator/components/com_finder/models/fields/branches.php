<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die();

JFormHelper::loadFieldClass('list');

/**
 * Search Filter field for the Finder package.
 *
 * @since  3.5
 */
class JFormFieldBranches extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $type = 'Branches';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.5
	 */
	public function getOptions()
	{
		// Build the query.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from($db->quoteName('#__finder_taxonomy'))
			->where('parent_id = 1')
			->order('title ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		array_unshift($options, JHtml::_('select.option', '', JText::_('COM_FINDER_MAPS_SELECT_BRANCHE'), 'value', 'text'));

		return $options;
	}
}
