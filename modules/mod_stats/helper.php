<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_stats
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modStatsHelper
{
	function &getList(&$params)
	{
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$rows	= array();
		$query	= $db->getQuery(true);

		$serverinfo = $params->get('serverinfo');
		$siteinfo 	= $params->get('siteinfo');
		$counter 	= $params->get('counter');
		$increase 	= $params->get('increase');

		$i = 0;
		if ($serverinfo)
		{
			$rows[$i]->title	= JText::_('OS');
			$rows[$i]->data		= substr(php_uname(), 0, 7);
			$i++;
			$rows[$i]->title	= JText::_('PHP');
			$rows[$i]->data 	= phpversion();
			$i++;
			$rows[$i]->title 	= JText::_('MySQL');
			$rows[$i]->data 	= $db->getVersion();
			$i++;
			$rows[$i]->title 	= JText::_('Time');
			$rows[$i]->data 	= JHtml::_('date', 'now', '%H:%M');
			$i++;
			$rows[$i]->title 	= JText::_('Caching');
			$rows[$i]->data 	= $app->getCfg('caching') ? JText::_('Enabled'):JText::_('Disabled');
			$i++;
			$rows[$i]->title 	= JText::_('GZip');
			$rows[$i]->data 	= $app->getCfg('gzip') ? JText::_('Enabled'):JText::_('Disabled');
			$i++;
		}

		if ($siteinfo)
		{
            $query->select('COUNT(id) AS count_users');
            $query->from('#__users');
			$db->setQuery($query);
			$users = $db->loadResult();

            $query->clear();
            $query->select('COUNT(id) AS count_items');
            $query->from('#__content');
            $query->where('state = "1"');
			$db->setQuery($query);
			$items = $db->loadResult();

            $query->clear();
            $query->select('COUNT(id) AS count_links ');
            $query->from('#__weblinks');
            $query->where('state = "1"');
			$db->setQuery($query);
			$links = $db->loadResult();

			if ($users) {
				$rows[$i]->title 	= JText::_('Users');
				$rows[$i]->data 	= $users;
				$i++;
			}

			if ($items) {
				$rows[$i]->title 	= JText::_('Content');
				$rows[$i]->data 	= $items;
				$i++;
			}

			if ($links) {
				$rows[$i]->title 	= JText::_('WEB_LINKS');
				$rows[$i]->data 	= $links;
				$i++;
			}

		}

		if ($counter)
		{
            $query->clear();
            $query->select('SUM(hits) AS count_hits');
            $query->from('#__content');
            $query->where('state = "1"');
			$db->setQuery($query);
			$hits = $db->loadResult();

			if ($hits) {
				$rows[$i]->title 	= JText::_('Content View Hits');
				$rows[$i]->data 	= $hits + $increase;
				$i++;
			}
		}

		return $rows;
	}
}
