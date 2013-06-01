<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_whosonline
 *
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 * @since       1.5
 */
class ModWhosonlineHelper
{
	// show online count
	public static function getOnlineCount()
	{
		$db		= JFactory::getDbo();
		// calculate number of guests and users
		$result	= array();
		$user_array  = 0;
		$guest_array = 0;
		$query	= $db->getQuery(true)
			->select('guest, client_id')
			->from('#__session')
			->where('client_id = 0');
		$db->setQuery($query);
		$sessions = (array) $db->loadObjectList();

		if (count($sessions))
		{
			foreach ($sessions as $session)
			{
				// if guest increase guest count by 1
				if ($session->guest == 1)
				{
					$guest_array ++;
				}
				// if member increase member count by 1
				if ($session->guest == 0)
				{
					$user_array ++;
				}
			}
		}

		$result['user']  = $user_array;
		$result['guest'] = $guest_array;

		return $result;
	}

	// show online member names
	public static function getOnlineUserNames($params)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select($db->quoteName(array('a.username', 'a.time', 'a.userid', 'a.client_id')))
			->from('#__session AS a')
			->where($db->quoteName('a.userid') . ' != 0')
			->where($db->quoteName('a.client_id') . ' = 0')
			->group($db->quoteName(array('a.username', 'a.time', 'a.userid', 'a.client_id')));
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin') && $params->get('filter_groups', 0) == 1)
		{
			$groups = $user->getAuthorisedGroups();
			if (empty($groups))
			{
				return array();
			}
			$query->join('LEFT', '#__user_usergroup_map AS m ON m.user_id = a.userid')
				->join('LEFT', '#__usergroups AS ug ON ug.id = m.group_id')
				->where('ug.id in (' . implode(',', $groups) . ')')
				->where('ug.id <> 1');
		}
		$db->setQuery($query);
		return (array) $db->loadObjectList();
	}
}
