<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('ordering');

/**
 * Supports an HTML select list of plugins
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.6
 */
class JFormFieldPluginordering extends JFormFieldOrdering
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Pluginordering';

	/**
	 * Builds the query for the ordering list.
	 *
	 * @return  JDatabaseQuery  The query for the ordering form field
	 */
	protected function getQuery()
	{
		$db = JFactory::getDbo();
		$folder	= $this->form->getValue('folder');

		// Build the query for the ordering list.
		$query = $db->getQuery(true)
			->select(array($db->quoteName('ordering', 'value'), $db->quoteName('name', 'text'), $db->quoteName('type'), $db->quote('folder'), $db->quote('extension_id')))
			->from($db->quoteName('#__extensions'))
			->where('(type =' . $db->quote('plugin') . 'AND folder=' . $db->quote($folder) . ')')
			->order('ordering');

		return $query;
	}

	/**
	 * Retrieves the current Item's Id.
	 *
	 * @return  integer  The current item ID
	 */
	protected function getItemId()
	{
		return (int) $this->form->getValue('extension_id');
	}
}
