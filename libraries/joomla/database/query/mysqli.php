<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Query Building Class.
 *
 * @since  11.1
 */
class JDatabaseQueryMysqli extends JDatabaseQuery implements JDatabaseQueryLimitable
{
	/**
	 * @var    integer  The offset for the result set.
	 * @since  12.1
	 */
	protected $offset;

	/**
	 * @var    integer  The limit for the result set.
	 * @since  12.1
	 */
	protected $limit;

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string  The completed query.
	 *
	 * @since   11.1
	 */
	public function __toString()
	{
		switch ($this->type)
		{
			case 'select':
				if ($this->selectRowNumber)
				{
					$orderBy = $this->selectRowNumber['orderBy'];

					if ($this->selectRowNumber['partitionBy'] !== null && $this->selectRowNumber['partitionColumnAlias'] !== null)
					{
						$orderBy = $this->selectRowNumber['partitionBy'] . ',' . $orderBy;
					}

					$tmpOffset    = $this->offset;
					$tmpLimit     = $this->limit;
					$this->offset = 0;
					$this->limit  = 0;
					$tmpOrder     = $this->order;
					$this->order  = new JDatabaseQueryElement('ORDER BY', $orderBy);
					$query        = parent::__toString();
					$this->order  = $tmpOrder;
					$this->offset = $tmpOffset;
					$this->limit  = $tmpLimit;

					if ($this->offset || $this->limit || $this->order)
					{
						// Add support for second order by, offset and limit
						$query = PHP_EOL . "SELECT * FROM ( " . (string) $query . " ) w" . (string) $this->order;
						$query = $this->processLimit($query, $this->limit, $this->offset);
					}

					return $query;
				}
		}

		return parent::__toString();
	}

	/**
	 * Method to modify a query already in string format with the needed
	 * additions to make the query limited to a particular number of
	 * results, or start at a particular offset.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return string
	 *
	 * @since 12.1
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		if ($limit > 0 && $offset > 0)
		{
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
		}
		elseif ($limit > 0)
		{
			$query .= ' LIMIT ' . $limit;
		}

		return $query;
	}

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
			$concat_string = 'CONCAT_WS(' . $this->quote($separator);

			foreach ($values as $value)
			{
				$concat_string .= ', ' . $value;
			}

			return $concat_string . ')';
		}
		else
		{
			return 'CONCAT(' . implode(',', $values) . ')';
		}
	}

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
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   12.1
	 */
	public function setLimit($limit = 0, $offset = 0)
	{
		$this->limit  = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Return correct regexp operator for mysqli.
	 *
	 * Ensure that the regexp operator is mysqli compatible.
	 *
	 * Usage:
	 * $query->where('field ' . $query->regexp($search));
	 *
	 * @param   string  $value  The regex pattern.
	 *
	 * @return  string  Returns the regex operator.
	 *
	 * @since   11.3
	 */
	public function regexp($value)
	{
		return ' REGEXP ' . $value;
	}

	/**
	 * Return correct rand() function for Mysql.
	 *
	 * Ensure that the rand() function is Mysql compatible.
	 * 
	 * Usage:
	 * $query->Rand();
	 * 
	 * @return  string  The correct rand function.
	 *
	 * @since   3.5
	 */
	public function Rand()
	{
		return ' RAND() ';
	}

	/**
	 * Find a value in a varchar used like a set.
	 *
	 * Ensure that the value is an integer before passing to the method.
	 *
	 * Usage:
	 * $query->findInSet((int) $parent->id, 'a.assigned_cat_ids')
	 *
	 * @param   string  $value  The value to search for.
	 *
	 * @param   string  $set    The set of values.
	 *
	 * @return  string  Returns the find_in_set() Mysql translation.
	 *
	 * @since   3.7.0
	 */
	public function findInSet($value, $set)
	{
		return " find_in_set(" . $value . ", " . $set . ")";
	}

	/**
	 * Return the number of the current row, support for partition, starting from 1
	 *
	 * @param   string  $orderBy               An expression of ordering for window function.
	 * @param   string  $orderColumnAlias      An alias for new ordering column.
	 * @param   string  $partitionBy           An expression of grouping for window function.
	 * @param   string  $partitionColumnAlias  An alias for calculated grouping column.
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  RuntimeException
	 */
	public function selectRowNumber($orderBy, $orderColumnAlias, $partitionBy = null, $partitionColumnAlias = null)
	{
		$this->validateRowNumber($orderBy, $orderColumnAlias, $partitionBy, $partitionColumnAlias);

		if ($partitionBy !== null && $partitionColumnAlias !== null)
		{
			$column = "(SELECT @rownum := IF(@group = CONCAT_WS(',', $partitionBy)"
				. " OR ((@group := CONCAT_WS(',', $partitionBy)) AND 0), @rownum + 1, 1)"
				. " FROM (SELECT @rownum := 0, @group := '') AS r) AS $orderColumnAlias";
		}
		else
		{
			$column = "(SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS $orderColumnAlias";
		}

		$this->select($column);

		return $this;
	}
}
