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
 * @since  11.1
 */
class JDatabaseQuerySqlsrv extends JDatabaseQuery implements JDatabaseQueryLimitable
{
	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc.  The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $name_quotes = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $null_date = '1900-01-01 00:00:00';

	/**
	 * @var    integer  The affected row limit for the current SQL statement.
	 * @since  3.2
	 */
	protected $limit = 0;

	/**
	 * @var    integer  The affected row offset to apply for the current SQL statement.
	 * @since  3.2
	 */
	protected $offset = 0;

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 *
	 * @since   11.1
	 */
	public function __toString()
	{
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
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				if ($this instanceof JDatabaseQueryLimitable && ($this->limit > 0 || $this->offset > 0))
				{
					$query = $this->processLimit($query, $this->limit, $this->offset);
				}

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
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * @param   string  $value  The value to cast as a char.	 
	 *
	 * @param   string  $len    The lenght of the char.
	 *
	 * @return  string  Returns the cast value.
	 *
	 * @since   11.1
	 */
	public function castAsChar($value, $len = null)
	{
		if (!$len)
		{			
			return 'CAST(' . $value . ' as NVARCHAR(30))';
		}
		else
		{
			return 'CAST(' . $value . ' as NVARCHAR(' . $len . '))';
		}
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
	 * @since   11.1
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
	 * @since   11.1
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			return '(' . implode('+' . $this->quote($separator) . '+', $values) . ')';
		}
		else
		{
			return '(' . implode('+', $values) . ')';
		}
	}

	/**
	 * Gets the current date and time.
	 *
	 * @return  string
	 *
	 * @since   11.1
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
	 * @since   11.1
	 */
	public function length($value)
	{
		return 'LEN(' . $value . ')';
	}

	/**
	 * Add to the current date and time.
	 * Usage:
	 * $query->select($query->dateAdd());
	 * Prefixing the interval with a - (negative sign) will cause subtraction to be used.
	 *
	 * @param   datetime  $date      The date to add to; type may be time or datetime.
	 * @param   string    $interval  The string representation of the appropriate number of units
	 * @param   string    $datePart  The part of the date to perform the addition on
	 *
	 * @return  string  The string with the appropriate sql for addition of dates
	 *
	 * @since   13.1
	 * @note    Not all drivers support all units.
	 * @link    http://msdn.microsoft.com/en-us/library/ms186819.aspx for more information
	 */
	public function dateAdd($date, $interval, $datePart)
	{
		return "DATEADD('" . $datePart . "', '" . $interval . "', '" . $date . "'" . ')';
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
		if ($limit)
		{
			$total = $offset + $limit;

			$position = stripos($query, 'SELECT');
			$distinct = stripos($query, 'SELECT DISTINCT');

			if ($position === $distinct)
			{
				$query = substr_replace($query, 'SELECT DISTINCT TOP ' . (int) $total, $position, 15);
			}
			else
			{
				$query = substr_replace($query, 'SELECT TOP ' . (int) $total, $position, 6);
			}
		}

		if (!$offset)
		{
			return $query;
		}

		return PHP_EOL
			. 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ('
			. $query
			. PHP_EOL . ') AS A) AS A WHERE RowNumber > ' . (int) $offset;
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
	 * Split a string of sql expression into an array of individual columns.
	 * Single line or line end comments and multi line comments are stripped off.
	 * Always return at least one column.
	 *
	 * @param   string  $string  Input string of sql expression like select expression.
	 *
	 * @return  array[]  The columns from the input string separated into an array.
	 *
	 * @since   3.7.0
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
		$columns   = array();
		$column    = array();
		$current   = '';
		$previous  = null;
		$operators = array(
			'+' => '',
			'-' => '',
			'*' => '',
			'/' => '',
			'%' => '',
			'&' => '',
			'|' => '',
			'~' => '',
			'^' => '',
		);

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
				|| ($current2 == '/*') || ($current == '#' && $current3 != '#__')
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
							$start = $i + 1;
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

						$open = false;
						$endString = '';
					}
				}
				else
				{
					$open = true;

					if ($current == '#' || $current2 == '--')
					{
						$endString = "\n";
						$comment = true;
					}
					elseif ($current2 == '/*')
					{
						$endString = '*/';
						$comment = true;
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
						$start = $i;
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
				$column   = array();
				$colIdx   = 0;
				$previous = null;

				// Column saved, move cursor forward after comma
				$start = $i + 1;
			}
		}

		return $columns;
	}

	/**
	 * Add required aliases to columns for select statement in subquery.
	 *
	 * @return  array[]  Array of columns with added missing aliases.
	 *
	 * @since   3.7.0
	 */
	protected function fixSelectAliases()
	{
		$operators = array(
			'+' => '',
			'-' => '',
			'*' => '',
			'/' => '',
			'%' => '',
			'&' => '',
			'|' => '',
			'~' => '',
			'^' => '',
		);

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
				/* Ends with:
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
			if (isset($operators[$lastChar2])
				|| ($size > 2 && $lastChar2 === '.' && isset($operators[substr($column[$size - 3], -1)])))
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

		$selectColumns = array();

		foreach ($columns as $i => $column)
		{
			$selectColumns[$i] = implode(' ', $column);
		}

		$this->select = new JDatabaseQueryElement('SELECT', $selectColumns);

		return $columns;
	}

	/**
	 * Add missing columns names to GROUP BY clause.
	 *
	 * @param   array[]  $selectColumns  Array of columns from splitSqlExpression method.
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   3.7.0
	 */
	protected function fixGroupColumns($selectColumns)
	{
		// Cache tables columns
		static $cacheCols = array();

		// Known columns of all included tables
		$knownColumnsByAlias = array();

		$iquotes  = array('"' => '', '[' => '', "'" => '');
		$nquotes = array('"', '[', ']');

		// Aggregate functions
		$aFuncs = array(
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
		);

		// Aggregated columns
		$filteredColumns = array();

		// Aliases found in SELECT statement
		$knownAliases = array();
		$wildcardTables = array();

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
			$column = str_replace($nquotes, '', $groupColumns[$i]);

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
			$dir = array(" DESC\v", " ASC\v");

			$orderColumns = $this->splitSqlExpression(implode(',', $this->order->getElements()));

			foreach ($orderColumns as $i => $column)
			{
				$column = implode(' ', $column);
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
		$this->group = new JDatabaseQueryElement('GROUP BY', array_unique($groupColumns));

		return $this;
	}

	/**
	 * Return correct rand() function for MSSQL.
	 *
	 * Ensure that the rand() function is MSSQL compatible.
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
		return ' NEWID() ';
	}
}
