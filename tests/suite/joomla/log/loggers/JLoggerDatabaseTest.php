<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/log/loggers/database.php';
require_once dirname(__FILE__).'/stubs/database/inspector.php';

/**
 * Test class for JLoggerDatabase.
 */
class JLoggerDatabaseTest extends JoomlaDatabaseTestCase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/stubs/database/S01.xml');
	}

	/**
	 * Test the JLoggerDatabase::__construct method.
	 */
	public function testConstructor01()
	{
		// Setup the basic configuration.
		$config = array(
			'db_driver' => 'mysqli',
			'db_host' => 'db.domain.com'
		);
		$logger = new JLoggerDatabaseInspector($config);

		// Verify some internal values.
		$this->assertEquals($logger->driver, 'mysqli', 'Line: '.__LINE__);
		$this->assertEquals($logger->host, 'db.domain.com', 'Line: '.__LINE__);
		$this->assertEquals($logger->user, 'root', 'Line: '.__LINE__);
		$this->assertEquals($logger->dbo, null, 'Line: '.__LINE__);
	}

	/**
	 * Test the JLoggerDatabase::addEntry method.
	 */
	public function testAddEntry01()
	{
		// Setup the basic configuration.
		$config = array();
		$logger = new JLoggerDatabaseInspector($config);

		// Get the expected database from XML.
		$expected = $this->createXMLDataSet(dirname(__FILE__).'/stubs/database/S01E01.xml');

		// Add the new entries to the database.
		$logger->addEntry(new JLogEntry('Testing Entry 02', JLog::INFO, null, '2009-12-01 12:30:00'));
		$logger->addEntry(new JLogEntry('Testing3', JLog::EMERGENCY, 'deprecated', '2010-12-01 02:30:00'));

		// Get the actual dataset from the database.
		$actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
		$actual->addTable('jos_log_entries');

		// Verify that the data sets are equal.
		$this->assertDataSetsEqual($expected, $actual);
	}
}
