<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Oracle;

use Joomla\Database\Pdo\PdoQuery;

/**
 * Oracle Query Building Class.
 *
 * @since  1.0
 */
class OracleQuery extends PdoQuery
{
	/**
	 * Method to modify a query already in string format with the needed additions to make the query limited to a particular number of
	 * results, or start at a particular offset.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		// Check if we need to mangle the query.
		if ($limit || $offset)
		{
			$query = 'SELECT joomla2.*
		              FROM (
		                  SELECT joomla1.*, ROWNUM AS joomla_db_rownum
		                  FROM (
		                      ' . $query . '
		                  ) joomla1
		              ) joomla2';

			// Check if the limit value is greater than zero.
			if ($limit > 0)
			{
				$query .= ' WHERE joomla2.joomla_db_rownum BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $limit);
			}
			else
			{
				// Check if there is an offset and then use this.
				if ($offset)
				{
					$query .= ' WHERE joomla2.joomla_db_rownum > ' . ($offset + 1);
				}
			}
		}

		return $query;
	}
}
