<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	 * Testing getInstance
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		$this->object = JCacheStorage::getInstance(
			"file",
			array(
				'application' => null,
				'language' => 'en-GB',
				'locking' => true,
				'lifetime' => null,
				'cachebase' => JPATH_BASE . '/cache',
				'now' => time(),
			)
		);

		if (class_exists('JTestConfig'))
		{
			$config = new JTestConfig;
		}

		$this->assertThat(
			$this->object,
			$this->isInstanceOf("JCacheStorageFile"),
			'The wrong class was received.'
		);

		$this->assertThat(
			$this->object->_application,
			$this->equalTo(null),
			'Unexpected value for _application.'
		);

		$this->assertThat(
			$this->object->_language,
			$this->equalTo('en-GB'),
			'Unexpected value for _language.'
		);

		$this->assertThat(
			$this->object->_locking,
			$this->equalTo(true),
			'Unexpected value for _locking.'
		);

		$config = JFactory::getConfig();
		$this->assertThat(
			$this->object->_lifetime,
			$this->equalTo($config->get('cachetime', 1) * 60),
			'Unexpected value for _lifetime.'
		);

		$this->assertLessThan(
			$config->cachetime ?? 900,
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
		$object = $this->object;

		$this->assertThat(
			$object::isSupported(),
			$this->isTrue()
		);
	}
}
