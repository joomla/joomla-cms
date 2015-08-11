<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_status
 *
 * @since  3.5
 */
abstract class ModStatusHelper
{
	/**
	 * The count of logged users from the Database.
	 *
	 * @param   boolean  $admin  True if we want the backend user.
	 *
	 * @return  integer  The user count
	 *
	 * @since   3.5
	 */
	private function getOnlineCountFromDb($admin)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->clear()
			->select('COUNT(session_id)')
			->from('#__session');

		if ($admin == true)
		{
			$query->where('guest = 0 AND client_id = 1');
		}
		else
		{
			$query->where('guest = 0 AND client_id = 0');
		}

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * The count of logged users from the Redis Cache.
	 *
	 * @param   boolean  $admin  True if we want the backend user.
	 *
	 * @return  integer  The user count
	 *
	 * @since   3.5
	 */
	private function getOnlineCountFromRedis($admin)
	{
		// Get the number of frontend logged in users.
		$ds = JFactory::getDso();
		$result = array();

		try
		{
			// Get the number of users
			$result = $ds->smembers('utenti');
		}
		catch (RuntimeException $e)
		{
			// Don't worry be happy
			return count($result);
		}

		// Init the logged Users
		$logged_users = 0;

		// Get the datastore connection object and and get the value of each key.
		foreach ($result as $elm)
		{
			try
			{
				$data = $ds->get('user-' . $elm);
			}
			catch (RuntimeException $e)
			{
				$data = array();
			}

			// @todo: If the try fails $data is not defined here.
			$results[] = json_decode($data);

			foreach ($results as $k => $result)
			{
				if ((int) $results[$k]->client_id == $admin)
				{
					$logged_users++;
				}
			}
		}

		return $logged_users;
	}

	/**
	 * The count of logged users.
	 *
	 * @param   boolean  $admin  True if we want the backend user.
	 *
	 * @return  integer  The user count
	 *
	 * @since   3.5
	 */
	public static function getOnlineCount($admin)
	{
			$config  = JFactory::getConfig();
			$handler = $config->get('session_handler', 'none');
			$results = null;

			switch ($handler)
			{
				case 'database':
				case 'none':
					$results = self::getOnlineCountFromDb($admin);
					break;
				case 'redis':
					$results = self::getOnlineCountFromRedis($admin);
					break;
				default:
					break;
			}

			return $results;
	}
}
