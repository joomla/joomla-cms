<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use Joomla\Database\Exception\UnknownTypeException;

/**
 * Joomla Framework Query Building Class.
 *
 * @since  1.0
 *
 * @property-read  array                      $bounded             Holds key / value pair of bound objects.
 * @property-read  array                      $parameterMapping    Mapping array for parameter types.
 * @property-read  DatabaseInterface          $db                  The database driver.
 * @property-read  string                     $sql                 The SQL query (if a direct query string was provided).
 * @property-read  string                     $type                The query type.
 * @property-read  string|null                $alias               The query alias.
 * @property-read  Query\QueryElement         $element             The query element for a generic query (type = null).
 * @property-read  Query\QueryElement         $select              The select element.
 * @property-read  Query\QueryElement         $delete              The delete element.
 * @property-read  Query\QueryElement         $update              The update element.
 * @property-read  Query\QueryElement         $insert              The insert element.
 * @property-read  Query\QueryElement         $from                The from element.
 * @property-read  Query\QueryElement[]|null  $join                The join elements.
 * @property-read  Query\QueryElement         $set                 The set element.
 * @property-read  Query\QueryElement         $where               The where element.
 * @property-read  Query\QueryElement         $group               The group element.
 * @property-read  Query\QueryElement         $having              The having element.
 * @property-read  Query\QueryElement         $columns             The column list for an INSERT statement.
 * @property-read  Query\QueryElement         $values              The values list for an INSERT statement.
 * @property-read  Query\QueryElement         $order               The order element.
 * @property-read  boolean                    $autoIncrementField  The auto increment insert field element.
 * @property-read  Query\QueryElement         $call                The call element.
 * @property-read  Query\QueryElement         $exec                The exec element.
 * @property-read  Query\QueryElement[]|null  $merge               The list of query elements.
 * @property-read  DatabaseQuery|null         $querySet            The query object.
 * @property-read  array|null                 $selectRowNumber     Details of window function.
 * @property-read  string[]                   $nullDatetimeList    The list of zero or null representation of a datetime.
 * @property-read  integer|null               $offset              The offset for the result set.
 * @property-read  integer|null               $limit               The limit for the result set.
 * @property-read  integer                    $preparedIndex       An internal index for the bindArray function for unique prepared parameters.
 */
abstract class DatabaseQuery implements QueryInterface
{
	/**
	 * Holds key / value pair of bound objects.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $bounded = [];

	/**
	 * Mapping array for parameter types.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $parameterMapping = [
		ParameterType::BOOLEAN      => ParameterType::BOOLEAN,
		ParameterType::INTEGER      => ParameterType::INTEGER,
		ParameterType::LARGE_OBJECT => ParameterType::LARGE_OBJECT,
		ParameterType::NULL         => ParameterType::NULL,
		ParameterType::STRING       => ParameterType::STRING,
	];

	/**
	 * The database driver.
	 *
	 * @var    DatabaseInterface
	 * @since  1.0
	 */
	protected $db;

	/**
	 * The SQL query (if a direct query string was provided).
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $sql;

	/**
	 * The query type.
	 *
	 * @var    string|null
	 * @since  1.0
	 */
	protected $type = '';

	/**
	 * The query alias.
	 *
	 * @var    string|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $alias = null;

	/**
	 * The query element for a generic query (type = null).
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $element;

	/**
	 * The select element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $select;

	/**
	 * The delete element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $delete;

	/**
	 * The update element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $update;

	/**
	 * The insert element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $insert;

	/**
	 * The from element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $from;

	/**
	 * The join elements.
	 *
	 * @var    Query\QueryElement[]
	 * @since  1.0
	 */
	protected $join;

	/**
	 * The set element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $set;

	/**
	 * The where element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $where;

	/**
	 * The group by element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $group;

	/**
	 * The having element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $having;

	/**
	 * The column list for an INSERT statement.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $columns;

	/**
	 * The values list for an INSERT statement.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $values;

	/**
	 * The order element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $order;

	/**
	 * The auto increment insert field element.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoIncrementField = false;

	/**
	 * The call element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $call;

	/**
	 * The exec element.
	 *
	 * @var    Query\QueryElement
	 * @since  1.0
	 */
	protected $exec;

	/**
	 * The list of query elements, which may include UNION, UNION ALL, EXCEPT and INTERSECT.
	 *
	 * @var    Query\QueryElement[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $merge;

	/**
	 * The query object.
	 *
	 * @var    Query\DatabaseQuery
	 * @since  __DEPLOY_VERSION__
	 */
	protected $querySet;

	/**
	 * Details of window function.
	 *
	 * @var    array|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $selectRowNumber;

	/**
	 * The list of zero or null representation of a datetime.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $nullDatetimeList = [];

	/**
	 * The offset for the result set.
	 *
	 * @var    integer|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $offset;

	/**
	 * The limit for the result set.
	 *
	 * @var    integer|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $limit;

	/**
	 * An internal index for the bindArray function for unique prepared parameters.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $preparedIndex = 0;

	/**
	 * Class constructor.
	 *
	 * @param   DatabaseInterface  $db  The database driver.
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseInterface $db = null)
	{
		$this->db = $db;
	}

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		if ($this->sql)
		{
			return $this->processLimit($this->sql, $this->limit, $this->offset);
		}

		$query = '';

		switch ($this->type)
		{
			case 'element':
				$query .= (string) $this->element;

				break;

			case 'select':
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
						foreach ($this->merge as $element)
						{
							$query .= (string) $element;
						}
					}
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			case 'querySet':
				$query = $this->querySet;

				if ($query->order || ($query->limit || $query->offset))
				{
					// If ORDER BY or LIMIT statement exist then parentheses is required for the first query
					$query = "($query)";
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

				break;

			case 'update':
				$query .= (string) $this->update;

				if ($this->join)
				{
					// Special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				$query .= (string) $this->set;

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				break;

			case 'insert':
				$query .= (string) $this->insert;

				// Set method
				if ($this->set)
				{
					$query .= (string) $this->set;
				}
				elseif ($this->values)
				{
					// Columns-Values method
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
				}

				break;

			case 'call':
				$query .= (string) $this->call;

				break;

			case 'exec':
				$query .= (string) $this->exec;

				break;
		}

		$query = $this->processLimit($query, $this->limit, $this->offset);

		if ($this->type === 'select' && $this->alias !== null)
		{
			$query = '(' . $query . ') AS ' . $this->alias;
		}

		return $query;
	}

	/**
	 * Magic function to get protected variable value
	 *
	 * @param   string  $name  The name of the variable.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function __get($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
	}

	/**
	 * Add a single column, or array of columns to the CALL clause of the query.
	 *
	 * Usage:
	 * $query->call('a.*')->call('b.id');
	 * $query->call(array('a.*', 'b.id'));
	 *
	 * @param   mixed  $columns  A string or an array of field names.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  QueryTypeAlreadyDefinedException if the query type has already been defined
	 */
	public function call($columns)
	{
		if ($this->type !== null && $this->type !== '' && $this->type !== 'call')
		{
			throw new QueryTypeAlreadyDefinedException(
				\sprintf(
					'Cannot set the query type to "call" as the query type is already set to "%s".'
						. ' You should either call the `clear()` method to reset the type or create a new query object.',
					$this->type
				)
			);
		}

		$this->type = 'call';

		if ($this->call === null)
		{
			$this->call = new Query\QueryElement('CALL', $columns);
		}
		else
		{
			$this->call->append($columns);
		}

		return $this;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * Usage:
	 * $query->select($query->castAs('CHAR', 'a'));
	 *
	 * @param   string  $type    The type of string to cast as.
	 * @param   string  $value   The value to cast as a char.
	 * @param   string  $length  Optionally specify the length of the field (if the type supports it otherwise
	 *                           ignored).
	 *
	 * @return  string  SQL statement to cast the value as a char type.
	 *
	 * @since   1.0
	 */
	public function castAs(string $type, string $value, ?string $length = null)
	{
		switch (strtoupper($type))
		{
			case 'CHAR':
				return $value;

			default:
				throw new UnknownTypeException(
					sprintf(
						'Type %s was not recognised by the database driver as valid for casting',
						$type
					)
				);
		}
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
	 * @return  string  SQL statement to cast the value as a char type.
	 *
	 * @since       1.0
	 * @deprecated  3.0  Use $query->castAs('CHAR', $value)
	 */
	public function castAsChar($value)
	{
		return $this->castAs('CHAR', $value);
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
	 * @since   1.0
	 */
	public function charLength($field, $operator = null, $condition = null)
	{
		$statement = 'CHAR_LENGTH(' . $field . ')';

		if ($operator !== null && $condition !== null)
		{
			$statement .= ' ' . $operator . ' ' . $condition;
		}

		return $statement;
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function clear($clause = null)
	{
		$this->sql = null;

		switch ($clause)
		{
			case 'alias':
				$this->alias = null;
				break;

			case 'select':
				$this->select          = null;
				$this->type            = null;
				$this->selectRowNumber = null;

				break;

			case 'delete':
				$this->delete = null;
				$this->type   = null;

				break;

			case 'update':
				$this->update = null;
				$this->type   = null;

				break;

			case 'insert':
				$this->insert             = null;
				$this->type               = null;
				$this->autoIncrementField = null;

				break;

			case 'querySet':
				$this->querySet = null;
				$this->type     = null;

				break;

			case 'from':
				$this->from = null;

				break;

			case 'join':
				$this->join = null;

				break;

			case 'set':
				$this->set = null;

				break;

			case 'where':
				$this->where = null;

				break;

			case 'group':
				$this->group = null;

				break;

			case 'having':
				$this->having = null;

				break;

			case 'merge':
				$this->merge = null;

				break;

			case 'order':
				$this->order = null;

				break;

			case 'columns':
				$this->columns = null;

				break;

			case 'values':
				$this->values = null;

				break;

			case 'exec':
				$this->exec = null;
				$this->type = null;

				break;

			case 'call':
				$this->call = null;
				$this->type = null;

				break;

			case 'limit':
				$this->offset = 0;
				$this->limit  = 0;

				break;

			case 'offset':
				$this->offset = 0;

				break;

			case 'bounded':
				$this->bounded = [];

				break;

			default:
				$this->type               = null;
				$this->alias              = null;
				$this->bounded            = [];
				$this->select             = null;
				$this->selectRowNumber    = null;
				$this->delete             = null;
				$this->update             = null;
				$this->insert             = null;
				$this->querySet           = null;
				$this->from               = null;
				$this->join               = null;
				$this->set                = null;
				$this->where              = null;
				$this->group              = null;
				$this->having             = null;
				$this->merge              = null;
				$this->order              = null;
				$this->columns            = null;
				$this->values             = null;
				$this->autoIncrementField = null;
				$this->exec               = null;
				$this->call               = null;
				$this->offset             = 0;
				$this->limit              = 0;

				break;
		}

		return $this;
	}

	/**
	 * Adds a column, or array of column names that would be used for an INSERT INTO statement.
	 *
	 * @param   array|string  $columns  A column name, or array of column names.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function columns($columns)
	{
		if ($this->columns === null)
		{
			$this->columns = new Query\QueryElement('()', $columns);
		}
		else
		{
			$this->columns->append($columns);
		}

		return $this;
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
	 * @since   1.0
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator !== null)
		{
			return 'CONCATENATE(' . implode(' || ' . $this->quote($separator) . ' || ', $values) . ')';
		}

		return 'CONCATENATE(' . implode(' || ', $values) . ')';
	}

	/**
	 * Gets the current date and time.
	 *
	 * Usage:
	 * $query->where('published_up < '.$query->currentTimestamp());
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function currentTimestamp()
	{
		return 'CURRENT_TIMESTAMP()';
	}

	/**
	 * Add to the current date and time.
	 *
	 * Usage:
	 * $query->select($query->dateAdd());
	 *
	 * Prefixing the interval with a - (negative sign) will cause subtraction to be used.
	 * Note: Not all drivers support all units.
	 *
	 * @param   string  $date      The db quoted string representation of the date to add to. May be date or datetime
	 * @param   string  $interval  The string representation of the appropriate number of units
	 * @param   string  $datePart  The part of the date to perform the addition on
	 *
	 * @return  string  The string with the appropriate sql for addition of dates
	 *
	 * @link    https://dev.mysql.com/doc/en/date-and-time-functions.html
	 * @since   1.5.0
	 */
	public function dateAdd($date, $interval, $datePart)
	{
		return 'DATE_ADD(' . $date . ', INTERVAL ' . $interval . ' ' . $datePart . ')';
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the getDateFormat method directly.
	 *
	 * @return  string  The format string.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function dateFormat()
	{
		if (!($this->db instanceof DatabaseInterface))
		{
			throw new \RuntimeException(sprintf('A %s instance is not set to the query object.', DatabaseInterface::class));
		}

		return $this->db->getDateFormat();
	}

	/**
	 * Creates a HTML formatted dump of the query for debugging purposes.
	 *
	 * Usage:
	 * echo $query->dump();
	 *
	 * @return  string
	 *
	 * @since   1.0
	 * @deprecated  3.0  Deprecated without replacement
	 */
	public function dump()
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		return '<pre class="jdatabasequery">' . str_replace('#__', $this->db->getPrefix(), $this) . '</pre>';
	}

	/**
	 * Add a table name to the DELETE clause of the query.
	 *
	 * Usage:
	 * $query->delete('#__a')->where('id = 1');
	 *
	 * @param   string  $table  The name of the table to delete from.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  QueryTypeAlreadyDefinedException if the query type has already been defined
	 */
	public function delete($table = null)
	{
		if ($this->type !== null && $this->type !== '' && $this->type !== 'delete')
		{
			throw new QueryTypeAlreadyDefinedException(
				\sprintf(
					'Cannot set the query type to "delete" as the query type is already set to "%s".'
						. ' You should either call the `clear()` method to reset the type or create a new query object.',
					$this->type
				)
			);
		}

		$this->type   = 'delete';
		$this->delete = new Query\QueryElement('DELETE', null);

		if (!empty($table))
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Alias for escape method
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function e($text, $extra = false)
	{
		return $this->escape($text, $extra);
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the escape method directly.
	 *
	 * Note that 'e' is an alias for this method as it is in DatabaseDriver.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function escape($text, $extra = false)
	{
		if (!($this->db instanceof DatabaseInterface))
		{
			throw new \RuntimeException(sprintf('A %s instance is not set to the query object.', DatabaseInterface::class));
		}

		return $this->db->escape($text, $extra);
	}

	/**
	 * Add a single column, or array of columns to the EXEC clause of the query.
	 *
	 * Usage:
	 * $query->exec('a.*')->exec('b.id');
	 * $query->exec(array('a.*', 'b.id'));
	 *
	 * @param   array|string  $columns  A string or an array of field names.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  QueryTypeAlreadyDefinedException if the query type has already been defined
	 */
	public function exec($columns)
	{
		if ($this->type !== null && $this->type !== '' && $this->type !== 'exec')
		{
			throw new QueryTypeAlreadyDefinedException(
				\sprintf(
					'Cannot set the query type to "exec" as the query type is already set to "%s".'
						. ' You should either call the `clear()` method to reset the type or create a new query object.',
					$this->type
				)
			);
		}

		$this->type = 'exec';

		if ($this->exec === null)
		{
			$this->exec = new Query\QueryElement('EXEC', $columns);
		}
		else
		{
			$this->exec->append($columns);
		}

		return $this;
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
		return '';
	}

	/**
	 * Add a table to the FROM clause of the query.
	 *
	 * Usage:
	 * $query->select('*')->from('#__a');
	 * $query->select('*')->from($subquery->alias('a'));
	 *
	 * @param   string|DatabaseQuery  $table  The name of the table or a DatabaseQuery object (or a child of it) with alias set.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function from($table)
	{
		if ($table instanceof $this && $table->alias === null)
		{
			throw new \RuntimeException('JLIB_DATABASE_ERROR_NULL_SUBQUERY_ALIAS');
		}

		if ($this->from === null)
		{
			$this->from = new Query\QueryElement('FROM', $table);
		}
		else
		{
			$this->from->append($table);
		}

		return $this;
	}

	/**
	 * Add alias for current query.
	 *
	 * Usage:
	 * $query->select('*')->from('#__a')->alias('subquery');
	 *
	 * @param   string  $alias  Alias used for a JDatabaseQuery.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function alias($alias)
	{
		$this->alias = $alias;

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
	 * @since   1.0
	 */
	public function year($date)
	{
		return 'YEAR(' . $date . ')';
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
	 * @since   1.0
	 */
	public function month($date)
	{
		return 'MONTH(' . $date . ')';
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
	 * @since   1.0
	 */
	public function day($date)
	{
		return 'DAY(' . $date . ')';
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
	 * @since   1.0
	 */
	public function hour($date)
	{
		return 'HOUR(' . $date . ')';
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
	 * @since   1.0
	 */
	public function minute($date)
	{
		return 'MINUTE(' . $date . ')';
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
	 * @since   1.0
	 */
	public function second($date)
	{
		return 'SECOND(' . $date . ')';
	}

	/**
	 * Add a grouping column to the GROUP clause of the query.
	 *
	 * Usage:
	 * $query->group('id');
	 *
	 * @param   array|string  $columns  A string or array of ordering columns.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function group($columns)
	{
		if ($this->group === null)
		{
			$this->group = new Query\QueryElement('GROUP BY', $columns);
		}
		else
		{
			$this->group->append($columns);
		}

		return $this;
	}

	/**
	 * A conditions to the HAVING clause of the query.
	 *
	 * Usage:
	 * $query->group('id')->having('COUNT(id) > 5');
	 *
	 * @param   array|string  $conditions  A string or array of columns.
	 * @param   string        $glue        The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function having($conditions, $glue = 'AND')
	{
		if ($this->having === null)
		{
			$glue         = strtoupper($glue);
			$this->having = new Query\QueryElement('HAVING', $conditions, " $glue ");
		}
		else
		{
			$this->having->append($conditions);
		}

		return $this;
	}

	/**
	 * Add a table name to the INSERT clause of the query.
	 *
	 * Usage:
	 * $query->insert('#__a')->set('id = 1');
	 * $query->insert('#__a')->columns('id, title')->values('1,2')->values('3,4');
	 * $query->insert('#__a')->columns('id, title')->values(array('1,2', '3,4'));
	 *
	 * @param   string   $table           The name of the table to insert data into.
	 * @param   boolean  $incrementField  The name of the field to auto increment.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  QueryTypeAlreadyDefinedException if the query type has already been defined
	 */
	public function insert($table, $incrementField = false)
	{
		if ($this->type !== null && $this->type !== '' && $this->type !== 'insert')
		{
			throw new QueryTypeAlreadyDefinedException(
				\sprintf(
					'Cannot set the query type to "insert" as the query type is already set to "%s".'
						. ' You should either call the `clear()` method to reset the type or create a new query object.',
					$this->type
				)
			);
		}

		$this->type               = 'insert';
		$this->insert             = new Query\QueryElement('INSERT INTO', $table);
		$this->autoIncrementField = $incrementField;

		return $this;
	}

	/**
	 * Add a JOIN clause to the query.
	 *
	 * Usage:
	 * $query->join('INNER', 'b', 'b.id = a.id);
	 *
	 * @param   string  $type       The type of join. This string is prepended to the JOIN keyword.
	 * @param   string  $table      The name of table.
	 * @param   string  $condition  The join condition.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function join($type, $table, $condition = null)
	{
		$type = strtoupper($type) . ' JOIN';

		if ($condition !== null)
		{
			$this->join[] = new Query\QueryElement($type, [$table, $condition], ' ON ');
		}
		else
		{
			$this->join[] = new Query\QueryElement($type, $table);
		}

		return $this;
	}

	/**
	 * Add an INNER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->innerJoin('b', 'b.id = a.id')->innerJoin('c', 'c.id = b.id');
	 *
	 * @param   string  $table      The name of table.
	 * @param   string  $condition  The join condition.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function innerJoin($table, $condition = null)
	{
		return $this->join('INNER', $table, $condition);
	}

	/**
	 * Add an OUTER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->outerJoin('b', 'b.id = a.id')->leftJoin('c', 'c.id = b.id');
	 *
	 * @param   string  $table      The name of table.
	 * @param   string  $condition  The join condition.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function outerJoin($table, $condition = null)
	{
		return $this->join('OUTER', $table, $condition);
	}

	/**
	 * Add a LEFT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->leftJoin('b', 'b.id = a.id')->leftJoin('c', 'c.id = b.id');
	 *
	 * @param   string  $table      The name of table.
	 * @param   string  $condition  The join condition.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function leftJoin($table, $condition = null)
	{
		return $this->join('LEFT', $table, $condition);
	}

	/**
	 * Add a RIGHT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->rightJoin('b', 'b.id = a.id')->rightJoin('c', 'c.id = b.id');
	 *
	 * @param   string  $table      The name of table.
	 * @param   string  $condition  The join condition.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function rightJoin($table, $condition = null)
	{
		return $this->join('RIGHT', $table, $condition);
	}

	/**
	 * Get the length of a string in bytes.
	 *
	 * Note, use 'charLength' to find the number of characters in a string.
	 *
	 * Usage:
	 * query->where($query->length('a').' > 3');
	 *
	 * @param   string  $value  The string to measure.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function length($value)
	{
		return 'LENGTH(' . $value . ')';
	}

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the nullDate method directly.
	 *
	 * Usage:
	 * $query->where('modified_date <> '.$query->nullDate());
	 *
	 * @param   boolean  $quoted  Optionally wraps the null date in database quotes (true by default).
	 *
	 * @return  string  Null or zero representation of a timestamp.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function nullDate($quoted = true)
	{
		if (!($this->db instanceof DatabaseInterface))
		{
			throw new \RuntimeException(sprintf('A %s instance is not set to the query object.', DatabaseInterface::class));
		}

		$result = $this->db->getNullDate();

		if ($quoted)
		{
			return $this->db->quote($result);
		}

		return $result;
	}

	/**
	 * Generate a SQL statement to check if column represents a zero or null datetime.
	 *
	 * Usage:
	 * $query->where($query->isNullDatetime('modified_date'));
	 *
	 * @param   string  $column  A column name.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isNullDatetime($column)
	{
		if (!$this->db instanceof DatabaseInterface)
		{
			throw new \RuntimeException(sprintf('A %s instance is not set to the query object.', DatabaseInterface::class));
		}

		if ($this->nullDatetimeList)
		{
			return "($column IN ("
			. implode(', ', $this->db->quote($this->nullDatetimeList))
			. ") OR $column IS NULL)";
		}

		return "$column IS NULL";
	}

	/**
	 * Add a ordering column to the ORDER clause of the query.
	 *
	 * Usage:
	 * $query->order('foo')->order('bar');
	 * $query->order(array('foo','bar'));
	 *
	 * @param   array|string  $columns  A string or array of ordering columns.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function order($columns)
	{
		if ($this->order === null)
		{
			$this->order = new Query\QueryElement('ORDER BY', $columns);
		}
		else
		{
			$this->order->append($columns);
		}

		return $this;
	}

	/**
	 * Alias for quote method
	 *
	 * @param   array|string  $text    A string or an array of strings to quote.
	 * @param   boolean       $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function q($text, $escape = true)
	{
		return $this->quote($text, $escape);
	}

	/**
	 * Method to quote and optionally escape a string to database requirements for insertion into the database.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quote method directly.
	 *
	 * Note that 'q' is an alias for this method as it is in DatabaseDriver.
	 *
	 * Usage:
	 * $query->quote('fulltext');
	 * $query->q('fulltext');
	 * $query->q(array('option', 'fulltext'));
	 *
	 * @param   array|string  $text    A string or an array of strings to quote.
	 * @param   boolean       $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function quote($text, $escape = true)
	{
		if (!($this->db instanceof DatabaseInterface))
		{
			throw new \RuntimeException(sprintf('A %s instance is not set to the query object.', DatabaseInterface::class));
		}

		return $this->db->quote($text, $escape);
	}

	/**
	 * Alias for quoteName method
	 *
	 * @param   array|string  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
	 *                               Each type supports dot-notation name.
	 * @param   array|string  $as    The AS query part associated to $name. It can be string or array, in latter case it has to be
	 *                               same length of $name; if is null there will not be any AS part for string or array element.
	 *
	 * @return  array|string  The quote wrapped name, same type of $name.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function qn($name, $as = null)
	{
		return $this->quoteName($name, $as);
	}

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quoteName method directly.
	 *
	 * Note that 'qn' is an alias for this method as it is in DatabaseDriver.
	 *
	 * Usage:
	 * $query->quoteName('#__a');
	 * $query->qn('#__a');
	 *
	 * @param   array|string  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
	 *                               Each type supports dot-notation name.
	 * @param   array|string  $as    The AS query part associated to $name. It can be string or array, in latter case it has to be
	 *                               same length of $name; if is null there will not be any AS part for string or array element.
	 *
	 * @return  array|string  The quote wrapped name, same type of $name.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function quoteName($name, $as = null)
	{
		if (!($this->db instanceof DatabaseInterface))
		{
			throw new \RuntimeException(sprintf('A %s instance is not set to the query object.', DatabaseInterface::class));
		}

		return $this->db->quoteName($name, $as);
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
		return '';
	}

	/**
	 * Get the regular expression operator
	 *
	 * Usage:
	 * $query->where('field ' . $query->regexp($search));
	 *
	 * @param   string  $value  The regex pattern.
	 *
	 * @return  string
	 *
	 * @since   1.5.0
	 */
	public function regexp($value)
	{
		return ' ' . $value;
	}

	/**
	 * Add a single column, or array of columns to the SELECT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The select method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->select('a.*')->select('b.id');
	 * $query->select(array('a.*', 'b.id'));
	 *
	 * @param   array|string  $columns  A string or an array of field names.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  QueryTypeAlreadyDefinedException if the query type has already been defined
	 */
	public function select($columns)
	{
		if ($this->type !== null && $this->type !== '' && $this->type !== 'select')
		{
			throw new QueryTypeAlreadyDefinedException(
				\sprintf(
					'Cannot set the query type to "select" as the query type is already set to "%s".'
						. ' You should either call the `clear()` method to reset the type or create a new query object.',
					$this->type
				)
			);
		}

		$this->type = 'select';

		if ($this->select === null)
		{
			$this->select = new Query\QueryElement('SELECT', $columns);
		}
		else
		{
			$this->select->append($columns);
		}

		return $this;
	}

	/**
	 * Add a single condition string, or an array of strings to the SET clause of the query.
	 *
	 * Usage:
	 * $query->set('a = 1')->set('b = 2');
	 * $query->set(array('a = 1', 'b = 2');
	 *
	 * @param   array|string  $conditions  A string or array of string conditions.
	 * @param   string        $glue        The glue by which to join the condition strings. Defaults to ,.
	 *                                     Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function set($conditions, $glue = ',')
	{
		if ($this->set === null)
		{
			$glue      = strtoupper($glue);
			$this->set = new Query\QueryElement('SET', $conditions, \PHP_EOL . "\t$glue ");
		}
		else
		{
			$this->set->append($conditions);
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
	 * Allows a direct query to be provided to the database driver's setQuery() method, but still allow queries
	 * to have bounded variables.
	 *
	 * Usage:
	 * $query->setQuery('select * from #__users');
	 *
	 * @param   DatabaseQuery|string  $sql  A SQL query string or DatabaseQuery object
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setQuery($sql)
	{
		$this->sql = $sql;

		return $this;
	}

	/**
	 * Add a table name to the UPDATE clause of the query.
	 *
	 * Usage:
	 * $query->update('#__foo')->set(...);
	 *
	 * @param   string  $table  A table to update.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  QueryTypeAlreadyDefinedException if the query type has already been defined
	 */
	public function update($table)
	{
		if ($this->type !== null && $this->type !== '' && $this->type !== 'update')
		{
			throw new QueryTypeAlreadyDefinedException(
				\sprintf(
					'Cannot set the query type to "update" as the query type is already set to "%s".'
						. ' You should either call the `clear()` method to reset the type or create a new query object.',
					$this->type
				)
			);
		}

		$this->type   = 'update';
		$this->update = new Query\QueryElement('UPDATE', $table);

		return $this;
	}

	/**
	 * Adds a tuple, or array of tuples that would be used as values for an INSERT INTO statement.
	 *
	 * Usage:
	 * $query->values('1,2,3')->values('4,5,6');
	 * $query->values(array('1,2,3', '4,5,6'));
	 *
	 * @param   array|string  $values  A single tuple, or array of tuples.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function values($values)
	{
		if ($this->values === null)
		{
			$this->values = new Query\QueryElement('()', $values, '),(');
		}
		else
		{
			$this->values->append($values);
		}

		return $this;
	}

	/**
	 * Add a single condition, or an array of conditions to the WHERE clause of the query.
	 *
	 * Usage:
	 * $query->where('a = 1')->where('b = 2');
	 * $query->where(array('a = 1', 'b = 2'));
	 *
	 * @param   array|string  $conditions  A string or array of where conditions.
	 * @param   string        $glue        The glue by which to join the conditions. Defaults to AND.
	 *                                     Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function where($conditions, $glue = 'AND')
	{
		if ($this->where === null)
		{
			$glue        = strtoupper($glue);
			$this->where = new Query\QueryElement('WHERE', $conditions, " $glue ");
		}
		else
		{
			$this->where->append($conditions);
		}

		return $this;
	}

	/**
	 * Add a WHERE IN statement to the query.
	 *
	 * Note that all values must be the same data type.
	 *
	 * Usage
	 * $query->whereIn('id', [1, 2, 3]);
	 *
	 * @param   string        $keyName    Key name for the where clause
	 * @param   array         $keyValues  Array of values to be matched
	 * @param   array|string  $dataType   Constant corresponding to a SQL datatype. It can be an array, in this case it
	 *                                    has to be same length of $keyValues
	 *
	 * @return  $this
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function whereIn(string $keyName, array $keyValues, $dataType = ParameterType::INTEGER)
	{
		return $this->where(
			$keyName . ' IN (' . implode(',', $this->bindArray($keyValues, $dataType)) . ')'
		);
	}

	/**
	 * Add a WHERE NOT IN statement to the query.
	 *
	 * Note that all values must be the same data type.
	 *
	 * Usage
	 * $query->whereNotIn('id', [1, 2, 3]);
	 *
	 * @param   string        $keyName    Key name for the where clause
	 * @param   array         $keyValues  Array of values to be matched
	 * @param   array|string  $dataType   Constant corresponding to a SQL datatype. It can be an array, in this case it
	 *                                    has to be same length of $keyValues
	 *
	 * @return  $this
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function whereNotIn(string $keyName, array $keyValues, $dataType = ParameterType::INTEGER)
	{
		return $this->where(
			$keyName . ' NOT IN (' . implode(',', $this->bindArray($keyValues, $dataType)) . ')'
		);
	}

	/**
	 * Extend the WHERE clause with a single condition or an array of conditions, with a potentially
	 * different logical operator from the one in the current WHERE clause.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->extendWhere('XOR', array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) XOR (c = 3 AND d = 4)
	 *
	 * @param   string  $outerGlue   The glue by which to join the conditions to the current WHERE conditions.
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $innerGlue   The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	public function extendWhere($outerGlue, $conditions, $innerGlue = 'AND')
	{
		// Replace the current WHERE with a new one which has the old one as an unnamed child.
		$this->where = new Query\QueryElement('WHERE', $this->where->setName('()'), " $outerGlue ");

		// Append the new conditions as a new unnamed child.
		$this->where->append(new Query\QueryElement('()', $conditions, " $innerGlue "));

		return $this;
	}

	/**
	 * Extend the WHERE clause with an OR and a single condition or an array of conditions.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->orWhere(array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) OR (c = 3 AND d = 4)
	 *
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	public function orWhere($conditions, $glue = 'AND')
	{
		return $this->extendWhere('OR', $conditions, $glue);
	}

	/**
	 * Extend the WHERE clause with an AND and a single condition or an array of conditions.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->andWhere(array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) AND (c = 3 OR d = 4)
	 *
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to OR.
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	public function andWhere($conditions, $glue = 'OR')
	{
		return $this->extendWhere('AND', $conditions, $glue);
	}

	/**
	 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution.
	 *
	 * @param   array|string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
	 *                                                the form ':key', but can also be an integer.
	 * @param   mixed                 $value          The value that will be bound. It can be an array, in this case it has to be
	 *                                                same length of $key; The value is passed by reference to support output
	 *                                                parameters such as those possible with stored procedures.
	 * @param   array|string          $dataType       Constant corresponding to a SQL datatype. It can be an array, in this case it
	 *                                                has to be same length of $key
	 * @param   integer               $length         The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array                 $driverOptions  Optional driver options to be used.
	 *
	 * @return  $this
	 *
	 * @since   1.5.0
	 * @throws  \InvalidArgumentException
	 */
	public function bind($key, &$value, $dataType = ParameterType::STRING, $length = 0, $driverOptions = [])
	{
		if (!$key)
		{
			throw new \InvalidArgumentException('A key is required');
		}

		$key   = (array) $key;
		$count = \count($key);

		if (\is_array($value) && $count != \count($value))
		{
			throw new \InvalidArgumentException('Array length of $key and $value are not equal');
		}

		if (\is_array($dataType) && $count != \count($dataType))
		{
			throw new \InvalidArgumentException('Array length of $key and $dataType are not equal');
		}

		for ($i = 0; $i < $count; $i++)
		{
			if (\is_array($value))
			{
				$localValue = &$value[$i];
			}
			else
			{
				$localValue = &$value;
			}

			if (\is_array($dataType))
			{
				$localDataType = $dataType[$i];
			}
			else
			{
				$localDataType = $dataType;
			}

			// Validate parameter type
			if (!isset($this->parameterMapping[$localDataType]))
			{
				throw new \InvalidArgumentException(sprintf('Unsupported parameter type `%s`', $localDataType));
			}

			$obj                = new \stdClass;
			$obj->value         = &$localValue;
			$obj->dataType      = $this->parameterMapping[$localDataType];
			$obj->length        = $length;
			$obj->driverOptions = $driverOptions;

			// Add the Key/Value into the bounded array
			$this->bounded[$key[$i]] = $obj;

			unset($localValue);
		}

		return $this;
	}

	/**
	 * Method to unbind a bound variable.
	 *
	 * @param   array|string|integer  $key  The key or array of keys to unbind.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function unbind($key)
	{
		if (\is_array($key))
		{
			foreach ($key as $k)
			{
				unset($this->bounded[$k]);
			}
		}
		else
		{
			unset($this->bounded[$key]);
		}

		return $this;
	}

	/**
	 * Binds an array of values and returns an array of prepared parameter names.
	 *
	 * Note that all values must be the same data type.
	 *
	 * Usage:
	 * $query->where('column in (' . implode(',', $query->bindArray($keyValues, $dataType)) . ')');
	 *
	 * @param   array         $values    Values to bind
	 * @param   array|string  $dataType  Constant corresponding to a SQL datatype. It can be an array, in this case it
	 *                                   has to be same length of $key
	 *
	 * @return  array   An array with parameter names
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function bindArray(array $values, $dataType = ParameterType::INTEGER)
	{
		$parameterNames = [];

		for ($i = 0; $i < count($values); $i++)
		{
			$parameterNames[] = ':preparedArray' . (++$this->preparedIndex);
		}

		$this->bind($parameterNames, $values, $dataType);

		return $parameterNames;
	}

	/**
	 * Method to provide basic copy support.
	 *
	 * Any object pushed into the data of this class should have its own __clone() implementation.
	 * This method does not support copying objects in a multidimensional array.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __clone()
	{
		foreach ($this as $k => $v)
		{
			if ($k === 'db')
			{
				continue;
			}

			if (\is_object($v))
			{
				$this->{$k} = clone $v;
			}
			elseif (\is_array($v))
			{
				foreach ($v as $i => $element)
				{
					if (\is_object($element))
					{
						$this->{$k}[$i] = clone $element;
					}
				}
			}
		}
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
	 * Combine a select statement to the current query by one of the set operators.
	 * Operators: UNION, UNION ALL, EXCEPT or INTERSECT.
	 *
	 * @param   string                $name   The name of the set operator with parentheses.
	 * @param   DatabaseQuery|string  $query  The DatabaseQuery object or string.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function merge($name, $query)
	{
		$this->type = $this->type ?: 'select';

		$this->merge[] = new Query\QueryElement($name, $query);

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
		return $this->merge($distinct ? 'UNION ()' : 'UNION ALL ()', $query);
	}

	/**
	 * Add a query to UNION ALL with the current query.
	 *
	 * Usage:
	 * $query->unionAll('SELECT name FROM  #__foo')
	 *
	 * @param   DatabaseQuery|string  $query     The DatabaseQuery object or string to union.
	 *
	 * @return  $this
	 *
	 * @see     union
	 * @since   1.5.0
	 */
	public function unionAll($query)
	{
		return $this->union($query, false);
	}

	/**
	 * Set a single query to the query set.
	 * On this type of DatabaseQuery you can use union(), unionAll(), order() and setLimit()
	 *
	 * Usage:
	 * $query->querySet($query2->select('name')->from('#__foo')->order('id DESC')->setLimit(1))
	 *       ->unionAll($query3->select('name')->from('#__foo')->order('id')->setLimit(1))
	 *       ->order('name')
	 *       ->setLimit(1)
	 *
	 * @param   DatabaseQuery  $query  The DatabaseQuery object or string.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function querySet($query)
	{
		$this->type = 'querySet';

		$this->querySet = $query;

		return $this;
	}

	/**
	 * Create a DatabaseQuery object of type querySet from current query.
	 *
	 * Usage:
	 * $query->select('name')->from('#__foo')->order('id DESC')->setLimit(1)
	 *       ->toQuerySet()
	 *       ->unionAll($query2->select('name')->from('#__foo')->order('id')->setLimit(1))
	 *       ->order('name')
	 *       ->setLimit(1)
	 *
	 * @return  DatabaseQuery  A new object of the DatabaseQuery.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function toQuerySet()
	{
		return (new static($this->db))->querySet($this);
	}

	/**
	 * Find and replace sprintf-like tokens in a format string.
	 * Each token takes one of the following forms:
	 *     %%       - A literal percent character.
	 *     %[t]     - Where [t] is a type specifier.
	 *     %[n]$[x] - Where [n] is an argument specifier and [t] is a type specifier.
	 *
	 * Types:
	 * a - Numeric: Replacement text is coerced to a numeric type but not quoted or escaped.
	 * e - Escape: Replacement text is passed to $this->escape().
	 * E - Escape (extra): Replacement text is passed to $this->escape() with true as the second argument.
	 * n - Name Quote: Replacement text is passed to $this->quoteName().
	 * q - Quote: Replacement text is passed to $this->quote().
	 * Q - Quote (no escape): Replacement text is passed to $this->quote() with false as the second argument.
	 * r - Raw: Replacement text is used as-is. (Be careful)
	 *
	 * Date Types:
	 * - Replacement text automatically quoted (use uppercase for Name Quote).
	 * - Replacement text should be a string in date format or name of a date column.
	 * y/Y - Year
	 * m/M - Month
	 * d/D - Day
	 * h/H - Hour
	 * i/I - Minute
	 * s/S - Second
	 *
	 * Invariable Types:
	 * - Takes no argument.
	 * - Argument index not incremented.
	 * t - Replacement text is the result of $this->currentTimestamp().
	 * z - Replacement text is the result of $this->nullDate(false).
	 * Z - Replacement text is the result of $this->nullDate(true).
	 *
	 * Usage:
	 * $query->format('SELECT %1$n FROM %2$n WHERE %3$n = %4$a', 'foo', '#__foo', 'bar', 1);
	 * Returns: SELECT `foo` FROM `#__foo` WHERE `bar` = 1
	 *
	 * Notes:
	 * The argument specifier is optional but recommended for clarity.
	 * The argument index used for unspecified tokens is incremented only when used.
	 *
	 * @param   string  $format  The formatting string.
	 *
	 * @return  string  Returns a string produced according to the formatting string.
	 *
	 * @since   1.0
	 */
	public function format($format)
	{
		$query = $this;
		$args  = \array_slice(\func_get_args(), 1);
		array_unshift($args, null);

		$i    = 1;
		$func = function ($match) use ($query, $args, &$i)
		{
			if (isset($match[6]) && $match[6] === '%')
			{
				return '%';
			}

			// No argument required, do not increment the argument index.
			switch ($match[5])
			{
				case 't':
					return $query->currentTimestamp();

				case 'z':
					return $query->nullDate(false);

				case 'Z':
					return $query->nullDate(true);
			}

			// Increment the argument index only if argument specifier not provided.
			$index = is_numeric($match[4]) ? (int) $match[4] : $i++;

			if (!$index || !isset($args[$index]))
			{
				// TODO - What to do? sprintf() throws a Warning in these cases.
				$replacement = '';
			}
			else
			{
				$replacement = $args[$index];
			}

			switch ($match[5])
			{
				case 'a':
					return 0 + $replacement;

				case 'e':
					return $query->escape($replacement);

				case 'E':
					return $query->escape($replacement, true);

				case 'n':
					return $query->quoteName($replacement);

				case 'q':
					return $query->quote($replacement);

				case 'Q':
					return $query->quote($replacement, false);

				case 'r':
					return $replacement;

				// Dates
				case 'y':
					return $query->year($query->quote($replacement));

				case 'Y':
					return $query->year($query->quoteName($replacement));

				case 'm':
					return $query->month($query->quote($replacement));

				case 'M':
					return $query->month($query->quoteName($replacement));

				case 'd':
					return $query->day($query->quote($replacement));

				case 'D':
					return $query->day($query->quoteName($replacement));

				case 'h':
					return $query->hour($query->quote($replacement));

				case 'H':
					return $query->hour($query->quoteName($replacement));

				case 'i':
					return $query->minute($query->quote($replacement));

				case 'I':
					return $query->minute($query->quoteName($replacement));

				case 's':
					return $query->second($query->quote($replacement));

				case 'S':
					return $query->second($query->quoteName($replacement));
			}

			return '';
		};

		/**
		 * Regexp to find an replace all tokens.
		 * Matched fields:
		 * 0: Full token
		 * 1: Everything following '%'
		 * 2: Everything following '%' unless '%'
		 * 3: Argument specifier and '$'
		 * 4: Argument specifier
		 * 5: Type specifier
		 * 6: '%' if full token is '%%'
		 */
		return preg_replace_callback('#%(((([\d]+)\$)?([aeEnqQryYmMdDhHiIsStzZ]))|(%))#', $func, $format);
	}

	/**
	 * Validate arguments which are passed to selectRowNumber method and set up common variables.
	 *
	 * @param   string  $orderBy           An expression of ordering for window function.
	 * @param   string  $orderColumnAlias  An alias for new ordering column.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	protected function validateRowNumber($orderBy, $orderColumnAlias)
	{
		if ($this->selectRowNumber)
		{
			throw new \RuntimeException("Method 'selectRowNumber' can be called only once per instance.");
		}

		$this->type = 'select';

		$this->selectRowNumber = [
			'orderBy'          => $orderBy,
			'orderColumnAlias' => $orderColumnAlias,
		];
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

		return $this->select("ROW_NUMBER() OVER (ORDER BY $orderBy) AS $orderColumnAlias");
	}
}
