<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests;

/**
 * Abstract Test case for Joomla\Session\Storage, cache storage tests should override as needed
 *
 * @since  1.0
 */
abstract class StorageCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    String	Name of cache engine class
	 * @since  1.0
	 */
	protected static $className;

	/**
	 * @var    \Joomla\Session\Storage
	 * @since  1.0
	 */
	protected static $object;

	/**
	 * @var    String  key to use in cache
	 * @since  1.1
	 */
	protected static $key;

	/**
	 * @var    String  default value to store in cache
	 * @since  1.1
	 */
	protected static $value;

	/**
	 * @var    String  name for session
	 * @since  1.1
	 */
	protected static $sessionName;

	/**
	 * @var    String  path for session
	 * @since  1.1
	 */
	protected static $sessionPath;

	/**
	 * Gets the Class Name of the model for this class
	 *
	 * This method is called from setUp to figure out what we are testing.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	protected static function getClassModel()
	{
		return __CLASS__;
	}

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
		if (empty(static::$object))
		{
			$this->markTestSkipped('There is no caching engine.');
		}

		$key = md5(date(DATE_RFC2822));
		$value = 'Test value';
		static::$key = $key;
		static::$value = $value;
		static::$sessionName = 'SessionName';
		static::$sessionPath = 'SessionPath';
		static::$className = get_class(static::$object);

		parent::setUp();
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
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement test__Construct().
	 *
	 * @return void
	 */
	public function test__Construct()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testRegister().
	 *
	 * @return void
	 */
	public function testRegister()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test session open
	 *
	 * @return void
	 */
	public function testOpen()
	{
		$this->assertThat(static::$object->open(static::$sessionPath, static::$sessionName), $this->isTrue(), __LINE__);
	}

	/**
	 * Test close session
	 *
	 * @return void
	 */
	public function testClose()
	{
		static::$object->open(static::$sessionPath, static::$sessionName);
		$this->assertThat(static::$object->close(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test read default key and value
	 *
	 * @return void
	 */
	public function testRead()
	{
		static::$object->write(static::$key, static::$value);
		$this->assertThat(static::$object->read(static::$key), $this->equalTo(static::$value), __LINE__);
	}

	/**
	 * Test write nothing default key and value
	 *
	 * @return void
	 */
	public function testWrite()
	{
		$this->assertThat(static::$object->write(static::$key, static::$value), $this->isTrue(), __LINE__);
	}

	/**
	 * Test storage destroy no value
	 *
	 * @return void
	 */
	public function testDestroy()
	{
		// Create the key/value
		static::$object->write(static::$key, static::$value);
		$this->assertThat(static::$object->destroy(static::$key), $this->isTrue(), __LINE__);
	}

	/**
	 * Test garbage collection
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->assertThat(static::$object->gc(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test isSupported
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		$this->assertThat(static::$object->isSupported(), $this->isTrue(), __LINE__);
	}
}
