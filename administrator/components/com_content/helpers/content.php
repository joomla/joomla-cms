<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends JHelperContent
{
	public static $extension = 'com_content';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories'
		);

		if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
		{
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_content.article',
				$vName == 'fields.fields'
			);
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&extension=com_content',
				$vName == 'fields.groups'
			);
		}

		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_FEATURED'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use JComponentHelper::filterText() instead.
	 */
	public static function filterText($text)
	{
		JLog::add('ContentHelper::filterText() is deprecated. Use JComponentHelper::filterText() instead.', JLog::WARNING, 'deprecated');

		return JComponentHelper::filterText($text);
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
			$query->select('state, count(*) AS count')
				->from($db->qn('#__content'))
				->where('catid = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);
			$articles = $db->loadObjectList();

			foreach ($articles as $article)
			{
				if ($article->state == 1)
				{
					$item->count_published = $article->count;
				}

				if ($article->state == 0)
				{
					$item->count_unpublished = $article->count;
				}

				if ($article->state == 2)
				{
					$item->count_archived = $article->count;
				}

				if ($article->state == -2)
				{
					$item->count_trashed = $article->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The content objects
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
		$component = $parts[0];
		$section   = null;

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$join  = $db->qn('#__content') . ' AS c ON ct.content_item_id=c.id';
		$state = 'state';

		if ($section === 'category')
		{
			$join = $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id';
			$state = 'published as state';
		}

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select($state . ', count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $join)
				->group('state');
			$db->setQuery($query);
			$contents = $db->loadObjectList();

			foreach ($contents as $content)
			{
				if ($content->state == 1)
				{
					$item->count_published = $content->count;
				}

				if ($content->state == 0)
				{
					$item->count_unpublished = $content->count;
				}

				if ($content->state == 2)
				{
					$item->count_archived = $content->count;
				}

				if ($content->state == -2)
				{
					$item->count_trashed = $content->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getContexts()
	{
		JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_content.article' => JText::_('COM_CONTENT'),
		);

		return $contexts;
	}
}
