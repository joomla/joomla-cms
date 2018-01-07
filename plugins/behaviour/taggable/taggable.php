<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Taggable
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Event as CmsEvent;

/**
 * Implements the Taggable behaviour which allows extensions to automatically support tags for their content items.
 *
 * This plugin supersedes JHelperObserverTags.
 *
 * @since  4.0.0
 */
class PlgBehaviourTaggable extends JPlugin
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
		/** @var JTableInterface $table */
		$table			= $event['subject'];

		// Parse the type alias
		$typeAlias = $this->parseTypeAlias($table);

		// If the table doesn't support UCM we can't use the Taggable behaviour
		if (is_null($typeAlias))
		{
			return;
		}

		// If the table already has a tags helper we have nothing to do
		if (property_exists($table, 'tagsHelper'))
		{
			return;
		}

		$table->tagsHelper = new JHelperTags;
		$table->tagsHelper->typeAlias = $table->typeAlias;

		// This is required because getTagIds overrides the tags property of the Tags Helper.
		$cloneHelper = clone $table->tagsHelper;
		$tagIds = $cloneHelper->getTagIds($table->getId(), $typeAlias);

		if (!empty($tagIds))
		{
			$table->tagsHelper->tags = explode(',', $tagIds);
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
		/** @var JTableInterface $table */
		$table			= $event['subject'];

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

		/** @var JHelperTags $tagsHelper */
		$tagsHelper            = $table->tagsHelper;
		$tagsHelper->typeAlias = $typeAlias;

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
		/** @var JTableInterface $table */
		$table	= $event['subject'];
		$result = $event['result'];

		if (!$result)
		{
			return;
		}

		if (!is_object($table) || !($table instanceof JTableInterface))
		{
			return;
		}

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
		/** @var JTableInterface $table */
		$table			= $event['subject'];
		$pk				= $event['pk'];

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
		$table->tagsHelper->typeAlias = $typeAlias;

		$table->tagsHelper->deleteTagData($table, $pk);
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
	 * @param   CmsEvent\Table\AfterResetEvent  $event  The event to handle
	 *
	 * @return  void
	 * 
	 * @since   4.0.0
	 */
	public function onTableAfterReset(CmsEvent\Table\AfterResetEvent $event)
	{
		// Extract arguments
		/** @var JTableInterface $table */
		$table			= $event['subject'];

		// Parse the type alias
		$typeAlias = $this->parseTypeAlias($table);

		// If the table doesn't support UCM we can't use the Taggable behaviour
		if (is_null($typeAlias))
		{
			return;
		}

		$table->tagsHelper = new JHelperTags;
		$table->tagsHelper->typeAlias = $table->typeAlias;
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
		/** @var JTableInterface $table */
		$table			= $event['subject'];

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

		// This is required because getTagIds overrides the tags property of the Tags Helper.
		$cloneHelper = clone $table->tagsHelper;
		$tagIds = $cloneHelper->getTagIds($table->getId(), $typeAlias);

		if (!empty($tagIds))
		{
			$table->tagsHelper->tags = explode(',', $tagIds);
		}
	}

	/**
	 * Internal method
	 * Parses a TypeAlias of the form "{variableName}.type", replacing {variableName} with table-instance variables variableName
	 *
	 * @param   JTableInterface  &$table  The table
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 *
	 * @internal
	 */
	protected function parseTypeAlias(JTableInterface &$table)
	{
		if (!isset($table->typeAlias))
		{
			return null;
		}

		if (empty($table->typeAlias))
		{
			return null;
		}

		return preg_replace_callback('/{([^}]+)}/',
			function($matches) use ($table)
			{
				return $table->{$matches[1]};
			},
			$table->typeAlias
		);
	}
}
