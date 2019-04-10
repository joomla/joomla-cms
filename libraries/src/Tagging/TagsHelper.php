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

	public static function getTagById($id)
	{
		$tag = new Tag($id);

		if ($tag->id == $id)
		{
			return $tag;
		}

		return null;
	}

	public static function getTagByPath($path)
	{

	}

	public static function getContentItemTags($typeAlias, $contentId)
	{

	}
}
