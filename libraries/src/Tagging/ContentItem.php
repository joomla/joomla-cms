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
	/**
	 * ID of the content item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $content_id;

	/**
	 * Type alias of the content item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type_alias;

	/**
	 * Title of the content item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_title;

	/**
	 * Alias of the content item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_alias;

	/**
	 * Content of the content item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_body;

	/**
	 * Publish state flag
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_state;

	/**
	 * Access flag
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_access;

	/**
	 * Params of the content item
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_params;

	/**
	 * Featured flag
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_featured;

	/**
	 * Metadata of the content item
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_metadata;

	/**
	 * User who created this
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_created_user_id;

	/**
	 * Alias of the creating user
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_created_by_alias;

	/**
	 * Datetime when this was created
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_created_time;

	/**
	 * User who last modified this
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_modified_user_id;

	/**
	 * Datetime when this was modified
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_modified_time;

	/**
	 * Language of the content item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_language;

	/**
	 * Datetime when this should be published
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_publish_up;

	/**
	 * Datetime when this should be unpublished
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_publish_down;

	/**
	 * Images of the content item
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_images;

	/**
	 * URLs of the content item
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_urls;

	/**
	 * Hits to this content item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_hits;

	/**
	 * Ordering of the items
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_ordering;

	/**
	 * Meta key
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_metakey;

	/**
	 * Meta description of the content item
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_metadesc;

	/**
	 * Category ID
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $core_catid;

	/**
	 * ContentItem constructor.
	 *
	 * @param   string|null   $typeAlias  Type of the content item
	 * @param   integer|null  $contentId  ID of the content item
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

				$this->core_params = new Registry($this->core_params);
				$this->core_metadata = new Registry($this->core_metadata);
				$this->core_images = new Registry($this->core_images);
				$this->core_urls = new Registry($this->core_urls);
			}
		}
	}

	/**
	 * Save this content item
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save()
	{
		$table = new TagContent(Factory::getDbo());
		$table->bind(get_object_vars($this));

		return $table->store();
	}

	/**
	 * Delete this content item
	 *
	 * @return  boolean
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
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addTag(Tag $tag)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('#__tag_content_map')
			->columns($query->qn(['tag_id', 'type_alias', 'content_id']))
			->values(((int) $tag->id) . ',' . $query->q($this->type_alias) . ',' . ((int) $this->content_id));
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Remove the association between a tag and this content item
	 *
	 * @param   Tag  $tag  The tag to remove
	 *
	 * @return  boolean  True if tag was removed
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
	 * @return  Tag[]  Array of tags associated with this item
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
