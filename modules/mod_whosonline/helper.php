<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_whosonline
 *
 * @since  1.5
 */
class ModWhosonlineHelper
{
	/**
	 * Show online count
	 *
	 * @return  array  The number of Users and Guests online.
	 *
	 * @since   1.5
	 **/
	public static function getOnlineCount()
	{
		$cfg     = JFactory::getConfig();
		$handler = $cfg->get('session_handler', 'none');
		$results = null;

		switch ($handler)
		{
			case 'database':
			case 'none':
				$results = self::getOnlineCountFromDb();
				break;
			case 'redis':
				$results = self::getOnlineCountFromRedis();
				break;
			default:
				break;
		}

		return $results;
	}
	/**
	 * Show online user names
	 *
	 * @param   mixed  $params  The parameters
	 *
	 * @return  array  The name of Users and Guests online.
	 *
	 * @since   1.5
	 **/
	public static function getOnlineUserNames($params)
	{
		$cfg     = JFactory::getConfig();
		$handler = $cfg->get('session_handler', 'none');
		$results = null;

		switch ($handler)
		{
			case 'database':
			case 'none':
				$results = self::getOnlineUserNamesFromDb($params);
				break;
			case 'redis':
				$results = self::getOnlineUserNamesFromRedis($params);
				break;
			default:
				break;
		}

		return $results;
	}

	/**
	 * Get online count from Redis
	 *
	 * @return  array  The number of Users and Guests online.
	 *
	 * @since   3.5
	 **/
	private function getOnlineCountFromRedis()
	{
		$ds = JFactory::getDso();

		// Calculate number of guests and users
		$result      = array();
		$user_array  = 0;
		$guest_array = 0;

		try
		{
			$sessions     = $ds->keys('sess-*');
			$logged_users = $ds->smembers('utenti');
		}
		catch (RuntimeException $e)
		{
			// Don't worry be happy
			$sessions = array();
		}

		if ($sessions === false)
		{
			$sessions = array();
		}

		$result['user']  = count($logged_users);
		$result['guest'] = ((count($sessions) - count($logged_users)) > 0) ? (count($sessions) - count($logged_users)) : 0;

		return $result;
	}

	/**
	 * Get online count from the Database
	 *
	 * @return  array  The number of Users and Guests online.
	 *
	 * @since   3.5
	 **/
	private function getOnlineCountFromDb()
	{
		$db = JFactory::getDbo();

		// Calculate number of guests and users
		$result      = array();
		$user_array  = 0;
		$guest_array = 0;

		$query = $db->getQuery(true)
			->select('guest, client_id')
			->from('#__session')
			->where('client_id = 0');
		$db->setQuery($query);

		try
		{
			$sessions = (array) $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			// Don't worry be happy
			$sessions = array();
		}

		if (count($sessions))
		{
			foreach ($sessions as $session)
			{
				// If guest increase guest count by 1
				if ($session->guest == 1)
				{
					$guest_array ++;
				}

				// If member increase member count by 1
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

	/**
	 * Get online member names form Redis
	 *
	 * @param   mixed  $params  The parameters
	 *
	 * @return  array  The names of the online users.
	 *
	 * @since   3.5
	 **/
	private function getOnlineUserNamesFromRedis($params)
	{
		$ds = JFactory::getDso();

		try
		{
			return $ds->smembers('utenti');
		}
		catch (RuntimeException $e)
		{
			return array();
		}
	}

	/**
	 * Get online member names form the Database
	 *
	 * @param   mixed  $params  The parameters
	 *
	 * @return  array  The names of the online users.
	 *
	 * @since   3.5
	 **/
	private function getOnlineUserNamesFromDb($params)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('a.username', 'a.userid', 'a.client_id')))
			->from('#__session AS a')
			->where($db->quoteName('a.userid') . ' != 0')
			->where($db->quoteName('a.client_id') . ' = 0')
			->group($db->quoteName(array('a.username', 'a.userid', 'a.client_id')));

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

		try
		{
			return (array) $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return array();
		}
	}

	/**
	 * Purge the List
	 *
	 * @return  bolean  True on success
	 *
	 * @since   3.5
	 **/
	private function purgelist()
	{
		$ds = JFactory::getDso();

		try
		{
			$lista = $ds->smembers('utenti');
		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

			return false;
		}

		// Get the datastore connection object and verify its connected.
		foreach ($lista as $elm)
		{
			try
			{
				$exist = $ds->ttl($elm);
			}
			catch (Exception $e)
			{
				throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

				return false;
			}

			if ($exist == -1)
			{
				$ds->srem('utenti', $elm);
			}
		}
	}
}
