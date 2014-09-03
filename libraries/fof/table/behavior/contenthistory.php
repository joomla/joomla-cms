<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework table behavior class for content History
 *
 * @package  FrameworkOnFramework
 * @since    2.2.0
 */
class FOFTableBehaviorContenthistory extends FOFTableBehavior
{
	/**
	 * The event which runs after storing (saving) data to the database
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 *
	 * @return  boolean  True to allow saving without an error
	 */
	public function onAfterStore(&$table)
	{
		$aliasParts = explode('.', $table->getContentType());
		$table->checkContentType();

		if (JComponentHelper::getParams($aliasParts[0])->get('save_history', 0))
		{
			$historyHelper = new JHelperContenthistory($table->getContentType());
			$historyHelper->store($table);
		}

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
		$aliasParts = explode('.', $table->getContentType());

		if (JComponentHelper::getParams($aliasParts[0])->get('save_history', 0))
		{
			$historyHelper = new JHelperContenthistory($table->getContentType());
			$historyHelper->deleteHistory($table);
		}

		return true;
	}
}
