<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\PrivacyDashboard\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Helper class for admin privacy dashboard module
 *
 * @since  3.9.0
 */
class PrivacyDashboardHelper
{
	/**
	 * Method to retrieve information about the site privacy requests
	 *
	 * @return  array  Array containing site privacy requests
	 *
	 * @since   3.9.0
	 */
	public static function getData()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					'COUNT(*) AS count',
					$db->quoteName('status'),
					$db->quoteName('request_type'),
				]
			)
			->from($db->quoteName('#__privacy_requests'))
			->group($db->quoteName('status'))
			->group($db->quoteName('request_type'));

		$db->setQuery($query);

		try
		{
			return $db->loadObjectList();
		}
		catch (ExecutionFailureException $e)
		{
			return [];
		}
	}
}
