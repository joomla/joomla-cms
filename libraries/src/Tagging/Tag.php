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
	public $description;
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
	public $hits;
	public $language;
	public $version;
	public $publish_up;
	public $publish_down;

	public function __construct($tagId = null)
	{
		if ($tagId)
		{
			$table = new TagTable;

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

	public function save()
	{
		$table = new TagTable;
		$table->bind($this->getProperties());
		$table->aave();
	}

	public function delete()
	{
		$db = Factory::getDbo();

		// Delete mappings to tag
		$db->setQuery('DELETE FROM #__contentitem_tag_map WHERE tag_id = ' . (int) $this->id);
		$db->execute();

		// Delete tag
		$table = new TagTable;
		$table->delete($this->id);
	}

	public function addContentItem(ContentItem $item)
	{

	}

	public function removeContentItem(ContentItem $item)
	{

	}

	public function getContentItems()
	{

	}

	public function getItemCount()
	{

	}
}
