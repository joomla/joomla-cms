<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JCacheStorageRedis.
 */
class JCacheStorageRedisTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageRedis::isSupported())
		{
			$this->markTestSkipped('The Redis cache handler is not supported on this system.');
		}

		parent::setUp();

		// Parse the DSN details for the test server
		$dsn = defined('JTEST_CACHE_REDIS_DSN') ? JTEST_CACHE_REDIS_DSN : getenv('JTEST_CACHE_REDIS_DSN');

		if ($dsn)
		{
			// First let's trim the redis: part off the front of the DSN if it exists.
			if (strpos($dsn, 'redis:') === 0)
			{
				$dsn = substr($dsn, 6);
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
						JFactory::$config->set("redis_server_host", $v);
						break;
					case 'port':
						JFactory::$config->set("redis_server_port", $v);
						break;
					case 'db':
						JFactory::$config->set("redis_server_db", $v);
						break;
					case 'auth':
						JFactory::$config->set("redis_server_auth", $v);
						break;
				}
			}
		}
		else
		{
			$this->markTestSkipped('No configuration for Redis given');
		}

		try
		{
			$this->handler = new JCacheStorageRedis;
		}
		catch (JCacheExceptionConnecting $e)
		{
			$this->fail('Failed to connect to Redis');
		}

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}
}
