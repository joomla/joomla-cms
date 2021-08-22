<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_stats
 *
 * @since  1.5
 */
class ModStatsHelper
{
	/**
	 * Get list of stats
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array
	 */
	public static function &getList(&$params)
	{
		$app        = JFactory::getApplication();
		$db         = JFactory::getDbo();
		$rows       = array();
		$query      = $db->getQuery(true);
		$serverinfo = $params->get('serverinfo', 0);
		$siteinfo   = $params->get('siteinfo', 0);
		$counter    = $params->get('counter', 0);
		$increase   = $params->get('increase', 0);

		$i = 0;

		if ($serverinfo)
		{
			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_OS');
			$rows[$i]->data  = substr(php_uname(), 0, 7);
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_PHP');
			$rows[$i]->data  = phpversion();
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_($db->name);
			$rows[$i]->data  = $db->getVersion();
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_TIME');
			$rows[$i]->data  = JHtml::_('date', 'now', 'H:i');
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_CACHING');
			$rows[$i]->data  = $app->get('caching') ? JText::_('JENABLED') : JText::_('JDISABLED');
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_('MOD_STATS_GZIP');
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
				$rows[$i] = new stdClass;
				$rows[$i]->title = JText::_('MOD_STATS_USERS');
				$rows[$i]->data  = $users;
				$i++;
			}

			if ($items)
			{
				$rows[$i] = new stdClass;
				$rows[$i]->title = JText::_('MOD_STATS_ARTICLES');
				$rows[$i]->data  = $items;
				$i++;
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
				$rows[$i] = new stdClass;
				$rows[$i]->title = JText::_('MOD_STATS_ARTICLES_VIEW_HITS');
				$rows[$i]->data  = $hits + $increase;
				$i++;
			}
		}

		// Include additional data defined by published system plugins
		JPluginHelper::importPlugin('system');

		$arrays = (array) $app->triggerEvent('onGetStats', array('mod_stats'));

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
