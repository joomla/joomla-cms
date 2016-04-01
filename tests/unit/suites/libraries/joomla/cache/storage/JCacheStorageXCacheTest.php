<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageXcache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheStorageXcacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageXcache
	 */
	protected $object;

	/**
	 * @var    boolean
	 */
	protected $extensionAvailable;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->extensionAvailable = JCacheStorageXcache::isSupported();

		if ($this->extensionAvailable)
		{
			$this->object = JCacheStorage::getInstance('xcache');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Testing gc().
	 *
	 * @return  void
	 */
	public function testGc()
	{
		$this->assertTrue(
			$this->object->gc(),
			'Should return default true'
		);
	}

	/**
	 * Testing isSupported().
	 *
	 * @return  void
	 */
	public function testIsSupported()
	{
		$this->assertEquals(
			$this->extensionAvailable,
			$this->object->isSupported(),
			'Claims Wincache is not loaded.'
		);
	}

	/**
	 * Testing lock().
	 *
	 * @return  void
	 */
	public function testLock()
	{
		$this->assertFalse(
			$this->object->lock(),
			'Should return default false'
		);
	}

	/**
	 * Testing unlock().
	 *
	 * @return  void
	 */
	public function testUnlock()
	{
		$this->assertFalse(
			$this->object->unlock(),
			'Should return default false'
		);
	}
}
