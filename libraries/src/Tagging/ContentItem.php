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
use Joomla\CMS\Table\TagContent;
use Joomla\Registry\Registry;

/**
 * UCM ContentItem class
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentItem
{
	public $content_id;

	public $type_alias;

	public $title;

	public $alias;

	public $body;

	public $state;

	public $access;

	public $params;

	public $featured;

	public $metadata;

	public $created_user_id;

	public $created_by_alias;

	public $created_time;

	public $modified_user_id;

	public $modified_time;

	public $language;

	public $publish_up;

	public $publish_down;

	public $images;

	public $urls;

	public $hits;

	public $ordering;

	public $metakey;

	public $metadesc;

	public $catid;

	/**
	 * ContentItem constructor.
	 *
	 * @param   null  $typeAlias
	 * @param   null  $contentId
	 */
	public function __construct($typeAlias = null, $contentId = null)
	{
		if ($typeAlias && $contentId)
		{
			$table = new TagContent(Factory::getDbo());

			if ($table->load(['type_alias' => $typeAlias, 'content_id' => $contentId]))
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
	 * Save this content item
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save()
	{
		$table = new TagContent(Factory::getDbo());
		$table->bind($this);

		return $table->save();
	}

	/**
	 * Delete this content item
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete()
	{
		$db = Factory::getDbo();

		// Delete mappings to tag
		$db->setQuery('DELETE FROM #__contentitem_tag_map WHERE tag_id = ' . (int) $this->id);
		$db->execute();

		// Delete tag
		$table = new TagContent(Factory::getDbo());

		return $table->delete(['type_alias' => $this->type_alias, 'content_id' => $this->content_id]);
	}

	/**
	 * Associate a tag with this content item
	 *
	 * @param   Tag  $tag  Tag to associate with this content item
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addTag(Tag $tag)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('#__tag_content_map')
			->values(
				['tag_id' => $tag->id,
				'type_alias' => $this->type_alias,
				'content_id' => $this->content_id]
			);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Remove the association between a tag and this content item
	 *
	 * @param   Tag  $tag  The tag to remove
	 *
	 * @return  bool  True if tag was removed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeTag(Tag $tag)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__tag_content_map')
			->where('tag_id = ' . $tag->id)
			->where('type_alias = ' . $query->q($this->type_alias))
			->where('content_id = ' . $query->q($this->content_id));
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Get the tags associated with this content item
	 *
	 * @return  array|Tag  Array of tags associated with this item
	 *
	 * @since   __DEPLOY_VERSION
	 */
	public function getTags()
	{
		if (!$this->content_id)
		{
			return array();
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('t.*')
			->from($query->qn('#__tags', 't'))
			->leftJoin('#__tag_content_map m ON t.id = m.tag_id')
			->where('m.type_alias = ' . $query->q($this->type_alias))
			->where('m.content_id = ' . $query->q($this->content_id));
		$db->setQuery($query);
		$result = $db->loadObjectList('id', 'Joomla\CMS\Tagging\Tag');

		return $result;
	}
}
