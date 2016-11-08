<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageApc.
 */
class JCacheStorageApcTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageApc::isSupported())
		{
			$this->markTestSkipped('The APC cache handler is not supported on this system.');
		}

		parent::setUp();

		$this->handler = new JCacheStorageApc;

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}

	/**
	 * Overrides TestCaseCache::testCacheTimeout to skip the test due to an environment incompatibility
	 *
	 * @testdox  The cache handler correctly handles expired cache data
	 *
	 * @ticket   https://bugs.php.net/bug.php?id=58084
	 */
	public function testCacheTimeout()
	{
		$this->markTestSkipped('The APC cache TTL is not working in a single process/request. See https://bugs.php.net/bug.php?id=58084');
	}
}
