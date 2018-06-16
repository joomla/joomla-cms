<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper class for admin privacy dashboard module
 *
 * @since  3.9
 */
class ModPrivacyDashboardHelper
{
	/**
	 * Method to retrieve information about the site privacy requests
	 *
	 * @param   JObject  &$params  Params object
	 *
	 * @return  array  Array containing site privacy requests
	 *
	 * @since   3.9
	 */
	public static function getData(&$params)
	{
		$db    = JFactory::getDbo();
		$rows  = array();
		$query = $db->getQuery(true);

		$query->select('COUNT(*) AS count, ' . $db->quoteName('status') . ', ' . $db->quoteName('request_type'))
				->from($db->quoteName('#__privacy_requests'))
				->group($db->quoteName('status') . ', ' . $db->quoteName('request_type'));
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$rows = false;
		}

		return $rows;

	}
}
