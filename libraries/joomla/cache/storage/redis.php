<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis cache storage handler for PECL
 *
 * @since  3.4
 */
class JCacheStorageRedis extends JCacheStorage
{
	/**
	 * Redis connection object
	 *
	 * @var    Redis
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
	 * @return  Redis|boolean  Redis connection object on success, boolean on failure
	 *
	 * @since   3.4
	 * @note    As of 4.0 this method will throw a JCacheExceptionConnecting object on connection failure
	 */
	protected function getConnection()
	{
		if (static::isSupported() == false)
		{
			return false;
		}

		$app = JFactory::getApplication();

		$this->_persistent = $app->get('redis_persist', true);

		$server = array(
			'host' => $app->get('redis_server_host', 'localhost'),
			'port' => $app->get('redis_server_port', 6379),
			'auth' => $app->get('redis_server_auth', null),
			'db'   => (int) $app->get('redis_server_db', null),
		);

		static::$_redis = new Redis;

		if ($this->_persistent)
		{
			try
			{
				$connection = static::$_redis->pconnect($server['host'], $server['port']);
				$auth       = (!empty($server['auth'])) ? static::$_redis->auth($server['auth']) : true;
			}
			catch (RedisException $e)
			{
				JLog::add($e->getMessage(), JLog::DEBUG);
			}
		}
		else
		{
			try
			{
				$connection = static::$_redis->connect($server['host'], $server['port']);
				$auth       = (!empty($server['auth'])) ? static::$_redis->auth($server['auth']) : true;
			}
			catch (RedisException $e)
			{
				JLog::add($e->getMessage(), JLog::DEBUG);
			}
		}

		if ($connection == false)
		{
			static::$_redis = null;

			if ($app->isClient('administrator'))
			{
				JError::raiseWarning(500, 'Redis connection failed');
			}

			return false;
		}

		if ($auth == false)
		{
			if ($app->isClient('administrator'))
			{
				JError::raiseWarning(500, 'Redis authentication failed');
			}

			return false;
		}

		$select = static::$_redis->select($server['db']);

		if ($select == false)
		{
			static::$_redis = null;

			if ($app->isClient('administrator'))
			{
				JError::raiseWarning(500, 'Redis failed to select database');
			}

			return false;
		}

		try
		{
			static::$_redis->ping();
		}
		catch (RedisException $e)
		{
			static::$_redis = null;

			if ($app->isClient('administrator'))
			{
				JError::raiseWarning(500, 'Redis ping failed');
			}

			return false;
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
		if (static::isConnected() == false)
		{
			return false;
		}

		return static::$_redis->exists($this->_getCacheId($id, $group));
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
		if (static::isConnected() == false)
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
		if (static::isConnected() == false)
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
						$item = new JCacheStorageHelper($group);
					}
					else
					{
						$item = $data[$group];
					}

					$item->updateSize(strlen($key)*8/1024);
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
		if (static::isConnected() == false)
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
		if (static::isConnected() == false)
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
		if (static::isConnected() == false)
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
		return class_exists('Redis');
	}

	/**
	 * Test to see if the Redis connection is available.
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public static function isConnected()
	{
		return static::$_redis instanceof Redis;
	}
}
