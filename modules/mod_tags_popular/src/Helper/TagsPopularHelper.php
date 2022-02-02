<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\TagsPopular\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\Database\ParameterType;

/**
 * Helper for mod_tags_popular
 *
 * @since  3.1
 */
abstract class TagsPopularHelper
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
		$db          = Factory::getDbo();
		$user        = Factory::getUser();
		$groups      = $user->getAuthorisedViewLevels();
		$timeframe   = $params->get('timeframe', 'alltime');
		$maximum     = (int) $params->get('maximum', 5);
		$order_value = $params->get('order_value', 'title');
		$nowDate     = Factory::getDate()->toSql();
		$nullDate    = $db->getNullDate();

		$query = $db->getQuery(true)
			->select(
				[
					'MAX(' . $db->quoteName('tag_id') . ') AS ' . $db->quoteName('tag_id'),
					'COUNT(*) AS ' . $db->quoteName('count'),
					'MAX(' . $db->quoteName('t.title') . ') AS ' . $db->quoteName('title'),
					'MAX(' . $db->quoteName('t.access') . ') AS ' . $db->quoteName('access'),
					'MAX(' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('alias'),
					'MAX(' . $db->quoteName('t.params') . ') AS ' . $db->quoteName('params'),
				]
			)
			->group($db->quoteName(['tag_id', 'title', 'access', 'alias']))
			->from($db->quoteName('#__contentitem_tag_map', 'm'))
			->whereIn($db->quoteName('t.access'), $groups);

		// Only return published tags
		$query->where($db->quoteName('t.published') . ' = 1 ');

		// Filter by Parent Tag
		$parentTags = $params->get('parentTag', []);

		if ($parentTags)
		{
			$query->whereIn($db->quoteName('t.parent_id'), $parentTags);
		}

		// Optionally filter on language
		$language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language !== 'all')
		{
			if ($language === 'current_language')
			{
				$language = ContentHelper::getCurrentLanguage();
			}

			$query->whereIn($db->quoteName('t.language'), [$language, '*'], ParameterType::STRING);
		}

		if ($timeframe !== 'alltime')
		{
			$query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($db->quote($nowDate), '-1', strtoupper($timeframe)));
		}

		$query->join('INNER', $db->quoteName('#__tags', 't'), $db->quoteName('tag_id') . ' = ' . $db->quoteName('t.id'))
			->join(
				'INNER', $db->quoteName('#__ucm_content', 'c'), $db->quoteName('m.core_content_id') . ' = ' . $db->quoteName('c.core_content_id')
			);

		$query->where($db->quoteName('m.type_alias') . ' = ' . $db->quoteName('c.core_type_alias'));

		// Only return tags connected to published and authorised items
		$query->where($db->quoteName('c.core_state') . ' = 1')
			->where(
				'(' . $db->quoteName('c.core_access') . ' IN (' . implode(',', $query->bindArray($groups)) . ')'
				. ' OR ' . $db->quoteName('c.core_access') . ' = 0)'
			)
			->where(
				'(' . $db->quoteName('c.core_publish_up') . ' IS NULL'
				. ' OR ' . $db->quoteName('c.core_publish_up') . ' = :nullDate2'
				. ' OR ' . $db->quoteName('c.core_publish_up') . ' <= :nowDate2)'
			)
			->where(
				'(' . $db->quoteName('c.core_publish_down') . ' IS NULL'
				. ' OR ' . $db->quoteName('c.core_publish_down') . ' = :nullDate3'
				. ' OR ' . $db->quoteName('c.core_publish_down') . ' >= :nowDate3)'
			)
			->bind([':nullDate2', ':nullDate3'], $nullDate)
			->bind([':nowDate2', ':nowDate3'], $nowDate);

		// Set query depending on order_value param
		if ($order_value === 'rand()')
		{
			$query->order($query->rand());
		}
		else
		{
			$order_direction = $params->get('order_direction', 1) ? 'DESC' : 'ASC';

			if ($params->get('order_value', 'title') === 'title')
			{
				// Backup bound parameters array of the original query
				$bounded = $query->getBounded();

				if ($maximum > 0)
				{
					$query->setLimit($maximum);
				}

				$query->order($db->quoteName('count') . ' DESC');
				$equery = $db->getQuery(true)
					->select(
						$db->quoteName(
							[
								'a.tag_id',
								'a.count',
								'a.title',
								'a.access',
								'a.alias',
							]
						)
					)
					->from('(' . (string) $query . ') AS ' . $db->quoteName('a'))
					->order($db->quoteName('a.title') . ' ' . $order_direction);

				$query = $equery;

				// Rebind parameters
				foreach ($bounded as $key => $obj)
				{
					$query->bind($key, $obj->value, $obj->dataType);
				}
			}
			else
			{
				$query->order($db->quoteName($order_value) . ' ' . $order_direction);
			}
		}

		if ($maximum > 0)
		{
			$query->setLimit($maximum);
		}

		$db->setQuery($query);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$results = array();
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $results;
	}
}
