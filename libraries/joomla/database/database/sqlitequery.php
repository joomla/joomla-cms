<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.databasequery');

/**
 * Query Building Class.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       ¿
 */
class JDatabaseQuerySQLite extends JDatabaseQuery
{


	/* W I P =;) */


	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   11.1
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			return implode(' || ' . $this->quote($separator) . ' || ', $values);
		}
		else
		{
			return implode(' || ', $values);
		}
	}

	/**
	 * Gets the number of characters in a string.
	 *
	 * Note, use 'length' to find the number of bytes in a string.
	 *
	 * Usage:
	 * $query->select($query->charLength('a'));
	 *
	 * @param   string  $field  A value.
	 *
	 * @return  string  The required char length call.
	 *
	 * @since 11.1
	 */
	public function charLength($field)
	{
		return 'LENGTH(' . $field . ')';
	}

	/**
	 * Gets the current date and time.
	 *
	 * Usage:
	 * $query->where('published_up < '.$query->currentTimestamp());
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function currentTimestamp()
	{
		return 'CURRENT_TIMESTAMP';
	}


}
