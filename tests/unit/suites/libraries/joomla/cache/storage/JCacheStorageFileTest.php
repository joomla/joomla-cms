<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
		if (!JCacheStorageFile::isSupported())
		{
			$this->markTestSkipped('The file cache handler is not supported on this system.');
		}

		parent::setUp();

		$this->handler = new JCacheStorageFile(array('cachebase' => JPATH_CACHE));

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

	/**
	 * @testdox  Can't test race conditions with a single process, not even with pcntl_fork()
	 */
	public function testCacheLock()
	{
	}

	/**
	 * @testdox  Test the integrity checker
	 */
	public function testCacheIntegrityChecker()
	{
		// To determine the path of the cache buffer, is required to gain access
		// to the protected method JCacheStorageFile::_getFilePath()
		$reflector = new ReflectionObject($this->handler);
		$_getFilePath = $reflector->getMethod('_getFilePath');
		$_getFilePath->setAccessible(true);
		$file_path = $_getFilePath->invoke($this->handler, $this->id, $this->group);
		// Prepare arbitrary test data
		$testData = 'testData';

		// Craft a cache buffer file, and ask the cache object to read the data contained in it
		file_put_contents($file_path, JCacheStorageFile::PHP_HEADING_PROTECTION . $testData . JCacheStorageFile::INTEGRITY_DIGIT);
		$readback = $this->handler->get($this->id, $this->group);
		// Decode the data and comare it with the original
		$this->assertSame($readback, $testData, 'Integrity check failed on File Storage Engine');

		// Now save some data into the cache
		$this->handler->store($this->id, $this->group, $testData);
		// Intentionally corrupt the cache file, streaming arbitrary data directly on it
		file_put_contents($file_path, $testData);
		$readback = $this->handler->get($this->id, $this->group);
		// The resulting data must be boolean false
		$this->assertSame($readback, false, 'Integrity check failed on File Storage Engine');
	}
}
