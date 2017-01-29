<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Query Building Class.
 *
 * @since  11.3
 */
class JDatabaseQueryPostgresql extends JDatabaseQuery implements JDatabaseQueryLimitable
{
	/**
	 * @var    object  The FOR UPDATE element used in "FOR UPDATE"  lock
	 * @since  11.3
	 */
	protected $forUpdate = null;

	/**
	 * @var    object  The FOR SHARE element used in "FOR SHARE"  lock
	 * @since  11.3
	 */
	protected $forShare = null;

	/**
	 * @var    object  The NOWAIT element used in "FOR SHARE" and "FOR UPDATE" lock
	 * @since  11.3
	 */
	protected $noWait = null;

	/**
	 * @var    object  The LIMIT element
	 * @since  11.3
	 */
	protected $limit = null;

	/**
	 * @var    object  The OFFSET element
	 * @since  11.3
	 */
	protected $offset = null;

	/**
	 * @var    object  The RETURNING element of INSERT INTO
	 * @since  11.3
	 */
	protected $returning = null;

	/**
	 * Magic function to convert the query to a string, only for postgresql specific query
	 *
	 * @return  string	The completed query.
	 *
	 * @since   11.3
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->type)
		{
			case 'select':
				if ($this->selectRowNumber && $this->selectRowNumber['native'] === false)
				{
					// Workaround for postgresql version less than 8.4.0
					try
					{
						$this->db->setQuery('CREATE TEMP SEQUENCE ROW_NUMBER');
						$this->db->execute();
					}
					catch (JDatabaseExceptionExecuting $e)
					{
						// Do nothing, sequence exists
					}

					$orderBy          = $this->selectRowNumber['orderBy'];
					$orderColumnAlias = $this->selectRowNumber['orderColumnAlias'];

					$columns = "nextval('ROW_NUMBER') - 1 AS $orderColumnAlias";

					if ($this->select === null)
					{
						$query = PHP_EOL . "SELECT 1"
							. (string) $this->from
							. (string) $this->where;
					}
					else
					{
						$tmpOffset    = $this->offset;
						$tmpLimit     = $this->limit;
						$this->offset = 0;
						$this->limit  = 0;
						$tmpOrder     = $this->order;
						$this->order  = null;
						$query        = parent::__toString();
						$columns      = "w.*, $columns";
						$this->order  = $tmpOrder;
						$this->offset = $tmpOffset;
						$this->limit  = $tmpLimit;
					}

					// Add support for second order by, offset and limit
					$query = PHP_EOL . "SELECT $columns FROM (" . $query . PHP_EOL . "ORDER BY $orderBy"
						. PHP_EOL . ") w,(SELECT setval('ROW_NUMBER', 1)) AS r";

					if ($this->order)
					{
						$query .= (string) $this->order;
					}

					break;
				}

				$query .= (string) $this->select;
				$query .= (string) $this->from;

				if ($this->join)
				{
					// Special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->selectRowNumber)
				{
					if ($this->order)
					{
						$query .= (string) $this->order;
					}

					break;
				}

				if ($this->group)
				{
					$query .= (string) $this->group;
				}

				if ($this->having)
				{
					$query .= (string) $this->having;
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				if ($this->forUpdate)
				{
					$query .= (string) $this->forUpdate;
				}
				else
				{
					if ($this->forShare)
					{
						$query .= (string) $this->forShare;
					}
				}

				if ($this->noWait)
				{
					$query .= (string) $this->noWait;
				}

				break;

			case 'update':
				$query .= (string) $this->update;
				$query .= (string) $this->set;

				if ($this->join)
				{
					$tmpFrom     = $this->from;
					$tmpWhere    = $this->where ? clone $this->where : null;
					$this->from  = null;

					// Workaround for special case of JOIN with UPDATE
					foreach ($this->join as $join)
					{
						$joinElem = $join->getElements();

						$joinArray = preg_split('/\sON\s/i', $joinElem[0], 2);

						$this->from($joinArray[0]);

						if (isset($joinArray[1]))
						{
							$this->where($joinArray[1]);
						}
					}

					$query .= (string) $this->from;

					if ($this->where)
					{
						$query .= (string) $this->where;
					}

					$this->from  = $tmpFrom;
					$this->where = $tmpWhere;
				}
				elseif ($this->where)
				{
					$query .= (string) $this->where;
				}

				break;

			case 'insert':
				$query .= (string) $this->insert;

				if ($this->values)
				{
					if ($this->columns)
					{
						$query .= (string) $this->columns;
					}

					$elements = $this->values->getElements();

					if (!($elements[0] instanceof $this))
					{
						$query .= ' VALUES ';
					}

					$query .= (string) $this->values;

					if ($this->returning)
					{
						$query .= (string) $this->returning;
					}
				}

				break;

			default:
				$query = parent::__toString();
				break;
		}

		if ($this instanceof JDatabaseQueryLimitable)
		{
			$query = $this->processLimit($query, $this->limit, $this->offset);
		}

		return $query;
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  JDatabaseQueryPostgresql  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case 'limit':
				$this->limit = null;
				break;

			case 'offset':
				$this->offset = null;
				break;

			case 'forUpdate':
				$this->forUpdate = null;
				break;

			case 'forShare':
				$this->forShare = null;
				break;

			case 'noWait':
				$this->noWait = null;
				break;

			case 'returning':
				$this->returning = null;
				break;

			case 'select':
			case 'update':
			case 'delete':
			case 'insert':
			case 'from':
			case 'join':
			case 'set':
			case 'where':
			case 'group':
			case 'having':
			case 'order':
			case 'columns':
			case 'values':
				parent::clear($clause);
				break;

			default:
				$this->type = null;
				$this->limit = null;
				$this->offset = null;
				$this->forUpdate = null;
				$this->forShare = null;
				$this->noWait = null;
				$this->returning = null;
				parent::clear($clause);
				break;
		}

		return $this;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * Usage:
	 * $query->select($query->castAsChar('a'));
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @return  string  Returns the cast value.
	 *
	 * @since   11.3
	 */
	public function castAsChar($value)
	{
		return $value . '::text';
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * Usage:
	 * $query->select($query->concatenate(array('a', 'b')));
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   11.3
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
	 * Gets the current date and time.
	 *
	 * @return  string  Return string used in query to obtain
	 *
	 * @since   11.3
	 */
	public function currentTimestamp()
	{
		return 'NOW()';
	}

	/**
	 * Sets the FOR UPDATE lock on select's output row
	 *
	 * @param   string  $table_name  The table to lock
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to ',' .
	 *
	 * @return  JDatabaseQueryPostgresql  FOR UPDATE query element
	 *
	 * @since   11.3
	 */
	public function forUpdate($table_name, $glue = ',')
	{
		$this->type = 'forUpdate';

		if (is_null($this->forUpdate))
		{
			$glue            = strtoupper($glue);
			$this->forUpdate = new JDatabaseQueryElement('FOR UPDATE', 'OF ' . $table_name, "$glue ");
		}
		else
		{
			$this->forUpdate->append($table_name);
		}

		return $this;
	}

	/**
	 * Sets the FOR SHARE lock on select's output row
	 *
	 * @param   string  $table_name  The table to lock
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to ',' .
	 *
	 * @return  JDatabaseQueryPostgresql  FOR SHARE query element
	 *
	 * @since   11.3
	 */
	public function forShare($table_name, $glue = ',')
	{
		$this->type = 'forShare';

		if (is_null($this->forShare))
		{
			$glue           = strtoupper($glue);
			$this->forShare = new JDatabaseQueryElement('FOR SHARE', 'OF ' . $table_name, "$glue ");
		}
		else
		{
			$this->forShare->append($table_name);
		}

		return $this;
	}

	/**
	 * Used to get a string to extract year from date column.
	 *
	 * Usage:
	 * $query->select($query->year($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing year to be extracted.
	 *
	 * @return  string  Returns string to extract year from a date.
	 *
	 * @since   12.1
	 */
	public function year($date)
	{
		return 'EXTRACT (YEAR FROM ' . $date . ')';
	}

	/**
	 * Used to get a string to extract month from date column.
	 *
	 * Usage:
	 * $query->select($query->month($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing month to be extracted.
	 *
	 * @return  string  Returns string to extract month from a date.
	 *
	 * @since   12.1
	 */
	public function month($date)
	{
		return 'EXTRACT (MONTH FROM ' . $date . ')';
	}

	/**
	 * Used to get a string to extract day from date column.
	 *
	 * Usage:
	 * $query->select($query->day($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing day to be extracted.
	 *
	 * @return  string  Returns string to extract day from a date.
	 *
	 * @since   12.1
	 */
	public function day($date)
	{
		return 'EXTRACT (DAY FROM ' . $date . ')';
	}

	/**
	 * Used to get a string to extract hour from date column.
	 *
	 * Usage:
	 * $query->select($query->hour($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing hour to be extracted.
	 *
	 * @return  string  Returns string to extract hour from a date.
	 *
	 * @since   12.1
	 */
	public function hour($date)
	{
		return 'EXTRACT (HOUR FROM ' . $date . ')';
	}

	/**
	 * Used to get a string to extract minute from date column.
	 *
	 * Usage:
	 * $query->select($query->minute($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing minute to be extracted.
	 *
	 * @return  string  Returns string to extract minute from a date.
	 *
	 * @since   12.1
	 */
	public function minute($date)
	{
		return 'EXTRACT (MINUTE FROM ' . $date . ')';
	}

	/**
	 * Used to get a string to extract seconds from date column.
	 *
	 * Usage:
	 * $query->select($query->second($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing second to be extracted.
	 *
	 * @return  string  Returns string to extract second from a date.
	 *
	 * @since   12.1
	 */
	public function second($date)
	{
		return 'EXTRACT (SECOND FROM ' . $date . ')';
	}

	/**
	 * Sets the NOWAIT lock on select's output row
	 *
	 * @return  JDatabaseQueryPostgresql  NO WAIT query element
	 *
	 * @since   11.3
	 */
	public function noWait ()
	{
		$this->type = 'noWait';

		if (is_null($this->noWait))
		{
			$this->noWait = new JDatabaseQueryElement('NOWAIT', null);
		}

		return $this;
	}

	/**
	 * Set the LIMIT clause to the query
	 *
	 * @param   integer  $limit  An int of how many row will be returned
	 *
	 * @return  JDatabaseQueryPostgresql  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function limit($limit = 0)
	{
		if (is_null($this->limit))
		{
			$this->limit = new JDatabaseQueryElement('LIMIT', (int) $limit);
		}

		return $this;
	}

	/**
	 * Set the OFFSET clause to the query
	 *
	 * @param   integer  $offset  An int for skipping row
	 *
	 * @return  JDatabaseQueryPostgresql  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function offset($offset = 0)
	{
		if (is_null($this->offset))
		{
			$this->offset = new JDatabaseQueryElement('OFFSET', (int) $offset);
		}

		return $this;
	}

	/**
	 * Add the RETURNING element to INSERT INTO statement.
	 *
	 * @param   mixed  $pkCol  The name of the primary key column.
	 *
	 * @return  JDatabaseQueryPostgresql  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function returning($pkCol)
	{
		if (is_null($this->returning))
		{
			$this->returning = new JDatabaseQueryElement('RETURNING', $pkCol);
		}

		return $this;
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
	 * @return  JDatabaseQueryPostgresql  Returns this object to allow chaining.
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
	 * Method to modify a query already in string format with the needed
	 * additions to make the query limited to a particular number of
	 * results, or start at a particular offset.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		if ($limit > 0)
		{
			$query .= ' LIMIT ' . $limit;
		}

		if ($offset > 0)
		{
			$query .= ' OFFSET ' . $offset;
		}

		return $query;
	}

	/**
	 * Add to the current date and time in Postgresql.
	 * Usage:
	 * $query->select($query->dateAdd());
	 * Prefixing the interval with a - (negative sign) will cause subtraction to be used.
	 *
	 * @param   datetime  $date      The date to add to
	 * @param   string    $interval  The string representation of the appropriate number of units
	 * @param   string    $datePart  The part of the date to perform the addition on
	 *
	 * @return  string  The string with the appropriate sql for addition of dates
	 *
	 * @since   13.1
	 * @note    Not all drivers support all units. Check appropriate references
	 * @link    http://www.postgresql.org/docs/9.0/static/functions-datetime.html.
	 */
	public function dateAdd($date, $interval, $datePart)
	{
		if (substr($interval, 0, 1) != '-')
		{
			return "timestamp '" . $date . "' + interval '" . $interval . " " . $datePart . "'";
		}
		else
		{
			return "timestamp '" . $date . "' - interval '" . ltrim($interval, '-') . " " . $datePart . "'";
		}
	}

	/**
	 * Return correct regexp operator for Postgresql.
	 *
	 * Ensure that the regexp operator is Postgresql compatible.
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
		return ' ~* ' . $value;
	}

	/**
	 * Return correct rand() function for Postgresql.
	 *
	 * Ensure that the rand() function is Postgresql compatible.
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
		return ' RANDOM() ';
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
	 * @return  string  Returns the find_in_set() postgresql translation.
	 *
	 * @since   3.7.0
	 */
	public function findInSet($value, $set)
	{
		return " $value = ANY (string_to_array($set, ',')::integer[]) ";
	}

	/**
	 * Return the number of the current row.
	 *
	 * @param   string  $orderBy           An expression of ordering for window function.
	 * @param   string  $orderColumnAlias  An alias for new ordering column.
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  RuntimeException
	 */
	public function selectRowNumber($orderBy, $orderColumnAlias)
	{
		$this->validateRowNumber($orderBy, $orderColumnAlias);

		if (version_compare($this->db->getVersion(), '8.4.0') >= 0)
		{
			$this->selectRowNumber['native'] = true;
			$this->select("ROW_NUMBER() OVER (ORDER BY $orderBy) AS $orderColumnAlias");
		}
		else
		{
			$this->selectRowNumber['native'] = false;
		}

		return $this;
	}
}
