<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

/**
 * Abstract test case class for database testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCaseDatabase extends PHPUnit\DbUnit\TestCase
{
	use TestCaseTrait;

	/**
	 * @var    DatabaseDriver  The active database driver being used for the tests.
	 * @since  12.1
	 */
	protected static $driver;

	/**
	 * @var    DatabaseDriver  The saved database driver to be restored after these tests.
	 * @since  12.1
	 */
	private static $_stash;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function setUpBeforeClass()
	{
		// We always want the default database test case to use an SQLite memory database.
		$options = array(
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => 'jos_'
		);

		try
		{
			// Attempt to instantiate the driver.
			static::$driver = JDatabaseDriver::getInstance($options);
			static::$driver->connect();

			// Get the PDO instance for an SQLite memory database and load the test schema into it.
			static::$driver->getConnection()->exec(file_get_contents(JPATH_TESTS . '/schema/ddl.sql'));
		}
		catch (RuntimeException $e)
		{
			static::$driver = null;
		}

		// If for some reason an exception object was returned set our database object to null.
		if (static::$driver instanceof Exception)
		{
			static::$driver = null;
		}

		// Setup the factory pointer for the driver and stash the old one.
		self::$_stash = Factory::$database;
		Factory::$database = static::$driver;
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function tearDownAfterClass()
	{
		Factory::$database = self::$_stash;

		if (static::$driver !== null)
		{
			static::$driver->disconnect();
			static::$driver = null;
		}
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  PHPUnit\DbUnit\Database\Connection
	 *
	 * @since   12.1
	 */
	protected function getConnection()
	{
		if (!is_null(static::$driver))
		{
			return $this->createDefaultDBConnection(static::$driver->getConnection(), ':memory:');
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\XmlDataSet
	 *
	 * @since   11.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/stubs/database.xml');
	}

	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return  \PHPUnit\DbUnit\Operation\Composite
	 *
	 * @since   12.1
	 */
	protected function getSetUpOperation()
	{
		// Required given the use of InnoDB contraints.
		return new \PHPUnit\DbUnit\Operation\Composite(
			array(
				\PHPUnit\DbUnit\Operation\Factory::DELETE_ALL(),
				\PHPUnit\DbUnit\Operation\Factory::INSERT()
			)
		);
	}

	/**
	 * Returns the database operation executed in test cleanup.
	 *
	 * @return  \PHPUnit\DbUnit\Operation\Operation
	 *
	 * @since   12.1
	 */
	protected function getTearDownOperation()
	{
		// Required given the use of InnoDB contraints.
		return \PHPUnit\DbUnit\Operation\Factory::DELETE_ALL();
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function restoreFactoryState()
	{
		Factory::$application = $this->_stashedFactoryState['application'];
		Factory::$config = $this->_stashedFactoryState['config'];
		Factory::$container = $this->_stashedFactoryState['container'];
		Factory::$dates = $this->_stashedFactoryState['dates'];
		Factory::$session = $this->_stashedFactoryState['session'];
		Factory::$language = $this->_stashedFactoryState['language'];
		Factory::$document = $this->_stashedFactoryState['document'];
		Factory::$mailer = $this->_stashedFactoryState['mailer'];
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function saveFactoryState()
	{
		$this->_stashedFactoryState['application'] = Factory::$application;
		$this->_stashedFactoryState['config'] = Factory::$config;
		$this->_stashedFactoryState['container'] = Factory::$container;
		$this->_stashedFactoryState['dates'] = Factory::$dates;
		$this->_stashedFactoryState['session'] = Factory::$session;
		$this->_stashedFactoryState['language'] = Factory::$language;
		$this->_stashedFactoryState['document'] = Factory::$document;
		$this->_stashedFactoryState['mailer'] = Factory::$mailer;
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		if (empty(static::$driver))
		{
			$this->markTestSkipped('There is no database driver.');
		}

		parent::setUp();
	}
}
