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

if (!interface_exists('JDatabaseQueryLimitable'))
{
	/**
	 * Joomla Database Query Limitable Interface.
	 * Adds bind/unbind methods as well as a getBounded() method
	 * to retrieve the stored bounded variables on demand prior to
	 * query execution.
	 *
	 * @since  12.1
	 */
	interface JDatabaseQueryLimitable
	{
		/**
		 * Method to modify a query already in string format with the needed
		 * additions to make the query limited to a particular number of
		 * results, or start at a particular offset. This method is used
		 * automatically by the __toString() method if it detects that the
		 * query implements the FOFDatabaseQueryLimitable interface.
		 *
		 * @param   string   $query   The query in string format
		 * @param   integer  $limit   The limit for the result set
		 * @param   integer  $offset  The offset for the result set
		 *
		 * @return  string
		 *
		 * @since   12.1
		 */
		public function processLimit($query, $limit, $offset = 0);

		/**
		 * Sets the offset and limit for the result set, if the database driver supports it.
		 *
		 * Usage:
		 * $query->setLimit(100, 0); (retrieve 100 rows, starting at first record)
		 * $query->setLimit(50, 50); (retrieve 50 rows, starting at 50th record)
		 *
		 * @param   integer  $limit   The limit for the result set
		 * @param   integer  $offset  The offset for the result set
		 *
		 * @return  FOFDatabaseQuery  Returns this object to allow chaining.
		 *
		 * @since   12.1
		 */
		public function setLimit($limit = 0, $offset = 0);
	}
}

/**
 * Joomla Database Query Limitable Interface.
 * Adds bind/unbind methods as well as a getBounded() method
 * to retrieve the stored bounded variables on demand prior to
 * query execution.
 *
 * @since  12.1
 */
interface FOFDatabaseQueryLimitable extends JDatabaseQueryLimitable
{
}
