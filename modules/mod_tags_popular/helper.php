<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_popular
 *
 * @since  3.1
 */
abstract class ModTagsPopularHelper
{
	/**
	 * Get list of popular tags
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public static function getList(&$params)
	{
		$db          = JFactory::getDbo();
		$user        = JFactory::getUser();
		$groups      = implode(',', $user->getAuthorisedViewLevels());
		$timeframe   = $params->get('timeframe', 'alltime');
		$maximum     = $params->get('maximum', 5);
		$order_value = $params->get('order_value', 'title');
		$nowDate     = JFactory::getDate()->toSql();
		$nullDate    = $db->quote($db->getNullDate());

		$query = $db->getQuery(true)
			->select(
				array(
					'MAX(' . $db->quoteName('tag_id') . ') AS tag_id',
					' COUNT(*) AS count', 'MAX(t.title) AS title',
					'MAX(' . $db->quoteName('t.access') . ') AS access',
					'MAX(' . $db->quoteName('t.alias') . ') AS alias',
					'MAX(' . $db->quoteName('t.params') . ') AS params',
				)
			)
			->group($db->quoteName(array('tag_id', 'title', 'access', 'alias')))
			->from($db->quoteName('#__contentitem_tag_map', 'm'))
			->where($db->quoteName('t.access') . ' IN (' . $groups . ')');

		// Only return published tags
		$query->where($db->quoteName('t.published') . ' = 1 ');

		// Filter by Parent Tag
		$parentTags = $params->get('parentTag', 0);

		if ($parentTags)
		{
			$query->where($db->quoteName('t.parent_id') . ' IN (' . implode(',', $parentTags) . ')');
		}

		// Optionally filter on language
		$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language !== 'all')
		{
			if ($language === 'current_language')
			{
				$language = JHelperContent::getCurrentLanguage();
			}

			$query->where($db->quoteName('t.language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		if ($timeframe !== 'alltime')
		{
			$query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($nowDate, '-1', strtoupper($timeframe)));
		}

		$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('tag_id') . ' = t.id')
		->join('INNER', $db->qn('#__ucm_content', 'c') . ' ON ' . $db->qn('m.core_content_id') . ' = ' . $db->qn('c.core_content_id'));

		$query->where($db->quoteName('m.type_alias') . ' = ' . $db->quoteName('c.core_type_alias'));

		// Only return tags connected to published articles
		$query->where($db->quoteName('c.core_state') . ' = 1')
			->where('(' . $db->quoteName('c.core_publish_up') . ' = ' . $nullDate
				. ' OR ' . $db->quoteName('c.core_publish_up') . ' <= ' . $db->quote($nowDate) . ')')
			->where('(' . $db->quoteName('c.core_publish_down') . ' = ' . $nullDate
				. ' OR  ' . $db->quoteName('c.core_publish_down') . ' >= ' . $db->quote($nowDate) . ')');

		// Set query depending on order_value param
		if ($order_value === 'rand()')
		{
			$query->order($query->Rand());
		}
		else
		{
			$order_value     = $db->quoteName($order_value);
			$order_direction = $params->get('order_direction', 1) ? 'DESC' : 'ASC';

			if ($params->get('order_value', 'title') === 'title')
			{
				$query->setLimit($maximum);
				$query->order('count DESC');
				$equery = $db->getQuery(true)
					->select(
						array(
							'a.tag_id',
							'a.count',
							'a.title',
							'a.access',
							'a.alias',
						)
					)
					->from('(' . (string) $query . ') AS a')
					->order('a.title' . ' ' . $order_direction);

				$query = $equery;
			}
			else
			{
				$query->order($order_value . ' ' . $order_direction);
			}
		}

		$db->setQuery($query, 0, $maximum);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$results = array();
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $results;
	}
}
