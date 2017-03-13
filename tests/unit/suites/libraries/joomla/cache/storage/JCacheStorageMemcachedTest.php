<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageMemcached.
 */
class JCacheStorageMemcachedTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageMemcached::isSupported())
		{
			$this->markTestSkipped('The Memcached cache handler is not supported on this system.');
		}

		parent::setUp();

		try
		{
			$this->handler = new JCacheStorageMemcached;
		}
		catch (JCacheExceptionConnecting $e)
		{
			$this->markTestSkipped('Failed to connect to Memcached');
		}

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}
}
