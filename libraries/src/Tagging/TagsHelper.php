<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tagging;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Tags helper class, provides methods to perform various tasks relevant
 * tagging of content.
 *
 * @since  3.1
 */
class TagsHelper
{
	/**
	 * @param   $id  int  ID of the tag to load
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
	 * @param   $path  string  Path of the tag
	 *
	 * @return  Tag|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getTagByPath($path)
	{

	}

	/**
	 * Get tags associated with a content item
	 *
	 * @param   $typeAlias  string  The typealias of the content item
	 * @param   $contentId  int     The id of the content item
	 *
	 * @return  Tag[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getContentItemTags($typeAlias, $contentId)
	{

	}
}
