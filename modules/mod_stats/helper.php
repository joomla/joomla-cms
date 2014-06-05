<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_stats
 *
 * @package     Joomla.Site
 * @subpackage  mod_stats
 * @since       1.5
 */
class ModStatsHelper
{
	/**
	 * Get list of stats
	 *
	 * @param   JRegistry  &$params  module parameters
	 *
	 * @return  array
	 */
	public static function &getList(&$params)
	{
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$rows	= array();
		$query	= $db->getQuery(true);

		$serverinfo = $params->get('serverinfo');
		$siteinfo	= $params->get('siteinfo');
		$counter	= $params->get('counter');
		$increase	= $params->get('increase');

		$i = 0;

		if ($serverinfo)
		{
			$rows[$i] = new stdClass;
			$rows[$i]->title	= JText::_('MOD_STATS_OS');
			$rows[$i]->data		= substr(php_uname(), 0, 7);
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title	= JText::_('MOD_STATS_PHP');
			$rows[$i]->data	= phpversion();
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title = JText::_($db->name);
			$rows[$i]->data	= $db->getVersion();
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title	= JTEXT::_('MOD_STATS_TIME');
			$rows[$i]->data	= JHtml::_('date', 'now', 'H:i');
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title	= JText::_('MOD_STATS_CACHING');
			$rows[$i]->data	= $app->get('caching') ? JText::_('JENABLED') : JText::_('JDISABLED');
			$i++;

			$rows[$i] = new stdClass;
			$rows[$i]->title	= JText::_('MOD_STATS_GZIP');
			$rows[$i]->data	= $app->get('gzip') ? JText::_('JENABLED') : JText::_('JDISABLED');
			$i++;
		}

		if ($siteinfo)
		{
			$query->select('COUNT(id) AS count_users')
				->from('#__users');
			$db->setQuery($query);
			$users = $db->loadResult();

			$query->clear()
				->select('COUNT(id) AS count_items')
				->from('#__content')
				->where('state = 1');
			$db->setQuery($query);
			$items = $db->loadResult();

			$query->clear()
				->select('COUNT(id) AS count_links ')
				->from('#__weblinks')
				->where('state = 1');
			$db->setQuery($query);
			$links = $db->loadResult();

			if ($users)
			{
				$rows[$i] = new stdClass;
				$rows[$i]->title	= JText::_('MOD_STATS_USERS');
				$rows[$i]->data	= $users;
				$i++;
			}

			if ($items)
			{
				$rows[$i] = new stdClass;
				$rows[$i]->title	= JText::_('MOD_STATS_ARTICLES');
				$rows[$i]->data	= $items;
				$i++;
			}

			if ($links)
			{
				$rows[$i] = new stdClass;
				$rows[$i]->title	= JText::_('MOD_STATS_WEBLINKS');
				$rows[$i]->data	= $links;
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
			$hits = $db->loadResult();

			if ($hits)
			{
				$rows[$i] = new stdClass;
				$rows[$i]->title	= JText::_('MOD_STATS_ARTICLES_VIEW_HITS');
				$rows[$i]->data	= $hits + $increase;
			}
		}

		return $rows;
	}
}
