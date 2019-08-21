<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Storage;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheStorage;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\Redis\RedisDriver;


/**
 * Redis cache storage handler for PECL
 *
 * @since  3.4
 */
class RedisStorage extends CacheStorage
{
	/**
	 * Redis connection object
	 *
	 * @var    \Redis
	 * @since  3.4
	 */
	protected static $_redis = null;

	/**
	 * Persistent session flag
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $_persistent = false;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   3.4
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		if (static::$_redis === null)
		{
			$this->getConnection();
		}
	}

	/**
	 * Create the Redis connection
	 *
	 * @return  \Redis|boolean  Redis connection object on success, boolean on failure
	 *
	 * @since   3.4
	 * @note    As of 4.0 this method will throw a JCacheExceptionConnecting object on connection failure
	 */
	protected function getConnection()
	{
		if (RedisDriver::isSupported() == false)
		{
			return false;
		}

		try
		{
			static::$_redis = RedisDriver::getInstance();
		}
		catch (\RedisException $e)
		{
			static::$_redis = null;

			throw new \JCacheExceptionConnecting('Redis ping failed', 500);
		}

		return static::$_redis;
	}

	/**
	 * Check if the cache contains data stored by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function contains($id, $group)
	{
		if (RedisDriver::isConnected() == false)
		{
			return false;
		}

		// Redis exists returns integer values lets convert that to boolean see: https://redis.io/commands/exists
		return (bool) static::$_redis->exists($this->_getCacheId($id, $group));
	}

	/**
	 * Get cached data by ID and group
	 *
	 * @param   string   $id         The cache data ID
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   3.4
	 */
	public function get($id, $group, $checkTime = true)
	{
		if (RedisDriver::isConnected() == false)
		{
			return false;
		}

		return static::$_redis->get($this->_getCacheId($id, $group));
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   3.4
	 */
	public function getAll()
	{
		if (RedisDriver::isConnected() == false)
		{
			return false;
		}

		$allKeys = static::$_redis->keys('*');
		$data    = array();
		$secret  = $this->_hash;

		if (!empty($allKeys))
		{
			foreach ($allKeys as $key)
			{
				$namearr = explode('-', $key);

				if ($namearr !== false && $namearr[0] == $secret && $namearr[1] == 'cache')
				{
					$group = $namearr[2];

					if (!isset($data[$group]))
					{
						$item = new CacheStorageHelper($group);
					}
					else
					{
						$item = $data[$group];
					}

					$item->updateSize(strlen($key)*8);
					$data[$group] = $item;
				}
			}
		}

		return $data;
	}

	/**
	 * Store the data to cache by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public function store($id, $group, $data)
	{
		if (RedisDriver::isConnected() == false)
		{
			return false;
		}

		static::$_redis->setex($this->_getCacheId($id, $group), $this->_lifetime, $data);

		return true;
	}

	/**
	 * Remove a cached data entry by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public function remove($id, $group)
	{
		if (RedisDriver::isConnected() == false)
		{
			return false;
		}

		return (bool) static::$_redis->delete($this->_getCacheId($id, $group));
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode    : cleans all cache in the group
	 * notgroup mode : cleans all cache not in the group
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public function clean($group, $mode = null)
	{
		if (RedisDriver::isConnected() == false)
		{
			return false;
		}

		$allKeys = static::$_redis->keys('*');

		if ($allKeys === false)
		{
			$allKeys = array();
		}

		$secret = $this->_hash;

		foreach ($allKeys as $key)
		{
			if (strpos($key, $secret . '-cache-' . $group . '-') === 0 && $mode == 'group')
			{
				static::$_redis->delete($key);
			}

			if (strpos($key, $secret . '-cache-' . $group . '-') !== 0 && $mode != 'group')
			{
				static::$_redis->delete($key);
			}
		}

		return true;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public static function isSupported()
	{
		return class_exists('\\Redis');
	}

	/**
	 * Test to see if the Redis connection is available.
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public static function AisConnected()
	{
		return static::$_redis instanceof \Redis;
	}
}
