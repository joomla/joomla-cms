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

		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->value = $options[$i]->id;
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->title;
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

		return $db->loadObjectList();
	}
}
