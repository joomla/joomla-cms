<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlsrv;

use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;
use Joomla\Database\FetchMode;
use Joomla\Database\FetchOrientation;
use Joomla\Database\ParameterType;
use Joomla\Database\StatementInterface;

/**
 * SQL Server Database Statement.
 *
 * This class is modeled on \Doctrine\DBAL\Driver\SQLSrv\SQLSrvStatement
 *
 * @since  __DEPLOY_VERSION__
 */
class SqlsrvStatement implements StatementInterface
{
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
	 * Mapping array converting fetch modes to the native engine type.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $fetchMap = [
		FetchMode::MIXED       => SQLSRV_FETCH_BOTH,
		FetchMode::ASSOCIATIVE => SQLSRV_FETCH_ASSOC,
		FetchMode::NUMERIC     => SQLSRV_FETCH_NUMERIC,
	];

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
	 * The prepared statement.
	 *
	 * @var    resource
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
	 * References to the variables bound as statement parameters.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $variables = [];

	/**
	 * Constructor.
	 *
	 * @param   resource  $connection  The database connection resource
	 * @param   string    $query       The query this statement will process
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  PrepareStatementFailureException
	 */
	public function __construct($connection, string $query)
	{
		$this->connection = $connection;
		$this->query      = $query;
	}

	/**
	 * Binds a parameter to the specified variable name.
	 *
	 * @param   string|integer  $parameter       Parameter identifier. For a prepared statement using named placeholders, this will be a parameter
	 *                                           name of the form `:name`. For a prepared statement using question mark placeholders, this will be
	 *                                           the 1-indexed position of the parameter.
	 * @param   mixed           $variable        Name of the PHP variable to bind to the SQL statement parameter.
	 * @param   integer         $dataType        Constant corresponding to a SQL datatype, this should be the processed type from the QueryInterface.
	 * @param   integer         $length          The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array           $driverOptions   Optional driver options to be used.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function bindParam($parameter, &$variable, $dataType = ParameterType::STRING, $length = null, $driverOptions = null)
	{
		if (!is_numeric($parameter))
		{
			throw new \InvalidArgumentException(
				'SQL Server does not support named parameters for queries, use question mark (?) placeholders instead.'
			);
		}

		$this->variables[$parameter] =& $variable;
		$this->types[$parameter]     = $dataType;

		$this->statement = null;

		return true;
	}

	/**
	 * Binds a value to the specified variable.
	 *
	 * @param   string|integer  $parameter       Parameter identifier. For a prepared statement using named placeholders, this will be a parameter
	 *                                           name of the form `:name`. For a prepared statement using question mark placeholders, this will be
	 *                                           the 1-indexed position of the parameter.
	 * @param   mixed           $variable        Name of the PHP variable to bind to the SQL statement parameter.
	 * @param   integer         $dataType        Constant corresponding to a SQL datatype, this should be the processed type from the QueryInterface.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function bindValue($parameter, $variable, $dataType = ParameterType::STRING)
	{
		if (!is_numeric($parameter))
		{
			throw new \InvalidArgumentException(
				'SQL Server does not support named parameters for queries, use question mark (?) placeholders instead.'
			);
		}

		$this->variables[$parameter] = $variable;
		$this->types[$parameter]     = $dataType;
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
		if (!$this->result || !is_resource($this->statement))
		{
			return true;
		}

		// Emulate freeing the result fetching and discarding rows, similarly to what PDO does in this case
		while (sqlsrv_fetch($this->statement));

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
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

		if ($errors)
		{
			return $errors[0]['code'];
		}

		return false;
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
		return sqlsrv_errors(SQLSRV_ERR_ERRORS);
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
		if ($parameters)
		{
			$hasZeroIndex = array_key_exists(0, $parameters);

			foreach ($parameters as $key => $val)
			{
				$key = ($hasZeroIndex && is_numeric($key)) ? $key + 1 : $key;
				$this->bindValue($key, $val);
			}
		}

		if (!$this->statement)
		{
			$this->statement = $this->prepare();
		}

		if (!sqlsrv_execute($this->statement))
		{
			$errors = $this->errorInfo();

			throw new ExecutionFailureException($this->query, $errors[0]['message'], $errors[0]['code']);
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

		if (isset($this->fetchMap[$fetchStyle]))
		{
			return sqlsrv_fetch_array($this->statement, $this->fetchMap[$fetchStyle]) ?: false;
		}

		if (in_array($fetchStyle, [FetchMode::STANDARD_OBJECT, FetchMode::CUSTOM_OBJECT], true))
		{
			return sqlsrv_fetch_object($this->statement, $this->defaultObjectClass) ?: false;
		}

		throw new \InvalidArgumentException("Unknown fetch type '{$fetchStyle}'");
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
	 * @param   string  $className        Name of the created class.
	 * @param   array   $constructorArgs  Elements of this array are passed to the constructor.
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
		$params = [];

		foreach ($this->variables as $column => &$variable)
		{
			if ($this->types[$column] === ParameterType::LARGE_OBJECT)
			{
				$params[$column - 1] = [
					&$variable,
					SQLSRV_PARAM_IN,
					SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
					SQLSRV_SQLTYPE_VARBINARY('max'),
				];
			}
			else
			{
				$params[$column - 1] =& $variable;
			}
		}

		$options = [];

		// SQLSRV Function sqlsrv_num_rows requires a static or keyset cursor.
		if (strncmp(strtoupper(ltrim($this->query)), 'SELECT', strlen('SELECT')) === 0)
		{
			$options = ['Scrollable' => SQLSRV_CURSOR_KEYSET];
		}

		$statement = sqlsrv_prepare($this->connection, $this->query, $params, $options);

		if (!$statement)
		{
			$errors = $this->errorInfo();

			throw new PrepareStatementFailureException($errors[0]['message'], $errors[0]['code']);
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
		if (strncmp(strtoupper(ltrim($this->query)), 'SELECT', strlen('SELECT')) === 0)
		{
			return sqlsrv_num_rows($this->statement);
		}

		return sqlsrv_rows_affected($this->statement);
	}
}
