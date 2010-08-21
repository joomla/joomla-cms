<?php
/**
 * @version		$Id:storage.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Public cache handler
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.6
 */
class JCacheController
{
	/**
	 * @since	1.6
	 */
	public $cache;

	/**
	 * @since	1.6
	 */
	public $options;

	/**
	 * Constructor
	 *
	 * @param	array	$options	options
	 * @since	1.6
	 */
	public function __construct($options)
	{
		$this->cache 	= new JCache($options);
		$this->options 	= $this->cache->_options;

		// Overwrite default options with given options
		foreach ($options AS $option=>$value) {
			if (isset($options[$option])) {
				$this->options[$option] = $options[$option];
			}
		}
	}

	/**
	 * @since	1.6
	 */
	public function __call ($name, $arguments)
	{
		$nazaj = call_user_func_array (array ($this->cache, $name), $arguments);
		return $nazaj;
	}

	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param	string	$type	The cache object type to instantiate
	 * @return	object	A JCache object
	 * @since	1.6
	 */
	public static function getInstance($type = 'output', $options = array())
	{
		JCacheController::addIncludePath(JPATH_LIBRARIES.'/joomla/cache/controller');

		$type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));

		$class = 'JCacheController'.ucfirst($type);

		if (!class_exists($class)) {
			// Search for the class file in the JCache include paths.
			jimport('joomla.filesystem.path');

			if ($path = JPath::find(JCacheController::addIncludePath(), strtolower($type).'.php')) {
				require_once $path;
			} else {
				JError::raiseError(500, 'Unable to load Cache Controller: '.$type);
			}
		}

		return new $class($options);
	}

	/**
	 * Set caching enabled state
	 *
	 * @param	boolean	$enabled	True to enable caching
	 * @return	void
	 * @since	1.6
	 */
	public function setCaching($enabled)
	{
		$this->options['caching'] = (bool) $enabled;
		$this->cache->setCaching($enabled);
	}

	/**
	 * Set cache lifetime
	 *
	 * @param	int		$lt	Cache lifetime
	 * @return	void
	 * @since	1.6
	 */
	public function setLifeTime($lt)
	{
		$this->cache->setLifeTime($lt);
	}

	/**
	 * Add a directory where JCache should search for controllers. You may
	 * either pass a string or an array of directories.
	 *
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since	1.6
	 */
	public static function addIncludePath($path='')
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array();
		}
		if (!empty($path) && !in_array($path, $paths)) {
			jimport('joomla.filesystem.path');
			array_unshift($paths, JPath::clean($path));
		}
		return $paths;
	}

	/**
	 * Get stored cached data by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	mixed	False on no result, cached object otherwise
	 * @since	1.6
	 */
	public function get($id, $group=null)
	{
		$data = false;
		$data = $this->cache->get($id, $group);

		if ($data === false) {
			$locktest = new stdClass;
			$locktest->locked = null;
			$locktest->locklooped = null;
			$locktest = $this->cache->lock($id, $group);
			if ($locktest->locked == true && $locktest->locklooped == true) {
				$data = $this->cache->get($id, $group);
			}
			if ($locktest->locked == true) $this->cache->unlock($id, $group);
		}

		// check again, we might got it from second attempt
		if ($data !== false) {
			$data = unserialize(trim($data));  // trim to fix unserialize errors
		}
		return $data;
	}

	/**
	 * Store data to cache by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	mixed	$data	The data to store
	 * @return	boolean	True if cache was stored
	 * @since	1.6
	 */
	public function store($data, $id, $group=null)
	{
		$locktest = new stdClass;
		$locktest->locked = null;
		$locktest->locklooped = null;

		$locktest = $this->cache->lock($id, $group);

		if ($locktest->locked == false && $locktest->locklooped == true) {
			$locktest = $this->cache->lock($id, $group);
		}

		$sucess = $this->cache->store(serialize($data), $id,  $group);

		if ($locktest->locked == true) $this->cache->unlock($id, $group);

		return $sucess;
	}
}