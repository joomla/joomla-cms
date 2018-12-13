<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Logged\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

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
	 * @param   Registry           $params  The module parameters
	 * @param   CMSApplication     $app     The application
	 * @param   DatabaseInterface  $db      The database
	 *
	 * @return  mixed  An array of users, or false on error.
	 *
	 * @throws  \RuntimeException
	 */
	public static function getList(Registry $params, CMSApplication $app, DatabaseInterface $db)
	{
		$user  = $app->getIdentity();
		$query = $db->getQuery(true)
			->select($db->quoteName(['s.time','s.client_id', 'u.id', 'u.name', 'u.username']))
			->from($db->quoteName('#__session','s'))
			->join('LEFT', $db->quoteName('#__users' , 'u') . 'ON' . $db->quoteName('s.userid') . '=' .  $db->quoteName('u.id'))
			->where($db->quoteName('s.guest') . '=0')
			->setLimit($params->get('count', 5), 0);

		$db->setQuery($query);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			throw $e;
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
