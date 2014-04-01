<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	/**
	 * Get list of popular tags
	 *
	 * @param   JRegistry  &$params  module parameters
	 *
	 * @return mixed
	 */
	public static function getList(&$params)
	{
		$db				= JFactory::getDbo();
		$user     		= JFactory::getUser();
		$groups 		= implode(',', $user->getAuthorisedViewLevels());
		$timeframe		= $params->get('timeframe', 'alltime');
		$maximum		= $params->get('maximum', 5);
		$order_value	= $params->get('order_value', 'count');

		if ($order_value == 'rand()')
		{
			$order_direction	= '';
		}
		else
		{
			$order_value		= $db->quoteName($order_value);
			$order_direction	= $params->get('order_direction', 1) ? 'DESC' : 'ASC';
		}

		$query = $db->getQuery(true)
			->select(
				array(
					'MAX(' . $db->quoteName('tag_id') . ') AS tag_id',
					' COUNT(*) AS count', 'MAX(t.title) AS title',
					'MAX(' . $db->quoteName('t.access') . ') AS access',
					'MAX(' . $db->quoteName('t.alias') . ') AS alias'
				)
			)
			->group($db->quoteName(array('tag_id', 'title', 'access', 'alias')))
			->from($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('t.access') . ' IN (' . $groups . ')');

		// Only return published tags
		$query->where($db->quoteName('t.published') . ' = 1 ');

		// Optionally filter on language
		$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language != 'all')
		{
			if ($language == 'current_language')
			{
				$language = JHelperContent::getCurrentLanguage();
			}

			$query->where($db->quoteName('t.language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		if ($timeframe != 'alltime')
		{
			$now = new JDate;
			$query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($now->toSql('date'), '-1', strtoupper($timeframe)));
		}

		$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('tag_id') . ' = t.id')
			->order($order_value . ' ' . $order_direction);
		$db->setQuery($query, 0, $maximum);
		$results = $db->loadObjectList();

		return $results;
	}
}
