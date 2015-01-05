<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level')
			->from($db->quoteName('#__usergroups') . ' AS a')
			->join('LEFT', $db->quoteName('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id, a.title, a.lft, a.rgt')
			->order('a.lft ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
			$groups[] = JHtml::_('select.option', $options[$i]->value, $options[$i]->text);
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
