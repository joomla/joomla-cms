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
 * Abstract cache storage handler
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorage 
{
	/**
	 * @since	1.6
	 */
	protected $rawname;

	/**
	 * @since	1.6
	 */
	public $_now;

	/**
	 * @since	1.6
	 */
	public $_lifetime;

	/**
	 * @since	1.6
	 */
	public $_locking;

	/**
	 * @since	1.6
	 */
	public $_language;

	/**
	 * @since	1.6
	 */
	public $_application;

	/**
	 * @since	1.6
	 */
	public $_hash;

	/**
	 * Constructor
	 *
	 * @param	array	$options optional parameters
	 * @since	1.5
	 */
	public function __construct($options = array())
	{
		$config				= JFactory::getConfig();
		$this->_hash		= md5($config->get('secret'));
		$this->_application	= (isset($options['application'])) ? $options['application'] : null;
		$this->_language	= (isset($options['language'])) ? $options['language'] : 'en-GB';
		$this->_locking		= (isset($options['locking'])) ? $options['locking'] : true;
		$this->_lifetime	= (isset($options['lifetime'])) ? $options['lifetime']*60 : $config->get('cachetime')*60;
		$this->_now			= (isset($options['now'])) ? $options['now'] : time();

		// Set time threshold value.  If the lifetime is not set, default to 60 (0 is BAD)
		// _threshold is now available ONLY as a legacy (it's deprecated).  It's no longer used in the core.
		if (empty($this->_lifetime)) {
			$this->_threshold = $this->_now - 60;
			$this->_lifetime = 60;
		} else {
			$this->_threshold = $this->_now - $this->_lifetime;
		}

	}

	/**
	 * Returns a cache storage handler object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @param	string	$handler	The cache storage handler to instantiate
	 * @return	object	A JCacheStorageHandler object
	 * @since	1.5
	 */
	public static function getInstance($handler=null, $options = array())
	{
		static $now = null;

		JCacheStorage::addIncludePath(JPATH_LIBRARIES.DS.'joomla'.DS.'cache'.DS.'storage');

		if (!isset($handler)) {
			$conf = JFactory::getConfig();
			$handler = $conf->get('cache_handler');
			if (empty($handler)) {
				return JError::raiseWarning(500, JText::_('JLIB_CACHE_ERROR_CACHE_HANDLER_NOT_SET'));
			}
		}

		if (is_null($now)) {
			$now = time();
		}

		$options['now'] = $now;
		//We can't cache this since options may change...
		$handler = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $handler));

		$class = 'JCacheStorage'.ucfirst($handler);
		if (!class_exists($class)) {
			// Search for the class file in the JCacheStorage include paths.
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JCacheStorage::addIncludePath(), strtolower($handler).'.php')) {
				require_once $path;
			} else {
				return JError::raiseWarning(500, JText::sprintf('JLIB_CACHE_ERROR_CACHE_STORAGE_LOAD', $handler));
			}
		}

		return new $class($options);
	}

	/**
	 * Get cached data by id and group
	 *
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data object
	 * @since	1.5
	 */
	public function get($id, $group, $checkTime)
	{
		return false;
	}

	/**
	 * Get all cached data
	 *
	 * @return	mixed	Boolean false on failure or a cached data object
	 * @since	1.6
	 */
	public function getAll()
	{
		if (!class_exists('JCacheStorageHelper', false)) {
			require_once JPATH_LIBRARIES.DS.'joomla'.DS.'cache'.DS.'storage'.DS.'helpers'.DS.'helper.php';
		}
		return;
	}

	/**
	 * Store the data to cache by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function store($id, $group, $data)
	{
		return true;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function remove($id, $group)
	{
		return true;
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function clean($group, $mode)
	{
		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public function gc()
	{
		return true;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return true;
	}

	/**
	 * Lock cached item
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	integer	$locktime Cached item max lock time
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.6
	 */
	public function lock($id,$group,$locktime)
	{
		return false;
	}

	/**
	 * Unlock cached item
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.6
	 */
	public function unlock($id,$group)
	{
		return false;
	}

	/**
	 * Get a cache_id string from an id/group pair
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	string	The cache_id string
	 * @since	1.6
	 */
	protected function _getCacheId($id, $group)
	{
		$name	= md5($this->_application.'-'.$id.'-'.$this->_language);
		$this->rawname = $this->_hash.'-'.$name;
		return $this->_hash.'-cache-'.$group.'-'.$name;
	}

	/**
	 * Add a directory where JCacheStorage should search for handlers. You may
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
}
