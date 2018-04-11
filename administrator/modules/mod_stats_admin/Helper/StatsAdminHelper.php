<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\StatsAdmin\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Helper class for admin stats module
 *
 * @since  3.0
 */
class StatsAdminHelper
{
	/**
	 * Method to retrieve information about the site
	 *
	 * @param   JObject  &$params  Params object
	 *
	 * @return  array  Array containing site information
	 *
	 * @since   3.0
	 */
	public static function getStats(&$params)
	{
		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$rows  = array();
		$query = $db->getQuery(true);

		$serverinfo = $params->get('serverinfo');
		$siteinfo   = $params->get('siteinfo');

		$i = 0;

		if ($serverinfo)
		{
			$rows[$i]        = new \stdClass;
			$rows[$i]->title = Text::_('MOD_STATS_PHP');
			$rows[$i]->icon  = 'cogs';
			$rows[$i]->data  = phpversion();
			$i++;

			$rows[$i]        = new \stdClass;
			$rows[$i]->title = Text::_($db->name);
			$rows[$i]->icon  = 'database';
			$rows[$i]->data  = $db->getVersion();
			$i++;

			$rows[$i]        = new \stdClass;
			$rows[$i]->title = Text::_('MOD_STATS_CACHING');
			$rows[$i]->icon  = 'dashboard';
			$rows[$i]->data  = $app->get('caching') ? Text::_('JENABLED') : Text::_('JDISABLED');
			$i++;

			$rows[$i]        = new \stdClass;
			$rows[$i]->title = Text::_('MOD_STATS_GZIP');
			$rows[$i]->icon  = 'bolt';
			$rows[$i]->data  = $app->get('gzip') ? Text::_('JENABLED') : Text::_('JDISABLED');
			$i++;
		}

		if ($siteinfo)
		{
			$query->select('COUNT(id) AS count_users')
				->from('#__users');
			$db->setQuery($query);
			try
			{
				$users = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				$users = false;
			}

			$query->clear()
				->select('COUNT(id) AS count_items')
				->from('#__content')
				->where('state = 1');
			$db->setQuery($query);
			try
			{
				$items = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				$items = false;
			}

			if ($users)
			{
				$rows[$i]        = new \stdClass;
				$rows[$i]->title = Text::_('MOD_STATS_USERS');
				$rows[$i]->icon  = 'users';
				$rows[$i]->data  = $users;
				$i++;
			}

			if ($items)
			{
				$rows[$i]        = new \stdClass;
				$rows[$i]->title = Text::_('MOD_STATS_ARTICLES');
				$rows[$i]->icon  = 'file';
				$rows[$i]->data  = $items;
				$i++;
			}
		}

		// Include additional data defined by published system plugins
		PluginHelper::importPlugin('system');

		$arrays = (array) $app->triggerEvent('onGetStats', array('mod_stats_admin'));

		foreach ($arrays as $response)
		{
			foreach ($response as $row)
			{
				// We only add a row if the title and data are given
				if (isset($row['title']) && isset($row['data']))
				{
					$rows[$i]        = new \stdClass;
					$rows[$i]->title = $row['title'];
					$rows[$i]->icon  = $row['icon'] ?? 'info';
					$rows[$i]->data  = $row['data'];
					$i++;
				}
			}
		}

		return $rows;
	}
}
