<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Joomla Framework Database Factory class
 *
 * @since  1.0
 */
class DatabaseFactory
{
	/**
	 * Method to return a DatabaseDriver instance based on the given options.
	 *
	 * There are three global options and then the rest are specific to the database driver. The 'database' option determines which database is to
	 * be used for the connection. The 'select' option determines whether the connector should automatically select the chosen database.
	 *
	 * @param   string  $name     Name of the database driver you'd like to instantiate
	 * @param   array   $options  Parameters to be passed to the database driver.
	 *
	 * @return  DatabaseDriver
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getDriver($name = 'mysqli', array $options = [])
	{
		// Sanitize the database connector options.
		$options['driver']   = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		$options['database'] = isset($options['database']) ? $options['database'] : null;
		$options['select']   = isset($options['select']) ? $options['select'] : true;

		// Derive the class name from the driver.
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($options['driver'])) . '\\' . ucfirst(strtolower($options['driver'])) . 'Driver';

		// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
		if (!class_exists($class))
		{
			throw new Exception\UnsupportedAdapterException(sprintf('Unable to load Database Driver: %s', $options['driver']));
		}

		// Create our new DatabaseDriver connector based on the options given.
		try
		{
			return new $class($options);
		}
		catch (\RuntimeException $e)
		{
			throw new Exception\ConnectionFailureException(sprintf('Unable to connect to the Database: %s', $e->getMessage()), $e->getCode(), $e);
		}
	}

	/**
	 * Gets an exporter class object.
	 *
	 * @param   string          $name  Name of the driver you want an exporter for.
	 * @param   DatabaseDriver  $db    Optional DatabaseDriver instance to inject into the exporter.
	 *
	 * @return  DatabaseExporter
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException
	 */
	public function getExporter($name, DatabaseDriver $db = null)
	{
		// Derive the class name from the driver.
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($name)) . '\\' . ucfirst(strtolower($name)) . 'Exporter';

		// Make sure we have an exporter class for this driver.
		if (!class_exists($class))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new Exception\UnsupportedAdapterException('Database Exporter not found.');
		}

		/** @var $o DatabaseExporter */
		$o = new $class;

		if ($db instanceof DatabaseDriver)
		{
			$o->setDbo($db);
		}

		return $o;
	}

	/**
	 * Gets an importer class object.
	 *
	 * @param   string          $name  Name of the driver you want an importer for.
	 * @param   DatabaseDriver  $db    Optional DatabaseDriver instance to inject into the importer.
	 *
	 * @return  DatabaseImporter
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException
	 */
	public function getImporter($name, DatabaseDriver $db = null)
	{
		// Derive the class name from the driver.
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($name)) . '\\' . ucfirst(strtolower($name)) . 'Importer';

		// Make sure we have an importer class for this driver.
		if (!class_exists($class))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new Exception\UnsupportedAdapterException('Database importer not found.');
		}

		/** @var $o DatabaseImporter */
		$o = new $class;

		if ($db instanceof DatabaseDriver)
		{
			$o->setDbo($db);
		}

		return $o;
	}

	/**
	 * Get a new iterator on the current query.
	 *
	 * @param   string          $name    Name of the driver you want an iterator for.
	 * @param   DatabaseDriver  $db      DatabaseDriver instance with the query to be iterated.
	 * @param   string          $column  An optional column to use as the iterator key.
	 * @param   string          $class   The class of object that is returned.
	 *
	 * @return  DatabaseIterator
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function getIterator(string $name, DatabaseDriver $db, $column = null, string $class = '\\stdClass'): DatabaseIterator
	{
		// Derive the class name from the driver.
		$iteratorClass = __NAMESPACE__ . '\\' . ucfirst($name) . '\\' . ucfirst($name) . 'Iterator';

		// Make sure we have an iterator class for this driver.
		if (!class_exists($iteratorClass))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new \RuntimeException(sprintf('Class *%s* is not defined', $iteratorClass));
		}

		// Return a new iterator
		return new $iteratorClass($db->execute(), $column, $class);
	}

	/**
	 * Get the current query object or a new Query object.
	 *
	 * @param   string             $name  Name of the driver you want an query object for.
	 * @param   DatabaseInterface  $db    Optional Driver instance
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException
	 */
	public function getQuery($name, DatabaseInterface $db = null)
	{
		// Derive the class name from the driver.
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($name)) . '\\' . ucfirst(strtolower($name)) . 'Query';

		// Make sure we have a query class for this driver.
		if (!class_exists($class))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new Exception\UnsupportedAdapterException('Database Query class not found');
		}

		return new $class($db);
	}
}
