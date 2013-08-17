<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageMemcache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheStorageMemcacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageMemcache
	 * @access protected
	 */
	protected $object;

	/**
	 * @var    memcacheAvailable
	 * @access protected
	 */
	protected $memcacheAvailable;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		include_once JPATH_PLATFORM . '/joomla/cache/storage.php';
		include_once JPATH_PLATFORM . '/joomla/cache/storage/memcache.php';

		$memcachetest = false;

		if (!extension_loaded('memcache') || !class_exists('Memcache'))
		{
			$this->memcacheAvailable = false;
		}
		else
		{
			$config = JFactory::getConfig();
			$host = $config->get('memcache_server_host', 'localhost');
			$port = $config->get('memcache_server_port', 11211);

			$memcache = new Memcache;
			$memcachetest = @$memcache->connect($host, $port);
		}

		if (!$memcachetest)
		{
			$this->memcacheAvailable = false;
		}
		else
		{
			$this->memcacheAvailable = true;
		}

		if ($this->memcacheAvailable)
		{
			$this->object = JCacheStorage::getInstance('memcache');
		}
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
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testGetConfig().
	 */
	public function testGetConfig()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testGet().
	 */
	public function testGet()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testStore().
	 */
	public function testStore()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testRemove().
	 */
	public function testRemove()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testClean().
	 */
	public function testClean()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test...
	 *
	 * @return void
	 *
	 * @todo Implement testGc().
	 */
	public function testGc()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Testing isSupported().
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		if ($this->memcacheAvailable)
		{
			$this->assertThat(
				$this->object->isSupported(),
				$this->isTrue(),
				'Claims memcache is not loaded.'
			);
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
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
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}
}
