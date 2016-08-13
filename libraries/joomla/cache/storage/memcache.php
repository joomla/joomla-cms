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
 * Memcache cache storage handler
 *
 * @see    https://secure.php.net/manual/en/book.memcache.php
 * @since  11.1
 */
class JCacheStorageMemcache extends JCacheStorage
{
	/**
	 * Memcache connection object
	 *
	 * @var    Memcache
	 * @since  11.1
	 */
	protected static $_db = null;

	/**
	 * Persistent session flag
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_persistent = false;

	/**
	 * Payload compression level
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $_compress = 0;

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

		if (static::isSupported() && static::$_db === null)
		{
			$this->getConnection();
		}
	}

	/**
	 * Create the Memcache connection
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	protected function getConnection()
	{
		$config            = JFactory::getConfig();
		$this->_persistent = $config->get('memcache_persist', true);
		$this->_compress   = $config->get('memcache_compress', false) == false ? 0 : MEMCACHE_COMPRESSED;

		// Create the memcache connection
		static::$_db = new Memcache;
		static::$_db->addserver($config->get('memcache_server_host', 'localhost'), $config->get('memcache_server_port', 11211), $this->_persistent);

		$memcachetest = @static::$_db->connect($server['host'], $server['port']);

		if ($memcachetest == false)
		{
			throw new RuntimeException('Could not connect to memcache server', 404);
		}

		// Memcahed has no list keys, we do our own accounting, initialise key index
		if (static::$_db->get($this->_hash . '-index') === false)
		{
			static::$_db->set($this->_hash . '-index', array(), $this->_compress, 0);
		}

		return;
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
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		return static::$_db->get($this->_getCacheId($id, $group));
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		$keys   = static::$_db->get($this->_hash . '-index');
		$secret = $this->_hash;

		$data = array();

		if (!empty($keys))
		{
			foreach ($keys as $key)
			{
				if (empty($key))
				{
					continue;
				}

				$namearr = explode('-', $key->name);

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

					$item->updateSize($key->size / 1024);

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
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);

		if (!$this->lockindex())
		{
			return false;
		}

		$index = static::$_db->get($this->_hash . '-index');

		if ($index === false)
		{
			$index = array();
		}

		$tmparr       = new stdClass;
		$tmparr->name = $cache_id;
		$tmparr->size = strlen($data);

		$index[] = $tmparr;
		static::$_db->replace($this->_hash . '-index', $index, 0, 0);
		$this->unlockindex();

		// Prevent double writes, write only if it doesn't exist else replace
		if (!static::$_db->replace($cache_id, $data, $this->_compress, $this->_lifetime))
		{
			static::$_db->set($cache_id, $data, $this->_compress, $this->_lifetime);
		}

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
	 * @since   11.1
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);

		if (!$this->lockindex())
		{
			return false;
		}

		$index = static::$_db->get($this->_hash . '-index');

		if ($index === false)
		{
			$index = array();
		}

		foreach ($index as $key => $value)
		{
			if ($value->name == $cache_id)
			{
				unset($index[$key]);
			}

			break;
		}

		static::$_db->replace($this->_hash . '-index', $index, 0, 0);
		$this->unlockindex();

		return static::$_db->delete($cache_id);
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
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		if (!$this->lockindex())
		{
			return false;
		}

		$index = static::$_db->get($this->_hash . '-index');

		if ($index === false)
		{
			$index = array();
		}

		$secret = $this->_hash;

		foreach ($index as $key => $value)
		{
			if (strpos($value->name, $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				static::$_db->delete($value->name, 0);
				unset($index[$key]);
			}
		}

		static::$_db->replace($this->_hash . '-index', $index, 0, 0);
		$this->unlockindex();

		return true;
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
		// First check if the PHP requirements are met
		$supported = extension_loaded('memcache') && class_exists('Memcache');

		if (!$supported)
		{
			return false;
		}

		// Now check if we can connect to the specified Memcache server
		$config = JFactory::getConfig();

		$memcache = new Memcache;
		return @$memcache->connect($config->get('memcache_server_host', 'localhost'), $config->get('memcache_server_port', 11211));
	}

	/**
	 * Lock cached item
	 *
	 * @param   string   $id        The cache data ID
	 * @param   string   $group     The cache data group
	 * @param   integer  $locktime  Cached item max lock time
	 *
	 * @return  mixed  Boolean false if locking failed or an object containing properties lock and locklooped
	 *
	 * @since   11.1
	 */
	public function lock($id, $group, $locktime)
	{
		$returning             = new stdClass;
		$returning->locklooped = false;

		$looptime = $locktime * 10;

		$cache_id = $this->_getCacheId($id, $group);

		if (!$this->lockindex())
		{
			return false;
		}

		$index = static::$_db->get($this->_hash . '-index');

		if ($index === false)
		{
			$index = array();
		}

		$tmparr       = new stdClass;
		$tmparr->name = $cache_id;
		$tmparr->size = 1;

		$index[] = $tmparr;
		static::$_db->replace($this->_hash . '-index', $index, 0, 0);
		$this->unlockindex();

		$data_lock = static::$_db->add($cache_id . '_lock', 1, false, $locktime);

		if ($data_lock === false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released. That implies that data get from other thread has finished
			while ($data_lock === false)
			{
				if ($lock_counter > $looptime)
				{
					$returning->locked = false;
					$returning->locklooped = true;
					break;
				}

				usleep(100);
				$data_lock = static::$_db->add($cache_id . '_lock', 1, false, $locktime);
				$lock_counter++;
			}
		}

		$returning->locked = $data_lock;

		return $returning;
	}

	/**
	 * Unlock cached item
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function unlock($id, $group = null)
	{
		$cache_id = $this->_getCacheId($id, $group) . '_lock';

		if (!$this->lockindex())
		{
			return false;
		}

		$index = static::$_db->get($this->_hash . '-index');

		if ($index === false)
		{
			$index = array();
		}

		foreach ($index as $key => $value)
		{
			if ($value->name == $cache_id)
			{
				unset($index[$key]);
			}

			break;
		}

		static::$_db->replace($this->_hash . '-index', $index, 0, 0);
		$this->unlockindex();

		return static::$_db->delete($cache_id);
	}

	/**
	 * Lock cache index
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	protected function lockindex()
	{
		$looptime  = 300;
		$data_lock = static::$_db->add($this->_hash . '-index_lock', 1, false, 30);

		if ($data_lock === false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released.  that implies that data get from other thread has finished
			while ($data_lock === false)
			{
				if ($lock_counter > $looptime)
				{
					return false;
					break;
				}

				usleep(100);
				$data_lock = static::$_db->add($this->_hash . '-index_lock', 1, false, 30);
				$lock_counter++;
			}
		}

		return true;
	}

	/**
	 * Unlock cache index
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	protected function unlockindex()
	{
		return static::$_db->delete($this->_hash . '-index_lock');
	}
}
