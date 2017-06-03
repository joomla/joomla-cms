<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\DatabaseHandler;
use Joomla\Test\TestDatabase;

/**
 * Test class for Joomla\Session\Handler\DatabaseHandler.
 */
class DatabaseHandlerTest extends TestDatabase
{
	/**
	 * DatabaseHandler for testing
	 *
	 * @var  DatabaseHandler
	 */
	private $handler;

	/**
	 * Flag if the session table has been created
	 *
	 * @var  boolean
	 */
	private static $sessionTableCreated = false;

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the handler is supported in this environment
		if (!DatabaseHandler::isSupported() || !class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers()))
		{
			static::markTestSkipped('The DatabaseHandler is unsupported in this environment.');
		}

		parent::setUpBeforeClass();

		// Drop the session table from the test data schema, we will create it with the preferred schema later
		static::$driver->dropTable('#__session');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->handler = new DatabaseHandler(static::$driver);

		// Make sure our session table is present
		if (!self::$sessionTableCreated)
		{
			$this->handler->createDatabaseTable();

			self::$sessionTableCreated = true;
		}
	}

	/**
	 * @covers  Joomla\Session\Handler\DatabaseHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(DatabaseHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\DatabaseHandler::close()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::destroy()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::gc()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::read()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::open()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::write()
	 */
	public function testValidateSessionDataIsCorrectlyReadWrittenAndDestroyed()
	{
		$sessionData = array('foo' => 'bar', 'joomla' => 'rocks');
		$sessionId   = 'sid';

		$this->assertTrue($this->handler->open('', $sessionId));
		$this->assertTrue($this->handler->write($sessionId, json_encode(array('foo' => 'bar'))));
		$this->assertTrue($this->handler->write($sessionId, json_encode($sessionData)));
		$this->assertSame($sessionData, json_decode($this->handler->read($sessionId), true));
		$this->assertTrue($this->handler->destroy($sessionId));
		$this->assertTrue($this->handler->gc(900));
		$this->assertTrue($this->handler->close());
	}
}
