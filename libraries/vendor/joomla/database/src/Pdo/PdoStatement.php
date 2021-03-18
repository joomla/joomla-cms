<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Pdo;

use Joomla\Database\FetchMode;
use Joomla\Database\FetchOrientation;
use Joomla\Database\ParameterType;
use Joomla\Database\StatementInterface;

/**
 * PDO Database Statement.
 *
 * @since  __DEPLOY_VERSION__
 */
class PdoStatement implements StatementInterface
{
	/**
	 * Mapping array for fetch modes.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private const FETCH_MODE_MAP = [
		FetchMode::ASSOCIATIVE     => \PDO::FETCH_ASSOC,
		FetchMode::NUMERIC         => \PDO::FETCH_NUM,
		FetchMode::MIXED           => \PDO::FETCH_BOTH,
		FetchMode::STANDARD_OBJECT => \PDO::FETCH_OBJ,
		FetchMode::COLUMN          => \PDO::FETCH_COLUMN,
		FetchMode::CUSTOM_OBJECT   => \PDO::FETCH_CLASS,
	];

	/**
	 * Mapping array for parameter types.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private const PARAMETER_TYPE_MAP = [
		ParameterType::BOOLEAN      => \PDO::PARAM_BOOL,
		ParameterType::INTEGER      => \PDO::PARAM_INT,
		ParameterType::LARGE_OBJECT => \PDO::PARAM_LOB,
		ParameterType::NULL         => \PDO::PARAM_NULL,
		ParameterType::STRING       => \PDO::PARAM_STR,
	];

	/**
	 * The decorated PDOStatement object.
	 *
	 * @var    \PDOStatement
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pdoStatement;

	/**
	 * Statement constructor
	 *
	 * @param   \PDOStatement  $pdoStatement  The decorated PDOStatement object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\PDOStatement $pdoStatement)
	{
		$this->pdoStatement = $pdoStatement;
	}

	/**
	 * Binds a parameter to the specified variable name.
	 *
	 * @param   string|integer  $parameter      Parameter identifier. For a prepared statement using named placeholders, this will be a parameter
	 *                                          name of the form `:name`. For a prepared statement using question mark placeholders, this will be
	 *                                          the 1-indexed position of the parameter.
	 * @param   mixed           $variable       Name of the PHP variable to bind to the SQL statement parameter.
	 * @param   string          $dataType       Constant corresponding to a SQL datatype, this should be the processed type from the QueryInterface.
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array           $driverOptions  Optional driver options to be used.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function bindParam($parameter, &$variable, string $dataType = ParameterType::STRING, ?int $length = null, ?array $driverOptions = null)
	{
		$type            = $this->convertParameterType($dataType);
		$extraParameters = array_slice(func_get_args(), 3);

		if (count($extraParameters) !== 0)
		{
			$extraParameters[0] = $extraParameters[0] ?? 0;
		}

		$this->pdoStatement->bindParam($parameter, $variable, $type, ...$extraParameters);

		return true;
	}

	/**
	 * Closes the cursor, enabling the statement to be executed again.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function closeCursor(): void
	{
		$this->pdoStatement->closeCursor();
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
		return $this->pdoStatement->errorCode();
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
		return $this->pdoStatement->errorInfo();
	}

	/**
	 * Executes a prepared statement
	 *
	 * @param   array|null  $parameters  An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute(?array $parameters = null)
	{
		return $this->pdoStatement->execute($parameters);
	}

	/**
	 * Fetches the next row from a result set
	 *
	 * @param   integer|null  $fetchStyle         Controls how the next row will be returned to the caller. This value must be one of the
	 *                                            FetchMode constants, defaulting to value of FetchMode::MIXED.
	 * @param   integer       $cursorOrientation  For a StatementInterface object representing a scrollable cursor, this value determines which row
	 *                                            will be returned to the caller. This value must be one of the FetchOrientation constants,
	 *                                            defaulting to FetchOrientation::NEXT.
	 * @param   integer       $cursorOffset       For a StatementInterface object representing a scrollable cursor for which the cursorOrientation
	 *                                            parameter is set to FetchOrientation::ABS, this value specifies the absolute number of the row in
	 *                                            the result set that shall be fetched. For a StatementInterface object representing a scrollable
	 *                                            cursor for which the cursorOrientation parameter is set to FetchOrientation::REL, this value
	 *                                            specifies the row to fetch relative to the cursor position before `fetch()` was called.
	 *
	 * @return  mixed  The return value of this function on success depends on the fetch type. In all cases, boolean false is returned on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fetch(?int $fetchStyle = null, int $cursorOrientation = FetchOrientation::NEXT, int $cursorOffset = 0)
	{
		if ($fetchStyle === null)
		{
			return $this->pdoStatement->fetch();
		}

		return $this->pdoStatement->fetch($this->convertFetchMode($fetchStyle), $cursorOrientation, $cursorOffset);
	}

	/**
	 * Returns the number of rows affected by the last SQL statement.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function rowCount(): int
	{
		return $this->pdoStatement->rowCount();
	}

	/**
	 * Sets the fetch mode to use while iterating this statement.
	 *
	 * @param   integer  $fetchMode  The fetch mode, must be one of the FetchMode constants.
	 * @param   mixed    ...$args    Optional mode-specific arguments.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setFetchMode(int $fetchMode, ...$args): void
	{
		$this->pdoStatement->setFetchMode($this->convertFetchMode($fetchMode), ...$args);
	}

	/**
	 * Converts the database API's fetch mode to a PDO fetch mode
	 *
	 * @param   integer  $mode  Fetch mode to convert
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException if the fetch mode is unsupported
	 */
	private function convertFetchMode(int $mode): int
	{
		if (!isset(self::FETCH_MODE_MAP[$mode]))
		{
			throw new \InvalidArgumentException(sprintf('Unsupported fetch mode `%s`', $mode));
		}

		return self::FETCH_MODE_MAP[$mode];
	}

	/**
	 * Converts the database API's parameter type to a PDO parameter type
	 *
	 * @param   string  $type  Parameter type to convert
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException if the parameter type is unsupported
	 */
	private function convertParameterType(string $type): int
	{
		if (!isset(self::PARAMETER_TYPE_MAP[$type]))
		{
			throw new \InvalidArgumentException(sprintf('Unsupported parameter type `%s`', $type));
		}

		return self::PARAMETER_TYPE_MAP[$type];
	}
}
