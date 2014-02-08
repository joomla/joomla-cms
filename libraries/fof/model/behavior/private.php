<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  model
 * @copyright   Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * FrameworkOnFramework model behavior class to filter front-end access to items
 * craeted by the currently logged in user only.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFModelBehaviorPrivate extends FOFModelBehavior
{
	/**
	 * This event runs after we have built the query used to fetch a record
	 * list in a model. It is used to apply automatic query filters.
	 *
	 * @param   FOFModel        &$model  The model which calls this event
	 * @param   JDatabaseQuery  &$query  The model which calls this event
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
		$table = $model->getTable();
		$createdField = $table->getColumnAlias('created_by');

		// Make sure the access field actually exists
		if (!in_array($createdField, $table->getKnownFields()))
		{
			return;
		}

		// Get the current user's id
		$user_id = FOFPlatform::getInstance()->getUser()->id;

		// And filter the query output by the user id
		$db = JFactory::getDbo();

		$alias = $model->getTableAlias();
		$alias = $alias ? $alias . '.' : '';

		$query->where($alias . $db->qn($createdField) . ' = ' . $db->q($user_id));
	}

	/**
	 * The event runs after FOFModel has called FOFTable and retrieved a single
	 * item from the database. It is used to apply automatic filters.
	 *
	 * @param   FOFModel  &$model   The model which was called
	 * @param   FOFTable  &$record  The record loaded from the databae
	 *
	 * @return  void
	 */
	public function onAfterGetItem(&$model, &$record)
	{
		if ($record instanceof FOFTable)
		{
			$keyName = $record->getKeyName();
			if ($record->$keyName === null)
			{
				return;
			}

			$fieldName = $record->getColumnAlias('created_by');

			// Make sure the field actually exists
			if (!in_array($fieldName, $record->getKnownFields()))
			{
				return;
			}

			$user_id = FOFPlatform::getInstance()->getUser()->id;

			if ($record->$fieldName != $user_id)
			{
				$record = null;
			}
		}
	}
}
