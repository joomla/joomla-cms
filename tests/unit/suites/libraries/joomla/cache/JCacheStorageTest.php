<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorage.
 */
class JCacheStorageTest extends TestCase
{
	/**
	 * @var  JCacheStorage
	 */
	protected $object;

	/**
	 * @var  array
	 */
	protected static $actualError;

	/**
	 * Array of known cache stores and whether they are available for this test

	 * @var  array
	 */
	private $available = array();

	/**
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param   JException  $error  The JException object from JError
	 *
	 * @return  boolean  To not continue with JError processing
	 */
	public static function errorCallback($error)
	{
		self::$actualError['code'] = $error->get('code');
		self::$actualError['msg'] = $error->get('message');
		self::$actualError['info'] = $error->get('info');

		return false;
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveErrorHandlers();
		$this->setErrorCallback('JCacheStorageTest');
		self::$actualError = array();

		$this->object = new JCacheStorage;

		$this->checkStores();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

		// Mock the returns on JApplicationCms::get() to use the default values
		JFactory::$application->expects($this->any())
			->method('get')
			->willReturnArgument(1);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	protected function checkStores()
	{
		$this->available = array(
			'apc'       => JCacheStorageApc::isSupported(),
			'apcu'      => JCacheStorageApcu::isSupported(),
			'cachelite' => JCacheStorageCachelite::isSupported(),
			'file'      => true,
			'memcache'  => JCacheStorageMemcache::isSupported(),
			'memcached' => JCacheStorageMemcached::isSupported(),
			'redis'     => JCacheStorageRedis::isSupported(),
			'wincache'  => JCacheStorageWincache::isSupported(),
			'xcache'    => JCacheStorageXcache::isSupported(),
		);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->restoreErrorHandlers();
		$this->restoreFactoryState();
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Test Cases for getInstance
	 *
	 * @return array
	 */
	public function casesGetInstance()
	{
		$this->checkStores();

		return array(
			'defaultapc' => array(
				'apc',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				($this->available['apc'] ? 'JCacheStorageApc' : false),
			),
			'defaultapcu' => array(
				'apcu',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				($this->available['apcu'] ? 'JCacheStorageApcu' : false),
			),
			'defaultcachelite' => array(
				'cachelite',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'cachebase' => JPATH_BASE . '/cache',
					'caching' => true,
					'now' => time(),
				),
				($this->available['cachelite'] ? 'JCacheStorageCachelite' : false),
			),
			'defaultfile' => array(
				'file',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'cachebase' => JPATH_BASE . '/cache',
					'now' => time(),
				),
				'JCacheStorageFile',
			),
			'defaultmemcache' => array(
				'memcache',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				$this->available['memcache'] ? 'JCacheStorageMemcache' : false,
			),
			'defaultmemcached' => array(
				'memcached',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				$this->available['memcached'] ? 'JCacheStorageMemcached' : false,
			),
			'defaultredis' => array(
				'redis',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				$this->available['redis'] ? 'JCacheStorageRedis' : false,
			),
			'defaultwincache' => array(
				'wincache',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				$this->available['wincache'] ? 'JCacheStorageWincache' : false,
			),
			'defaultxcache' => array(
				'xcache',
				array(
					'application' => null,
					'language' => 'en-GB',
					'locking' => true,
					'lifetime' => null,
					'now' => time(),
				),
				$this->available['xcache'] ? 'JCacheStorageXcache' : false,
			),
		);
	}

	/**
	 * Testing getInstance
	 *
	 * @param   string  $handler   cache storage
	 * @param   array   $options   options for cache storage
	 * @param   string  $expClass  name of expected cache storage class
	 *
	 * @return void
	 *
	 * @dataProvider casesGetInstance
	 */
	public function testGetInstance($handler, $options, $expClass)
	{
		if (is_bool($expClass))
		{
			$this->markTestSkipped('The caching method ' . $handler . ' is not supported on this system.');
		}

		$this->object = JCacheStorage::getInstance($handler, $options);

		if (class_exists('JTestConfig'))
		{
			$config = new JTestConfig;
		}

		$this->assertThat(
			$this->object,
			$this->isInstanceOf($expClass),
			'The wrong class was received.'
		);

		$this->assertThat(
			$this->object->_application,
			$this->equalTo($options['application']),
			'Unexpected value for _application.'
		);

		$this->assertThat(
			$this->object->_language,
			$this->equalTo($options['language']),
			'Unexpected value for _language.'
		);

		$this->assertThat(
			$this->object->_locking,
			$this->equalTo($options['locking']),
			'Unexpected value for _locking.'
		);

		$config = JFactory::getConfig();
		$lifetime = !is_null($options['lifetime']) ? $options['lifetime'] * 60 : $config->get('cachetime', 1) * 60;
		$this->assertThat(
			$this->object->_lifetime,

			// @todo remove: $this->equalTo(empty($options['lifetime']) ? $config->get('cachetime')*60 : $options['lifetime']*60),
			$this->equalTo($lifetime),
			'Unexpected value for _lifetime.'
		);

		$this->assertLessThan(
			isset($config->cachetime) ? $config->cachetime : 900,
			abs($this->object->_now - time()),
			'Unexpected value for configuration lifetime.'
		);
	}

	/**
	 * Testing get()
	 *
	 * @return void
	 */
	public function testGet()
	{
		$this->assertThat(
			$this->object->get(1, '', time()),
			$this->equalTo(false)
		);
	}

	/**
	 * Testing store().
	 *
	 * @return void
	 */
	public function testStore()
	{
		$this->assertThat(
			$this->object->store(1, '', 'Cached'),
			$this->isTrue()
		);
	}

	/**
	 * Testing remove().
	 *
	 * @return void
	 */
	public function testRemove()
	{
		$this->assertThat(
			$this->object->remove(1, ''),
			$this->isTrue()
		);
	}

	/**
	 * Testing clean().
	 *
	 * @return void
	 */
	public function testClean()
	{
		$this->assertThat(
			$this->object->clean('', 'group'),
			$this->isTrue()
		);
	}

	/**
	 * Testing gc().
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->assertThat(
			$this->object->gc(),
			$this->isTrue()
		);
	}

	/**
	 * Testing isSupported().
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		$this->assertThat(
			$this->object::isSupported(),
			$this->isTrue()
		);
	}
}
