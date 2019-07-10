<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Taggable
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Event as CmsEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Tagging\ContentItem;
use Joomla\CMS\Tagging\Tag;
use Joomla\CMS\Tagging\TaggableTableInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Implements the Taggable behaviour which allows extensions to automatically support tags for their content items.
 *
 * This plugin supersedes JHelperObserverTags.
 *
 * @since  4.0.0
 */
class PlgBehaviourTaggable extends CMSPlugin
{
	protected $tags = [];

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                $config    An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                          (this list is not meant to be comprehensive).
	 *
	 * @since   4.0.0
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->allowLegacyListeners = false;

		parent::__construct($subject, $config);
	}

	/**
	 * Runs when a new table object is being created
	 *
	 * @param   CmsEvent\Table\ObjectCreateEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableBeforeBind(CmsEvent\Table\BeforeBindEvent $event)
	{
		/**
		 * Extract arguments
		 * @var TableInterface $table
		 */
		$table			= $event['subject'];

		if (!$table instanceof TaggableTableInterface)
		{
			return;
		}

		$key = $table->getTypeAlias() . '.' . $table->getId();

		if (isset($event['src']['tags']))
		{
			$this->tags[$key] = $event['src']['tags'];
		}
	}

	/**
	 * Post-processor for $table->store($updateNulls)
	 *
	 * @param   CmsEvent\Table\AfterStoreEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableAfterStore(CmsEvent\Table\AfterStoreEvent $event)
	{
		// Extract arguments
		/** @var TableInterface $table */
		$table	= $event['subject'];
		$result = $event['result'];
		$db     = $table->getDbo();

		if (!$result || !is_object($table) || !($table instanceof TaggableTableInterface))
		{
			return;
		}

		$typeAlias = $table->getTypeAlias();
		$id = $table->getId();
		$contentItem = new ContentItem($typeAlias, $id);
		$contentType = new ContentType($db);

		if (!$contentType->load(['type_alias' => $typeAlias]))
		{
			return;
		}

		$fieldMapping = json_decode($contentType->field_mappings);
		$fields = array_keys(get_object_vars($contentItem));

		foreach ($fields as $field)
		{
			if (isset($fieldMapping->common->$field))
			{
				$contentItem->$field = $table->{$fieldMapping->common->$field};
			}
		}

		// Create or update the content item
		$contentItem->save();

		$key = $typeAlias . '.' . $id;

		/**
		 * If the tags have not been set in onTableBeforeBind, this seems
		 * to have been called from somewhere else and thus we bail here.
		 * We don't want to delete tags if the tag system had no chance to set them.
		 */
		if (!isset($this->tags[$key]))
		{
			return;
		}

		$tags = $contentItem->getTags();
		$oldTags = array_keys($tags);
		$newTags = ArrayHelper::toInteger($this->tags[$key]);

		$remove = array_diff($oldTags, $newTags);
		$add = array_diff($newTags, $oldTags);

		// Remove tags which have been removed.
		foreach ($remove as $tid)
		{
			$contentItem->removeTag($tags[$tid]);
		}

		// Add tags which have been added.
		foreach ($add as $tid)
		{
			$tag = new Tag($tid);
			$contentItem->addTag($tag);
		}
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   CmsEvent\Table\BeforeDeleteEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableBeforeDelete(CmsEvent\Table\BeforeDeleteEvent $event)
	{
		// Extract arguments
		/** @var JTableInterface $table */
		$table			= $event['subject'];
		$pk				= $event['pk'];

		$contentItem = new ContentItem($table->getTypeAlias(), $pk);

		if ($contentItem->content_id == $pk)
		{
			$contentItem->delete();
		}
	}

	/**
	 * Handles the tag setting in $table->batchTag($value, $pks, $contexts)
	 *
	 * @param   CmsEvent\Table\SetNewTagsEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableSetNewTags(CmsEvent\Table\SetNewTagsEvent $event)
	{
		// Extract arguments
		/** @var JTableInterface $table */
		$table			= $event['subject'];
		$newTags		= $event['newTags'];
		$replaceTags	= $event['replaceTags'];

		// Parse the type alias
		$typeAlias = $this->parseTypeAlias($table);

		// If the table doesn't support UCM we can't use the Taggable behaviour
		if (is_null($typeAlias))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (!property_exists($table, 'tagsHelper'))
		{
			return;
		}

		// Get the Tags helper and assign the parsed alias
		/** @var JHelperTags $tagsHelper */
		$tagsHelper            = $table->tagsHelper;
		$tagsHelper->typeAlias = $typeAlias;

		if (!$tagsHelper->postStoreProcess($table, $newTags, $replaceTags))
		{
			throw new RuntimeException($table->getError());
		}
	}

	/**
	 * Runs when an existing table object is reset
	 *
	 * @param   CmsEvent\Table\AfterLoadEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableAfterLoad(CmsEvent\Table\AfterLoadEvent $event)
	{
		// Extract arguments
		/** @var TableInterface $table */
		$table			= $event['subject'];

		if (!$table instanceof TaggableTableInterface)
		{
			return;
		}

		$typeAlias = $table->getTypeAlias();
		$id = $table->getId();

		$contentItem = new ContentItem($typeAlias, $id);

		// The content item doesn't exist yet. Creating...
		if ($contentItem->content_id != $id)
		{
			$table->tags = $contentItem->getTags();
		}
	}
}
