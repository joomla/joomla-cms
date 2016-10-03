<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class working with users
 *
 * @since  2.5
 */
abstract class JHtmlUser
{
	/**
	 * Displays a list of user groups.
	 *
	 * @param   boolean  $includeSuperAdmin  true to include super admin groups, false to exclude them
	 *
	 * @return  array  An array containing a list of user groups.
	 *
	 * @since   2.5
	 */
	public static function groups($includeSuperAdmin = false)
	{
		$options = array_values(JHelperUsergroups::getInstance()->getAll());

		foreach ($options as &$option)
		{
			$option->value = $options->id;
			$option->text = str_repeat('- ', $option->level) . $option->title;
			$groups[] = JHtml::_('select.option', $option->value, $option->text);
		}

		// Exclude super admin groups if requested
		if (!$includeSuperAdmin)
		{
			$filteredGroups = array();

			foreach ($groups as $group)
			{
				if (!JAccess::checkGroup($group->value, 'core.admin'))
				{
					$filteredGroups[] = $group;
				}
			}

			$groups = $filteredGroups;
		}

		return $groups;
	}

	/**
	 * Get a list of users.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function userlist()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value, a.name AS text')
			->from('#__users AS a')
			->where('a.block = 0')
			->order('a.name');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		return $items;
	}
}
