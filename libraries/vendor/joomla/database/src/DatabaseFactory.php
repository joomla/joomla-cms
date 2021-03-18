<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
	 * Method to return a database driver based on the given options.
	 *
	 * There are three global options and then the rest are specific to the database driver. The 'database' option determines which database is to
	 * be used for the connection. The 'select' option determines whether the connector should automatically select the chosen database.
	 *
	 * @param   string  $name     Name of the database driver you'd like to instantiate
	 * @param   array   $options  Parameters to be passed to the database driver.
	 *
	 * @return  DatabaseInterface
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException if there is not a compatible database driver
	 */
	public function getDriver(string $name = 'mysqli', array $options = []): DatabaseInterface
	{
		// Sanitize the database connector options.
		$options['driver']   = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		$options['database'] = $options['database'] ?? null;
		$options['select']   = $options['select'] ?? true;
		$options['factory']  = $options['factory'] ?? $this;

		// Derive the class name from the driver.
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($options['driver'])) . '\\' . ucfirst(strtolower($options['driver'])) . 'Driver';

		// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
		if (!class_exists($class))
		{
			throw new Exception\UnsupportedAdapterException(sprintf('Unable to load Database Driver: %s', $options['driver']));
		}

		return new $class($options);
	}

	/**
	 * Gets an exporter class object.
	 *
	 * @param   string                  $name  Name of the driver you want an exporter for.
	 * @param   DatabaseInterface|null  $db    Optional database driver to inject into the query object.
	 *
	 * @return  DatabaseExporter
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException if there is not a compatible database exporter
	 */
	public function getExporter(string $name, ?DatabaseInterface $db = null): DatabaseExporter
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

		if ($db)
		{
			$o->setDbo($db);
		}

		return $o;
	}

	/**
	 * Gets an importer class object.
	 *
	 * @param   string                  $name  Name of the driver you want an importer for.
	 * @param   DatabaseInterface|null  $db    Optional database driver to inject into the query object.
	 *
	 * @return  DatabaseImporter
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException if there is not a compatible database importer
	 */
	public function getImporter(string $name, ?DatabaseInterface $db = null): DatabaseImporter
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

		if ($db)
		{
			$o->setDbo($db);
		}

		return $o;
	}

	/**
	 * Get a new iterator on the current query.
	 *
	 * @param   string              $name       Name of the driver you want an iterator for.
	 * @param   StatementInterface  $statement  Statement holding the result set to be iterated.
	 * @param   string|null         $column     An optional column to use as the iterator key.
	 * @param   string              $class      The class of object that is returned.
	 *
	 * @return  DatabaseIterator
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getIterator(
		string $name,
		StatementInterface $statement,
		?string $column = null,
		string $class = \stdClass::class
	): DatabaseIterator
	{
		// Derive the class name from the driver.
		$iteratorClass = __NAMESPACE__ . '\\' . ucfirst($name) . '\\' . ucfirst($name) . 'Iterator';

		// Make sure we have an iterator class for this driver.
		if (!class_exists($iteratorClass))
		{
			// We can work with the base iterator class so use that
			$iteratorClass = DatabaseIterator::class;
		}

		// Return a new iterator
		return new $iteratorClass($statement, $column, $class);
	}

	/**
	 * Get the current query object or a new Query object.
	 *
	 * @param   string                  $name  Name of the driver you want an query object for.
	 * @param   DatabaseInterface|null  $db    Optional database driver to inject into the query object.
	 *
	 * @return  QueryInterface
	 *
	 * @since   1.0
	 * @throws  Exception\UnsupportedAdapterException if there is not a compatible database query object
	 */
	public function getQuery(string $name, ?DatabaseInterface $db = null): QueryInterface
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
