<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageRedis.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       3.4
 */
class JCacheStorageRedisTest extends TestCase
{
	/**
	 * @var    JCacheStorageRedis
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
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

		$this->extensionAvailable = class_exists('Redis');

		if ($this->extensionAvailable)
		{
			$this->object = JCacheStorage::getInstance('redis');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testGetConnection().
	 */
	public function testGetConnection()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGet().
	 *
	 * @return void
	 */
	public function testGetAndStore()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetAll().
	 *
	 * @return void
	 */
	public function testGetAll()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testStore().
	 *
	 * @return void
	 */
	public function testStore()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testRemove().
	 *
	 * @return void
	 */
	public function testRemove()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testClean().
	 *
	 * @return void
	 */
	public function testClean()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGc().
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
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
			'Claims APC is not loaded.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLock().
	 *
	 * @return void
	 */
	public function testLock()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testUnlock().
	 *
	 * @return void
	 */
	public function testUnlock()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement test_getCacheId().
	 *
	 * @return void
	 */
	public function testGetCacheId()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
