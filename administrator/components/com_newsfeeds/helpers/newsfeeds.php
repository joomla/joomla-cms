<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds component helper.
 *
 * @since  1.6
 */
class NewsfeedsHelper extends JHelperContent
{
	public static $extension = 'com_newsfeeds';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_NEWSFEEDS_SUBMENU_NEWSFEEDS'),
			'index.php?option=com_newsfeeds&view=newsfeeds',
			$vName == 'newsfeeds'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_NEWSFEEDS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_newsfeeds',
			$vName == 'categories'
		);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The banner category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published AS state, count(*) AS count')
				->from($db->qn('#__newsfeeds'))
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
	 * @param   stdClass[]  &$items     The newsfeed tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = JFactory::getDbo();
		$parts     = explode('.', $extension);
		$section   = null;
		if (count($parts) > 1)
		{
			$section = $parts[1];
		}
		$join = $db->qn('#__newsfeeds') . ' AS c ON ct.content_item_id=c.id';
		if ($section === 'category')
		{
			$join = $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id';
		}

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published AS state, count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
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
