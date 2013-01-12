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
 * Tags helper class, provides methods to perform various tasks relevant
 * tagging of content.
 *
 * @since       3.1
 */
class JTagsHelper
{

	/**
	 * Method to add or update tags associated with an item. Generally used as a postSaveHook.
	 *
	 * @param   integer  $id      The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view for a url.
	 * @params  array    $tags    Array of tags to be applied.
	 *
	 * @return  void
	 * @since   3.1
	 */
	 public function tagItem($id, $prefix, $tags)
	 {
		// Delete the old tag maps.
		$db		= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__contentitem_tag_map'));
		$query->where($db->quoteName('item_name') . ' = ' .  $db->quote($prefix . '.' . (int) $id));
		$db->setQuery($query);
		$db->execute();

		// Set the new tag maps.
		// Have to break this up into individual queries for cross-database support.
		foreach ($tags as $tag)
		{
			$query2 = $db->getQuery(true);

			$query2->insert($db->quoteName('#__contentitem_tag_map'));
			$query2->columns(array($db->quoteName('item_name'), $db->quoteName('tag_id')));

			$query2->clear('values');
			$query2->values($db->quote($prefix . '.' . $id) . ', ' . $tag);
			$db->setQuery($query2);
			$db->execute();
		}

		return;
	}

	/**
	 * Method to get a list of tags for a given item.
	 * Normally used for displaying a list of tags within a layout
	 *
	 * @param   integer  $id      The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view to be used for a url.
	 *
	 * @return  string    Comma separated list of tag Ids.
	 *
	 * @return  void
	 * @since   3.1
	 */

	public function getTagIds($id, $prefix)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Load the tags.
		$query->clear();
		$query->select($db->quoteName('t.id') );

		$query->from($db->quoteName('#__tags') . ' AS t');
		$query->join('INNER', $db->quoteName('#__contentitem_tag_map') . ' AS m ' .
			' ON ' . $db->quoteName('m.tag_id') . ' = ' .  $db->quoteName('t.id'));
		$query->where($db->quoteName('m.item_name') . ' = ' . $db->quote($prefix . '.' . $id));
		$db->setQuery($query);

		// Add the tags to the content data.
		$tagsList = $db->loadColumn();
		$this->tags = implode(',', $tagsList);

		return $this->tags;
	}

	/**
	 * Method to get a list of tags for an item, optionally with the tag data.
	 *
	 * @param   integer  $contentName  Name of an item. Dot separated.
	 * @param   boolean  $getTagData   If true, data from the tags table will be included, defaults to true.
	 *
	 * @return  array    Array of of tag objects
	 *
	 * @since   3.1
	 */
	public function getItemTags($contentItemName, $getTagData = true)
	{
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(array($db->quoteName('m.tag_id'), $db->quoteName('t') .'.*'));
		$query->from($db->quoteName('#__contentitem_tag_map') . ' AS m ');
		$query->where($db->quoteName('m.item_name') . ' = ' . $db->quote($contentItemName),
			$db->quoteName('t.published') . ' =  1' );

		if ($getTagData)
		{
			$query->join('INNER', $db->quoteName('#__tags') . ' AS t ' . ' ON ' .
				$db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id'));
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
	public function getTagItems($tag_id = null, $getItemData = true)
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
		$this->tagItems = $db->loadObjectList();

		if ($getItemData)
		{
			foreach ($this->tagItems as $item)
			{
				$item_id = $item->getContentItemId($item->item_name);
				$table = $item->getTableName($item->item_name);

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
	public function explodeTagItemName($tagItemName)
	{
		return $explodedItemName = explode('.', $tagItemName);
	}

	/**
	 * Returns the type for a tag map record
	 *
	 * @param   string  $tagItemName  The tag item name.
	 *
	 * @return  string  The content type name for the item.
	 *
	 * @since   3.1
	 */
	public function getTypeName($tagItemName, $explodedItemName = null)
	{
		if (!isset($explodedItemName))
		{
			$this->explodedItemName = $this->explodeTagItemName();
		}

		return $this->explodedItemName[0];
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
	public function getContentItemId($tagItemName, $explodedItemName = array())
	{
		if (!isset($explodedItemName))
		{
			$this->explodedItemName = self::explodeTagItemName($tagItemName);
		}

		return $this->explodedItemName[2];
	}

	/**
	 * Returns the url segment for a tag map record.
	 *
	 * @param   string  $tagItemName  The tag item name.
	 *
	 * @return  string  The url string e.g. index.php?option=com_content&vew=article&id=3.
	 *
	 * @since   3.1
	 */
	public function getContentItemUrl($tagItemName, $explodedItemName = null)
	{
		if (!isset($explodedItemName))
		{
			$explodedItemName = self::explodeTagItemName($tagItemName);
		}

		$this->url = 'index.php&option=' . $explodedItemName[0] . '&view=' .  $explodedItemName[1] . '&id=' . $explodedItemName[2];

		return $this->url;
	}

	/**
	 * Returns the url segment for a tag map record.
	 *
	 * @param   string  $tagItemName  The tag item name.
	 *
	 * @return  string  The url string e.g. index.php?option=com_content&vew=article&id=3.
	 *
	 * @since   3.1
	 */
	public function getTagUrl($tagItemName, $explodedItemName = null)
	{
		if (!isset($explodedItemName))
		{
			$explodedItemName = self::explodeTagItemName($tagItemName);
		}

		$this->url = 'index.php&option=com_tags&view=tag&id=' . $id;

		return $this->url;
	}


	/**
	 * Method to get the table name for a type.
	 *
	 * @param   string  $tagItemName  Name of an item.
	 *
	 * @return  string  Name of the table for a tagged content item
	 *
	 * @since   3.1
	 */
	public function getTableName($tagItemName, $explodedItemName = null)
	{
		if (!isset($explodedItemName))
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
		$this->table = $db->loadResult();

		return $this->table;
	}
	/**
	 * Method to get a list of types.
	 *
	 * @param   string  $arrayType  Optionally specify that the returned list consist of objects, associative arrays, or arrays.
	 *                              Options are: rowList, assocList, and objectList
	 *
	 * @return  array   Array of of types
	 *
	 * @since   3.1
	 */
	public static function getTypes($arrayType = 'objectList')
	{
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__content_types'));
		$db->setQuery($query);
		if (empty($arrayType) || $arrayType == 'objectList')
		{
			$types = $db->loadObjectList();
		}
		elseif ($arrayType == 'assocList')
		{
					$types = $db->loadAssocList();
		}
		else
		{
			$types = $db->loadRowList();
		}

		return $types;
	}

}
