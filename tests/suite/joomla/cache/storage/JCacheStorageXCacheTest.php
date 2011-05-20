<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageXCache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 */
class JCacheStorageXCacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var	JCacheStorageXCache
	 * @access protected
	 */
	protected $object;

	/**
	 * @var	JCacheStorageXCache
	 * @access protected
	 */
	protected $xcacheAvailable;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		include_once JPATH_PLATFORM.'/joomla/cache/storage.php';
		include_once JPATH_PLATFORM.'/joomla/cache/storage/xcache.php';

		$this->xcacheAvailable = extension_loaded('xcache');
		$this->object = JCacheStorage::getInstance('xcache');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 * @access protected
	 */
	protected function tearDown()
	{
	}

	/**
	 * @return void
	 * @todo Implement testGet().
	 */
	public function testGet()
	{
		if ($this->xcacheAvailable) {
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else {
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testStore().
	 */
	public function testStore()
	{
		if ($this->xcacheAvailable) {
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else {
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testRemove().
	 */
	public function testRemove()
	{
		if ($this->xcacheAvailable) {
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else {
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testClean().
	 */
	public function testClean()
	{
		if ($this->xcacheAvailable) {
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else {
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Testing test().
	 *
	 * @return void
	 */
	public function testTest()
	{
		$this->assertThat(
			$this->object->test(),
			$this->equalTo($this->xcacheAvailable),
			'Claims xcache is not loaded.'
		);
	}

	/**
	 * @return void
	 * @todo Implement test_getCacheId().
	 */
	public function testGetCacheId()
	{
		if ($this->xcacheAvailable) {
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else {
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}
}