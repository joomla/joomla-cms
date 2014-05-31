<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis cache storage handler
 *
 * @package     Joomla.Platform
 * @subpackage  Cache
 
 * @since       11.1
 */
class JCacheStorageRedis extends JCacheStorage
{
	/**
	 * Redis connection object
	 *
	 * @var    Redis
	 * @since  11.1
	 */
	protected static $_redis = null;

	/**
	 * Persistent session flag
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_persistent = false;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		if (self::$_redis === null)
		{
			$this->getConnection();
		}
	}

	/**
	 * Return redis connection object
	 *
	 * @return  object   redis connection object
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	protected function getConnection()
	{
		if (static::isSupported() != true)
		{
			return false;
		}

		$config = JFactory::getConfig();
		$this->_persistent = $config->get('redis_persist', true);

		$server = array();
		$server['host'] = $config->get('redis_server_host', 'localhost');
		$server['port'] = $config->get('redis_server_port', 6379);
		$server['auth'] = $config->get('redis_server_auth', NULL);

		self::$_redis = new Redis();
		if($this->_persistent)
		{
			$redistest = self::$_redis->pconnect($server['host'], $server['port']) && self::$_redis->auth($server['auth']);
		}
		else
		{
			$redistest = self::$_redis->connect($server['host'], $server['port']) && self::$_redis->auth($server['auth']);
		}

		if ($redistest == false)
		{
			$app = JFactory::getApplication();
			if ($app->isAdmin())
			{
				JError::raiseWarning(500, JText::_('JLIB_CACHE_ERROR_CACHE_STORAGE_REDIS_NO_CONNECTION'));
			}

			return;
		}

		return;
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
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		$cache_id = $this->_getCacheId($id, $group);
		$back = self::$_redis->get($cache_id);

		return $back;
	}

	/**
	 * Get all cached data
	 *
	 * @return  array    data
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		parent::getAll();

		$allKeys = self::$_redis->keys('*');
		$data = array();
		$secret = $this->_hash;

		if (!empty($allKeys))
		{
			foreach ($allKeys as $key)
			{
				$namearr = explode('-', $key);

				if ($namearr !== false && $namearr[0] == $secret && $namearr[1] == 'cache')
				{
					$group = $namearr[2];

					if (!isset($data[$group])){
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
	 * Store the data to redis by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);

		$tmparr = new stdClass;
		$tmparr->name = $cache_id;
		$tmparr->size = strlen($data);

		$config = JFactory::getConfig();
		$lifetime = (int) $config->get('cachetime', 15);
		if ($this->_lifetime == $lifetime)
		{
			$this->_lifetime = $lifetime * 60;
		}

		$index[] = $tmparr;

		self::$_redis->setex($cache_id, 3600, $data);

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
	 * @since   11.1
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return self::$_redis->delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 * group mode    : cleans all cache in the group
	 * notgroup mode : cleans all cache not in the group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		$allKeys = self::$_redis->keys('*');
		if ($allKeys === false)
		{
			$allKeys = array();
		}
		$secret = $this->_hash;

		foreach ($allKeys as $key)
		{
			if (strpos($key, $secret . '-cache-' . $group . '-') === 0 && $mode == 'group')
			{
				self::$_redis->delete($key);
			}
			if (strpos($key, $secret . '-cache-' . $group . '-') !== 0 && $mode != 'group')
			{
				self::$_redis->delete($key);
			}
		}

		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		if (class_exists('Redis') != true)
		{
			return false;
		}

		return true;
	}
}
