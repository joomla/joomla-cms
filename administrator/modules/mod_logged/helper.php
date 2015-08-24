<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_logged
 *
 * @since  1.5
 */
abstract class ModLoggedHelper
{
	/**
	 * Get a list of logged users.
	 *
	 * @param   \Joomla\Registry\Registry  $params  The module parameters.
	 *
	 * @return  mixed  An array of users, or false on error.
	 *
	 * @throws  RuntimeException
	 */
	public static function getList($params)
	{
		$config  = JFactory::getConfig();
		$handler = $config->get('session_handler', 'none');
		$results = null;

		switch ($handler)
		{
			case 'database':
			case 'none':
				$results = self::getListFromDb($params);
				break;
			case 'redis':
				$results = self::getListFromRedis($params);
				break;
			default:
				break;
		}

		return $results;
	}

	/**
	 * Get a list of logged users from the Database.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module parameters.
	 *
	 * @return  mixed  An array of users, or false on error.
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	private function getListFromDb(&$params)
	{
		$db    = JFactory::getDbo();
		$user  = JFactory::getUser();
		$query = $db->getQuery(true)
			->select('s.time, s.client_id, u.id, u.name, u.username')
			->from('#__session AS s')
			->join('LEFT', '#__users AS u ON s.userid = u.id')
			->where('s.guest = 0');
		$db->setQuery($query, 0, $params->get('count', 5));

		try
		{
			$results = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw $e;
		}

		foreach ($results as $k => $result)
		{
			$results[$k]->logoutLink = '';

			if ($user->authorise('core.manage', 'com_users'))
			{
				$results[$k]->editLink   = JRoute::_('index.php?option=com_users&task=user.edit&id=' . $result->id);
				$results[$k]->logoutLink = JRoute::_('index.php?option=com_login&task=logout&uid=' . $result->id . '&' . JSession::getFormToken() . '=1');
			}

			if ($params->get('name', 1) == 0)
			{
				$results[$k]->name = $results[$k]->username;
			}
		}

		return $results;
	}

	/**
	 * Get a list of logged users from the Redis Cache.
	 *
	 * @param   \Joomla\Registry\Registry  $params  The module parameters.
	 *
	 * @return  mixed  An array of users, or false on error.
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	private function getListFromRedis($params)
	{
		$ds      = JFactory::getRedis();
		$user    = JFactory::getUser();
		$results = array();

		try
		{
			$lista = $ds->smembers('utenti');
		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

			return false;
		}

		// Get the database connection object and verify its connected.
		foreach ($lista as $elm)
		{
			try
			{
				$exist = $ds->get('user-' . $elm);
			}
			catch (Exception $e)
			{
				throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

				return false;
			}

			$data      = json_decode($exist);
			$results[] = $data;

			foreach ($results as $k => $result)
			{
				$results[$k]->logoutLink = '';
				$results[$k]->name       = '';
				$results[$k]->id   		 = $result->userid;

				if ($user->authorise('core.manage', 'com_users'))
				{
					$results[$k]->editLink   = JRoute::_('index.php?option=com_users&task=user.edit&id=' . $result->userid);
					$results[$k]->logoutLink = JRoute::_('index.php?option=com_login&task=logout&uid=' . $result->userid . '&' . JSession::getFormToken() . '=1');
				}

				if ($params->get('name', 1) == 0)
				{
					$results[$k]->name = $results[$k]->username;
				}
			}
		}

		return $results;
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param   \Joomla\Registry\Registry  $params  The module parameters.
	 *
	 * @return  string    The alternate title for the module.
	 */
	public static function getTitle($params)
	{
		return JText::plural('MOD_LOGGED_TITLE', $params->get('count'));
	}
}
