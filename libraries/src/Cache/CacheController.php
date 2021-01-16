<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

defined('JPATH_PLATFORM') or die;

/**
 * Public cache handler
 *
 * @since  1.7.0
 * @note   As of 4.0 this class will be abstract
 */
class CacheController
{
	/**
	 * Cache object
	 *
	 * @var    Cache
	 * @since  1.7.0
	 */
	public $cache;

	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  1.7.0
	 */
	public $options;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.7.0
	 */
	public function __construct($options)
	{
		$this->cache = new Cache($options);
		$this->options = & $this->cache->_options;

		// Overwrite default options with given options
		foreach ($options as $option => $value)
		{
			if (isset($options[$option]))
			{
				$this->options[$option] = $options[$option];
			}
		}
	}

	/**
	 * Magic method to proxy CacheController method calls to Cache
	 *
	 * @param   string  $name       Name of the function
	 * @param   array   $arguments  Array of arguments for the function
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->cache, $name), $arguments);
	}

	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param   string  $type     The cache object type to instantiate; default is output.
	 * @param   array   $options  Array of options
	 *
	 * @return  CacheController
	 *
	 * @since   1.7.0
	 * @throws  \RuntimeException
	 */
	public static function getInstance($type = 'output', $options = array())
	{
		self::addIncludePath(__DIR__ . '/Controller');

		$type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));

		$class = __NAMESPACE__ . '\\Controller\\' . ucfirst($type) . 'Controller';

		if (!class_exists($class))
		{
			$class = 'JCacheController' . ucfirst($type);
		}

		if (!class_exists($class))
		{
			// Search for the class file in the Cache include paths.
			\JLoader::import('joomla.filesystem.path');

			$path = \JPath::find(self::addIncludePath(), strtolower($type) . '.php');

			if ($path !== false)
			{
				\JLoader::register($class, $path);
			}

			// The class should now be loaded
			if (!class_exists($class))
			{
				throw new \RuntimeException('Unable to load Cache Controller: ' . $type, 500);
			}
		}

		return new $class($options);
	}

	/**
	 * Add a directory where Cache should search for controllers. You may either pass a string or an array of directories.
	 *
	 * @param   array|string  $path  A path to search.
	 *
	 * @return  array  An array with directory elements
	 *
	 * @since   1.7.0
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
			\JLoader::import('joomla.filesystem.path');
			array_unshift($paths, \JPath::clean($path));
		}

		return $paths;
	}

	/**
	 * Get stored cached data by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed  Boolean false on no result, cached object otherwise
	 *
	 * @since   1.7.0
	 * @deprecated  4.0  Implement own method in subclass
	 */
	public function get($id, $group = null)
	{
		$data = $this->cache->get($id, $group);

		if ($data === false)
		{
			$locktest = $this->cache->lock($id, $group);

			// If locklooped is true try to get the cached data again; it could exist now.
			if ($locktest->locked === true && $locktest->locklooped === true)
			{
				$data = $this->cache->get($id, $group);
			}

			if ($locktest->locked === true)
			{
				$this->cache->unlock($id, $group);
			}
		}

		// Check again because we might get it from second attempt
		if ($data !== false)
		{
			// Trim to fix unserialize errors
			$data = unserialize(trim($data));
		}

		return $data;
	}

	/**
	 * Store data to cache by ID and group
	 *
	 * @param   mixed    $data        The data to store
	 * @param   string   $id          The cache data ID
	 * @param   string   $group       The cache data group
	 * @param   boolean  $wrkarounds  True to use wrkarounds
	 *
	 * @return  boolean  True if cache stored
	 *
	 * @since   1.7.0
	 * @deprecated  4.0  Implement own method in subclass
	 */
	public function store($data, $id, $group = null, $wrkarounds = true)
	{
		$locktest = $this->cache->lock($id, $group);

		if ($locktest->locked === false && $locktest->locklooped === true)
		{
			// We can not store data because another process is in the middle of saving
			return false;
		}

		$result = $this->cache->store(serialize($data), $id, $group);

		if ($locktest->locked === true)
		{
			$this->cache->unlock($id, $group);
		}

		return $result;
	}
}
