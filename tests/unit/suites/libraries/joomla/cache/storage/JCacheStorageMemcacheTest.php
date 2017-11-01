<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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

		// Parse the DSN details for the test server
		$dsn = defined('JTEST_CACHE_MEMCACHE_DSN') ? JTEST_CACHE_MEMCACHE_DSN : getenv('JTEST_CACHE_MEMCACHE_DSN');

		if ($dsn)
		{
			// First let's trim the redis: part off the front of the DSN if it exists.
			if (strpos($dsn, 'memcache:') === 0)
			{
				$dsn = substr($dsn, 9);
			}

			// Call getConfig once to have the registry object prepared
			JFactory::getConfig();

			// Split the DSN into its parts over semicolons.
			$parts = explode(';', $dsn);

			// Parse each part and populate the options array.
			foreach ($parts as $part)
			{
				list ($k, $v) = explode('=', $part, 2);
				switch ($k)
				{
					case 'host':
						JFactory::$config->set("memcache_server_host", $v);
						break;
					case 'port':
						JFactory::$config->set("memcache_server_port", $v);
						break;
				}
			}
		}

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
