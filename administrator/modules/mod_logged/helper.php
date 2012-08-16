<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_logged
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 */
abstract class modLoggedHelper
{
	/**
	 * Get a list of logged users.
	 *
	 * @param	JObject	The module parameters.
	 * @return	mixed	An array of articles, or false on error.
	 */
	public static function getList($params)
	{
		// Initialise variables
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);

		$query->select('s.time, s.client_id, u.id, u.name, u.username');
		$query->from('#__session AS s');
		$query->leftJoin('#__users AS u ON s.userid = u.id');
		$query->where('s.guest = 0');
		$db->setQuery($query, 0, $params->get('count', 5));

		try
		{
			$results = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseError(500, $e->getMessage());
			return false;
		}

		foreach($results as $k => $result)
		{
			$results[$k]->logoutLink = '';

			if($user->authorise('core.manage', 'com_users'))
			{
				$results[$k]->editLink = JRoute::_('index.php?option=com_users&task=user.edit&id='.$result->id);
				$results[$k]->logoutLink = JRoute::_('index.php?option=com_login&task=logout&uid='.$result->id .'&'. JSession::getFormToken() .'=1');
			}
			if($params->get('name', 1) == 0) {
				$results[$k]->name = $results[$k]->username;
			}
		}

		return $results;
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param	JObject	The module parameters.
	 * @return	string	The alternate title for the module.
	 */
	public static function getTitle($params)
	{
		return JText::plural('MOD_LOGGED_TITLE', $params->get('count'));
	}
}
