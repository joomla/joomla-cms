<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageFile.
 */
class JCacheStorageFileTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageFile::isSupported() || $this->isBlacklisted('file'))
		{
			$this->markTestSkipped('The file cache handler is not supported on this system.');
		}

		parent::setUp();

		$this->handler = new JCacheStorageFile(array('cachebase' => JPATH_CACHE));

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 0.1;
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
		$data = 'testData';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');

		// Timer and max time (in seconds)
		$timer    = 0;
		$maxTime  = 3;

		// Testing interval (in seconds)
		$interval = 0.1;

		do
		{
			usleep($interval * 1000000);

			$timer += $interval;

			$this->handler->_now = time();
			$cache = $this->handler->get($this->id, $this->group);
		}
		while ($cache && $timer < 3);
		
        	$this->assertFalse($cache, 'No data should be returned from the cache store when expired.');
	}
}
