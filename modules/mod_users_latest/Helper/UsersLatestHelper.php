<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\UsersLatest\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Helper for mod_users_latest
 *
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 *
 * @since       1.6
 */
class UsersLatestHelper
{
	/**
	 * Get users sorted by activation date
	 *
	 * @param   \Joomla\Registry\Registry  $params  module parameters
	 *
	 * @return  array  The array of users
	 *
	 * @since   1.6
	 */
	public static function getUsers($params)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('a.id', 'a.name', 'a.username', 'a.registerDate')))
			->order($db->quoteName('a.registerDate') . ' DESC')
			->from('#__users AS a');
		$user = Factory::getUser();

		if (!$user->authorise('core.admin') && $params->get('filter_groups', 0) == 1)
		{
			$groups = $user->getAuthorisedGroups();

			if (empty($groups))
			{
				return array();
			}

			$query->join('LEFT', '#__user_usergroup_map AS m ON m.user_id = a.id')
				->join('LEFT', '#__usergroups AS ug ON ug.id = m.group_id')
				->where('ug.id in (' . implode(',', $groups) . ')')
				->where('ug.id <> 1');
		}

		$db->setQuery($query, 0, $params->get('shownumber'));

		try
		{
			return (array) $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return array();
		}
	}
}
