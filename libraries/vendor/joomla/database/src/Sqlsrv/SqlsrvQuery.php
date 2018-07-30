<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlsrv;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Database\Query\LimitableInterface;
use Joomla\Database\Query\QueryElement;

/**
 * SQL Server Query Building Class.
 *
 * @since  1.0
 */
class SqlsrvQuery extends DatabaseQuery implements LimitableInterface
{
	/**
	 * The character(s) used to quote SQL statement names such as table names or field names, etc.
	 *
	 * If a single character string the same character is used for both sides of the quoted name, else the first character will be used for the
	 * opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $name_quotes = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $null_date = '1900-01-01 00:00:00';

	/**
	 * Holds key / value pair of bound objects.
	 *
	 * @var    mixed
	 * @since  1.5.0
	 */
	protected $bounded = array();

	/**
	 * The offset for the result set.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $offset;

	/**
	 * The limit for the result set.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $limit;

	/**
	 * The list of zero or null representation of a datetime.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $nullDatetimeList = ['1900-01-01 00:00:00'];

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string  The completed query.
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		// For the moment if we are given a query string we can't effectively process limits, fix this later
		if ($this->sql)
		{
			return $this->sql;
		}

		$query = '';

		switch ($this->type)
		{
			case 'select':
				// Add required aliases for offset or fixGroupColumns method
				$columns = $this->fixSelectAliases();

				$query = (string) $this->select;

				if ($this->group)
				{
					$this->fixGroupColumns($columns);
				}

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

				if ($this->selectRowNumber === null)
				{
					if ($this->group)
					{
						$query .= (string) $this->group;
					}

					if ($this->having)
					{
						$query .= (string) $this->having;
					}

					if ($this->merge)
					{
						// Special case for merge
						foreach ($this->merge as $idx => $element)
						{
							$query .= (string) $element . ' AS merge_' . (int) ($idx + 1);
						}
					}
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}
				else
				{
					$query .= PHP_EOL . '/*ORDER BY (SELECT 0)*/';
				}

				$query = $this->processLimit($query, $this->limit, $this->offset);

				break;

			case 'querySet':
				$query = $this->querySet;

				if ($query->order || $query->limit || $query->offset)
				{
					// If ORDER BY or LIMIT statement exist then parentheses is required for the first query
					$query = PHP_EOL . "SELECT * FROM ($query) AS merge_0";
				}

				if ($this->merge)
				{
					// Special case for merge
					foreach ($this->merge as $idx => $element)
					{
						$query .= (string) $element . ' AS merge_' . (int) ($idx + 1);
					}
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				$query = $this->processLimit($query, $this->limit, $this->offset);

				break;

			case 'insert':
				$query .= (string) $this->insert;

				// Set method
				if ($this->set)
				{
					$query .= (string) $this->set;
				}
				// Columns-Values method
				elseif ($this->values)
				{
					if ($this->columns)
					{
						$query .= (string) $this->columns;
					}

					$elements = $this->insert->getElements();
					$tableName = array_shift($elements);

					$query .= 'VALUES ';
					$query .= (string) $this->values;

					if ($this->autoIncrementField)
					{
						$query = 'SET IDENTITY_INSERT ' . $tableName . ' ON;' . $query . 'SET IDENTITY_INSERT ' . $tableName . ' OFF;';
					}

					if ($this->where)
					{
						$query .= (string) $this->where;
					}
				}

				break;

			case 'delete':
				$query .= (string) $this->delete;
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

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			case 'update':
				if ($this->join)
				{
					$tmpUpdate    = $this->update;
					$tmpFrom      = $this->from;
					$this->update = null;
					$this->from   = null;

					$updateElem  = $tmpUpdate->getElements();
					$updateArray = explode(' ', $updateElem[0]);

					// Use table alias if exists
					$this->update(end($updateArray));
					$this->from($updateElem[0]);

					$query .= (string) $this->update;
					$query .= (string) $this->set;
					$query .= (string) $this->from;

					$this->update = $tmpUpdate;
					$this->from   = $tmpFrom;

					// Special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}
				else
				{
					$query .= (string) $this->update;
					$query .= (string) $this->set;
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			default:
				$query = parent::__toString();

				break;
		}

		return $query;
	}

	/**
	 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution. Also
	 * removes a variable that has been bounded from the internal bounded array when the passed in value is null.
	 *
	 * @param   string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
	 *                                          the form ':key', but can also be an integer.
	 * @param   mixed           $value          The value that will be bound. The value is passed by reference to support output
	 *                                          parameters such as those possible with stored procedures.
	 * @param   string          $dataType       The corresponding bind type. (Unused)
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters. (Unused)
	 * @param   array           $driverOptions  Optional driver options to be used. (Unused)
	 *
	 * @return  SqlsrvQuery
	 *
	 * @since   1.5.0
	 */
	public function bind($key = null, &$value = null, $dataType = ParameterType::STRING, $length = 0, $driverOptions = array())
	{
		// Case 1: Empty Key (reset $bounded array)
		if (empty($key))
		{
			$this->bounded = array();

			return $this;
		}

		// Case 2: Key Provided, null value (unset key from $bounded array)
		if (is_null($value))
		{
			if (isset($this->bounded[$key]))
			{
				unset($this->bounded[$key]);
			}

			return $this;
		}

		$obj           = new \stdClass;
		$obj->value    = &$value;
		$obj->dataType = $dataType;

		// Case 3: Simply add the Key/Value into the bounded array
		$this->bounded[$key] = $obj;

		return $this;
	}

	/**
	 * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then that item is
	 * returned.
	 *
	 * @param   mixed  $key  The bounded variable key to retrieve.
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function &getBounded($key = null)
	{
		if (empty($key))
		{
			return $this->bounded;
		}

		if (isset($this->bounded[$key]))
		{
			return $this->bounded[$key];
		}
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  SqlsrvQuery  Returns this object to allow chaining.
	 *
	 * @since   1.5.0
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case null:
				$this->bounded = array();
				break;
		}

		return parent::clear($clause);
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @return  string  Returns the cast value.
	 *
	 * @since   1.0
	 */
	public function castAsChar($value)
	{
		return 'CAST(' . $value . ' as NVARCHAR(10))';
	}

	/**
	 * Gets the function to determine the length of a character string.
	 *
	 * @param   string  $field      A value.
	 * @param   string  $operator   Comparison operator between charLength integer value and $condition
	 * @param   string  $condition  Integer value to compare charLength with.
	 *
	 * @return  string  The required char length call.
	 *
	 * @since   1.0
	 */
	public function charLength($field, $operator = null, $condition = null)
	{
		return 'DATALENGTH(' . $field . ')' . (isset($operator) && isset($condition) ? ' ' . $operator . ' ' . $condition : '');
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   1.0
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			return '(' . implode('+' . $this->quote($separator) . '+', $values) . ')';
		}

		return '(' . implode('+', $values) . ')';
	}

	/**
	 * Gets the current date and time.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function currentTimestamp()
	{
		return 'GETDATE()';
	}

	/**
	 * Get the length of a string in bytes.
	 *
	 * @param   string  $value  The string to measure.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function length($value)
	{
		return 'LEN(' . $value . ')';
	}

	/**
	 * Add a grouping column to the GROUP clause of the query.
	 *
	 * Usage:
	 * $query->group('id');
	 *
	 * @param   mixed  $columns  A string or array of ordering columns.
	 *
	 * @return  SqlsrvQuery  Returns this object to allow chaining.
	 *
	 * @since   1.5.0
	 */
	public function group($columns)
	{
		if (!($this->db instanceof DatabaseInterface))
		{
			throw new \RuntimeException('JLIB_DATABASE_ERROR_INVALID_DB_OBJECT');
		}

		// Transform $columns into an array for filtering purposes
		is_string($columns) && $columns = explode(',', str_replace(' ', '', $columns));

		// Get the _formatted_ FROM string and remove everything except `table AS alias`
		$fromStr = str_replace(array('[', ']'), '', str_replace('#__', $this->db->getPrefix(), str_replace('FROM ', '', (string) $this->from)));

		// Start setting up an array of alias => table
		list($table, $alias) = preg_split("/\sAS\s/i", $fromStr);

		$tmpCols = $this->db->getTableColumns(trim($table));
		$cols = array();

		foreach ($tmpCols as $name => $type)
		{
			$cols[] = $alias . '.' . $name;
		}

		// Now we need to get all tables from any joins
		// Go through all joins and add them to the tables array
		foreach ($this->join as $join)
		{
			$joinTbl = str_replace(
				'#__',
				$this->db->getPrefix(),
				str_replace(
					']',
					'',
					preg_replace("/.*(#.+\sAS\s[^\s]*).*/i", '$1', (string) $join)
				)
			);

			list($table, $alias) = preg_split("/\sAS\s/i", $joinTbl);

			$tmpCols = $this->db->getTableColumns(trim($table));

			foreach ($tmpCols as $name => $tmpColType)
			{
				$cols[] = $alias . '.' . $name;
			}
		}

		$selectStr = str_replace('SELECT ', '', (string) $this->select);

		// Remove any functions (e.g. COUNT(), SUM(), CONCAT())
		$selectCols = preg_replace("/([^,]*\([^\)]*\)[^,]*,?)/", '', $selectStr);

		// Remove any "as alias" statements
		$selectCols = preg_replace("/(\sas\s[^,]*)/i", '', $selectCols);

		// Remove any extra commas
		$selectCols = preg_replace('/,{2,}/', ',', $selectCols);

		// Remove any trailing commas and all whitespaces
		$selectCols = trim(str_replace(' ', '', preg_replace('/,?$/', '', $selectCols)));

		// Get an array to compare against
		$selectCols = explode(',', $selectCols);

		// Find all alias.* and fill with proper table column names
		foreach ($selectCols as $key => $aliasColName)
		{
			if (preg_match("/.+\*/", $aliasColName, $match))
			{
				// Grab the table alias minus the .*
				$aliasStar = preg_replace("/(.+)\.\*/", '$1', $aliasColName);

				// Unset the array key
				unset($selectCols[$key]);

				// Get the table name
				$tableColumns = preg_grep("/{$aliasStar}\.+/", $cols);
				$columns = array_merge($columns, $tableColumns);
			}
		}

		// Finally, get a unique string of all column names that need to be included in the group statement
		$columns = array_unique(array_merge($columns, $selectCols));
		$columns = implode(',', $columns);

		// Recreate it every time, to ensure we have checked _all_ select statements
		$this->group = new QueryElement('GROUP BY', $columns);

		return $this;
	}

	/**
	 * Get the function to return a random floating-point value
	 *
	 * Usage:
	 * $query->rand();
	 *
	 * @return  string
	 *
	 * @since   1.5.0
	 */
	public function rand()
	{
		return ' NEWID() ';
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
	 * @param   string  $set    The set of values.
	 *
	 * @return  string  A representation of the MySQL find_in_set() function for the driver.
	 *
	 * @since   1.5.0
	 */
	public function findInSet($value, $set)
	{
		return "CHARINDEX(',$value,', ',' + $set + ',') > 0";
	}

	/**
	 * Add required aliases to columns for select statement in subquery.
	 *
	 * @return  array[]  Array of columns with added missing aliases.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function fixSelectAliases()
	{
		$operators = [
			'+' => '',
			'-' => '',
			'*' => '',
			'/' => '',
			'%' => '',
			'&' => '',
			'|' => '',
			'~' => '',
			'^' => '',
		];

		// Split into array and remove comments
		$columns = $this->splitSqlExpression(implode(',', $this->select->getElements()));

		foreach ($columns as $i => $column)
		{
			$size = count($column);

			if ($size == 0)
			{
				continue;
			}

			if ($size > 2 && strcasecmp($column[$size - 2], 'AS') === 0)
			{
				// Alias exists, replace it to uppercase
				$columns[$i][$size - 2] = 'AS';
				continue;
			}

			if ($i == 0 && stripos(' DISTINCT ALL ', " $column[0] ") !== false)
			{
				// This words are reserved, they are not column names
				array_shift($column);
				$size--;
			}

			$lastWord = strtoupper($column[$size - 1]);
			$length   = strlen($lastWord);
			$lastChar = $lastWord[$length - 1];

			if ($lastChar == '*')
			{
				// Skip on wildcard
				continue;
			}

			if ($lastChar == ')'
				|| ($size == 1 && $lastChar == "'")
				|| $lastWord[0] == '@'
				|| $lastWord == 'NULL'
				|| $lastWord == 'END'
				|| is_numeric($lastWord))
			{
				/*
				 * Ends with:
				 * - SQL function
				 * - single static value like 'only '+'string'
				 * - @@var
				 * - NULL
				 * - CASE ... END
				 * - Numeric
				 */
				$columns[$i][] = 'AS';
				$columns[$i][] = $this->quoteName('columnAlias' . $i);
				continue;
			}

			if ($size == 1)
			{
				continue;
			}

			$lastChar2 = substr($column[$size - 2], -1);

			// Check if column ends with  '- a.x' or '- a. x'
			if (isset($operators[$lastChar2]) || ($size > 2 && $lastChar2 === '.' && isset($operators[substr($column[$size - 3], -1)])))
			{
				// Ignore plus signs if column start with them
				if ($size != 2 || ltrim($column[0], '+') !== '' || $column[1][0] === "'")
				{
					// If operator exists before last word then alias is required for subquery
					$columns[$i][] = 'AS';
					$columns[$i][] = $this->quoteName('columnAlias' . $i);
					continue;
				}
			}
			elseif ($column[$size - 1][0] !== '.' && $lastChar2 !== '.')
			{
				// If columns is like name name2 then second word is alias.
				// Add missing AS before the alias, exception for 'a. x' and 'a .x'
				array_splice($columns[$i], -1, 0, 'AS');
			}
		}

		$selectColumns = [];

		foreach ($columns as $i => $column)
		{
			$selectColumns[$i] = implode(' ', $column);
		}

		$this->select = new QueryElement('SELECT', $selectColumns);

		return $columns;
	}

	/**
	 * Add missing columns names to GROUP BY clause.
	 *
	 * @param   array[]  $selectColumns  Array of columns from splitSqlExpression method.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fixGroupColumns($selectColumns)
	{
		// Cache tables columns
		static $cacheCols = [];

		// Known columns of all included tables
		$knownColumnsByAlias = [];

		$iquotes = ['"' => '', '[' => '', "'" => ''];
		$nquotes = ['"', '[', ']'];

		// Aggregate functions
		$aFuncs = [
			'AVG(',
			'CHECKSUM_AGG(',
			'COUNT(',
			'COUNT_BIG(',
			'GROUPING(',
			'GROUPING_ID(',
			'MIN(',
			'MAX(',
			'SUM(',
			'STDEV(',
			'STDEVP(',
			'VAR(',
			'VARP(',
		];

		// Aggregated columns
		$filteredColumns = [];

		// Aliases found in SELECT statement
		$knownAliases   = [];
		$wildcardTables = [];

		foreach ($selectColumns as $i => $column)
		{
			$size = count($column);

			if ($size === 0)
			{
				continue;
			}

			if ($size > 2 && $column[$size - 2] === 'AS')
			{
				// Save and remove AS alias
				$alias = $column[$size - 1];

				if (isset($iquotes[$alias[0]]))
				{
					$alias = substr($alias, 1, -1);
				}

				// Remove alias
				$selectColumns[$i] = $column = array_slice($column, 0, -2);

				if ($size === 3 || ($size === 4 && strpos('+-*/%&|~^', $column[0][0]) !== false))
				{
					$lastWord = $column[$size - 3];

					if ($lastWord[0] === "'" || $lastWord === 'NULL' || is_numeric($lastWord))
					{
						unset($selectColumns[$i]);

						continue;
					}
				}

				// Remember pair alias => column expression
				$knownAliases[$alias] = implode(' ', $column);
			}

			$aggregated = false;

			foreach ($column as $j => $block)
			{
				if (substr($block, -2) === '.*')
				{
					// Found column ends with .*
					if (isset($iquotes[$block[0]]))
					{
						// Quoted table
						$wildcardTables[] = substr($block, 1, -3);
					}
					else
					{
						$wildcardTables[] = substr($block, 0, -2);
					}
				}
				elseif (str_ireplace($aFuncs, '', $block) != $block)
				{
					$aggregated = true;
				}

				if ($block[0] === "'")
				{
					// Shrink static strings which could contain column name
					$column[$j] = "''";
				}
			}

			if (!$aggregated)
			{
				// Without aggregated columns and aliases
				$filteredColumns[] = implode(' ', $selectColumns[$i]);
			}

			// Without aliases and static strings
			$selectColumns[$i] = implode(' ', $column);
		}

		// If select statement use table.* expression
		if ($wildcardTables)
		{
			// Split FROM statement into list of tables
			$tables = $this->splitSqlExpression(implode(',', $this->from->getElements()));

			foreach ($tables as $i => $table)
			{
				$table = implode(' ', $table);

				// Exclude subquery from the FROM clause
				if (strpos($table, '(') === false)
				{
					// Unquote
					$table = str_replace($nquotes, '', $table);
					$table = str_replace('#__', $this->db->getPrefix(), $table);
					$table = explode(' ', $table);
					$alias = end($table);
					$table = $table[0];

					// Chek if exists a wildcard with current alias table?
					if (in_array($alias, $wildcardTables, true))
					{
						if (!isset($cacheCols[$table]))
						{
							$cacheCols[$table] = $this->db->getTableColumns($table);
						}

						if ($this->join || $table != $alias)
						{
							foreach ($cacheCols[$table] as $name => $type)
							{
								$knownColumnsByAlias[$alias][] = $alias . '.' . $name;
							}
						}
						else
						{
							foreach ($cacheCols[$table] as $name => $type)
							{
								$knownColumnsByAlias[$alias][] = $name;
							}
						}
					}
				}
			}

			// Now we need to get all tables from any joins
			// Go through all joins and add them to the tables array
			if ($this->join)
			{
				foreach ($this->join as $join)
				{
					// Unquote and replace prefix
					$joinTbl = str_replace($nquotes, '', (string) $join);
					$joinTbl = str_replace("#__", $this->db->getPrefix(), $joinTbl);

					// Exclude subquery
					if (preg_match('/JOIN\s+(\w+)(?:\s+AS)?(?:\s+(\w+))?/i', $joinTbl, $matches))
					{
						$table = $matches[1];
						$alias = isset($matches[2]) ? $matches[2] : $table;

						// Chek if exists a wildcard with current alias table?
						if (in_array($alias, $wildcardTables, true))
						{
							if (!isset($cacheCols[$table]))
							{
								$cacheCols[$table] = $this->db->getTableColumns($table);
							}

							foreach ($cacheCols[$table] as $name => $type)
							{
								$knownColumnsByAlias[$alias][] = $alias . '.' . $name;
							}
						}
					}
				}
			}
		}

		$selectExpression = implode(',', $selectColumns);

		// Split into the right columns
		$groupColumns = $this->splitSqlExpression(implode(',', $this->group->getElements()));

		// Remove column aliases from GROUP statement - SQLSRV does not support it
		foreach ($groupColumns as $i => $column)
		{
			$groupColumns[$i] = implode(' ', $column);
			$column           = str_replace($nquotes, '', $groupColumns[$i]);

			if (isset($knownAliases[$column]))
			{
				// Be sure that this is not a valid column name
				if (!preg_match('/\b' . preg_quote($column, '/') . '\b/', $selectExpression))
				{
					// Replace column alias by column expression
					$groupColumns[$i] = $knownAliases[$column];
				}
			}
		}

		// Find all alias.* and fill with proper table column names
		foreach ($filteredColumns as $i => $column)
		{
			if (substr($column, -2) === '.*')
			{
				unset($filteredColumns[$i]);

				// Extract alias.* columns into GROUP BY statement
				$groupColumns = array_merge($groupColumns, $knownColumnsByAlias[substr($column, 0, -2)]);
			}
		}

		$groupColumns = array_merge($groupColumns, $filteredColumns);

		if ($this->order)
		{
			// Remove direction suffixes
			$dir = [" DESC\v", " ASC\v"];

			$orderColumns = $this->splitSqlExpression(implode(',', $this->order->getElements()));

			foreach ($orderColumns as $i => $column)
			{
				$column           = implode(' ', $column);
				$orderColumns[$i] = $column = trim(str_ireplace($dir, '', "$column\v"), "\v");

				if (isset($knownAliases[str_replace($nquotes, '', $column)]))
				{
					unset($orderColumns[$i]);
				}

				if (str_ireplace($aFuncs, '', $column) != $column)
				{
					// Do not add aggregate expression
					unset($orderColumns[$i]);
				}
			}

			$groupColumns = array_merge($groupColumns, $orderColumns);
		}

		// Get a unique string of all column names that need to be included in the group statement
		$this->group = new QueryElement('GROUP BY', array_unique($groupColumns));

		return $this;
	}

	/**
	 * Split a string of sql expression into an array of individual columns.
	 * Single line or line end comments and multi line comments are stripped off.
	 * Always return at least one column.
	 *
	 * @param   string  $string  Input string of sql expression like select expression.
	 *
	 * @return  array[]  The columns from the input string separated into an array.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function splitSqlExpression($string)
	{
		// Append whitespace as equivalent to the last comma
		$string .= ' ';

		$colIdx    = 0;
		$start     = 0;
		$open      = false;
		$openC     = 0;
		$comment   = false;
		$endString = '';
		$length    = strlen($string);
		$columns   = [];
		$column    = [];
		$current   = '';
		$previous  = null;
		$operators = [
			'+' => '',
			'-' => '',
			'*' => '',
			'/' => '',
			'%' => '',
			'&' => '',
			'|' => '',
			'~' => '',
			'^' => '',
		];

		$addBlock = function ($block) use (&$column, &$colIdx)
		{
			if (isset($column[$colIdx]))
			{
				$column[$colIdx] .= $block;
			}
			else
			{
				$column[$colIdx] = $block;
			}
		};

		for ($i = 0; $i < $length; $i++)
		{
			$current      = substr($string, $i, 1);
			$current2     = substr($string, $i, 2);
			$current3     = substr($string, $i, 3);
			$lenEndString = strlen($endString);
			$testEnd      = substr($string, $i, $lenEndString);

			if ($current == '[' || $current == '"' || $current == "'" || $current2 == '--'
				|| ($current2 == '/*')
				|| ($current == '#' && $current3 != '#__')
				|| ($lenEndString && $testEnd == $endString))
			{
				if ($open)
				{
					if ($testEnd === $endString)
					{
						if ($comment)
						{
							if ($lenEndString > 1)
							{
								$i += ($lenEndString - 1);
							}

							// Move cursor after close tag of comment
							$start   = $i + 1;
							$comment = false;
						}
						elseif ($current == "'" || $current == ']' || $current == '"')
						{
							// Check for escaped quote like '', ]] or ""
							$n = 1;

							while ($i + $n < $length && $string[$i + $n] == $current)
							{
								$n++;
							}

							// Jump to the last quote
							$i += $n - 1;

							if ($n % 2 === 0)
							{
								// There is only escaped quote
								continue;
							}
							elseif ($n > 2)
							{
								// The last right close quote is not escaped
								$current = $string[$i];
							}
						}

						$open      = false;
						$endString = '';
					}
				}
				else
				{
					$open = true;

					if ($current == '#' || $current2 == '--')
					{
						$endString = "\n";
						$comment   = true;
					}
					elseif ($current2 == '/*')
					{
						$endString = '*/';
						$comment   = true;
					}
					elseif ($current == '[')
					{
						$endString = ']';
					}
					else
					{
						$endString = $current;
					}

					if ($comment && $start < $i)
					{
						// Add string exists before comment
						$addBlock(substr($string, $start, $i - $start));
						$previous = $string[$i - 1];
						$start    = $i;
					}
				}
			}
			elseif (!$open)
			{
				if ($current == '(')
				{
					$openC++;
					$previous = $current;
				}
				elseif ($current == ')')
				{
					$openC--;
					$previous = $current;
				}
				elseif ($current == '.')
				{
					if ($i === $start && $colIdx > 0 && !isset($column[$colIdx]))
					{
						// Remove whitepace placed before dot
						$colIdx--;
					}

					$previous = $current;
				}
				elseif ($openC === 0)
				{
					if (ctype_space($current))
					{
						// Normalize whitepace
						$string[$i] = ' ';

						if ($start < $i)
						{
							// Add text placed before whitespace
							$addBlock(substr($string, $start, $i - $start));
							$colIdx++;
							$previous = $string[$i - 1];
						}
						elseif (isset($column[$colIdx]))
						{
							if ($colIdx > 1 || !isset($operators[$previous]))
							{
								// There was whitespace after comment
								$colIdx++;
							}
						}

						// Move cursor forward
						$start = $i + 1;
					}
					elseif (isset($operators[$current]) && ($current !== '*' || $previous !== '.'))
					{
						if ($start < $i)
						{
							// Add text before operator
							$addBlock(substr($string, $start, $i - $start));
							$colIdx++;
						}
						elseif (!isset($column[$colIdx]) && isset($operators[$previous]))
						{
							// Do not create whitespace between operators
							$colIdx--;
						}

						// Add operator
						$addBlock($current);
						$previous = $current;
						$colIdx++;

						// Move cursor forward
						$start = $i + 1;
					}
					else
					{
						$previous = $current;
					}
				}
			}

			if (($current == ',' && !$open && $openC == 0) || $i == $length - 1)
			{
				if ($start < $i && !$comment)
				{
					// Save remaining text
					$addBlock(substr($string, $start, $i - $start));
				}

				$columns[] = $column;

				// Reset values
				$column   = [];
				$colIdx   = 0;
				$previous = null;

				// Column saved, move cursor forward after comma
				$start = $i + 1;
			}
		}

		return $columns;
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		if ($offset > 0)
		{
			// Find a position of the last comment
			$commentPos = strrpos($query, '/*ORDER BY (SELECT 0)*/');

			// If the last comment belongs to this query, not previous subquery
			if ($commentPos !== false && $commentPos + 2 === strripos($query, 'ORDER BY', $commentPos + 2))
			{
				// We can not use OFFSET without ORDER BY
				$query = substr_replace($query, 'ORDER BY (SELECT 0)', $commentPos, 23);
			}

			$query .= PHP_EOL . 'OFFSET ' . (int) $offset . ' ROWS';

			if ($limit > 0)
			{
				$query .= PHP_EOL . 'FETCH NEXT ' . (int) $limit . ' ROWS ONLY';
			}
		}
		elseif ($limit > 0)
		{
			$position = stripos($query, 'SELECT');
			$distinct = stripos($query, 'SELECT DISTINCT');

			if ($position === $distinct)
			{
				$query = substr_replace($query, 'SELECT DISTINCT TOP ' . (int) $limit, $position, 15);
			}
			else
			{
				$query = substr_replace($query, 'SELECT TOP ' . (int) $limit, $position, 6);
			}
		}

		return $query;
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
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setLimit($limit = 0, $offset = 0)
	{
		$this->limit  = (int) $limit;
		$this->offset = (int) $offset;

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
}
