<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_popular
 *
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 * @since       3.1
 */
abstract class ModTagsPopularHelper
{
	public static function getList($params)
	{
		$db        = JFactory::getDbo();
		$user      = JFactory::getUser();
		$groups    = implode(',', $user->getAuthorisedViewLevels());
		$timeframe = $params->get('timeframe', 'alltime');
		$maximum   = $params->get('maximum', 5);

		$query = $db->getQuery(true);

		$query->select(array($db->quoteName('tag_id'), $db->quoteName('type_alias'), $db->quoteName('content_item_id'), ' COUNT(*) AS count', 't.title', 't.access', 't.alias'));
		$query->group($db->quoteName('tag_id'));
		$query->from($db->quoteName('#__contentitem_tag_map'));
		$query->where('t.access IN (' . $groups . ')');

		if ($timeframe != 'alltime')
		{
			// This is just going to work in MySQL until we get date math in a library
			$query->where($db->quoteName('tag_date') . ' > ' . $query->currentTimestamp() . ' - INTERVAL 1 ' . strtoupper($timeframe));
		}

		// If current menu item is language specific only show selected tags
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$active = $menu->getActive();
		$params = new JRegistry;
		if ($active)
		{
			$params->loadString($active->params);
		}

		if ($active->language != '*') 
		{
			$query->where('t.language = ' . $db->quote($active->language) . ' OR ' . 't.language = ' . $db->quote('*'));
		}

		$query->join('LEFT', '#__tags AS t ON tag_id=t.id');
		$query->order('count DESC LIMIT 0,' . $maximum);
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}
}
