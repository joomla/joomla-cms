<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Database connector class.
 *
 * @since       11.1
 * @deprecated  13.3 (Platform) & 4.0 (CMS)
 */
abstract class FOFDatabase
{
	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 * @deprecated  13.1 (Platform) & 4.0 (CMS)
	 */
	public function query()
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::query() is deprecated, use FOFDatabaseDriver::execute() instead.', JLog::WARNING, 'deprecated');
		}

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
	 * @deprecated  13.1 (Platform) & 4.0 (CMS)
	 */
	public static function getConnectors()
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::getConnectors() is deprecated, use FOFDatabaseDriver::getConnectors() instead.', JLog::WARNING, 'deprecated');
		}

		return FOFDatabaseDriver::getConnectors();
	}

	/**
	 * Gets the error message from the database connection.
	 *
	 * @param   boolean  $escaped  True to escape the message string for use in JavaScript.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @deprecated  13.3 (Platform) & 4.0 (CMS)
	 * @since   11.1
	 */
	public function getErrorMsg($escaped = false)
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::getErrorMsg() is deprecated, use exception handling instead.', JLog::WARNING, 'deprecated');
		}

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
	 * @deprecated  13.3 (Platform) & 4.0 (CMS)
	 */
	public function getErrorNum()
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::getErrorNum() is deprecated, use exception handling instead.', JLog::WARNING, 'deprecated');
		}

		return $this->errorNum;
	}

	/**
	 * Method to return a FOFDatabaseDriver instance based on the given options.  There are three global options and then
	 * the rest are specific to the database driver.  The 'driver' option defines which FOFDatabaseDriver class is
	 * used for the connection -- the default is 'mysqli'.  The 'database' option determines which database is to
	 * be used for the connection.  The 'select' option determines whether the connector should automatically select
	 * the chosen database.
	 *
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method.  This ensures that we don't end up with unnecessary database connection resources.
	 *
	 * @param   array  $options  Parameters to be passed to the database driver.
	 *
	 * @return  FOFDatabaseDriver  A database object.
	 *
	 * @since       11.1
	 * @deprecated  13.1 (Platform) & 4.0 (CMS)
	 */
	public static function getInstance($options = array())
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::getInstance() is deprecated, use FOFDatabaseDriver::getInstance() instead.', JLog::WARNING, 'deprecated');
		}

		return FOFDatabaseDriver::getInstance($options);
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 *
	 * @param   string  $query  Input SQL string with which to split into individual queries.
	 *
	 * @return  array  The queries from the input string separated into an array.
	 *
	 * @since   11.1
	 * @deprecated  13.1 (Platform) & 4.0 (CMS)
	 */
	public static function splitSql($query)
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::splitSql() is deprecated, use FOFDatabaseDriver::splitSql() instead.', JLog::WARNING, 'deprecated');
		}

		return FOFDatabaseDriver::splitSql($query);
	}

	/**
	 * Return the most recent error message for the database connector.
	 *
	 * @param   boolean  $showSQL  True to display the SQL statement sent to the database as well as the error.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @since   11.1
	 * @deprecated  13.3 (Platform) & 4.0 (CMS)
	 */
	public function stderr($showSQL = false)
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::stderr() is deprecated.', JLog::WARNING, 'deprecated');
		}

		if ($this->errorNum != 0)
		{
			return JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $this->errorNum, $this->errorMsg)
			. ($showSQL ? "<br>SQL = <pre>$this->sql</pre>" : '');
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
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use FOFDatabaseDriver::isSupported() instead.
	 */
	public static function test()
	{
		if (class_exists('JLog'))
		{
			JLog::add('FOFDatabase::test() is deprecated. Use FOFDatabaseDriver::isSupported() instead.', JLog::WARNING, 'deprecated');
		}

		return static::isSupported();
	}
}
