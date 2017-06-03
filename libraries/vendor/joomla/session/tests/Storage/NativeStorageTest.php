<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\FilesystemHandler;
use Joomla\Session\Storage\NativeStorage;

/**
 * Test class for Joomla\Session\Storage\NativeStorage.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class NativeStorageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Save path for our storage handler
	 *
	 * @var  string
	 */
	private $savePath;

	/**
	 * Storage object for testing
	 *
	 * @var  NativeStorage
	 */
	private $storage;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		$this->savePath = sys_get_temp_dir() . '/jfw-test';

		$this->iniSet('session.save_handler', 'files');
		$this->iniSet('session.save_path', $this->savePath);

		if (!is_dir($this->savePath))
		{
			mkdir($this->savePath, 0755);
		}

		$this->storage = new NativeStorage(new FilesystemHandler($this->savePath));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function tearDown()
	{
		// Reset our session data
		session_write_close();
		array_map('unlink', glob($this->savePath . '/*'));

		if (is_dir($this->savePath))
		{
			rmdir($this->savePath);
		}
	}

	/**
	 * Data provider for set tests
	 *
	 * @return  array
	 */
	public function setProvider()
	{
		return array(
			array('joomla', 'rocks'),
			array('joomla.framework', 'too much awesomeness')
		);
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::getId()
	 * @covers  Joomla\Session\Storage\NativeStorage::isActive()
	 * @covers  Joomla\Session\Storage\NativeStorage::isStarted()
	 * @covers  Joomla\Session\Storage\NativeStorage::start()
	 */
	public function testValidateTheStorageIsStartedCorrectly()
	{
		// There shouldn't be an ID yet
		$this->assertEmpty($this->storage->getId());

		// The storage should successfully start
		$this->storage->start();
		$this->assertTrue($this->storage->isStarted());

		// There should now be an ID
		$this->assertNotEmpty($this->storage->getId());

		// And the storage should be active
		$this->assertTrue($this->storage->isActive());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::getId()
	 * @covers  Joomla\Session\Storage\NativeStorage::setId()
	 * @covers  Joomla\Session\Storage\NativeStorage::start()
	 */
	public function testValidateAnInjectedSessionIdIsUsedWhenTheSessionStarts()
	{
		$mockId = '1234abcd';

		// Inject our ID
		$this->storage->setId($mockId);

		// The storage should successfully start
		$this->storage->start();

		// The ID should match our injected value
		$this->assertSame($mockId, $this->storage->getId());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::setId()
	 * @use     Joomla\Session\Storage\NativeStorage::start()
	 *
	 * @expectedException  \LogicException
	 */
	public function testValidateTheSessionIdCannotBeChangedAfterTheSessionIsStarted()
	{
		// The storage should successfully start
		$this->storage->start();

		// Setting an ID now should throw an exception
		$this->storage->setId('fail');
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::getName()
	 * @covers  Joomla\Session\Storage\NativeStorage::setName()
	 * @covers  Joomla\Session\Storage\NativeStorage::start()
	 */
	public function testValidateAnInjectedSessionNameIsUsedWhenTheSessionStarts()
	{
		$mockName = '1234abcd';

		// Inject our name
		$this->storage->setName($mockName);

		// The storage should successfully start
		$this->storage->start();

		// The name should match our injected value
		$this->assertSame($mockName, $this->storage->getName());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::setName()
	 * @use     Joomla\Session\Storage\NativeStorage::start()
	 *
	 * @expectedException  \LogicException
	 */
	public function testValidateTheSessionNameCannotBeChangedAfterTheSessionIsStarted()
	{
		// The storage should successfully start
		$this->storage->start();

		// Setting an name now should throw an exception
		$this->storage->setName('fail');
	}


	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::get()
	 */
	public function testValidateTheCorrectValueIsReturnedWhenGetIsCalled()
	{
		// Default return null
		$this->assertNull($this->storage->get('foo', null));

		// Return the specified default
		$this->assertSame('bar', $this->storage->get('foo', 'bar'));
	}

	/**
	 * @param   string  $key    The key to set
	 * @param   string  $value  The value to set
	 *
	 * @dataProvider  setProvider
	 *
	 * @covers  Joomla\Session\Storage\NativeStorage::set()
	 * @uses    Joomla\Session\Storage\NativeStorage::get()
	 */
	public function testValidateAValueIsCorrectlyStored($key, $value)
	{
		$this->storage->set($key, $value);
		$this->assertSame($value, $this->storage->get($key, null));
	}

	/**
	 * @param   string  $key    The key to set
	 * @param   string  $value  The value to set
	 *
	 * @dataProvider  setProvider
	 *
	 * @covers  Joomla\Session\Storage\NativeStorage::has()
	 * @uses    Joomla\Session\Storage\NativeStorage::set()
	 */
	public function testValidateTheKeyIsCorrectlyCheckedForExistence($key, $value)
	{
		$this->storage->set($key, $value);
		$this->assertTrue($this->storage->has($key));
		$this->assertFalse($this->storage->has($key . '.fake.ending'));
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::remove()
	 * @uses    Joomla\Session\Storage\NativeStorage::has()
	 * @uses    Joomla\Session\Storage\NativeStorage::set()
	 */
	public function testValidateAKeyIsCorrectlyRemovedFromTheStore()
	{
		$this->storage->set('foo', 'bar');
		$this->assertTrue($this->storage->has('foo'));

		$this->storage->remove('foo');
		$this->assertFalse($this->storage->has('foo'));
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::all()
	 * @covers  Joomla\Session\Storage\NativeStorage::clear()
	 * @uses    Joomla\Session\Storage\NativeStorage::set()
	 */
	public function testValidateAllDataIsReturnedFromTheStore()
	{
		// Set some data into our session
		$this->storage->set('foo', 'bar');
		$this->storage->set('joomla.framework', 'is awesome');

		$this->assertSame(
			array(
				'foo' => 'bar',
				'joomla.framework' => 'is awesome'
			),
			$this->storage->all()
		);

		// Now clear the data
		$this->storage->clear();
		$this->assertEmpty($this->storage->all());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::regenerate()
	 * @uses    Joomla\Session\Storage\NativeStorage::all()
	 * @uses    Joomla\Session\Storage\NativeStorage::getId()
	 * @uses    Joomla\Session\Storage\NativeStorage::set()
	 * @uses    Joomla\Session\Storage\NativeStorage::start()
	 */
	public function testValidateTheStorageIsCorrectlyRegenerated()
	{
		// First we need to start the store
		$this->storage->start();

		// Grab the session ID to check in a moment
		$sessionId = $this->storage->getId();

		// And add some data to validate it is carried forward
		$this->storage->set('foo', 'bar');

		// Now regenerate the store
		$this->assertTrue($this->storage->regenerate());

		// Validate the regeneration
		$this->assertNotSame($sessionId, $this->storage->getId());
		$this->assertArrayHasKey('foo', $this->storage->all());
		$this->assertTrue($this->storage->isActive());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::close()
	 * @uses    Joomla\Session\Storage\NativeStorage::isStarted()
	 * @uses    Joomla\Session\Storage\NativeStorage::start()
	 */
	public function testValidateTheStorageIsCorrectlyClosed()
	{
		$this->storage->start();
		$this->storage->close();

		// Validate the closure
		$this->assertFalse($this->storage->isStarted());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::getHandler()
	 */
	public function testValidateTheHandlerIsCorrectlyRetrieved()
	{
		$this->assertInstanceOf('\\Joomla\\Session\\Handler\\FilesystemHandler', $this->storage->getHandler());
	}

	/**
	 * @covers  Joomla\Session\Storage\NativeStorage::setHandler()
	 * @uses    Joomla\Session\Storage\NativeStorage::getHandler()
	 */
	public function testValidateTheHandlerIsCorrectlyInjected()
	{
		$handler = new FilesystemHandler($this->savePath);
		$this->storage->setHandler($handler);

		$this->assertSame($handler, $this->storage->getHandler());
	}
}
