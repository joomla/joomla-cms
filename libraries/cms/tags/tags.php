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
class JTags
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
		$query->where($db->quoteName('type_alias') . ' = ' .  $db->quote($prefix));
		$query->where($db->quoteName('content_item_id') . ' = ' .  (int) $id);
		$db->setQuery($query);
		$db->execute();

		// Set the new tag maps.
		if (!empty($tags))
		{
			// Have to break this up into individual queries for cross-database support.
			foreach ($tags as $tag)
			{
				$query2 = $db->getQuery(true);

				$query2->insert($db->quoteName('#__contentitem_tag_map'));
				$query2->columns(array($db->quoteName('type_alias'),$db->quoteName('content_item_id'), $db->quoteName('tag_id'), $db->quoteName('tag_date') ));

				$query2->clear('values');
				$query2->values($db->quote($prefix) . ', ' . $id . ', ' . $tag . ', ' . $query->currentTimestamp(), $created_date, $modified_date, $publish_up, $publish_down, $title, $language);
				$db->setQuery($query2);
				$db->execute();
			}
		}

		return;
	}

	/**
	 * Method to add  tags associated to a list of items. Generally used for batch processing.
	 *
	 * @param   integer  $ids     The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view for a url.
	 * @params  array    $tag     Tag to be applied. Note that his method handles single tags only.
	 *
	 * @return  void
	 * @since   3.1
	 */
	public function tagItems( $tag, $ids, $contexts)
	{
		foreach ($contexts as $context)
		{
			$prefix =  str_replace(strrchr($context,'.'),'',$context);
			$pk = ltrim(strrchr($context,'.'), '.');
			// Check whether the tag is present already.
			$db		= JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete();
			$query->from($db->quoteName('#__contentitem_tag_map'));
			$query->where($db->quoteName('type_alias') . ' = ' .  $db->quote($prefix));
			$query->where($db->quoteName('content_item_id') . ' = ' .  (int) $pk);
			$query->where($db->quoteName('tag_id') . ' = ' .  (int) $tag);
			$db->setQuery($query);echo $query->dump();
			$result = $db->loadResult();

			// If the tag isn't there already add it.
			if (empty($result))
			{
					$query2 = $db->getQuery(true);

					$query2->insert($db->quoteName('#__contentitem_tag_map'));
					$query2->columns(array($db->quoteName('type_alias'),$db->quoteName('content_item_id'), $db->quoteName('tag_id'), $db->quoteName('tag_date') ));

					$query2->clear('values');
					$query2->values($db->quote($prefix) . ', ' . $pk . ', ' . $tag . ', ' . $query->currentTimestamp());
					$db->setQuery($query2);
					$db->execute();
			}
		}

		return;
	}

	/**
	 * Method to remove  tags associated with a list of items. Generally used for batch processing.
	 *
	 * @param   integer  $ids     The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view for a url.
	 * @params  array    $tag     Tag to be applied. Note that his method handles single tags only.
	 *
	 * @return  void
	 * @since   3.1
	 */
	public function unTagItems($ids, $prefix, $tag)
	{
		foreach ($ids as $id)

		$db		= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__contentitem_tag_map');
		$query->where($db->quoteName('type_alias') . ' = ' .  $db->quote($prefix));
		$query->where($db->quoteName('content_item_id') . ' = ' .  (int) $id);
		$query->where($db->quoteName('tag_id') . ' = ' .  (int) $tag);
		$db->setQuery($query);
		$db->execute();

		return;
	}

	/**
	 * Method to get a list of tags for a given item.
	 * Normally used for displaying a list of tags within a layout
	 *
	 * @param   integer  $id      The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view to be used for a url.
	 *
	 * @return  string   Comma separated list of tag Ids.
	 *
	 * @since   3.1
	 */

	public function getTagIds($id, $prefix)
	{
		if (is_array($id))
		{
			$id=implode(',', $id);
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Load the tags.
		$query->clear();
		$query->select($db->quoteName('t.id') );

		$query->from($db->quoteName('#__tags') . ' AS t ');
		$query->join('INNER', $db->quoteName('#__contentitem_tag_map') . ' AS m'  .
			' ON ' . $db->quoteName('m.tag_id') . ' = ' .  $db->quoteName('t.id') . ' AND ' .
					$db->quoteName('m.type_alias') . ' = ' .
					$db->quote($prefix ) . ' AND ' . $db->quoteName('m.content_item_id') . ' IN ( ' . $id .')'  );

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
	public function getItemTags($contentType, $id, $getTagData = true)
	{
		if (is_array($id))
		{
			$id=implode($id);
		}
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(array($db->quoteName('m.tag_id'), $db->quoteName('t') .'.*'));
		$query->from($db->quoteName('#__contentitem_tag_map') . ' AS m ');
		$query->where(array($db->quoteName('m.type_alias') . ' = ' . $db->quote($contentType),
				$db->quoteName('m.content_item_id') . ' = ' . $db->quote($id),
			$db->quoteName('t.published') . ' =  1') );

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

		$query->select($db->quoteName('type_alias'), $db->quoteName('id'));
		$query->from($db->quoteName('#__contentitem_tag_map'));
		$query->where($db->quoteName('tag_id') . ' = ' . (int) $tag_id);

		$db->setQuery($query);
		$this->tagItems = $db->loadObjectList();

		if ($getItemData)
		{
			foreach ($this->tagItems as $item)
			{
				$item_id = $item->content_item_id;
				$table = $item->getTableName($item->type_alias);

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
	 * @param   string  $typeAlias  The tag item name to explode.
	 *
	 * @return  array   The exploded type alias. If name doe not exist an empty array is returned.
	 *
	 * @since   3.1
	 */
	public function explodeTypeAlias($typeAlias)
	{
		return $explodedTypeAlias = explode('.', $typeAlias);
	}

	/**
	 * Returns the component for a tag map record
	 *
	 * @param   string  $typeAlias  The tag item name.
	 *
	 * @return  string  The content type title for the item.
	 *
	 * @since   3.1
	 */
	public function getTypeName($typeAlias, $explodedTypeAlias = null)
	{
		if (!isset($explodedTypeAlias))
		{
			$this->explodedTypeAlias = $this->explodeTypeAlias();
		}

		return $this->explodedTypeAlias[0];
	}

	/**
	 * Returns the url segment for a tag map record.
	 *
	 * @param   string   $typeAlias          The tag item name.
	 * @param   array    $explodedTypeAlias  Exploded alias if it exists
	 * @param   integer  $id                 Id of the item
	 *
	 * @return  string  The url string e.g. index.php?option=com_content&vew=article&id=3.
	 *
	 * @since   3.1
	 */
	public function getContentItemUrl($typeAlias, $explodedTypeAlias = null, $id)
	{
		if (!isset($explodedTypeAlias))
		{
			$explodedTypeAlias = self::explodedTypeAlias($tagAlias);
		}

		$this->url = 'index.php?option=' . $explodedTypeAlias[0] . '&view=' .  $explodedTypeAlias[1] . '&id=' . $id;

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
	public function getTagUrl($typeAlias, $explodedTypeAlias = null, $id)
	{
		if (!isset($explodedTypeAlias))
		{
			$explodedTypeAlias = self::explodeTypeAlias($tagItemName);
		}

		$this->url = 'index.php&option=com_tags&view=tag&id=' . $id;

		return $this->url;
	}


	/**
	 * Method to get the table name for a type alias.
	 *
	 * @param   string  $tagAlias  A type alias.
	 *
	 * @return  string  Name of the table for a type
	 *
	 * @since   3.1
	 */
	public function getTableName($tagItemAlias)
	{

		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('table'));
		$query->from($db->quoteName('#__content_types'));
		$query->where($db->quoteName('alias') . ' = ' .  $db->quote($tagItemAlias));
		$db->setQuery($query);
		$this->table = $db->loadResult();

		return $this->table;
	}
	/**
	 * Method to get a list of types with associated data.
	 *
	 * @param   string   $arrayType     Optionally specify that the returned list consist of objects, associative arrays, or arrays.
	 *                                  Options are: rowList, assocList, and objectList
	 * @param   array    $selectTypes   Optional array of type ids to limit the results to. Often from a request.
	 * @param   boolean  $useAlias      If true, the alias is used to match, if false the type_id is used.
	 *
	 * @return  array   Array of of types
	 *
	 * @since   3.1
	 */
	public static function getTypes($arrayType = 'objectList', $selectTypes = null, $useAlias = true)
	{
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');

		if (!empty($selectTypes))
		{
			if (is_array($selectTypes))
			{
				$selectTypes = implode(',', $selectTypes);
			}
			if ($useAlias)
			{
				$query->where($db->qn('alias') . ' IN (' . $selectTypes . ')') ;
			}
			else
			{
				$query->where($db->qn('type_id') . ' IN (' . $selectTypes . ')') ;
			}
		}

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
	/**
	 * Method to delete all instances of a tag from the mapping table. Generally used when a tag is deleted.
	 *
	 * @param   integer  $tag_id      The tag_id (primary key) for the deleted tag.
	 * @return  void
	 * @since   3.1
	 */
	public function tagDeleteInstances($tag_id)
	{
		// Delete the old tag maps.
		$db		= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__contentitem_tag_map'));
		$query->where($db->quoteName('tag_id') . ' = ' .  (int) $tag_id);
		$db->setQuery($query);
		$db->execute();
	}
}
