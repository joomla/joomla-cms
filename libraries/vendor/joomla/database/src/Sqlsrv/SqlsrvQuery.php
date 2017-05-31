<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlsrv;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Query\PreparableInterface;
use Joomla\Database\Query\QueryElement;

/**
 * SQL Server Query Building Class.
 *
 * @since  1.0
 */
class SqlsrvQuery extends DatabaseQuery implements PreparableInterface
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
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->type)
		{
			case 'insert':
				$query .= (string) $this->insert;

				// Set method
				if ($this->set)
				{
					$query .= (string) $this->set;
				}
				elseif ($this->values)
				// Columns-Values method
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
	 * @param   mixed           &$value         The value that will be bound. The value is passed by reference to support output
	 *                                          parameters such as those possible with stored procedures.
	 * @param   string          $dataType       The corresponding bind type. (Unused)
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters. (Unused)
	 * @param   array           $driverOptions  Optional driver options to be used. (Unused)
	 *
	 * @return  SqlsrvQuery
	 *
	 * @since   1.5.0
	 */
	public function bind($key = null, &$value = null, $dataType = 's', $length = 0, $driverOptions = array())
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

		$obj        = new \stdClass;
		$obj->value = &$value;

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
		is_string($columns) && $columns = explode(',', str_replace(" ", "", $columns));

		// Get the _formatted_ FROM string and remove everything except `table AS alias`
		$fromStr = str_replace(array("[","]"), "", str_replace("#__", $this->db->getPrefix(), str_replace("FROM ", "", (string) $this->from)));

		// Start setting up an array of alias => table
		list($table, $alias) = preg_split("/\sAS\s/i", $fromStr);

		$tmpCols = $this->db->getTableColumns(trim($table));
		$cols = array();

		foreach ($tmpCols as $name => $type)
		{
			$cols[] = $alias . "." . $name;
		}

		// Now we need to get all tables from any joins
		// Go through all joins and add them to the tables array
		foreach ($this->join as $join)
		{
			$joinTbl = str_replace("#__", $this->db->getPrefix(), str_replace("]", "", preg_replace("/.*(#.+\sAS\s[^\s]*).*/i", "$1", (string) $join)));

			list($table, $alias) = preg_split("/\sAS\s/i", $joinTbl);

			$tmpCols = $this->db->getTableColumns(trim($table));

			foreach ($tmpCols as $name => $tmpColType)
			{
				array_push($cols, $alias . "." . $name);
			}
		}

		$selectStr = str_replace("SELECT ", "", (string) $this->select);

		// Remove any functions (e.g. COUNT(), SUM(), CONCAT())
		$selectCols = preg_replace("/([^,]*\([^\)]*\)[^,]*,?)/", "", $selectStr);

		// Remove any "as alias" statements
		$selectCols = preg_replace("/(\sas\s[^,]*)/i", "", $selectCols);

		// Remove any extra commas
		$selectCols = preg_replace("/,{2,}/", ",", $selectCols);

		// Remove any trailing commas and all whitespaces
		$selectCols = trim(str_replace(" ", "", preg_replace("/,?$/", "", $selectCols)));

		// Get an array to compare against
		$selectCols = explode(",", $selectCols);

		// Find all alias.* and fill with proper table column names
		foreach ($selectCols as $key => $aliasColName)
		{
			if (preg_match("/.+\*/", $aliasColName, $match))
			{
				// Grab the table alias minus the .*
				$aliasStar = preg_replace("/(.+)\.\*/", "$1", $aliasColName);

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
}
