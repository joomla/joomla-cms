<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Mysqli;

use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;
use Joomla\Database\FetchMode;
use Joomla\Database\FetchOrientation;
use Joomla\Database\ParameterType;
use Joomla\Database\StatementInterface;

/**
 * MySQLi Database Statement.
 *
 * This class is modeled on \Doctrine\DBAL\Driver\Mysqli\MysqliStatement
 *
 * @since  __DEPLOY_VERSION__
 */
class MysqliStatement implements StatementInterface
{
	/**
	 * Values which have been bound to the statement.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $bindedValues;

	/**
	 * Column names from the executed statement.
	 *
	 * @var    array|boolean|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $columnNames;

	/**
	 * The database connection resource.
	 *
	 * @var    \mysqli
	 * @since  __DEPLOY_VERSION__
	 */
	protected $connection;

	/**
	 * The default fetch mode for the statement.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $defaultFetchStyle = FetchMode::MIXED;

	/**
	 * The default class to use for building object result sets.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $defaultObjectClass = \stdClass::class;

	/**
	 * The query string being prepared.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $query;

	/**
	 * Internal tracking flag to set whether there is a result set available for processing
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $result = false;

	/**
	 * Values which have been bound to the rows of each result set.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $rowBindedValues;

	/**
	 * The prepared statement.
	 *
	 * @var    \mysqli_stmt
	 * @since  __DEPLOY_VERSION__
	 */
	protected $statement;

	/**
	 * Bound parameter types.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $types;

	/**
	 * Constructor.
	 *
	 * @param   \mysqli  $connection  The database connection resource
	 * @param   string   $query       The query this statement will process
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  PrepareStatementFailureException
	 */
	public function __construct(\mysqli $connection, string $query)
	{
		$this->connection = $connection;
		$this->query      = $query;
		$this->statement  = $connection->prepare($query);

		if (!$this->statement)
		{
			throw new PrepareStatementFailureException($this->connection->error, $this->connection->errno);
		}

		$paramCount = $this->statement->param_count;

		if ($paramCount > 0)
		{
			$this->types        = str_repeat('s', $paramCount);
			$this->bindedValues = array_fill(1, $paramCount, null);
		}
	}

	/**
	 * Binds a parameter to the specified variable name.
	 *
	 * @param   string|integer  $parameter      Parameter identifier. For a prepared statement using named placeholders, this will be a parameter
	 *                                          name of the form `:name`. For a prepared statement using question mark placeholders, this will be
	 *                                          the 1-indexed position of the parameter.
	 * @param   mixed           $variable       Name of the PHP variable to bind to the SQL statement parameter.
	 * @param   integer         $dataType       Constant corresponding to a SQL datatype, this should be the processed type from the QueryInterface.
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array           $driverOptions  Optional driver options to be used.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function bindParam($parameter, &$variable, $dataType = ParameterType::STRING, $length = null, $driverOptions = null)
	{
		$this->bindedValues[$parameter] =& $variable;
		$this->types[$parameter - 1]    = $dataType;

		return true;
	}

	/**
	 * Binds a array of values to bound parameters.
	 *
	 * @param   array  $values  The values to bind to the statement
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function bindValues($values)
	{
		$params    = [];
		$types     = str_repeat('s', count($values));
		$params[0] = $types;

		foreach ($values as &$v)
		{
			$params[] =& $v;
		}

		return call_user_func_array([$this->statement, 'bind_param'], $params);
	}

	/**
	 * Closes the cursor, enabling the statement to be executed again.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function closeCursor()
	{
		$this->statement->free_result();
		$this->result = false;

		return true;
	}

	/**
	 * Fetches the SQLSTATE associated with the last operation on the statement handle.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function errorCode()
	{
		return $this->statement->errno;
	}

	/**
	 * Fetches extended error information associated with the last operation on the statement handle.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function errorInfo()
	{
		return $this->statement->error;
	}

	/**
	 * Executes a prepared statement
	 *
	 * @param   array  $parameters  An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute($parameters = null)
	{
		if ($this->bindedValues !== null)
		{
			if ($parameters !== null)
			{
				if (!$this->bindValues($params))
				{
					throw new PrepareStatementFailureException($this->statement->error, $this->statement->errno);
				}
			}
			else
			{
				if (!call_user_func_array([$this->statement, 'bind_param'], [$this->types] + $this->bindedValues))
				{
					throw new PrepareStatementFailureException($this->statement->error, $this->statement->errno);
				}
			}
		}

		if (!$this->statement->execute())
		{
			throw new ExecutionFailureException($this->query, $this->statement->error, $this->statement->errno);
		}

		if ($this->columnNames === null)
		{
			$meta = $this->statement->result_metadata();

			if ($meta !== false)
			{
				$columnNames = [];

				foreach ($meta->fetch_fields() as $col)
				{
					$columnNames[] = $col->name;
				}

				$meta->free();

				$this->columnNames = $columnNames;
			}
			else
			{
				$this->columnNames = false;
			}
		}

		if ($this->columnNames !== false)
		{
			$this->statement->store_result();

			$this->rowBindedValues = array_fill(0, count($this->columnNames), null);
			$refs                  = [];

			foreach ($this->rowBindedValues as $key => &$value)
			{
				$refs[$key] =& $value;
			}

			if (!call_user_func_array([$this->statement, 'bind_result'], $refs))
			{
				throw new \RuntimeException($this->statement->error, $this->statement->errno);
			}
		}

		$this->result = true;

		return true;
	}

	/**
	 * Fetches the next row from a result set
	 *
	 * @param   integer $fetchStyle          Controls how the next row will be returned to the caller. This value must be one of the
	 *                                       FetchMode constants, defaulting to value of FetchMode::MIXED.
	 * @param   integer $cursorOrientation   For a StatementInterface object representing a scrollable cursor, this value determines which row will
	 *                                       be returned to the caller. This value must be one of the FetchOrientation constants, defaulting to
	 *                                       FetchOrientation::NEXT.
	 * @param   integer $cursorOffset        For a StatementInterface object representing a scrollable cursor for which the cursorOrientation
	 *                                       parameter is set to FetchOrientation::ABS, this value specifies the absolute number of the row in the
	 *                                       result set that shall be fetched. For a StatementInterface object representing a scrollable cursor for
	 *                                       which the cursorOrientation parameter is set to FetchOrientation::REL, this value specifies the row to
	 *                                       fetch relative to the cursor position before StatementInterface::fetch() was called.
	 *
	 * @return  mixed  The return value of this function on success depends on the fetch type. In all cases, boolean false is returned on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fetch($fetchStyle = null, $cursorOrientation = FetchOrientation::NEXT, $cursorOffset = 0)
	{
		if (!$this->result)
		{
			return false;
		}

		$fetchStyle = $fetchStyle ?: $this->defaultFetchStyle;

		if ($fetchStyle === FetchMode::COLUMN)
		{
			return $this->fetchColumn();
		}

		$values = $this->fetchData();

		if ($values === null)
		{
			return false;
		}

		if ($values === false)
		{
			throw new \RuntimeException($this->statement->error, $this->statement->errno);
		}

		switch ($fetchStyle)
		{
			case FetchMode::NUMERIC:
				return $values;

			case FetchMode::ASSOCIATIVE:
				return array_combine($this->columnNames, $values);

			case FetchMode::MIXED:
				$ret = array_combine($this->columnNames, $values);
				$ret += $values;

				return $ret;

			case FetchMode::STANDARD_OBJECT:
				$assoc = array_combine($this->columnNames, $values);
				$class = $this->defaultObjectClass;
				$ret   = new $class;

				foreach ($assoc as $column => $value)
				{
					$ret->$column = $value;
				}

				return $ret;

			default:
				throw new \InvalidArgumentException("Unknown fetch type '{$fetchStyle}'");
		}
	}

	/**
	 * Returns a single column from the next row of a result set
	 *
	 * @param   integer  $columnIndex  0-indexed number of the column you wish to retrieve from the row.
	 *                                 If no value is supplied, the first column is retrieved.
	 *
	 * @return  mixed  Returns a single column from the next row of a result set or boolean false if there are no more rows.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fetchColumn($columnIndex = 0)
	{
		$row = $this->fetch(FetchMode::NUMERIC);

		if ($row === false)
		{
			return false;
		}

		return $row[$columnIndex] ?? null;
	}

	/**
	 * Fetch the data from the statement.
	 *
	 * @return  array|boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function fetchData()
	{
		$return = $this->statement->fetch();

		if ($return === true)
		{
			$values = [];

			foreach ($this->rowBindedValues as $v)
			{
				$values[] = $v;
			}

			return $values;
		}

		return $return;
	}

	/**
	 * Fetches the next row and returns it as an object.
	 *
	 * @param   string $className       Name of the created class.
	 * @param   array  $constructorArgs Elements of this array are passed to the constructor.
	 *
	 * @return  mixed  An instance of the required class with property names that correspond to the column names or boolean false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fetchObject($className = null, $constructorArgs = null)
	{
		$this->defaultObjectClass = $className;

		return $this->fetch(FetchMode::STANDARD_OBJECT);
	}

	/**
	 * Returns the number of rows affected by the last SQL statement.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function rowCount()
	{
		if ($this->columnNames === false)
		{
			return $this->statement->affected_rows;
		}

		return $this->statement->num_rows;
	}
}
