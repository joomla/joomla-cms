<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die();

JFormHelper::loadFieldClass('list');

/**
 * Search Filter field for the Finder package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class JFormFieldSearchFilter extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7
	 */
	protected $type = 'SearchFilter';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Build the query.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('f.' . $db->quoteName('title') . ' AS text, f.' . $db->quoteName('filter_id') . ' AS value');
		$query->from($db->quoteName('#__finder_filters') . ' AS f');
		$query->where('f.' . $db->quoteName('state') . ' = 1');
		$query->order('f.title ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		array_unshift($options, JHtml::_('select.option', '', JText::_('COM_FINDER_SELECT_SEARCH_FILTER'), 'value', 'text'));

		return $options;
	}
}
