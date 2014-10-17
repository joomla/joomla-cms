<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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

		JFactory::$database = $this->getMockDatabase();

		// Set up our mock database
		$this->db = JFactory::getDbo();
		$this->db->name = 'mysqli';

		// Register the object
		$this->object = JSchemaChangeset::getInstance($this->db, null);
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

	public function dataDriver()
	{
		return array(
			array('mysqli'),
			array('postgresql'),
			array('sqlsrv'),
		);
	}

	/**
	 * Tests the __construct method with the given driver
	 *
	 * @medium
	 *
	 * @dataProvider dataDriver
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function test__construct($driver)
	{
		$this->db->name = $driver;

		$this->assertThat(
			new JSchemaChangeset($this->db, null),
			$this->isInstanceOf('JSchemaChangeset')
		);
	}

	/**
	 * Tests the getInstance method with the MySQL driver
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetInstanceMysql()
	{
		$this->assertThat(
			JSchemaChangeset::getInstance($this->db, null),
			$this->isInstanceOf('JSchemaChangeset')
		);
	}

	public function testGetStatus()
	{
		$this->assertThat(
			$this->object->getStatus(),
			$this->isType('array')
		);
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
		$this->assertThat(
			$this->object->getSchema(),
			$this->isType('string')
		);
	}
}
