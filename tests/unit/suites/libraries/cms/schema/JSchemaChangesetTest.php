<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JSchemaChangeset.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 * @since       3.0
 */
class JSchemaChangesetTest extends TestCase
{
	/**
	 * The mock database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.0
	 */
	protected $db;

	/**
	 * Object under test
	 *
	 * @var    JSchemaChangeset
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		// Store the factory state so we can mock the necessary objects
		$this->saveFactoryState();

		JFactory::$database = $this->getMockDatabase('Mysqli');

		// Register the object
		$this->object = JSchemaChangeset::getInstance(JFactory::getDbo(), null);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function tearDown()
	{
		// Restore the factory state
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Provides the testable database drivers
	 *
	 * @return  array
	 */
	public function dataDriver()
	{
		return array(
			array('Mysql'),
			array('Postgresql'),
			array('Sqlsrv'),
		);
	}

	/**
	 * Tests the __construct method with the given driver
	 *
	 * @medium
	 *
	 * @param   string  $driver  Driver to test against
	 *
	 * @return  void
	 *
	 * @dataProvider dataDriver
	 * @since   3.0
	 */
	public function test__construct($driver)
	{
		// Skip the Mysql driver on PHP 7
		if ($driver === 'Mysql' && PHP_MAJOR_VERSION >= 7)
		{
			$this->markTestSkipped('ext/mysql is unsupported on PHP 7.');
		}

		$db     = $this->getMockDatabase($driver);
		$schema = new JSchemaChangeset($db, null);

		$this->assertAttributeInstanceOf('JDatabaseDriver' . $driver, 'db', $schema);
	}

	/**
	 * Tests the getInstance method with the MySQLi driver
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetInstanceMysqli()
	{
		$this->assertAttributeInstanceOf('JDatabaseDriverMysqli', 'db', $this->object);
	}

	/**
	 * Tests the getStatus method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetStatus()
	{
		$this->assertInternalType('array', $this->object->getStatus());
	}

	/**
	 * Tests the getSchema method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetSchema()
	{
		$this->assertInternalType('string', $this->object->getSchema());
	}
}
