<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageMemcached.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheStorageMemcachedTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageMemcached
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
	 */
	protected function setUp()
	{
		$memcachedtest = false;

		if (extension_loaded('memcached') || class_exists('Memcached'))
		{
			$config = JFactory::getConfig();
			$host = $config->get('memcached_server_host', 'localhost');
			$port = $config->get('memcached_server_port', 11211);

			$memcached = new Memcached;
			$memcachedtest = @$memcached->addServer($host, $port);
		}

		$this->extensionAvailable = $memcachedtest;

		if ($this->extensionAvailable)
		{
			$this->object = JCacheStorage::getInstance('memcached');
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
			'Claims Memcache is not loaded.'
		);
	}
}
