<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock classes
 *
 * Include mocks here
 *
 * We now return to our regularly scheduled environment.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheStorageMainTest extends TestCase
{
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
		JFactory::$session = $this->getMockSession();

		require_once dirname(__DIR__) . '/controller/JCacheControllerRaw.php';
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

		parent::tearDown();
	}

	/**
	 * Test provider
	 *
	 * @return  array
	 */
	public static function provider()
	{
		static $ret = array();

		if (empty($ret))
		{
			$names = JCache::getStores();

			foreach ($names as $name)
			{
				// Make sure the adapter is not in our blacklist; this means the adapter cannot be tested or there is an internal failure
				if (!in_array($name, array('redis')))
				{
					$ret["$name adapter"] = array($name);
				}
			}
		}

		return $ret;
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @return  void
	 *
	 * @dataProvider  provider
	 */
	public function testCacheHit($store)
	{
		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertSame($new, $data, 'Expected: ' . $data . ' Actual: ' . $new);
		unset($cache);
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @return  void
	 *
	 * @dataProvider  provider
	 */
	public function testCacheMiss($store)
	{
		$id = 'randomTestID2423423';
		$group = '_testing';
		$data = 'testData';

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: ' . $new);
		unset($cache);
	}

	/**
	 * Test...
	 *
	 * @medium
	 *
	 * @dataProvider provider
	 *
	 * @param   string  $store  The store.
	 *
	 * @return void
	 */
	public function testCacheTimeout($store)
	{
		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$cache->setLifeTime(2);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);

		sleep(5);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setLifeTime(2);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: ' . ((string) $new));
		unset($cache);
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @return  void
	 *
	 * @dataProvider  provider
	 */
	public function testCacheRemove($store)
	{
		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$test = $cache->remove($id, $group);
		$this->assertTrue($test, 'Removal Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: ' . ((string) $new));
		unset($cache);
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @return  void
	 *
	 * @dataProvider  provider
	 */
	public function testCacheClearGroup($store)
	{
		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->clean($group), 'Clean Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: ' . ((string) $new));
		unset($cache);
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @return  void
	 *
	 * @dataProvider  provider
	 */
	public function testCacheClearNotGroup($store)
	{
		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue((bool) $cache->clean($group, 'notgroup'), 'Clean Failed');
		unset($cache);

		$cache = JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertSame($new, $data, 'Expected: ' . $data . ' Actual: ' . ((string) $new));
		unset($cache);
	}
}
