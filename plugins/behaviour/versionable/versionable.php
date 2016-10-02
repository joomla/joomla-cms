<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Taggable
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Event\DispatcherInterface;
use Joomla\Cms\Event as CmsEvent;

/**
 * Implements the Taggable behaviour which allows extensions to automatically support tags for their content items.
 *
 * This plugin supersedes JTableObserverContenthistory.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgBehaviourVersionable extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                $config    An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                          (this list is not meant to be comprehensive).
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function onTableObjectCreate(CmsEvent\Table\ObjectCreateEvent $event)
	{
		// Extract arguments
		/** @var JTableInterface $table */
		$table			= $event['subject'];

		// When we create the object the table is empty, so we can't parse the typeAlias field
		$typeAlias = $table->typeAlias;

		// If the table doesn't support UCM we can't use the Taggable behaviour
		if (is_null($typeAlias))
		{
			return;
		}

		// If the table already has a tags helper we have nothing to do
		if (property_exists($table, 'contenthistoryHelper'))
		{
			return;
		}

		$table->contenthistoryHelper = new JHelperContenthistory($typeAlias);
		$table->contenthistoryHelper->typeAlias = $table->typeAlias;
	}

	/**
	 * Post-processor for $table->store($updateNulls)
	 *
	 * @param   CmsEvent\Table\AfterStoreEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
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

		// If the table doesn't support UCM we can't use the Versionable behaviour
		if (is_null($typeAlias))
		{
			return;
		}

		// If the table doesn't have a tags helper we can't proceed
		if (!property_exists($table, 'contenthistoryHelper'))
		{
			return;
		}

		// Get the Tags helper and assign the parsed alias
		$table->contenthistoryHelper->typeAlias = $typeAlias;

		$aliasParts = explode('.', $table->contenthistoryHelper->typeAlias);

		if ($aliasParts[0] && JComponentHelper::getParams($aliasParts[0])->get('save_history', 0))
		{
			$table->contenthistoryHelper->store($table);
		}
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   CmsEvent\Table\BeforeDeleteEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onTableBeforeDelete(CmsEvent\Table\BeforeDeleteEvent $event)
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
		if (!property_exists($table, 'contenthistoryHelper'))
		{
			return;
		}

		$table->contenthistoryHelper->typeAlias = $typeAlias;
		$aliasParts = explode('.', $table->contenthistoryHelper->typeAlias);

		if ($aliasParts[0] && JComponentHelper::getParams($aliasParts[0])->get('save_history', 0))
		{
			$table->contenthistoryHelper->deleteHistory($table);
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
	 * @since   __DEPLOY_VERSION__
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
