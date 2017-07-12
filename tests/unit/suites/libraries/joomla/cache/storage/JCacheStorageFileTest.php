<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
		if (!JCacheStorageFile::isSupported())
		{
			$this->markTestSkipped('The file cache handler is not supported on this system.');
		}

		parent::setUp();

		// Use a uniq path for tests
		$path = JPATH_CACHE . '/' . uniqid();

		mkdir($path);

		$this->handler = new JCacheStorageFile(array('cachebase' => $path));

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
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

		// Test whether data was stored.
		$this->assertEquals($data, $this->handler->get($this->id, $this->group), 'Some data should be available in lifetime.');

		// If we add only lifetime then the cache still be valid
		$this->handler->_now += 1 + $this->handler->_lifetime;

		$this->assertFalse($this->handler->get($this->id, $this->group), 'No data should be returned from the cache store when expired.');
	}
}
