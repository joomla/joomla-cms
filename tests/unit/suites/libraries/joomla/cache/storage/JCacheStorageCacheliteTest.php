<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageCachelite.
 */
class JCacheStorageCacheliteTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageCachelite::isSupported() || $this->isBlacklisted('cachelite'))
		{
			$this->markTestSkipped('The Cache_Lite cache handler is not supported on this system.');
		}

		parent::setUp();

		$this->handler = new JCacheStorageCachelite(array('caching' => true, 'cachebase' => JPATH_TESTS . '/tmp'));

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		parent::tearDown();

		// Reset the Cache_Lite instance.
		TestReflection::setValue('JCacheStorageCachelite', 'CacheLiteInstance', null);
	}

	/**
	 * Overrides TestCaseCache::testCacheTimeout to deal with the adapter's stored time values in this test
	 *
	 * @testdox  The cache handler correctly handles expired cache data
	 *
	 * @medium
	 */
	public function testCacheTimeout()
	{
		/** @var Cache_Lite $cacheLiteInstance */
		$cacheLiteInstance = TestReflection::getValue('JCacheStorageCachelite', 'CacheLiteInstance');
		$cacheLiteInstance->_lifeTime = 0.1;

		$data = 'testData';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');

		// Timer and max time (in seconds)
		$timer    = 0;
		$maxTime  = 3;

		// Testing interval (in seconds)
		$interval = 0.1;

		do
		{
			$timer += $interval;

			usleep($interval * 1000000);

			$cache = $this->handler->get($this->id, $this->group);

		}
		while ($cache && $timer < 3);

		$this->assertFalse($cache, 'No data should be returned from the cache store when expired.');
	}
}
