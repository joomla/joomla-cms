<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/database/inspector.php';

/**
 * Test class for JLogLoggerDatabase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogLoggerDatabaseTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/stubs/database/S01.xml');
	}

	/**
	 * Test the JLogLoggerDatabase::__construct method.
	 *
	 * @return void
	 */
	public function testConstructor01()
	{
		// Setup the basic configuration.
		$config = array(
			'db_driver' => 'mysqli',
			'db_host' => 'db.domain.com'
		);
		$logger = new JLogLoggerDatabaseInspector($config);

		// Verify some internal values.
		$this->assertEquals($logger->driver, 'mysqli', 'Line: ' . __LINE__);
		$this->assertEquals($logger->host, 'db.domain.com', 'Line: ' . __LINE__);
		$this->assertEquals($logger->user, 'root', 'Line: ' . __LINE__);
		$this->assertEquals($logger->db, null, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerDatabase::addEntry method.
	 *
	 * @return void
	 */
	public function testAddEntry01()
	{
		// Setup the basic configuration.
		$config = array();
		$logger = new JLogLoggerDatabaseInspector($config);

		// Get the expected database from XML.
		$expected = $this->createXMLDataSet(__DIR__ . '/stubs/database/S01E01.xml');

		// Add the new entries to the database.
		$logger->addEntry(new JLogEntry('Testing Entry 02', JLog::INFO, null, '2009-12-01 12:30:00'));
		$logger->addEntry(new JLogEntry('Testing3', JLog::EMERGENCY, 'deprecated', '2010-12-01 02:30:00'));

		// Get the actual dataset from the database.
		$actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
		$actual->addTable('jos_log_entries');

		// Verify that the data sets are equal.
		$this->assertDataSetsEqual($expected, $actual);
	}

	/**
	 * Test the JLogLoggerDatabase::addEntry method.
	 *
	 * @return void
	 */
	public function testAddEntry02()
	{
		// Setup the logger.
		$config = array(
			'db_driver' => 'sqlite',
			'db_database' => ':memory:',
			'db_prefix' => 'jos_'
		);
		$logger = new JLogLoggerDatabase($config);
		TestReflection::setValue($logger, 'db', JFactory::$database);

		// Get the expected database from XML.
		$expected = $this->createXMLDataSet(__DIR__ . '/stubs/database/S01E01.xml');

		// Add the new entries to the database.
		$logger->addEntry(new JLogEntry('Testing Entry 02', JLog::INFO, null, '2009-12-01 12:30:00'));
		$logger->addEntry(new JLogEntry('Testing3', JLog::EMERGENCY, 'deprecated', '2010-12-01 02:30:00'));

		// Get the actual dataset from the database.
		$actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
		$actual->addTable('jos_log_entries');

		// Verify that the data sets are equal.
		$this->assertDataSetsEqual($expected, $actual);
	}

	/**
	 * Test the JLogLoggerDatabase::connect method.
	 *
	 * @return void
	 */
	public function testConnect01()
	{
		// Setup the basic configuration.
		$config = array(
			'db_driver' => 'sqlite',
			'db_database' => ':memory:',
			'db_prefix' => 'jos_'
		);

		$logger = new JLogLoggerDatabaseInspector($config);
		$logger->connect();

		$this->assertTrue($logger->db instanceof JDatabaseDriver, 'Line: ' . __LINE__);
	}

	/**
	 * Failing test for the JLogLoggerDatabase::connect method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 *
	 * @expectedException RuntimeException
	 */
	public function testConnect02()
	{
		// Setup the basic configuration.
		$config = array(
			'db_driver' => 'failure',
			'db_host' => 'foo',
			'db_database' => 'bar',
			'db_prefix' => 'blah_'
		);

		$logger = new JLogLoggerDatabaseInspector($config);
		$logger->connect();
	}
}
