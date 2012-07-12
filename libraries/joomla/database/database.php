<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Database connector class.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.1
 * @deprecated  13.1
 */
abstract class JDatabase
{
	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function query()
	{
		JLog::add('JDatabase::query() is deprecated, use JDatabaseDriver::execute() instead.', JLog::WARNING, 'deprecated');

		return $this->execute();
	}

	/**
	 * Get a list of available database connectors.  The list will only be populated with connectors that both
	 * the class exists and the static test method returns true.  This gives us the ability to have a multitude
	 * of connector classes that are self-aware as to whether or not they are able to be used on a given system.
	 *
	 * @return  array  An array of available database connectors.
	 *
	 * @since   11.1
	 * @deprecated  13.1
	 */
	public static function getConnectors()
	{
		JLog::add('JDatabase::getConnectors() is deprecated, use JDatabaseDriver::getConnectors() instead.', JLog::WARNING, 'deprecated');

		return JDatabaseDriver::getConnectors();
	}

	/**
	 * Gets the error message from the database connection.
	 *
	 * @param   boolean  $escaped  True to escape the message string for use in JavaScript.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public function getErrorMsg($escaped = false)
	{
		JLog::add('JDatabase::getErrorMsg() is deprecated, use exception handling instead.', JLog::WARNING, 'deprecated');

		if ($escaped)
		{
			return addslashes($this->errorMsg);
		}
		else
		{
			return $this->errorMsg;
		}
	}

	/**
	 * Gets the error number from the database connection.
	 *
	 * @return      integer  The error number for the most recent query.
	 *
	 * @since       11.1
	 * @deprecated  12.1
	 */
	public function getErrorNum()
	{
		JLog::add('JDatabase::getErrorNum() is deprecated, use exception handling instead.', JLog::WARNING, 'deprecated');

		return $this->errorNum;
	}

	/**
	 * Method to return a JDatabaseDriver instance based on the given options.  There are three global options and then
	 * the rest are specific to the database driver.  The 'driver' option defines which JDatabaseDriver class is
	 * used for the connection -- the default is 'mysql'.  The 'database' option determines which database is to
	 * be used for the connection.  The 'select' option determines whether the connector should automatically select
	 * the chosen database.
	 *
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method.  This ensures that we don't end up with unnecessary database connection resources.
	 *
	 * @param   array  $options  Parameters to be passed to the database driver.
	 *
	 * @return  JDatabaseDriver  A database object.
	 *
	 * @since       11.1
	 * @deprecated  13.1
	 */
	public static function getInstance($options = array())
	{
		JLog::add('JDatabase::getInstance() is deprecated, use JDatabaseDriver::getInstance() instead.', JLog::WARNING, 'deprecated');

		return JDatabaseDriver::getInstance($options);
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 *
	 * @param   string  $sql  Input SQL string with which to split into individual queries.
	 *
	 * @return  array  The queries from the input string separated into an array.
	 *
	 * @since   11.1
	 * @deprecated  13.1
	 */
	public static function splitSql($sql)
	{
		JLog::add('JDatabase::splitSql() is deprecated, use JDatabaseDriver::splitSql() instead.', JLog::WARNING, 'deprecated');

		return JDatabaseDriver::splitSql($sql);
	}

	/**
	 * Return the most recent error message for the database connector.
	 *
	 * @param   boolean  $showSQL  True to display the SQL statement sent to the database as well as the error.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @since   11.1
	 * @deprecated  12.1
	 */
	public function stderr($showSQL = false)
	{
		JLog::add('JDatabase::stderr() is deprecated.', JLog::WARNING, 'deprecated');

		if ($this->errorNum != 0)
		{
			return JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $this->errorNum, $this->errorMsg)
			. ($showSQL ? "<br />SQL = <pre>$this->sql</pre>" : '');
		}
		else
		{
			return JText::_('JLIB_DATABASE_FUNCTION_NOERROR');
		}
	}

	/**
	 * Test to see if the connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 * @deprecated  12.3 Use JDatabaseDriver::isSupported() instead.
	 */
	public static function test()
	{
		JLog::add('JDatabase::test() is deprecated. Use JDatabaseDriver::isSupported() instead.', JLog::WARNING, 'deprecated');

		return static::isSupported();
	}
}
