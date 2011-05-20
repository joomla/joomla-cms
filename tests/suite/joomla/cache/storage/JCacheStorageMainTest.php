<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.cache.cache');

/*
 * Mock classes
 */
// Include mocks here
/*
 * We now return to our regularly scheduled environment.
 */

class JCacheStorageTest_Main extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		require_once dirname(dirname(__FILE__)).'/controller/JCacheControllerRaw.php';
	}

	public static function provider()
	{
		static $ret = array();

		if (empty($ret)) {
			$names = JCache::getStores();
			foreach ($names AS $name) {
				$ret[] = array($name);
			}
		}
		return $ret;
	}

	/**
	 * @dataProvider provider
	 */
	function testCacheHit($store)
	{
		if ($store == 'eaccelerator') {
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache') {
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertSame($new, $data, 'Expected: '.$data.' Actual: '.$new);
		unset($cache);
	}

	/**
	 * @dataProvider provider
	 */
	function testCacheMiss($store)
	{
		if ($store == 'eaccelerator') {
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache') {
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID2423423';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: '.$new);
		unset($cache);
	}

	/**
	 * @dataProvider provider
	 */
	function testCacheTimeout($store)
	{
		if ($store == 'eaccelerator') {
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache') {
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$cache->setLifeTime(2);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		sleep(5);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setLifeTime(2);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: '.((string) $new));
		unset($cache);
	}

	/**
	 * @dataProvider provider
	 */
	function testCacheRemove($store)
	{

		if ($store == 'eaccelerator') {
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache') {
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$test = $cache->remove($id, $group);
		$this->assertTrue($test, 'Removal Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: '.((string) $new));
		unset($cache);
	}

	/**
	 * @dataProvider provider
	 */
	function testCacheClearGroup($store)
	{

		if ($store == 'eaccelerator') {
			$this->markTestSkipped('Eaccelerator does not wotk with cli, skipped');
		}

		if ($store == 'xcache') {
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$this->assertTrue($cache->clean($group), 'Clean Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertFalse($new, 'Expected: false Actual: '.((string) $new));
		unset($cache);
	}

	/**
	 * @dataProvider provider
	 */
	function testCacheClearNotGroup($store)
	{
		if ($store == 'eaccelerator') {
			$this->markTestSkipped('Eaccelerator does not work with cli, skipped');
		}

		if ($store == 'xcache') {
			$this->markTestSkipped('Xcache does not work with cli, skipped');
		}

		$id = 'randomTestID';
		$group = '_testing';
		$data = 'testData';
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$this->assertTrue($cache->store($data, $id, $group), 'Initial Store Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$this->assertTrue((bool)$cache->clean($group, 'notgroup'), 'Clean Failed');
		unset($cache);
		$cache =& JCache::getInstance('', array('storage'=>$store));
		$cache->setCaching(true);
		$new = $cache->get($id, $group);
		$this->assertSame($new, $data, 'Expected: '.$data.' Actual: '.((string) $new));
		unset($cache);
	}
}