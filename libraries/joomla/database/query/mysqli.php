<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
					$orderBy      = $this->selectRowNumber['orderBy'];
					$tmpOffset    = $this->offset;
					$tmpLimit     = $this->limit;
					$this->offset = 0;
					$this->limit  = 0;
					$tmpOrder     = $this->order;
					$this->order  = null;
					$query        = parent::__toString();
					$this->order  = $tmpOrder;
					$this->offset = $tmpOffset;
					$this->limit  = $tmpLimit;

					// Add support for second order by, offset and limit
					$query = PHP_EOL . 'SELECT * FROM (' . $query . PHP_EOL . "ORDER BY $orderBy" . PHP_EOL . ') w';

					if ($this->order)
					{
						$query .= (string) $this->order;
					}

					return $this->processLimit($query, $this->limit, $this->offset);
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
	 * Return the number of the current row.
	 *
	 * @param   string  $orderBy           An expression of ordering for window function.
	 * @param   string  $orderColumnAlias  An alias for new ordering column.
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   3.7.0
	 * @throws  RuntimeException
	 */
	public function selectRowNumber($orderBy, $orderColumnAlias)
	{
		$this->validateRowNumber($orderBy, $orderColumnAlias);
		$this->select("(SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS $orderColumnAlias");

		return $this;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * Usage:
	 * $query->select($query->castAsChar('a'));
	 * $query->select($query->castAsChar('a', 40));
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @param   string  $len    The lenght of the char.
	 *
	 * @return  string  Returns the cast value.
	 *
	 * @since   3.7.0
	 */
	public function castAsChar($value, $len = null)
	{
		if (!$len)
		{
			return $value;
		}
		else
		{
			return ' CAST(' . $value . ' AS CHAR(' . $len . '))';
		}
	}
}
