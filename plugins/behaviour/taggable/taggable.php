<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Taggable
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Event as CmsEvent;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Implements the Taggable behaviour which allows extensions to automatically support tags for their content items.
 *
 * This plugin supersedes JHelperObserverTags.
 *
 * @since  4.0.0
 */
class PlgBehaviourTaggable extends CMSPlugin
{
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
	public function onTableObjectCreate(CmsEvent\Table\ObjectCreateEvent $event)
	{
		// Extract arguments
		/** @var TableInterface $table */
		$table			= $event['subject'];

		// If the tags table doesn't implement the interface bail
		if (!($table instanceof TaggableTableInterface))
		{
			return;
		}

		// If the table already has a tags helper we have nothing to do
		if (!is_null($table->getTagsHelper()))
		{
			return;
		}

		$tagsHelper = new TagsHelper;
		$tagsHelper->typeAlias = $table->typeAlias;
		$table->setTagsHelper($tagsHelper);

		// This is required because getTagIds overrides the tags property of the Tags Helper.
		$cloneHelper = clone $table->getTagsHelper();
		$tagIds = $cloneHelper->getTagIds($table->getId(), $table->getTypeAlias());

		if (!empty($tagIds))
		{
			$table->getTagsHelper()->tags = explode(',', $tagIds);
		}
	}

	/**
	 * Pre-processor for $table->store($updateNulls)
	 *
	 * @param   CmsEvent\Table\BeforeStoreEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableBeforeStore(CmsEvent\Table\BeforeStoreEvent $event)
	{
		// Extract arguments
		/** @var TableInterface $table */
		$table			= $event['subject'];

		// If the tags table doesn't implement the interface bail
		if (!($table instanceof TaggableTableInterface))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (is_null($table->getTagsHelper()))
		{
			return;
		}

		/** @var TagsHelper $tagsHelper */
		$tagsHelper            = $table->getTagsHelper();
		$tagsHelper->typeAlias = $table->getTypeAlias();

		$newTags = $table->newTags ?? array();

		if (empty($newTags))
		{
			$tagsHelper->preStoreProcess($table);
		}
		else
		{
			$tagsHelper->preStoreProcess($table, (array) $newTags);
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

		if (!$result)
		{
			return;
		}

		if (!is_object($table) || !($table instanceof TaggableTableInterface))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (is_null($table->getTagsHelper()))
		{
			return;
		}

		// Get the Tags helper and assign the parsed alias
		/** @var TagsHelper $tagsHelper */
		$tagsHelper            = $table->getTagsHelper();
		$tagsHelper->typeAlias = $table->getTypeAlias();

		$newTags = $table->newTags ?? array();

		if (empty($newTags))
		{
			$result = $tagsHelper->postStoreProcess($table);
		}
		else
		{
			if (is_string($newTags) && (strpos($newTags, ',') !== false))
			{
				$newTags = explode(',', $newTags);
			}
			elseif (!is_array($newTags))
			{
				$newTags = (array) $newTags;
			}

			$result = $tagsHelper->postStoreProcess($table, $newTags);
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
		/** @var TableInterface $table */
		$table			= $event['subject'];
		$pk				= $event['pk'];

		// If the tags table doesn't implement the interface bail
		if (!($table instanceof TaggableTableInterface))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (is_null($table->getTagsHelper()))
		{
			return;
		}

		// Get the Tags helper and assign the parsed alias
		$table->getTagsHelper()->typeAlias = $table->getTypeAlias();

		$table->getTagsHelper()->deleteTagData($table, $pk);
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
		/** @var TableInterface $table */
		$table			= $event['subject'];
		$newTags		= $event['newTags'];
		$replaceTags	= $event['replaceTags'];

		// If the tags table doesn't implement the interface bail
		if (!($table instanceof TaggableTableInterface))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (is_null($table->getTagsHelper()))
		{
			return;
		}

		// Get the Tags helper and assign the parsed alias
		/** @var TagsHelper $tagsHelper */
		$tagsHelper            = $table->getTagsHelper();
		$tagsHelper->typeAlias = $table->getTypeAlias();

		if (!$tagsHelper->postStoreProcess($table, $newTags, $replaceTags))
		{
			throw new RuntimeException($table->getError());
		}
	}

	/**
	 * Runs when an existing table object is reset
	 *
	 * @param   CmsEvent\Table\AfterResetEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableAfterReset(CmsEvent\Table\AfterResetEvent $event)
	{
		// Extract arguments
		/** @var TableInterface $table */
		$table			= $event['subject'];

		// If the tags table doesn't implement the interface bail
		if (!($table instanceof TaggableTableInterface))
		{
			return;
		}

		// Parse the type alias
		$tagsHelper = new TagsHelper;
		$tagsHelper->typeAlias = $table->getTypeAlias();
		$table->setTagsHelper($tagsHelper);
	}

	/**
	 * Runs when an existing table object has been loaded
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

		// If the tags table doesn't implement the interface bail
		if (!($table instanceof TaggableTableInterface))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (is_null($table->getTagsHelper()))
		{
			return;
		}

		// This is required because getTagIds overrides the tags property of the Tags Helper.
		$cloneHelper = clone $table->getTagsHelper();
		$tagIds = $cloneHelper->getTagIds($table->getId(), $table->getTypeAlias());

		if (!empty($tagIds))
		{
			$table->getTagsHelper()->tags = explode(',', $tagIds);
		}
	}

	/**
	 * Runs when an existing table object has been loaded
	 *
	 * @param   CmsEvent\Model\BeforeBatchEvent $event The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeBatch(CmsEvent\Model\BeforeBatchEvent $event)
	{
		/** @var TableInterface $oldTable */
		$sourceTable = $event['src'];

		if (!($sourceTable instanceof TaggableTableInterface))
		{
			return;
		}

		if ($event['type'] === 'copy')
		{
			$sourceTable->newTags = $sourceTable->getTagsHelper()->tags;
		}
		else
		{
			/**
			 * All other batch actions we don't want the tags to be modified so clear the helper - that way no actions
			 * will be performed on store
			 */
			$sourceTable->clearTagsHelper();
		}
	}
}
