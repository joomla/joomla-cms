<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageMemcache.
 */
class JCacheStorageMemcacheTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageMemcache::isSupported())
		{
			$this->markTestSkipped('The Memcache cache handler is not supported on this system.');
		}

		parent::setUp();

		try
		{
			$this->handler = new JCacheStorageMemcache;
		}
		catch (JCacheExceptionConnecting $e)
		{
			$this->markTestSkipped('Failed to connect to Memcache');
		}

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}
}
