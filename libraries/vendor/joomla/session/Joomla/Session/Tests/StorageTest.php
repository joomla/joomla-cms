<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests;

use Joomla\Session\Storage;

/**
 * Test class for Joomla\Session\Storage.  Because Storage
 * is an Absract class, we can not invoke it by object - so
 * we over-ride most of the tests and instead call it using static
 * method calls.  Do NOT do this for live code - if you want
 * a Cache engine which stores data in an ArrayObject, subclass
 * Joomla\Session\Storage and invoke it as an object.
 * These tests merely provide a baseline model for how all other
 * classes should respond to the same data and method calls.
 *
 * @since  1.0
 */
class StorageTest extends StorageCase
{
	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		// Dummy object for testing
		static::$object = $this;
		parent::setUp();
		static::$className  = '\\Joomla\\Session\\Storage';
	}

	/**
	 * Test getInstance
	 *
	 * @todo Implement testGetInstance().
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		$className = static::$className;
		$instance = $className::getInstance();
		$instanceClass = get_class($instance);

		// Can't be us because we are abstract
		$this->assertNotEquals($className, $instanceClass,  __LINE__);

		// Should Default to None
		$storageClass = 'Joomla\\Session\\Storage\\None';
		$this->assertInstanceOf($storageClass, $instance, __LINE__);
	}

	/**
	 * Test __construct: can't construct an abstract class
	 *
	 * @return void
	 */
	public function test__Construct()
	{
		$reflectStorage = new \ReflectionClass('Joomla\\Session\\Storage');
		$this->assertThat($reflectStorage->isAbstract(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test Register is not valid for an abstract model
	 *
	 * @return void
	 */
	public function testRegister()
	{
		$reflectStorage = new \ReflectionClass('Joomla\Session\\Storage');
		$this->assertThat($reflectStorage->isAbstract(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test session open
	 *
	 * @return void
	 */
	public function testOpen()
	{
		$this->markTestSkipped('Unexpected failures presently, debug soon.');
		$className = static::$className;
		$this->assertThat($className::open(static::$sessionPath, static::$sessionName), $this->isTrue(), __LINE__);
	}

	/**
	 * Test close session
	 *
	 * @return void
	 */
	public function testClose()
	{
		$this->markTestSkipped('Unexpected failures presently, debug soon.');
		$className = static::$className;
		$this->assertThat($className::close(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test read default key and value
	 *
	 * @return void
	 */
	public function testRead()
	{
		$this->markTestSkipped('Unexpected failures presently, debug soon.');
		$className = static::$className;
		$this->assertThat($className::read(static::$key), $this->isNull(), __LINE__);
	}

	/**
	 * Test write nothing default key and value
	 *
	 * @return void
	 */
	public function testWrite()
	{
		$this->markTestSkipped('Unexpected failures presently, debug soon.');
		$className = static::$className;
		$this->assertThat($className::write(static::$key, static::$value), $this->isTrue(), __LINE__);
	}

	/**
	 * Test storage destroy no value
	 *
	 * @return void
	 */
	public function testDestroy()
	{
		$this->markTestSkipped('Unexpected failures presently, debug soon.');
		$className = static::$className;
		$this->assertThat($className::destroy(static::$key), $this->isTrue(), __LINE__);
	}

	/**
	 * Test garbage collection
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->markTestSkipped('Unexpected failures presently, debug soon.');
		$className = static::$className;
		$this->assertThat($className::gc(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test isSupported
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		$className = static::$className;
		$this->assertThat($className::isSupported(), $this->isTrue(), __LINE__);
	}
}
