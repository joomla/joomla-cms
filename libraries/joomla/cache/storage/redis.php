<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis cache storage handler for PECL
 *
 * @package     Joomla.Platform
 * @subpackage  Cache
 * @since       3.4
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
	 * Return redis connection object
	 *
	 * @return  mixed  Redis connection object on success, void or boolean on failure
	 *
	 * @since   3.4
	 *
	 * @throws  RuntimeException
	 */
	protected function getConnection()
	{
		if (static::isSupported() == false)
		{
			return false;
		}

		$config  = JFactory::getConfig();
		$app     = JFactory::getApplication();
		$caching = (bool) $config->get('caching');

		if ($caching == false)
		{
			return false;
		}

		$this->_persistent = $config->get('redis_persist', true);

		$server = array(
			'host' => $config->get('redis_server_host', 'localhost'),
			'port' => $config->get('redis_server_port', 6379),
			'auth' => $config->get('redis_server_auth', null),
			'db'   => (int) $config->get('redis_server_db', null)
		);

		static::$_redis = new Redis;

		if ($this->_persistent)
		{
			try
			{
				$connection = static::$_redis->pconnect($server['host'], $server['port']);
				$auth       = (!empty($server['auth'])) ? static::$_redis->auth($server['auth']) : true;
			}
			catch (Exception $e)
			{
			}
		}
		else
		{
			try
			{
				$connection = static::$_redis->connect($server['host'], $server['port']);
				$auth       = (!empty($server['auth'])) ? static::$_redis->auth($server['auth']) : true;
			}
			catch (Exception $e)
			{
			}
		}

		if ($connection == false)
		{
			static::$_redis = null;

			if ($app->isAdmin())
			{
				JError::raiseWarning(500, 'Redis connection failed');
			}

			return;
		}

		if ($auth == false)
		{
			if ($app->isAdmin())
			{
				JError::raiseWarning(500, 'Redis authentication failed');
			}

			return;
		}

		$select = static::$_redis->select($server['db']);

		if ($select == false)
		{
			static::$_redis = null;

			if ($app->isAdmin())
			{
				JError::raiseWarning(500, 'Redis failed to select database');
			}

			return;
		}

		try
		{
			static::$_redis->ping();
		}
		catch (RedisException $e)
		{
			static::$_redis = null;

			if ($app->isAdmin())
			{
				JError::raiseWarning(500, 'Redis ping failed');
			}

			return;
		}

		return static::$_redis;
	}

	/**
	 * Get cached data from redis by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data string
	 *
	 * @since   3.4
	 */
	public function get($id, $group, $checkTime = true)
	{
		if (static::isConnected() == false)
		{
			return false;
		}

		$cache_id = $this->_getCacheId($id, $group);
		$back     = static::$_redis->get($cache_id);

		return $back;
	}

	/**
	 * Get all cached data
	 *
	 * @return  array  Array of cached data
	 *
	 * @since   3.4
	 */
	public function getAll()
	{
		if (static::isConnected() == false)
		{
			return false;
		}

		parent::getAll();

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
	 * Store the data to Redis by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.4
	 */
	public function store($id, $group, $data)
	{
		if (static::isConnected() == false)
		{
			return false;
		}

		$cache_id     = $this->_getCacheId($id, $group);
		$tmparr       = new stdClass;
		$tmparr->name = $cache_id;
		$tmparr->size = strlen($data);

		$config       = JFactory::getConfig();
		$lifetime     = (int) $config->get('cachetime', 15);

		if ($this->_lifetime == $lifetime)
		{
			$this->_lifetime = $lifetime * 60;
		}

		$index[] = $tmparr;

		static::$_redis->setex($cache_id, 3600, $data);

		return true;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.4
	 */
	public function remove($id, $group)
	{
		if (static::isConnected() == false)
		{
			return false;
		}

		$cache_id = $this->_getCacheId($id, $group);

		return static::$_redis->delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *                          group mode : cleans all cache in the group
	 *                          notgroup mode : cleans all cache not in the group
	 *
	 * @return  boolean  True on success, false otherwise
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
	 * Test to see if the cache storage is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.4
	 */
	public static function isSupported()
	{
		return class_exists('Redis');
	}

	/**
	 * Test to see if the Redis connection is up
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.4
	 */
	public static function isConnected()
	{
		return (bool) static::$_redis;
	}
}
