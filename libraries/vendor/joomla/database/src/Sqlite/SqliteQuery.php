<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlite;

use Joomla\Database\DatabaseQuery;
use Joomla\Database\Pdo\PdoQuery;
use Joomla\Database\Query\QueryElement;

/**
 * SQLite Query Building Class.
 *
 * @since  1.0
 */
class SqliteQuery extends PdoQuery
{
	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string  The completed query.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __toString()
	{
		switch ($this->type)
		{
			case 'select':
				if ($this->selectRowNumber)
				{
					$orderBy          = $this->selectRowNumber['orderBy'];
					$orderColumnAlias = $this->selectRowNumber['orderColumnAlias'];

					$column = "ROW_NUMBER() AS $orderColumnAlias";

					if ($this->select === null)
					{
						$query = PHP_EOL . 'SELECT 1'
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
						$column       = "w.*, $column";
						$this->order  = $tmpOrder;
						$this->offset = $tmpOffset;
						$this->limit  = $tmpLimit;
					}

					// Special sqlite query to count ROW_NUMBER
					$query = PHP_EOL . "SELECT $column"
						. PHP_EOL . "FROM ($query" . PHP_EOL . "ORDER BY $orderBy"
						. PHP_EOL . ') AS w,(SELECT ROW_NUMBER(0)) AS r'
						// Forbid to flatten subqueries.
						. ((string) $this->order ?: PHP_EOL . 'ORDER BY NULL');

					return $this->processLimit($query, $this->limit, $this->offset);
				}

				break;

			case 'querySet':
				$query = $this->querySet;

				if ($query->order || $query->limit || $query->offset)
				{
					// If ORDER BY or LIMIT statement exist then parentheses is required for the first query
					$query = PHP_EOL . "SELECT * FROM ($query)";
				}

				if ($this->merge)
				{
					// Special case for merge
					foreach ($this->merge as $element)
					{
						$query .= (string) $element;
					}
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				return $query;

			case 'update':
				if ($this->join)
				{
					$table = $this->update->getElements();
					$table = $table[0];

					$tableName = explode(' ', $table);
					$tableName = $tableName[0];

					if ($this->columns === null)
					{
						$fields = $this->db->getTableColumns($tableName);

						foreach ($fields as $key => $value)
						{
							$fields[$key] = $key;
						}

						$this->columns = new QueryElement('()', $fields);
					}

					$fields   = $this->columns->getElements();
					$elements = $this->set->getElements();

					foreach ($elements as $nameValue)
					{
						$setArray = explode(' = ', $nameValue, 2);

						if ($setArray[0][0] === '`')
						{
							// Unquote column name
							$setArray[0] = substr($setArray[0], 1, -1);
						}

						$fields[$setArray[0]] = $setArray[1];
					}

					$select = new static($this->db);
					$select->select(array_values($fields))
						->from($table);

					$select->join  = $this->join;
					$select->where = $this->where;

					return 'INSERT OR REPLACE INTO ' . $tableName
						. ' (' . implode(',', array_keys($fields)) . ')'
						. (string) $select;
				}
		}

		return parent::__toString();
	}

	/**
	 * Gets the number of characters in a string.
	 *
	 * Note, use 'length' to find the number of bytes in a string.
	 *
	 * Usage:
	 * $query->select($query->charLength('a'));
	 *
	 * @param   string       $field      A value.
	 * @param   string|null  $operator   Comparison operator between charLength integer value and $condition
	 * @param   string|null  $condition  Integer value to compare charLength with.
	 *
	 * @return  string  The required char length call.
	 *
	 * @since   1.1.0
	 */
	public function charLength($field, $operator = null, $condition = null)
	{
		$statement = 'length(' . $field . ')';

		if ($operator !== null && $condition !== null)
		{
			$statement .= ' ' . $operator . ' ' . $condition;
		}

		return $statement;
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * Usage:
	 * $query->select($query->concatenate(array('a', 'b')));
	 *
	 * @param   string[]     $values     An array of values to concatenate.
	 * @param   string|null  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   1.1.0
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator !== null)
		{
			return implode(' || ' . $this->quote($separator) . ' || ', $values);
		}

		return implode(' || ', $values);
	}

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
		if ($limit > 0 || $offset > 0)
		{
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
		}

		return $query;
	}

	/**
	 * Return the number of the current row.
	 *
	 * Usage:
	 * $query->select('id');
	 * $query->selectRowNumber('ordering,publish_up DESC', 'new_ordering');
	 * $query->from('#__content');
	 *
	 * @param   string  $orderBy           An expression of ordering for window function.
	 * @param   string  $orderColumnAlias  An alias for new ordering column.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function selectRowNumber($orderBy, $orderColumnAlias)
	{
		$this->validateRowNumber($orderBy, $orderColumnAlias);

		return $this;
	}

	/**
	 * Add a query to UNION with the current query.
	 *
	 * Usage:
	 * $query->union('SELECT name FROM  #__foo')
	 * $query->union('SELECT name FROM  #__foo', true)
	 *
	 * @param   DatabaseQuery|string  $query     The DatabaseQuery object or string to union.
	 * @param   boolean               $distinct  True to only return distinct rows from the union.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function union($query, $distinct = true)
	{
		// Set up the name with parentheses, the DISTINCT flag is redundant
		return $this->merge($distinct ? 'UNION SELECT * FROM ()' : 'UNION ALL SELECT * FROM ()', $query);
	}

	/**
	 * Aggregate function to get input values concatenated into a string, separated by delimiter
	 *
	 * Usage:
	 * $query->groupConcat('id', ',');
	 *
	 * @param   string  $expression  The expression to apply concatenation to, this may be a column name or complex SQL statement.
	 * @param   string  $separator   The delimiter of each concatenated value
	 *
	 * @return  string  Input values concatenated into a string, separated by delimiter
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function groupConcat($expression, $separator = ',')
	{
		return 'group_concat(' . $expression . ', ' . $this->quote($separator) . ')';
	}
}
