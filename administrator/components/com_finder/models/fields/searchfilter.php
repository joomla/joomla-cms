<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die();

JFormHelper::loadFieldClass('list');

/**
 * Search Filter field for the Finder package.
 *
 * @since  2.5
 */
class JFormFieldSearchFilter extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type = 'SearchFilter';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.5
	 */
	public function getOptions()
	{
		// Build the query.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('f.title AS text, f.filter_id AS value')
			->from($db->quoteName('#__finder_filters') . ' AS f')
			->where('f.state = 1')
			->order('f.title ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		array_unshift($options, JHtml::_('select.option', '', JText::_('COM_FINDER_SELECT_SEARCH_FILTER'), 'value', 'text'));

		return $options;
	}
}
