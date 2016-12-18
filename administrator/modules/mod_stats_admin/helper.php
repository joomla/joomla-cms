<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper class for admin stats module
 *
 * @since  3.0
 */
class ModStatsHelper
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
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$rows  = array();
		$query = $db->getQuery(true);

		$serverinfo = $params->get('serverinfo');
		$siteinfo   = $params->get('siteinfo');
		$counter    = $params->get('counter');
		$increase   = $params->get('increase');

		$i = 0;

		if ($serverinfo)
		{
			$rows[$i]        = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_OS');
			$rows[$i]->icon  = 'screen';
			$rows[$i]->data  = substr(php_uname(), 0, 7);
			$i++;

			$rows[$i]        = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_PHP');
			$rows[$i]->icon  = 'cogs';
			$rows[$i]->data  = phpversion();
			$i++;

			$rows[$i]        = new stdClass;
			$rows[$i]->title = JText::_($db->name);
			$rows[$i]->icon  = 'database';
			$rows[$i]->data  = $db->getVersion();
			$i++;

			$rows[$i]        = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_TIME');
			$rows[$i]->icon  = 'clock';
			$rows[$i]->data  = JHtml::_('date', 'now', 'H:i');
			$i++;

			$rows[$i]        = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_CACHING');
			$rows[$i]->icon  = 'dashboard';
			$rows[$i]->data  = $app->get('caching') ? JText::_('JENABLED') : JText::_('JDISABLED');
			$i++;

			$rows[$i]        = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_GZIP');
			$rows[$i]->icon  = 'lightning';
			$rows[$i]->data  = $app->get('gzip') ? JText::_('JENABLED') : JText::_('JDISABLED');
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
			catch (RuntimeException $e)
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
			catch (RuntimeException $e)
			{
				$items = false;
			}

			if ($users)
			{
				$rows[$i]        = new stdClass;
				$rows[$i]->title = JText::_('MOD_STATS_USERS');
				$rows[$i]->icon  = 'users';
				$rows[$i]->data  = $users;
				$i++;
			}

			if ($items)
			{
				$rows[$i]        = new stdClass;
				$rows[$i]->title = JText::_('MOD_STATS_ARTICLES');
				$rows[$i]->icon  = 'file';
				$rows[$i]->data  = $items;
				$i++;
			}

			if (JComponentHelper::isInstalled('com_weblinks'))
			{
				$query->clear()
					->select('COUNT(id) AS count_links')
					->from('#__weblinks')
					->where('state = 1');
				$db->setQuery($query);

				try
				{
					$links = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					$links = false;
				}

				if ($links)
				{
					$rows[$i]        = new stdClass;
					$rows[$i]->title = JText::_('MOD_STATS_WEBLINKS');
					$rows[$i]->icon  = 'out-2';
					$rows[$i]->data  = $links;
					$i++;
				}
			}
		}

		if ($counter)
		{
			$query->clear()
				->select('SUM(hits) AS count_hits')
				->from('#__content')
				->where('state = 1');
			$db->setQuery($query);

			try
			{
				$hits = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$hits = false;
			}

			if ($hits)
			{
				$rows[$i]        = new stdClass;
				$rows[$i]->title = JText::_('MOD_STATS_ARTICLES_VIEW_HITS');
				$rows[$i]->icon  = 'eye';
				$rows[$i]->data  = number_format($hits + $increase, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'));
				$i++;
			}
		}

		// Include additional data defined by published system plugins
		JPluginHelper::importPlugin('system');

		$app    = JFactory::getApplication();
		$arrays = (array) $app->triggerEvent('onGetStats', array('mod_stats_admin'));

		foreach ($arrays as $response)
		{
			foreach ($response as $row)
			{
				// We only add a row if the title and data are given
				if (isset($row['title']) && isset($row['data']))
				{
					$rows[$i]        = new stdClass;
					$rows[$i]->title = $row['title'];
					$rows[$i]->icon  = isset($row['icon']) ? $row['icon'] : 'info';
					$rows[$i]->data  = $row['data'];
					$i++;
				}
			}
		}

		return $rows;
	}
}
