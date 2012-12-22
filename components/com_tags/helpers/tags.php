<?php
/**
 * @package     Joomla.Site
 * @subpackage  Tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Tags helper class, provides static methods to perform various tasks relevant
 * tagging of content.
 *
 * @since       3.1
 */
class TagsHelperTags
{

	/**
	 * Method to get a list of tags for an item, optionally with the tag data.
	 *
	 * @param   integer  $contentName  Name of an item.
	 * @param   boolean  $getTagData   If true, data from the tags table will be included, defaults to true.
	 *
	 * @return  array    Array of of tag objects
	 *
	 * @since   3.1
	 */
	public static function getItemTags($contentItemName, $getTagData = true)
	{
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('m.tag_id'));
		$query->from($db->quoteName('#__contentitem_tag_map') . ' AS m ');
		$query->where($db->quoteName('item_name') . ' = ' . $db->quote($contentItemName));

		if ($getTagData)
		{
			$query->join($db->quoteName('#__tags') . ' AS t ' . ' ON ' .
				$db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.tag_id'));
		}
		
		$db->setQuery($query);
		$this->itemTags = $db->loadObjectList();

		return $this->itemTags;
	}

	/**
	 * Method to get a list of items for a tag.
	 *
	 * @param   integer  $contentName   Name of an item.
	 * @param   boolean  $getItemData   If true, data from the item tables will be included, defaults to true.
	 *
	 * @return  array    Array of of tag objects
	 *
	 * @since   3.1
	 */
	public static function getTagItems($tag_id = null, $getItemData = true)
	{
		if (empty($tag_id))
		{
			$app = JFactory::getApplication('site');

			// Load state from the request.
			$tag_id = $app->input->getInt('id');
		}

		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('item_name'));
		$query->from($db->quoteName('#__contentitem_tag_map'));
		$query->where($db->quoteName('tag_id') . ' = ' . (int) $tag_id);

		$db->setQuery($query);
		$tagItems = $db->loadObjectList();

		if ($getItemData)
		{
			foreach ($tagItems as $item)
			{
				$item_id = self::getContentItemId($item->item_name);
				$table = self::getTableName($item->item_name);

				$query2 = $db->getQuery(true);
				$query2->clear();

				$query2->select('*');
				$query2->from($db->quoteName($table));
				$query2->where($db->quoteName('id') . ' = ' . (int) $item_id);

				$db->setQuery($query2);
				$item->itemData = $db->loadAssoc();
			}
		}

		return $this->tagItems;
	}

	/**
	 * Returns content name from a tag map record as an array
	 *
	 * @param   string  $tagItemName  The tag item name to explode.
	 *
	 * @return  array   The exploded tag name. If name doe not exist an empty array is returned.
	 *
	 * @since   3.1
	 */
	public static function explodeTagItemName($tagItemName)
	{
		return $explodedItemName = explode('.', $tagItemName);
	}

	/**
	 * Returns the type for a tag map record
	 *
	 * @param   string  $tagItemName  The tag item name.
	 *
	 * @return  string  The type name for the item.
	 *
	 * @since   3.1
	 */
	public static function getTypeName($tagItemName)
	{
		if (!isset($this->explodedItemName))
		{
			$this->explodedItemName = $this->explodeTagItemName();
		}

		return $this->explodedItemName;
	}

	/**
	 * Returns the content item id for a tag map record
	 *
	 * @param   string  $tagItemName  The tag item name.
	 *
	 * @return  integer  The content item id or null if not found.
	 *
	 * @since   3.1
	 */
	public static function getContentItemId($tagItemName)
	{
		if (!isset($item->explodedItemName))
		{
			$explodedItemName = self::explodeTagItemName($tagItemName);
			return $explodedItemName[2];
		}

		return $item->explodedItemName[2];
	}

	/**
	 * Method to get the table name for a type.
	 *
	 * @param   integer  $contentName  Name of an item.
	 *
	 * @return  array    Array of of tag objects
	 *
	 * @since   3.1
	 */
	public static function getTableName($tagItemName)
	{
		if (!isset($item->explodedItemName))
		{
			$explodedItemName = self::explodeTagItemName($tagItemName);
		}
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('table'));
		$query->from($db->quoteName('#__content_types'));
		$query->where($db->quoteName('alias') . ' = ' .  $db->quote($explodedItemName[1]));
		$db->setQuery($query);
		$table = $db->loadResult();

		return $table;
	}
}
