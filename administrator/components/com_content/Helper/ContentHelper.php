<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelperInterface;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper implements ComponentHelperInterface
{
	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function countItems(array $items, string $section)
	{
		$db = \JFactory::getDbo();

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
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  $items      The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function countTagItems(array $items, string $extension)
	{
		$db = \JFactory::getDbo();
		$parts     = explode('.', $extension);
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
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 * @param   object  $item     The item
	 *
	 * @return  string|null  The new section
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validateSection($section, $item = null)
	{
		if (\JFactory::getApplication()->isClient('site'))
		{
			// On the front end we need to map some sections
			switch ($section)
			{
				// Editing an article
				case 'form':

				// Category list view
				case 'featured':
				case 'category':
					$section = 'article';
			}
		}

		if ($section != 'article')
		{
			// We don't know other sections
			return null;
		}

		return $section;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getContexts(): array
	{
		\JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_content.article'    => \JText::_('COM_CONTENT'),
			'com_content.categories' => \JText::_('JCATEGORY')
		);

		return $contexts;
	}
}
