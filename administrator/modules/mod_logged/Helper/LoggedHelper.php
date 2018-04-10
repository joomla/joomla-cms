<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Module\Logged\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * Helper for mod_logged
 *
 * @since  1.5
 */
abstract class LoggedHelper
{
	/**
	 * Get a list of logged users.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module parameters.
	 *
	 * @return  mixed  An array of users, or false on error.
	 *
	 * @throws  \RuntimeException
	 */
	public static function getList(&$params)
	{
		$db    = Factory::getDbo();
		$user  = Factory::getUser();
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
		catch (\RuntimeException $e)
		{
			throw $e;
		}

		foreach ($results as $k => $result)
		{
			$results[$k]->logoutLink = '';

			if ($user->authorise('core.manage', 'com_users'))
			{
				$results[$k]->editLink   = Route::_('index.php?option=com_users&task=user.edit&id=' . $result->id);
				$results[$k]->logoutLink = Route::_('index.php?option=com_login&task=logout&uid=' . $result->id . '&' . Session::getFormToken() . '=1');
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
	 * @param   \Joomla\Registry\Registry  $params  The module parameters.
	 *
	 * @return  string    The alternate title for the module.
	 */
	public static function getTitle($params)
	{
		return Text::plural('MOD_LOGGED_TITLE', $params->get('count', 5));
	}
}
