<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Interface defining a query statement.
 *
 * This interface is a partial standalone implementation of PDOStatement.
 *
 * @since  __DEPLOY_VERSION__
 */
interface StatementInterface
{
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
	public function bindParam($parameter, &$variable, string $dataType = ParameterType::STRING, ?int $length = null, ?array $driverOptions = null);

	/**
	 * Closes the cursor, enabling the statement to be executed again.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function closeCursor(): void;

	/**
	 * Fetches the SQLSTATE associated with the last operation on the statement handle.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function errorCode();

	/**
	 * Fetches extended error information associated with the last operation on the statement handle.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function errorInfo();

	/**
	 * Executes a prepared statement
	 *
	 * @param   array|null  $parameters  An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute(?array $parameters = null);

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
	public function fetch(?int $fetchStyle = null, int $cursorOrientation = FetchOrientation::NEXT, int $cursorOffset = 0);

	/**
	 * Returns the number of rows affected by the last SQL statement.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function rowCount(): int;

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
	public function setFetchMode(int $fetchMode, ...$args): void;
}
