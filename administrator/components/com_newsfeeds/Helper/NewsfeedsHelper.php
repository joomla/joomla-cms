<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

/**
 * Newsfeeds component helper.
 *
 * @since  1.6
 */
class NewsfeedsHelper extends ContentHelper
{
	/**
	 * Name of the extension
	 *
	 * @var    string
	 */
	public static $extension = 'com_newsfeeds';

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  &$items  The banner category objects
	 *
	 * @return  \stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$db = Factory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published AS state, count(*) AS count')
				->from($db->quoteName('#__newsfeeds'))
				->where('catid = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);
			$newfeeds = $db->loadObjectList();

			foreach ($newfeeds as $newsfeed)
			{
				if ($newsfeed->state == 1)
				{
					$item->count_published = $newsfeed->count;
				}

				if ($newsfeed->state == 0)
				{
					$item->count_unpublished = $newsfeed->count;
				}

				if ($newsfeed->state == 2)
				{
					$item->count_archived = $newsfeed->count;
				}

				if ($newsfeed->state == -2)
				{
					$item->count_trashed = $newsfeed->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  &$items     The newsfeed tag objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  \stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = Factory::getDbo();
		$parts     = explode('.', $extension);
		$section   = null;

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$join = $db->quoteName('#__newsfeeds') . ' AS c ON ct.content_item_id=c.id';

		if ($section === 'category')
		{
			$join = $db->quoteName('#__categories') . ' AS c ON ct.content_item_id=c.id';
		}

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published AS state, count(*) AS count')
				->from($db->quoteName('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->quote($extension))
				->join('LEFT', $join)
				->group('state');

			$db->setQuery($query);
			$newsfeeds = $db->loadObjectList();

			foreach ($newsfeeds as $newsfeed)
			{
				if ($newsfeed->state == 1)
				{
					$item->count_published = $newsfeed->count;
				}

				if ($newsfeed->state == 0)
				{
					$item->count_unpublished = $newsfeed->count;
				}

				if ($newsfeed->state == 2)
				{
					$item->count_archived = $newsfeed->count;
				}

				if ($newsfeed->state == -2)
				{
					$item->count_trashed = $newsfeed->count;
				}
			}
		}

		return $items;
	}
}
