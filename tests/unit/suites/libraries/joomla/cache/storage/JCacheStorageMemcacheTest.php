<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageMemcache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheStorageMemcacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageMemcache
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
		$memcachetest = false;

		if (extension_loaded('memcache') || class_exists('Memcache'))
		{
			$config = JFactory::getConfig();
			$host = $config->get('memcache_server_host', 'localhost');
			$port = $config->get('memcache_server_port', 11211);

			$memcache = new Memcache;
			$memcachetest = @$memcache->connect($host, $port);
		}

		$this->extensionAvailable = $memcachetest;

		if ($this->extensionAvailable)
		{
			$this->object = JCacheStorage::getInstance('memcache');
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
