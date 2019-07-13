<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tagging;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Tree\NodeTrait;
use Joomla\Registry\Registry;

use Joomla\CMS\Tree\NodeInterface;
use Joomla\CMS\Table\Tag as TagTable;

/**
 * Tag node class
 *
 * @since  __DEPLOY_VERSION__
 */
class Tag extends CMSObject implements NodeInterface
{
	use NodeTrait;

	public $id;

	public $parent_id;

	public $lft;

	public $rgt;

	public $level;

	public $path;

	public $title;

	public $alias;

	public $note;

	public $description = '';

	public $published;

	public $checked_out;

	public $checked_out_time;

	public $access;

	public $params;

	public $metadesc;

	public $metakey;

	public $metadata;

	public $created_user_id;

	public $created_time;

	public $created_by_alias;

	public $modified_user_id;

	public $modified_time;

	public $images;

	public $urls;

	public $hits = 0;

	public $language = '*';

	public $version;

	public $publish_up;

	public $publish_down;

	/**
	 * Tag constructor.
	 *
	 * @param   int  $tagId  ID of the tag to load
	 */
	public function __construct($tagId = null)
	{
		if ($tagId)
		{
			$db = Factory::getDbo();
			$table = new TagTable($db);

			if ($table->load($tagId))
			{
				foreach ($table->getProperties() as $key => $value)
				{
					$this->$key = $value;
				}

				$this->params = new Registry($this->params);
				$this->metadata = new Registry($this->metadata);
				$this->images = new Registry($this->images);
			}
		}
	}

	/**
	 * Save this tag to the database
	 *
	 * @return  bool  True if saving was successfull
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save()
	{
		$db = Factory::getDbo();
		$table = new TagTable($db);

		if ($this->id)
		{
			$table->load($this->id);
		}
		else
		{
			$table->setLocation($this->parent_id, 'last-child');
		}

		$data = $this->getProperties();
		unset($data['path']);
		$table->bind($data);
		$table->check();
		$result = $table->store();

		if (!$this->id)
		{
			$this->id = $table->id;
		}

		$table->rebuild();

		return $result;
	}

	/**
	 * Delete this tag from the database with all its associations
	 *
	 * @return  bool  True if deleting was successfull
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete()
	{
		$db = Factory::getDbo();

		// Delete mappings to tag
		$query = $db->getQuery(true);
		$query->delete($query->qn('#__tag_content_map'))
			->where($query->qn('tag_id') . ' = ' . (int) $this->id);
		$db->setQuery($query);
		$db->execute();

		// Delete tag
		$db = Factory::getDbo();
		$table = new TagTable($db);

		return $table->delete($this->id);
	}

	/**
	 * Add an association between this tag and the given Content Item
	 *
	 * @param   ContentItem  $item  Content Item to add the association for
	 *
	 * @return  bool  True if successfull
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addContentItem(ContentItem $item)
	{
		// If the tag or the content item is not in the database, we have to fail
		if (!$item->content_id || !$item->type_alias || !$this->id)
		{
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Check if the mapping already exists
		$query->select($query->qn(['a.tag_id', 'a.type_alias', 'a.content_id']))
			->from($query->qn('#__tag_content_map', 'a'))
			->where($query->qn('a.tag_id') . ' = ' . $query->q($this->id))
			->where($query->qn('a.type_alias') . ' = ' . $query->q($item->type_alias))
			->where($query->qn('a.content_id') . ' = ' . $query->q($item->content_id));
		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			return true;
		}

		$query->clear()
			->insert($query->qn('#__tag_content_map'))
			->columns($query->qn(['tag_id', 'type_alias', 'content_id']))
			->values($query->q($this->id) . ',' . $query->q($item->type_alias) . ',' . $query->q($item->content_id));
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Remove an association between this tag and the given Content Item
	 *
	 * @param   ContentItem  $item  Content Item to remove the association for
	 *
	 * @return  bool  True if successfull
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeContentItem(ContentItem $item)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($query->qn('#__tag_content_map'))
			->where($query->qn('tag_id') . ' = ' . $query->q($this->id))
			->where($query->qn('type_alias') . ' = ' . $query->q($item->type_alias))
			->where($query->qn('content_id') . ' = ' . $query->q($item->content_id));

		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Get content items associated with this tag
	 *
	 * @return  ContentItem
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getContentItems()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*')
			->from($query->qn('#__tag_content_map', 'm'))
			->leftJoin($query->qn('#__tag_content', 'c') . ' ON m.type_alias = c.type_alias AND m.content_id = c.content_id')
			->where($query->qn('m.tag_id') . ' = ' . $query->q($this->id));

		$db->setQuery($query);
		$items = $db->loadObjectList('', ContentItem::class);

		return $items;
	}

	/**
	 * Get number of content items associated to this tag
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItemCount()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(c.*)')
			->from($query->qn('#__tag_content_map', 'm'))
			->leftJoin($query->qn('#__tag_content', 'c') . ' ON m.type_alias = c.type_alias AND m.content_id = c.content_id')
			->where($query->qn('m.tag_id') . ' = ' . $query->q($this->id));

		$db->setQuery($query);
		return $db->loadResult();
	}
}
