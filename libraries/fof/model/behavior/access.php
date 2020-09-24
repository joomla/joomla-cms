<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  model
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @note        This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework model behavior class to filter front-end access to items
 * based on the viewing access levels.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFModelBehaviorAccess extends FOFModelBehavior
{
	/**
	 * This event runs after we have built the query used to fetch a record
	 * list in a model. It is used to apply automatic query filters.
	 *
	 * @param   FOFModel        &$model  The model which calls this event
	 * @param   FOFDatabaseQuery  &$query  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterBuildQuery(&$model, &$query)
	{
		// This behavior only applies to the front-end.
		if (!FOFPlatform::getInstance()->isFrontend())
		{
			return;
		}

		// Get the name of the access field
		$table       = $model->getTable();
		$accessField = $table->getColumnAlias('access');

		// Make sure the field actually exists
		if (!in_array($accessField, $table->getKnownFields()))
		{
			return;
		}

		$model->applyAccessFiltering(null);
	}

	/**
	 * The event runs after FOFModel has called FOFTable and retrieved a single
	 * item from the database. It is used to apply automatic filters.
	 *
	 * @param   FOFModel  &$model   The model which was called
	 * @param   FOFTable  &$record  The record loaded from the database
	 *
	 * @return  void
	 */
	public function onAfterGetItem(&$model, &$record)
	{
		if ($record instanceof FOFTable)
		{
			$fieldName = $record->getColumnAlias('access');

			// Make sure the field actually exists
			if (!in_array($fieldName, $record->getKnownFields()))
			{
				return;
			}

			// Get the user
			$user = FOFPlatform::getInstance()->getUser();

			// Filter by authorised access levels
			if (!in_array($record->$fieldName, $user->getAuthorisedViewLevels()))
			{
				$record = null;
			}
		}
	}
}
