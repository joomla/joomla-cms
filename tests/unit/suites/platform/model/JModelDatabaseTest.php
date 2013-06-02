<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('DatabaseModel', __DIR__ . '/stubs/tdatabase.php');

/**
 * Tests for the JViewBase class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mapper
 * @since       12.1
 */
class JModelDatabaseTest extends TestCase
{
	/**
	 * @var    DatabaseModel
	 * @since  12.1
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JModelDatabase::__construct
	 * @since   12.1
	 */
	public function test__construct()
	{
		$this->assertSame(JFactory::getDbo(), $this->_instance->getDb(), 'Checks default database driver.');

		// Create a new datbase mock for injection.
		$db = TestMockDatabaseDriver::create($this);
		$class = new DatabaseModel(null, $db);
		$this->assertSame($db, $class->getDb(), 'Checks injected database driver.');
	}

	/**
	 * Tests the getDb method.
	 *
	 * @return  void
	 *
	 * @covers  JModelDatabase::getDb
	 * @since   12.1
	 */
	public function testGetDb()
	{
		// Reset the db property to a known value.
		TestReflection::setValue($this->_instance, 'db', 'foo');

		$this->assertEquals('foo', $this->_instance->getDb());
	}

	/**
	 * Tests the setDb method.
	 *
	 * @return  void
	 *
	 * @covers  JModelDatabase::setDb
	 * @since   12.1
	 */
	public function testSetDb()
	{
		$db = TestMockDatabaseDriver::create($this);
		$this->_instance->setDb($db);

		$this->assertAttributeSame($db, 'db', $this->_instance);
	}

	/**
	 * Tests the loadDb method.
	 *
	 * @return  void
	 *
	 * @covers  JModelDatabase::loadDb
	 * @since   12.1
	 */
	public function testLoadDb()
	{
		JFactory::$database = 'database';
		$this->assertEquals('database', TestReflection::invoke($this->_instance, 'loadDb'));
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$database = TestMockDatabaseDriver::create($this);

		$this->_instance = new DatabaseModel;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::teardown();
	}
}
