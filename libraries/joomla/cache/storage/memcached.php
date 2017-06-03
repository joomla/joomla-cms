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
 * Memcached cache storage handler
 *
 * @see    http://php.net/manual/en/book.memcached.php
 * @since  12.1
 */
class JCacheStorageMemcached extends JCacheStorage
{
	/**
	 * Memcached connection object
	 *
	 * @var    Memcached
	 * @since  12.1
	 */
	protected static $_db = null;

	/**
	 * Payload compression level
	 *
	 * @var    integer
	 * @since  12.1
	 */
	protected $_compress = 0;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   12.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->_compress = JFactory::getConfig()->get('memcached_compress', false) ? Memcached::OPT_COMPRESSION : 0;

		if (static::$_db === null)
		{
			$this->getConnection();
		}
	}

	/**
	 * Create the Memcached connection
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	protected function getConnection()
	{
		if (!static::isSupported())
		{
			throw new RuntimeException('Memcached Extension is not available');
		}

		$config = JFactory::getConfig();

		$host = $config->get('memcached_server_host', 'localhost');
		$port = $config->get('memcached_server_port', 11211);


		// Create the memcached connection
		if ($config->get('memcached_persist', true))
		{
			static::$_db = new Memcached($this->_hash);
			$servers = static::$_db->getServerList();

			if ($servers && ($servers[0]['host'] != $host || $servers[0]['port'] != $port))
			{
				static::$_db->resetServerList();
				$servers = array();
			}

			if (!$servers)
			{
				static::$_db->addServer($host, $port);
			}
		}
		else
		{
			static::$_db = new Memcached;
			static::$_db->addServer($host, $port);
		}

		static::$_db->setOption(Memcached::OPT_COMPRESSION, $this->_compress);

		$stats  = static::$_db->getStats();
		$result = !empty($stats["$host:$port"]) && $stats["$host:$port"]['pid'] > 0;

		if (!$result)
		{
			// Null out the connection to inform the constructor it will need to attempt to connect if this class is instantiated again
			static::$_db = null;

			throw new JCacheExceptionConnecting('Could not connect to memcached server');
		}
	}

	/**
	 * Get a cache_id string from an id/group pair
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  string   The cache_id string
	 *
	 * @since   11.1
	 */
	protected function _getCacheId($id, $group)
	{
		$prefix   = JCache::getPlatformPrefix();
		$length   = strlen($prefix);
		$cache_id = parent::_getCacheId($id, $group);

		if ($length)
		{
			// Memcached use suffix instead of prefix
			$cache_id = substr($cache_id, $length) . strrev($prefix);
		}

		return $cache_id;
	}

	/**
	 * Get a cache_id string from an id/group pair
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   12.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		$cache_id = $this->_getCacheId($id, $group);
		$back = self::$_db->get($cache_id);

		return $back;
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   12.1
	 */
	public function getAll()
	{
		$keys = self::$_db->getAllKeys();
		$secret = $this->_hash;

		$data = array();

		foreach ($keys as $key)
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

				$content = self::$_db->get($key);

				$size = \Joomla\String\StringHelper::strlen($content);

				$item->updateSize($size / 1024);

				$data[$group] = $item;
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
	 * @since   12.1
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);

		// Prevent double writes, write only if it doesn't exist else replace
		if (!self::$_db->replace($cache_id, $data, $this->_lifetime))
		{
			self::$_db->set($cache_id, $data, $this->_lifetime);
		}

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
	 * @since   12.1
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);

		return self::$_db->delete($cache_id);
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
	 * @since   12.1
	 */
	public function clean($group, $mode = null)
	{
		$keys = static::$_db->getAllKeys();

		$secret = $this->_hash;

		foreach ($keys as $key)
		{
			if (strpos($key, $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				static::$_db->delete($key, 0);
			}
		}

		return true;
	}

	/**
	 * Flush all existing items in storage.
	 *
	 * @return  boolean
	 *
	 * @since   3.6.3
	 */
	public function flush()
	{
		return static::$_db->flush();
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		/*
		 * GAE and HHVM have both had instances where Memcached the class was defined but no extension was loaded.
		 * If the class is there, we can assume support.
		 */
		return class_exists('Memcached');
	}
}
