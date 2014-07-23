<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_logged
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 * @since       1.5
 */
abstract class ModLoggedHelper
{
	/**
	 * Get a list of logged users.
	 *
	 * @param   JRegistry  &$params  The module parameters.
	 *
	 * @return  mixed  An array of users, or false on error.
	 *
	 * @throws  RuntimeException
	 */
	public static function getList(&$params)
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
	 * Get the alternate title for the module
	 *
	 * @param   JRegistry  $params  The module parameters.
	 *
	 * @return  string    The alternate title for the module.
	 */
	public static function getTitle($params)
	{
		return JText::plural('MOD_LOGGED_TITLE', $params->get('count'));
	}
}
