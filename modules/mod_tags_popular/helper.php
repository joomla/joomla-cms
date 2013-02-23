<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
abstract class modTagsPopularHelper
{
	public static function getList($params)
	{
		$db			= JFactory::getDbo();
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$groups		= implode(',', $user->getAuthorisedViewLevels());
		$date		= JFactory::getDate();
		$timeframe = $params->get('timeframe', 'alltime');
		$maximum = $params->get('maximum', 5);

		$query		= $db->getQuery(true);

			$query->select(array($db->quoteName('tag_id'), $db->quoteName('type_alias'), $db->quoteName('content_item_id'), ' COUNT(*) AS count', 't.title', 't.access', 't.alias'));
			$query->group($db->quoteName('tag_id'));
			$query->from($db->quoteName('#__contentitem_tag_map'));
			$query->where('t.access IN (' . $groups . ')');
			if ($timeframe != 'alltime' )
			{
				// This is just going to work in MySQL until we get date math in a library
				if ($timeframe = 'hour')
				{
					$query->where( tag_date . ' > ' . $query->currentTimestamp() . ' - INTERVAL 1 HOUR ');
				}
				elseif ($timeframe = 'day')
				{
					$query->where( tag_date . ' > ' . $query->currentTimestamp() . '  - INTERVAL 1 DAY');
				}
				elseif ($timeframe = 'month')
				{
					$query->where( $db->quoteName('tag_date') . ' > ' . $query->currentTimestamp() . ' - INTERVAL 1 MONTH');
				}
				elseif ($timeframe = 'year')
				{
					$query->where( $db->quoteName('tag_date') . ' > ' . $query->currentTimestamp(). '  - INTERVAL 1 YEAR');
				}
			}

			$query->join('LEFT','#__tags AS t ON tag_id=t.id');
			$query->order('count DESC LIMIT 0,' . $maximum);
			$db->setQuery($query);
			$results = $db->loadObjectList();


		return $results;
	}
}
