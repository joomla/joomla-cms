<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Postgresql;

use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;
use Joomla\Database\FetchMode;
use Joomla\Database\FetchOrientation;
use Joomla\Database\ParameterType;
use Joomla\Database\StatementInterface;

/**
 * PostgreSQL Database Statement.
 *
 * @since  __DEPLOY_VERSION__
 */
class PostgresqlStatement implements StatementInterface
{
	/**
	 * Holds key / value pair of bound objects.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $bounded = [];

	/**
	 * The database connection resource.
	 *
	 * @var    resource
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
	 * The current query count.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $queryCount;

	/**
	 * Contains the name of the prepared query
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $queryName = 'query';

	/**
	 * Internal tracking flag to set whether there is a result set available for processing
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $result = false;

	/**
	 * The prepared statement.
	 *
	 * @var    resource
	 * @since  __DEPLOY_VERSION__
	 */
	protected $statement;

	/**
	 * Constructor.
	 *
	 * @param   resource  $connection  The database connection resource
	 * @param   string    $query       The query this statement will process
	 * @param   integer   $queryCount  The current query count
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  PrepareStatementFailureException
	 */
	public function __construct($connection, string $query, int $queryCount)
	{
		$this->connection = $connection;
		$this->query      = $query;
		$this->queryCount = $queryCount;
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
		$this->bounded[$parameter] = $variable;

		return true;
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
		if ($this->result && $this->statement)
		{
			pg_free_result($this->statement);
		}

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
		return pg_result_error_field($this->statement, PGSQL_DIAG_SQLSTATE);
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
		return pg_result_error($this->statement);
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
		$prepared = $this->prepare();

		// Error suppression is required otherwise the underlying API will emit non-catchable warnings
		$this->statement = @pg_execute($this->connection, $this->queryName . $this->queryCount, array_values($this->bounded));

		if (!$this->statement)
		{
			throw new ExecutionFailureException($this->query, $this->errorInfo(), $this->errorCode());
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

		switch ($fetchStyle)
		{
			case FetchMode::NUMERIC:
				return pg_fetch_row($this->statement);

			case FetchMode::ASSOCIATIVE:
			case FetchMode::MIXED:
				return pg_fetch_assoc($this->statement);

			case FetchMode::STANDARD_OBJECT:
			case FetchMode::CUSTOM_OBJECT:
				return pg_fetch_object($this->statement, null, $this->defaultObjectClass);

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
	 * Prepares the SQL Server statement resource for execution
	 *
	 * @return  resource
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function prepare()
	{
		$statement = pg_prepare($this->connection, $this->queryName . $this->queryCount, $this->query);

		if (!$statement)
		{
			$errors = $this->errorInfo();

			throw new PrepareStatementFailureException(pg_last_error($this->connection));
		}

		return $statement;
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
		return pg_affected_rows($this->statement);
	}
}
