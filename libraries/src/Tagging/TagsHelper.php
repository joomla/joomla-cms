<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tagging;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Tag as TagTable;
use Joomla\CMS\Tagging\Tag;

/**
 * Tags helper class, provides methods to perform various tasks relevant
 * tagging of content.
 *
 * @since  3.1
 */
class TagsHelper
{
	/**
	 * Get a tag object by its ID
	 *
	 * @param   int  $id  ID of the tag to load
	 *
	 * @return  Tag|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getTagById($id)
	{
		$tag = new Tag($id);

		if ($tag->id == $id)
		{
			return $tag;
		}

		return null;
	}

	/**
	 * Get a tag based on its path
	 *
	 * @param   string  $path  Path of the tag
	 *
	 * @return  Tag|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getTagByPath($path)
	{
		$db = Factory::getDbo();
		$table = new TagTable($db);

		if (!$table->load(['path' => $path]))
		{
			return null;
		}

		$tag = new Tag($table->getId());

		return $tag;
	}

	/**
	 * Get tags associated with a content item
	 *
	 * @param   string      $typeAlias  The typealias of the content item
	 * @param   int         $contentId  The id of the content item
	 * @param   int[]|null  $access     An array of allowed viewlevel IDs
	 * @param   int|null    $published  Only return tags with this status
	 *
	 * @return  Tag[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getContentItemTags($typeAlias, $contentId, $access = null, $published = null)
	{
		$contentItem = new ContentItem($typeAlias, $contentId);

		$tags = $contentItem->getTags();

		if ($access || $published)
		{
			foreach ($tags as $i => $tag)
			{
				if ($access && !in_array($tag->access, $access))
				{
					unset($tags[$i]);
					continue;
				}

				if (!is_null($published) && $tag->published == $published)
				{
					unset($tags[$i]);
					continue;
				}
			}
		}

		return $tags;
	}
}
