<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Abstract test case class for unit testing cache handlers.
 */
abstract class TestCaseCache extends TestCase
{
	/**
	 * The cache handler being tested
	 *
	 * @var  JCacheStorage
	 */
	protected $handler;

	/**
	 * The ID (key) to use for the cache data
	 *
	 * @var  string
	 */
	protected $id;

	/**
	 * The group for cache data
	 *
	 * @var  string
	 */
	protected $group = '_testing';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession();

		$this->id = bin2hex(random_bytes(8));
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		if ($this->handler instanceof JCacheStorage)
		{
			// Deprecated, temporary have to stay because flush method is not implemented in all storages.
			$this->handler->clean($this->group);
			$this->handler->flush();
		}

		parent::tearDown();
	}

	/**
	 * @testdox  Data is correctly stored to the cache store and reported as existing
	 */
	public function testCacheContains()
	{
		$data = 'testData';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');
		$this->assertTrue($this->handler->contains($this->id, $this->group), 'Failed validating data exists in the cache store');
	}

	/**
	 * @testdox  Data is correctly stored to and retrieved from the cache storage handler
	 */
	public function testCacheHit()
	{
		$data = 'testData';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');
		$this->assertSame($this->handler->get($this->id, $this->group), $data, 'Failed retrieving data from the cache store');
	}

	/**
	 * @testdox  Non-existing data cannot be retrieved from the cache storage handler
	 */
	public function testCacheMiss()
	{
		$this->assertFalse($this->handler->get($this->id, $this->group), 'No data should be returned from the cache store when the key has not been previously set.');
	}

	/**
	 * @testdox  The cache handler correctly handles expired cache data
	 *
	 * @medium
	 */
	public function testCacheTimeout()
	{
		$data = 'testData';

		if ($this->handler->_lifetime > 1)
		{
			// Minimum lifetime for memcache(-d) and redis can be only 1
			$this->handler->_lifetime = 1;
		}

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');

		// Test whether data was stored.
		$this->assertEquals($data, $this->handler->get($this->id, $this->group), 'Some data should be available in lifetime.');

		// Timer and testing interval (in seconds)
		$timer    = 0;
		$interval = 0.05;

		// Wait for lifetime minus the first interval.
		usleep(($this->handler->_lifetime - $interval) * 1000000);

		do
		{
			usleep($interval * 1000000);

			$cache  = $this->handler->get($this->id, $this->group);
			$timer += $interval;
		}
		while ($cache === $data && $timer < 5);

		$this->assertFalse($cache, 'No data should be returned from the cache store when expired.');
	}

	/**
	 * @testdox  Data is removed from the cache store
	 */
	public function testCacheRemove()
	{
		$data = 'testData';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');
		$this->assertTrue($this->handler->remove($this->id, $this->group), 'Removal Failed');
		$this->assertFalse($this->handler->get($this->id, $this->group), 'No data should be returned from the cache store after being removed.');
	}

	/**
	 * @testdox  Data within a group is removed from the cache store
	 */
	public function testCacheClearGroup()
	{
		$data = 'testData';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');
		$this->assertTrue($this->handler->clean($this->group, 'group'), 'Removal Failed');
		$this->assertFalse($this->handler->get($this->id, $this->group), 'No data should be returned from the cache store after being removed.');
	}

	/**
	 * @testdox  Data not within the specified group is removed from the cache store
	 */
	public function testCacheClearNotGroup()
	{
		$data        = 'testData';
		$secondId    = bin2hex(random_bytes(8));
		$secondGroup = 'group2';

		$this->assertTrue($this->handler->store($this->id, $this->group, $data), 'Initial Store Failed');
		$this->assertTrue($this->handler->store($secondId, $data, $secondGroup), 'Initial Store Failed');
		$this->assertTrue($this->handler->clean($this->group, 'notgroup'), 'Removal Failed');
		$this->assertSame($this->handler->get($this->id, $this->group), $data, 'Data in the group specified in JCacheStorage::clean() should still exist');
		$this->assertFalse($this->handler->get($secondId, $secondGroup), 'Data in the groups not specified in JCacheStorage::clean() should not exist');
	}

	/**
	 * @testdox  The cache handler is supported in this environment
	 */
	public function testIsSupported()
	{
		$this->assertTrue($this->handler->isSupported(), 'Claims the cache handler is not supported.');
	}
}
