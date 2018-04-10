<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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

		// Mock the returns on JApplicationCms::get() to use the default values
		JFactory::$application->expects($this->any())
			->method('get')
			->will($this->returnCallback(array($this, 'applicationGetterCallback')));

		$this->handler = new JCacheStorageRedis;

		// This adapter doesn't throw an Exception on a connection failure so we'll have to use Reflection to get into the class to check it
		if (!(TestReflection::getValue($this->handler, '_redis') instanceof Redis))
		{
			$this->markTestSkipped('Failed to connect to Redis');
		}

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}

	/**
	 * Callback for application getter that returns redis credentials
	 *
	 * @return mixed
	 */
	public function applicationGetterCallback()
	{
		// Parse the DSN details for the test server
		$dsn  = defined('JTEST_CACHE_REDIS_DSN') ? JTEST_CACHE_REDIS_DSN : getenv('JTEST_CACHE_REDIS_DSN');
		$args = func_get_args();

		if ($dsn)
		{
			// First let's trim the redis: part off the front of the DSN if it exists.
			if (strpos($dsn, 'redis:') === 0)
			{
				$dsn = substr($dsn, 6);
			}

			// Split the DSN into its parts over semicolons.
			$parts = explode(';', $dsn);
			$connection = array();

			// Parse each part and populate the options array.
			foreach ($parts as $part)
			{
				list ($k, $v) = explode('=', $part, 2);
				$connection[$k] = $v;
			}

			switch ($args[0])
			{
				case "redis_server_host":
					return $connection["host"];
					break;
				case "redis_server_port":
					return $connection["port"];
					break;
				case "redis_server_auth":
					return $connection["auth"];
					break;
				case "redis_server_db":
					return $connection["db"];
					break;
			}
		}
		return $args[1];
	}
}
