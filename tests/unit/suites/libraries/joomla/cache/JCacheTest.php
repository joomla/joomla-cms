<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JCache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 */
class JCacheTest extends TestCase
{
	/** @var JCache */
	protected $object;

	private $available = array();

	private $testData_A = "Now is the time for all good people to throw a party.";
	private $testData_B = "And this is the cache that tries men's souls";

	private $defaultOptions;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->checkAvailability();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Check availability of all cache handlers
	 *
	 * @return void
	 */
	private function checkAvailability()
	{
		$this->available = array(
			'apc'       => JCacheStorageApc::isSupported(),
			'apcu'      => JCacheStorageApcu::isSupported(),
			'file'      => true,
			'memcache'  => JCacheStorageMemcache::isSupported(),
			'memcached' => JCacheStorageMemcached::isSupported(),
			'redis'     => JCacheStorageRedis::isSupported(),
			'wincache'  => JCacheStorageWincache::isSupported(),
			'xcache'    => JCacheStorageXcache::isSupported(),
		);
	}

	private function setDefaultOptions()
	{
		$this->defaultOptions = array(
			'defaultgroup' => '',
			'cachebase'    => JPATH_BASE . '/cache',
			'lifetime'     => 15 * 60, // Minutes to seconds
			'storage'      => 'file',
		);
	}

	/**
	 * Test Cases for getInstance
	 *
	 * @return array
	 */
	public function casesGetInstance()
	{
		$this->setDefaultOptions();

		return array(
			'simple' => array(
				'output',
				array('storage' => 'file'),
				'JCacheControllerOutput',
			),
			'complexOutput' => array(
				'output',
				$this->defaultOptions,
				'JCacheControllerOutput',
			),
			'complexPage' => array(
				'page',
				$this->defaultOptions,
				'JCacheControllerPage',
			),
			'complexView' => array(
				'view',
				$this->defaultOptions,
				'JCacheControllerView',
			),
			'complexCallback' => array(
				'callback',
				$this->defaultOptions,
				'JCacheControllerCallback',
			),
		);
	}

	/**
	 * Testing getInstance
	 *
	 * @param   string  $handler   cache handler
	 * @param   array   $options   options for cache handler
	 * @param   string  $expClass  name of expected cache class
	 *
	 * @return void
	 *
	 * @dataProvider casesGetInstance
	 */
	public function testGetInstance($handler, $options, $expClass)
	{
		$this->object = JCache::getInstance($handler, $options);
		$this->assertInstanceOf(
			$expClass,
			$this->object
		);
	}

	/**
	 * Test Cases for setCaching
	 *
	 * @return array
	 */
	public function casesSetCaching()
	{
		$this->setDefaultOptions();

		return array(
			'simple' => array(
				'output',
				array('storage' => 'file'),
			),
			'complexOutput' => array(
				'output',
				$this->defaultOptions,
			),
			'complexPage' => array(
				'page',
				$this->defaultOptions,
			),
			'complexView' => array(
				'view',
				$this->defaultOptions,
			),
			'complexCallback' => array(
				'callback',
				$this->defaultOptions,
			),
		);
	}

	/**
	 * Testing setCaching
	 *
	 * @param   string  $handler  cache handler
	 * @param   array   $options  options for cache handler
	 *
	 * @return void
	 *
	 * @dataProvider casesSetCaching
	 */
	public function testSetCaching($handler, $options)
	{
		$this->object = JCache::getInstance($handler, $options);

		$caching = (bool) $this->object->options['caching'];
		$toggled = !$caching;
		$this->object->setCaching($toggled);
		$this->assertEquals(
			$toggled,
			$this->object->options['caching']
		);
	}

	/**
	 * Test Cases for setLifetime
	 *
	 * @return array
	 */
	public function casesSetLifetime()
	{
		$this->setDefaultOptions();

		return array(
			'simple' => array(
				'output',
				array('storage' => 'file'),
				900,
			),
			'complexOutput' => array(
				'output',
				$this->defaultOptions,
				15 * 60,
			),
			'complexPage' => array(
				'page',
				$this->defaultOptions,
				15 * 60,
			),
			'complexView' => array(
				'view',
				$this->defaultOptions,
				15 * 60,
			),
			'complexCallback' => array(
				'callback',
				$this->defaultOptions,
				15 * 60,
			),
		);
	}

	/**
	 * Testing setLifeTime
	 *
	 * @param   string   $handler   cache handler
	 * @param   array    $options   options for cache handler
	 * @param   integer  $lifetime  lifetime of cache to be set
	 *
	 * @return void
	 *
	 * @dataProvider casesSetLifetime
	 */
	public function testSetLifeTime($handler, $options, $lifetime)
	{
		$this->object = JCache::getInstance($handler, $options);
		$this->object->setLifeTime($lifetime);
		$this->assertEquals(
			$lifetime,
			$this->object->options['lifetime']
		);
	}

	/**
	 * Test Cases for getStores
	 *
	 * @return array
	 */
	public function casesGetStores()
	{
		$this->setDefaultOptions();

		return array(
			'simple' => array(
				'output',
				array('storage' => 'file'),
				'file',
			),
			'complexOutput' => array(
				'output',
				$this->defaultOptions,
				'file',
			),
			'complexPage' => array(
				'page',
				$this->defaultOptions,
				'file',
			),
			'complexView' => array(
				'view',
				$this->defaultOptions,
				'file',
			),
			'complexCallback' => array(
				'callback',
				$this->defaultOptions,
				'file',
			),
		);
	}

	/**
	 * Testing getStores
	 *
	 * @param   string  $handler   cache handler
	 * @param   array   $options   options for cache handler
	 * @param   string  $expected  returned stores
	 *
	 * @return void
	 *
	 * @dataProvider    casesGetStores
	 */
	public function testGetStores($handler, $options, $expected)
	{
		$this->object = JCache::getInstance($handler, $options);
		$this->assertEquals(
			$expected,
			$this->object->options['storage']
		);
	}

	/**
	 * Test Cases for get() / store()
	 *
	 * @return array
	 */
	public function casesStore()
	{
		$this->setDefaultOptions();

		return array(
			'simple' => array(
				'output',
				array('lifetime' => 600, 'storage' => 'file'),
				42,
				'',
				$this->testData_B,
				false,
			),
			'complexOutput' => array(
				'output',
				$this->defaultOptions,
				42,
				'',
				$this->testData_B,
				false,
			),
		);
	}

	/**
	 * Testing store(), contains(), and get()
	 *
	 * @param   string  $handler   cache handler
	 * @param   array   $options   options for cache handler
	 * @param   string  $id        cache element ID
	 * @param   string  $group     cache group
	 * @param   string  $data      data to be cached
	 * @param   string  $expected  expected return
	 *
	 * @return void
	 *
	 * @dataProvider casesStore
	 */
	public function testStoreContainsAndGet($handler, $options, $id, $group, $data, $expected)
	{
		$this->object = JCache::getInstance($handler, $options);
		$this->object->setCaching(true);

		$this->assertTrue(
			$this->object->store($data, $id, $group)
		);

		$this->assertTrue(
			$this->object->contains($id, $group)
		);

		$this->assertEquals(
			$data,
			$this->object->get($id, $group)
		);
	}

	/**
	 * Testing remove().
	 *
	 * @return void
	 */
	public function testRemove()
	{
		$options = array('storage' => 'file');
		$this->object = JCache::getInstance('output', $options);
		$this->object->setCaching(true);

		$this->object->store($this->testData_A, 42, '');
		$this->object->store($this->testData_B, 43, '');

		$this->assertEquals(
			$this->testData_B,
			$this->object->get(43, '')
		);
		$this->assertTrue(
			$this->object->remove(43, '')
		);
		$this->assertFalse(
			$this->object->get(43, '')
		);
		$this->assertEquals(
			$this->testData_A,
			$this->object->get(42, '')
		);
	}

	/**
	 * Testing clean().
	 *
	 * @return void
	 */
	public function testClean()
	{
		$options = array('storage' => 'file');
		$this->object = JCache::getInstance('output', $options);
		$this->object->setCaching(true);

		$this->object->store($this->testData_A, 42, '');
		$this->object->store($this->testData_B, 43, '');

		$this->assertEquals(
			$this->testData_B,
			$this->object->get(43, '')
		);
		$this->assertTrue(
			$this->object->clean('')
		);
		$this->assertFalse(
			$this->object->get(43, '')
		);
		$this->assertFalse(
			$this->object->get(42, '')
		);
	}

	/**
	 * Testing Gc().
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->object = JCache::getInstance('output', array('storage' => 'file', 'lifetime' => 5/60, 'defaultgroup' => ''));
		$this->object->setCaching(true);

		$this->object->store($this->testData_A, 42, '');
		$this->object->store($this->testData_B, 43, '');

		$handler = $this->object->cache->_getStorage();
		$path    = TestReflection::invoke($handler, '_getFilePath', 42, '');

		// Changing the time of last modification to the past
		$this->assertTrue(touch($path, $handler->_now - $handler->_lifetime - 1));

		// Collect Garbage
		$this->object->gc();

		$this->assertFileNotExists($path, "Cache file should not exist.");

		$this->assertFalse($this->object->get(42, ''));

		// To be sure that cache is working
		$this->assertEquals($this->testData_B, $this->object->get(43, ''));
	}
}
