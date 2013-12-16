<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * FrameworkOnFramework table behavior class. It defines the events which are
 * called by a Table.
 *
 * @codeCoverageIgnore
 * @package  FrameworkOnFramework
 * @since    2.1
 */
abstract class FOFTableBehavior extends JEvent
{
	/**
	 * This event runs before binding data to the table
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   array     &$data   The data to bind
	 *
	 * @return  boolean  True on success
	 */
	public function onBeforeBind(&$table, &$data)
	{
		return true;
	}

	/**
	 * The event which runs after binding data to the table
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   object|array  &$src  The data to bind
	 *
	 * @return  boolean  True on success
	 */
	public function onAfterBind(&$table, &$src)
	{
		return true;
	}

	/**
	 * The event which runs after loading a record from the database
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   boolean  &$result  Did the load succeeded?
	 *
	 * @return  void
	 */
	public function onAfterLoad(&$table, &$result)
	{

	}

	/**
	 * The event which runs before storing (saving) data to the database
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow saving
	 */
	public function onBeforeStore(&$table, $updateNulls)
	{
		return true;
	}

	/**
	 * The event which runs after storing (saving) data to the database
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * 
	 * @return  boolean  True to allow saving without an error
	 */
	public function onAfterStore(&$table)
	{
		return true;
	}

	/**
	 * The event which runs before moving a record
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow moving
	 */
	public function onBeforeMove(&$table, $updateNulls) 
	{
		return true;
	}

	/**
	 * The event which runs after moving a record
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * 
	 * @return  boolean  True to allow moving without an error
	 */
	public function onAfterMove(&$table)
	{
		return true;
	}

	/**
	 * The event which runs before reordering a table
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   string  $where  The WHERE clause of the SQL query to run on reordering (record filter)
	 *
	 * @return  boolean  True to allow reordering
	 */
	public function onBeforeReorder(&$table, $where = '')
	{
		return true;
	}

	/**
	 * The event which runs after reordering a table
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * 
	 * @return  boolean  True to allow the reordering to complete without an error
	 */
	public function onAfterReorder(&$table)
	{
		return true;
	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	public function onBeforeDelete(&$table, $oid)
	{
		return true;
	}

	/**
	 * The event which runs after deleting a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record which was deleted
	 *
	 * @return  boolean  True to allow the deletion without errors
	 */
	public function onAfterDelete(&$table, $oid)
	{
		return true;
	}

	/**
	 * The event which runs before hitting a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record to hit
	 * @param   boolean  $log  Should we log the hit?
	 *
	 * @return  boolean  True to allow the hit
	 */
	public function onBeforeHit(&$table, $oid, $log)
	{
		return true;
	}

	/**
	 * The event which runs after hitting a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record which was hit
	 *
	 * @return  boolean  True to allow the hitting without errors
	 */
	public function onAfterHit(&$table, $oid)
	{
		return true;
	}

	/**
	 * The even which runs before copying a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record being copied
	 *
	 * @return  boolean  True to allow the copy to take place
	 */
	public function onBeforeCopy(&$table, $oid)
	{
		return true;
	}

	/**
	 * The even which runs after copying a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record which was copied (not the new one)
	 *
	 * @return  boolean  True to allow the copy without errors
	 */
	public function onAfterCopy(&$table, $oid)
	{
		return true;
	}

	/**
	 * The event which runs before a record is (un)published
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer|array  &$cid     The PK IDs of the records being (un)published
	 * @param   integer        $publish  1 to publish, 0 to unpublish
	 *
	 * @return  boolean  True to allow the (un)publish to proceed
	 */
	public function onBeforePublish(&$table, &$cid, $publish)
	{
		return true;
	}

	/**
	 * The event which runs after the object is reset to its default values.
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * 
	 * @return  boolean  True to allow the reset to complete without errors
	 */
	public function onAfterReset(&$table)
	{
		return true;
	}

	/**
	 * The even which runs before the object is reset to its default values.
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * 
	 * @return  boolean  True to allow the reset to complete
	 */
	public function onBeforeReset(&$table)
	{
		return true;
	}
}
