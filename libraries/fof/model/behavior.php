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
 * FrameworkOnFramework model behavior class. It defines the events which are
 * called by a Model.
 *
 * @codeCoverageIgnore
 * @package  FrameworkOnFramework
 * @since    2.1
 */
abstract class FOFModelBehavior extends JEvent
{
	/**
	 * This event runs before saving data in the model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 * @param   array     &$data   The data to save
	 *
	 * @return  void
	 */
	public function onBeforeSave(&$model, &$data)
	{
	}

	/**
	 * This event runs before deleting a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeDelete(&$model)
	{
	}

	/**
	 * This event runs before copying a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeCopy(&$model)
	{
	}

	/**
	 * This event runs before publishing a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforePublish(&$model)
	{
	}

	/**
	 * This event runs before registering a hit on a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeHit(&$model)
	{
	}

	/**
	 * This event runs before moving a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeMove(&$model)
	{
	}

	/**
	 * This event runs before changing the records' order in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeReorder(&$model)
	{
	}

	/**
	 * This event runs when we are building the query used to fetch a record
	 * list in a model
	 *
	 * @param   FOFModel        &$model  The model which calls this event
	 * @param   JDatabaseQuery  &$query  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeBuildQuery(&$model, &$query)
	{
	}

	/**
	 * This event runs after saving a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterSave(&$model)
	{
	}

	/**
	 * This event runs after deleting a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterDelete(&$model)
	{
	}

	/**
	 * This event runs after copying a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterCopy(&$model)
	{
	}

	/**
	 * This event runs after publishing a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterPublish(&$model)
	{
	}

	/**
	 * This event runs after registering a hit on a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterHit(&$model)
	{
	}

	/**
	 * This event runs after moving a record in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterMove(&$model)
	{
	}

	/**
	 * This event runs after reordering records in a model
	 *
	 * @param   FOFModel  &$model  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterReorder(&$model)
	{
	}

	/**
	 * This event runs after we have built the query used to fetch a record
	 * list in a model
	 *
	 * @param   FOFModel        &$model  The model which calls this event
	 * @param   JDatabaseQuery  &$query  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterBuildQuery(&$model, &$query)
	{
	}

	/**
	 * This event runs after getting a single item
	 *
	 * @param   FOFModel  &$model   The model which calls this event
	 * @param   FOFTable  &$record  The record loaded by this model
	 *
	 * @return  void
	 */
	public function onAfterGetItem(&$model, &$record)
	{
	}
}
