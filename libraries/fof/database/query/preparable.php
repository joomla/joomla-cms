<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

if (!interface_exists('JDatabaseQueryPreparable'))
{
	/**
	 * Joomla Database Query Preparable Interface.
	 * Adds bind/unbind methods as well as a getBounded() method
	 * to retrieve the stored bounded variables on demand prior to
	 * query execution.
	 *
	 * @since  12.1
	 */
	interface JDatabaseQueryPreparable
	{
		/**
		 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution. Also
		 * removes a variable that has been bounded from the internal bounded array when the passed in value is null.
		 *
		 * @param   string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
		 *                                          the form ':key', but can also be an integer.
		 * @param   mixed           &$value         The value that will be bound. The value is passed by reference to support output
		 *                                          parameters such as those possible with stored procedures.
		 * @param   integer         $dataType       Constant corresponding to a SQL datatype.
		 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters.
		 * @param   array           $driverOptions  Optional driver options to be used.
		 *
		 * @return  FOFDatabaseQuery
		 *
		 * @since   12.1
		 */
		public function bind($key = null, &$value = null, $dataType = PDO::PARAM_STR, $length = 0, $driverOptions = array());

		/**
		 * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then that item is
		 * returned.
		 *
		 * @param   mixed  $key  The bounded variable key to retrieve.
		 *
		 * @return  mixed
		 *
		 * @since   12.1
		 */
		public function &getBounded($key = null);
	}
}

interface FOFDatabaseQueryPreparable extends JDatabaseQueryPreparable
{

}