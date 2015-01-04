<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 */
class JCacheTest extends PHPUnit_Framework_TestCase
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
		include_once JPATH_PLATFORM . '/joomla/cache/cache.php';
		include_once JPATH_PLATFORM . '/joomla/cache/controller.php';
		include_once JPATH_PLATFORM . '/joomla/cache/storage.php';

		$this->checkAvailability();
	}

	/**
	 * Check availability of all cache handlers
	 *
	 * @return void
	 */
	private function checkAvailability()
	{
		$config = JFactory::getConfig();
		$host = $config->get('memcache_server_host', 'localhost');
		$port = $config->get('memcache_server_port', 11211);
		$memcacheServerAvailable = @fsockopen($host, $port, $errNo, $errStr, 0.01);

		$this->available['file'] = true;
		$this->available['apc'] = extension_loaded('apc');
		$this->available['eaccelerator'] = extension_loaded('eaccelerator') && function_exists('eaccelerator_get');
		$this->available['memcache'] =
			extension_loaded('memcache')
			&& class_exists('Memcache')
			&& $memcacheServerAvailable;
		$this->available['xcache'] = extension_loaded('xcache');
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
	 * Testing store() and get()
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
	public function testStoreAndGet($handler, $options, $id, $group, $data, $expected)
	{
		$this->object = JCache::getInstance($handler, $options);
		$this->object->setCaching(true);

		$this->assertTrue(
			$this->object->store($data, $id, $group)
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
		$this->object = JCache::getInstance('output', array('lifetime' => 2, 'defaultgroup' => ''));

		$this->object->store($this->testData_A, 42, '');
		$this->object->store($this->testData_B, 43, '');

		sleep(5);

		$this->object->gc();

		$this->assertFalse(
			$this->object->get(42, '')
		);
		$this->assertFalse(
			$this->object->get(43, '')
		);
	}

	/**
	 * Test Cases for getStorage
	 *
	 * @return array
	 */
	public function casesGetStorage()
	{
		$this->setDefaultOptions();

		$storages = array(
			'file'         => 'JCacheStorageFile',
			'apc'          => 'JCacheStorageApc',
			'xcache'       => 'JCacheStorageXcache',
			'memcache'     => 'JCacheStorageMemcache',
			'eaccelerator' => 'JCacheStorageEaccelerator',
		);

		$cases = array();
		foreach ($storages as $key => $class)
		{
			$options = $this->defaultOptions;
			$options['storage'] = $key;
			$cases[$key] = array('output', $options, $class);
		}

		return $cases;
	}

	/**
	 * Testing getStorage
	 *
	 * @param   string  $handler   cache handler
	 * @param   array   $options   options for cache handler
	 * @param   string  $expected  expected storage class
	 *
	 * @return void
	 *
	 * @dataProvider casesGetStorage
	 */
	public function testGetStorage($handler, $options, $expected)
	{
		if (!$this->available[$options['storage']])
		{
			$this->markTestSkipped("The {$options['storage']} storage handler is currently not available");
		}
		$this->object = JCache::getInstance($handler, $options);

		$this->assertThat(
			$this->object->cache->_getStorage(),
			$this->isInstanceOf($expected)
		);
	}
}
