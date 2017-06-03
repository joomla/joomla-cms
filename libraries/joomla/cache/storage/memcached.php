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
	 * Persistent session flag
	 *
	 * @var    boolean
	 * @since  12.1
	 */
	protected $_persistent = false;

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

		if (self::$_db === null)
		{
			$this->getConnection();
		}
	}

	/**
	 * Return memcached connection object
	 *
	 * @return  object   memcached connection object
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	protected function getConnection()
	{
		if ((extension_loaded('memcached') && class_exists('Memcached')) != true)
		{
			return false;
		}

		$config = JFactory::getConfig();
		$this->_persistent = $config->get('memcached_persist', true);
		$this->_compress = $config->get('memcached_compress', false) == false ? 0 : Memcached::OPT_COMPRESSION;

		/*
		 * This will be an array of loveliness
		 * @todo: multiple servers
		 * $servers = (isset($params['servers'])) ? $params['servers'] : array();
		 */
		$server = array();
		$server['host'] = $config->get('memcached_server_host', 'localhost');
		$server['port'] = $config->get('memcached_server_port', 11211);

		// Create the memcache connection
		if ($this->_persistent)
		{
			$session = JFactory::getSession();
			self::$_db = new Memcached($session->getId());
		}
		else
		{
			self::$_db = new Memcached;
		}

		$memcachedtest = self::$_db->addServer($server['host'], $server['port']);

		if ($memcachedtest == false)
		{
			throw new RuntimeException('Could not connect to memcached server', 404);
		}

		self::$_db->setOption(Memcached::OPT_COMPRESSION, $this->_compress);

		return;
	}

	/**
	 * Get cached data from memcached by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data string
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
	 * @return  array    data
	 *
	 * @since   12.1
	 */
	public function getAll()
	{
		parent::getAll();

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
	 * Store the data to memcached by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
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
		$keys = self::$_db->getAllKeys();

		$secret = $this->_hash;

		foreach ($keys as $key)
		{
			if (strpos($key, $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				self::$_db->delete($key, 0);
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
		if ((extension_loaded('memcached') && class_exists('Memcached')) != true)
		{
			return false;
		}

		$config = JFactory::getConfig();
		$host = $config->get('memcached_server_host', 'localhost');
		$port = $config->get('memcached_server_port', 11211);

		$memcached = new Memcached;
		$memcachedtest = @$memcached->addServer($host, $port);

		if (!$memcachedtest)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
