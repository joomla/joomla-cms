<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.cache.cache');

/**
 * Mock classes
 *
 * Include mocks here
 *
 * We now return to our regularly scheduled environment.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheStorageTest_Main extends PHPUnit_Framework_TestCase
{
	/**
	 * Test setUp
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		require_once dirname(__DIR__) . '/controller/JCacheControllerRaw.php';
	}

	/**
	 * Test provider
	 *
	 * @return array
	 */
	public static function provider()
	{
		static $ret = array();

		if (empty($ret))
		{
			$names = JCache::getStores();

			foreach ($names as $name)
			{
				$ret[] = array($name);
			}
		}

		return $ret;
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testCacheHit($store)
	{
		if ($store == 'eaccelerator')
		{
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache')
		{
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage' => $store));
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
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testCacheMiss($store)
	{
		if ($store == 'eaccelerator')
		{
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache')
		{
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID2423423';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: ' . $new);
		unset($cache);
	}

	/**
	 * Test...
	 *
	 * @param   string  $store  The store.
	 *
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testCacheTimeout($store)
	{
		if ($store == 'eaccelerator')
		{
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache')
		{
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$cache->setLifeTime(2);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		sleep(5);
		$cache =& JCache::getInstance('', array('storage' => $store));
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
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testCacheRemove($store)
	{

		if ($store == 'eaccelerator')
		{
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache')
		{
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$test = $cache->remove($id, $group);
		$this->assertTrue($test, 'Removal Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage' => $store));
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
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testCacheClearGroup($store)
	{

		if ($store == 'eaccelerator')
		{
			$this->markTestSkipped('Eaccelerator does not wotk with cli, skipped');
		}

		if ($store == 'xcache')
		{
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->clean($group), 'Clean Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage' => $store));
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
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testCacheClearNotGroup($store)
	{
		if ($store == 'eaccelerator')
		{
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache')
		{
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);

		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$this->assertTrue((bool) $cache->clean($group, 'notgroup'), 'Clean Failed');
		unset($cache);

		$cache =& JCache::getInstance('', array('storage' => $store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertSame($new, $data, 'Expected: ' . $data . ' Actual: ' . ((string) $new));
		unset($cache);
	}
}
