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

		$query->select(array($db->quoteName('tag_id'), $db->quoteName('type_alias'), $db->quoteName('content_item_id'), ' COUNT(*) AS count', 't.title', 't.access', 't.alias'))
			->group($db->quoteName(array('tag_id', 'type_alias', 'content_item_id', 't.title', 't.access', 't.alias')))
			->from($db->quoteName('#__contentitem_tag_map'))
			->where('t.access IN (' . $groups . ')');

		if ($timeframe != 'alltime')
		{
			$now = new JDate;
			$query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($now->toSql('date'), '-1', strtoupper($timeframe)));
		}

		$query->join('LEFT', '#__tags AS t ON tag_id=t.id')
			->order('count DESC');
		$db->setQuery($query, 0, $maximum);
		$results = $db->loadObjectList();

		return $results;
	}
}
