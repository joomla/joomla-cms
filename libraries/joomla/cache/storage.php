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
 * Abstract cache storage handler
 *
 * @since  11.1
 * @note   As of 4.0 this class will be abstract
 */
class JCacheStorage
{
	/**
	 * The raw object name
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $rawname;

	/**
	 * Time that the cache storage handler was instantiated
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $_now;

	/**
	 * Cache lifetime
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $_lifetime;

	/**
	 * Flag if locking is enabled
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	public $_locking;

	/**
	 * Language code
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_language;

	/**
	 * Application name
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_application;

	/**
	 * Object hash
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_hash;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		$config = JFactory::getConfig();

		$this->_hash        = md5($config->get('secret'));
		$this->_application = (isset($options['application'])) ? $options['application'] : null;
		$this->_language    = (isset($options['language'])) ? $options['language'] : 'en-GB';
		$this->_locking     = (isset($options['locking'])) ? $options['locking'] : true;
		$this->_lifetime    = (isset($options['lifetime'])) ? $options['lifetime'] * 60 : $config->get('cachetime') * 60;
		$this->_now         = (isset($options['now'])) ? $options['now'] : time();

		// Set time threshold value.  If the lifetime is not set, default to 60 (0 is BAD)
		// _threshold is now available ONLY as a legacy (it's deprecated).  It's no longer used in the core.
		if (empty($this->_lifetime))
		{
			$this->_threshold = $this->_now - 60;
			$this->_lifetime = 60;
		}
		else
		{
			$this->_threshold = $this->_now - $this->_lifetime;
		}
	}

	/**
	 * Returns a cache storage handler object.
	 *
	 * @param   string  $handler  The cache storage handler to instantiate
	 * @param   array   $options  Array of handler options
	 *
	 * @return  JCacheStorage
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 * @throws  RuntimeException
	 */
	public static function getInstance($handler = null, $options = array())
	{
		static $now = null;

		// @deprecated  4.0  This class path is autoloaded, manual inclusion is no longer necessary
		self::addIncludePath(JPATH_PLATFORM . '/joomla/cache/storage');

		if (!isset($handler))
		{
			$handler = JFactory::getConfig()->get('cache_handler');

			if (empty($handler))
			{
				throw new UnexpectedValueException('Cache Storage Handler not set.');
			}
		}

		if (is_null($now))
		{
			$now = time();
		}

		$options['now'] = $now;

		// We can't cache this since options may change...
		$handler = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $handler));

		/** @var JCacheStorage $class */
		$class = 'JCacheStorage' . ucfirst($handler);

		if (!class_exists($class))
		{
			// Search for the class file in the JCacheStorage include paths.
			jimport('joomla.filesystem.path');

			$path = JPath::find(self::addIncludePath(), strtolower($handler) . '.php');

			if ($path === false)
			{
				throw new RuntimeException(sprintf('Unable to load Cache Storage: %s', $handler));
			}

			include_once $path;

			// The class should now be loaded
			if (!class_exists($class))
			{
				throw new RuntimeException(sprintf('Unable to load Cache Storage: %s', $handler));
			}
		}

		// Validate the cache storage is supported on this platform
		if (!$class::isSupported())
		{
			throw new RuntimeException(sprintf('The %s Cache Storage is not supported on this platform.', $handler));
		}

		return new $class($options);
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
		return false;
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
		return false;
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
		return true;
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
		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function gc()
	{
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
		return true;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 * @deprecated  12.3 (Platform) & 4.0 (CMS)
	 */
	public static function test()
	{
		JLog::add(__METHOD__ . '() is deprecated. Use JCacheStorage::isSupported() instead.', JLog::WARNING, 'deprecated');

		return static::isSupported();
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
		return false;
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
		return false;
	}

	/**
	 * Get a cache ID string from an ID/group pair
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function _getCacheId($id, $group)
	{
		$name          = md5($this->_application . '-' . $id . '-' . $this->_language);
		$this->rawname = $this->_hash . '-' . $name;

		return JCache::getPlatformPrefix() . $this->_hash . '-cache-' . $group . '-' . $name;
	}

	/**
	 * Add a directory where JCacheStorage should search for handlers. You may either pass a string or an array of directories.
	 *
	 * @param   array|string  $path  A path to search.
	 *
	 * @return  array  An array with directory elements
	 *
	 * @since   11.1
	 */
	public static function addIncludePath($path = '')
	{
		static $paths;

		if (!isset($paths))
		{
			$paths = array();
		}

		if (!empty($path) && !in_array($path, $paths))
		{
			jimport('joomla.filesystem.path');
			array_unshift($paths, JPath::clean($path));
		}

		return $paths;
	}
}
