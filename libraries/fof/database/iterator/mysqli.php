<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * MySQLi database iterator.
 */
class FOFDatabaseIteratorMysqli extends FOFDatabaseIterator
{
	/**
	 * Get the number of rows in the result set for the executed SQL given by the cursor.
	 *
	 * @return  integer  The number of rows in the result set.
	 *
	 * @see     Countable::count()
	 */
	public function count()
	{
		return @mysqli_num_rows($this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchObject()
	{
		return @mysqli_fetch_object($this->cursor, $this->class);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @return  void
	 */
	protected function freeResult()
	{
		@mysqli_free_result($this->cursor);
	}
}
