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
 * @since       11.1
 */
class JDatabaseQueryMySQL extends JDatabaseQuery
{
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
   function concatenate($values, $separator = null)
   {
		if ($separator) {
			$concat_string = 'CONCAT_WS('.$this->quote($separator);

			foreach($values as $value)
			{
				$concat_string .= ', '.$value;
			}

			return $concat_string.')';
		}
		else {
			return 'CONCAT('.implode(',', $values).')';
		}
	}
}