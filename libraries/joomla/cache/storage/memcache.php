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

		$this->_compress = JFactory::getConfig()->get('memcache_compress', false) ? MEMCACHE_COMPRESSED : 0;

		if (static::$_db === null)
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
		if (!static::isSupported())
		{
			throw new RuntimeException('Memcache Extension is not available');
		}

		$config = JFactory::getConfig();

		$host = $config->get('memcache_server_host', 'localhost');
		$port = $config->get('memcache_server_port', 11211);

		// Create the memcache connection
		static::$_db = new Memcache;

		if ($config->get('memcache_persist', true))
		{
			$result = @static::$_db->pconnect($host, $port);
		}
		else
		{
			$result = @static::$_db->connect($host, $port);
		}

		if (!$result)
		{
			static::$_db = null;

			throw new RuntimeException('Could not connect to memcache server');
		}

		$options = array('text_file' => 'memcache.php');
		JLog::addLogger($options, JLog::ALL, array('memcache'));
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
			// Memcache use suffix instead of prefix
			$cache_id = substr($cache_id, $length) . strrev($prefix);
		}

		return $cache_id;
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
		$data  = array();
		$index = static::$_db->get($this->_hash . '-index');

		if (is_array($index))
		{
			foreach ($index as $group_key)
			{
				// From $group_key=com_contentG extract $group=com_content
				if ($group_key[0] === '_' || ctype_digit(substr($group_key, -1, 1))) {
					$group = $group_key;
				}
				else
				{
					$group = substr($group_key, 0, -1);
				}

				// Keys like [hash => size, ...]
				$index2key = $this->_hash . '-' . $group_key . '-index';
				$index2    = static::$_db->get($index2key);

				if ($index2)
				{
					foreach ($index2 as $size)
					{
						if (!isset($data[$group]))
						{
							$item = new JCacheStorageHelper($group);
						}
						else
						{
							$item = $data[$group];
						}

						$item->updateSize($size / 1024);

						$data[$group] = $item;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Get group key for cache id
	 *
	 * @param   string  $group         The cache data group
	 * @param   string  $cache_id_sfx  The cache id suffix

	 * @return  string  Group key for given group and cache id suffix
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getGroupKey($group, $cache_id_sfx)
	{
		$key = $group;

		// If the group does not begin with '_' and does not end with number
		// then add suffix A or B or ... or P.
		// For groups like _system or com_custom_part1 do not add suffix
		if ($group[0] !== '_' && !ctype_digit(substr($group, -1, 1)))
		{
			// The cache id suffix looks like:
			// com_content-329adb1b3633424818ec393fb9780000 and then key will be com_contentD
			// Total can be 16 different keys per one group
			$key .= chr(hexdec($cache_id_sfx[strlen($group) + 1]) + 65);
		}

		return $key;
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
		$group    = (string) $group;
		$cache_id = $this->_getCacheId($id, $group);

		// Cache id suffix contains group and 33 chars plus plafform suffix
		$cache_id_sfx = substr($cache_id, strlen($this->_hash . '-cache-'));

		// By group key we can use different indexes
		$group_key = static::getGroupKey($group, $cache_id_sfx);

		// Start time
		$mtime = microtime(true);

		if (!$this->lockindex($group_key))
		{
			$waited = sprintf("%.6f", microtime(true) - $mtime);
			JLog::add("Lock index2 failed for $group_key after $waited", JLog::ERROR, 'memcache');
			return false;
		}

		$index2key = $this->_hash . '-' . $group_key . '-index';
		$index2    = static::$_db->get($index2key);

		if (!is_array($index2))
		{
			if (!$this->lockindex())
			{
				JLog::add("Lock index failed before adding $group_key", JLog::ERROR, 'memcache');
				$this->unlockindex($group_key);
				return false;
			}

			$index = static::$_db->get($this->_hash . '-index');

			if (!is_array($index))
			{
				$index = array();
			}

			// Checking for race condition
			if (!in_array($group_key, $index, true))
			{
				$index[] = $group_key;
				static::$_db->set($this->_hash . '-index', $index, 0, 0);
			}

			$this->unlockindex();

			// Initialize secondary index
			$index2 = array();
		}

		$size = $index2[$cache_id_sfx] = strlen($data);

		if (!static::$_db->set($index2key, $index2, 0, 0))
		{
			JLog::add("Saving index2 failed for $group_key", JLog::WARNING, 'memcache');
		}

		$this->unlockindex($group_key);

		// We do not have to use replace in single memcache server
		if (!static::$_db->set($cache_id, $data, $this->_compress, $this->_lifetime))
		{
			$msg = "Saving data failed for $group_key (size: %d, compression: %s)";
			JLog::add(sprintf($msg, $size, $this->_compress), JLog::ERROR, 'memcache');
			return false;
		}

		// Debug information about performance
		$lasttime = microtime(true) - $mtime;
		$maxtime  = (float) static::$_db->get($this->_hash . '-maxtime');

		if ($lasttime > $maxtime)
		{
			static::$_db->set($this->_hash . '-maxtime', $lasttime, 0, 86400);
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
		$group    = (string) $group;
		$cache_id = $this->_getCacheId($id, $group);

		// Cache id suffix contains group and 33 chars plus plafform suffix
		$cache_id_sfx = substr($cache_id, strlen($this->_hash . '-cache-'));

		// Group_key is a group with possibly added one uppercase letter
		$group_key = static::getGroupKey($group, $cache_id_sfx);

		if (!$this->lockindex($group_key))
		{
			JLog::add("Lock index2 failed for removing $group_key", JLog::ERROR, 'memcache');
			return false;
		}

		$index2key = $this->_hash . '-' . $group_key . '-index';
		$index2    = static::$_db->get($index2key);

		if (isset($index2[$cache_id_sfx]))
		{
			unset($index2[$cache_id_sfx]);
			static::$_db->set($index2key, $index2, 0, 0);
		}

		$this->unlockindex($group_key);

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
		$index  = static::$_db->get($this->_hash . '-index');

		if (is_array($index))
		{
			$length = strlen($group);

			foreach ($index as $group_key)
			{
				if ($mode === 'group' xor !strncmp($group_key, $group, $length))
				{
					continue;
				}

				if (!$this->lockindex($group_key))
				{
					JLog::add("Lock index2 failed on cleaning $group_key", JLog::WARNING, 'memcache');
					continue;
				}

				$index2key = $this->_hash . '-' . $group_key . '-index';
				$index2    = static::$_db->get($index2key);

				if (is_array($index2))
				{
					foreach ($index2 as $cache_id_sfx => $size)
					{
						$cache_id = $this->_hash . '-cache-' . $cache_id_sfx;
						static::$_db->delete($cache_id);
						unset($index2[$cache_id_sfx]);
					}

					static::$_db->set($index2key, $index2, 0, 0);
				}

				$this->unlockindex($group_key);
			}
		}

		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc()
	{
		$index = static::$_db->get($this->_hash . '-index');

		if (is_array($index))
		{
			// Stats data
			$total_index2    = 0;
			$total_expired   = 0;
			$max_index2_size = array('group_key', 0);

			sort($index, SORT_REGULAR);

			foreach ($index as $group_key)
			{
				if (!$this->lockindex($group_key))
				{
					JLog::add("Lock index2 failed in gc for $group_key", JLog::WARNING, 'memcache');
					continue;
				}

				$index2key = $this->_hash . '-' . $group_key . '-index';
				$index2    = static::$_db->get($index2key);

				if (is_array($index2))
				{
					$size = count($index2);

					if ($size > $max_index2_size[1])
					{
						$max_index2_size = array($group_key, $size);
					}

					foreach ($index2 as $cache_id_sfx => $size)
					{
						$cache_id = $this->_hash . '-cache-' . $cache_id_sfx;

						if (static::$_db->add($cache_id, '', 0, 1))
						{
							// We added a new value so that means the cache was expired
							static::$_db->delete($cache_id);
							unset($index2[$cache_id_sfx]);
							$total_expired++;
						}
					}

					static::$_db->set($index2key, $index2, 0, 0);
					$total_index2++;
				}

				$this->unlockindex($group_key);
			}

			// Get max data saving time
			$max_saving_time = static::$_db->get($this->_hash . '-maxtime');

			// Save useful stats
			$msg1 = "Total index2 arrays: %3d, max full index2 was %s: %d";
			$msg1 = sprintf($msg1, $total_index2, $max_index2_size[0], $max_index2_size[1]);
			$msg2 = "Total expired: %3d, max saving time in last 24h: %.6f";
			$msg2 = sprintf($msg2, $total_expired, $max_saving_time);

			JLog::add($msg1, JLog::DEBUG, 'memcache');
			JLog::add($msg2, JLog::DEBUG, 'memcache');
		}

		return true;
	}

	/**
	 * Flush all existing items in storage.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function flush()
	{
		if (!$this->lockindex())
		{
			return false;
		}

		// TODO: lock each index2

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
		return extension_loaded('memcache') && class_exists('Memcache');
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
		$returning = new stdClass;
		$returning->locklooped = false;

		$looptime = $locktime * 10;

		$cache_id = $this->_getCacheId($id, $group);

		$data_lock = static::$_db->add($cache_id . '_lock', 1, 0, $locktime);

		if ($data_lock === false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released.
			// That implies that data get from other thread has finished.
			while ($data_lock === false)
			{
				if ($lock_counter > $looptime)
				{
					break;
				}

				usleep(200);
				$data_lock = static::$_db->add($cache_id . '_lock', 1, 0, $locktime);
				$lock_counter++;
			}

			$returning->locklooped = true;
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
		return static::$_db->delete($cache_id);
	}

	/**
	 * Lock cache index or index2
	 *
	 * @param   string|null  $group_key  This is part of the index2 key or null
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	protected function lockindex($group_key = null)
	{
		$looptime   = 150;
		$group_key  = $group_key !== null ? "-$group_key" : '';
		$index_lock = $this->_hash . $group_key . '-index_lock';
		$data_lock  = static::$_db->add($index_lock, 1, 0, 30);

		if ($data_lock === false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released.  that implies that data get from other thread has finished
			while ($data_lock === false)
			{
				if ($lock_counter > $looptime)
				{
					return false;
				}

				usleep(200);
				$data_lock = static::$_db->add($index_lock, 1, 0, 30);
				$lock_counter++;
			}
		}

		return true;
	}

	/**
	 * Unlock cache index or index2
	 *
	 * @param   string|null  $group_key  This is part of the index2 key or null
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	protected function unlockindex($group_key = null)
	{
		$group_key  = $group_key !== null ? "-$group_key" : '';
		$index_lock = $this->_hash . $group_key . '-index_lock';
		return static::$_db->delete($index_lock);
	}
}
