<?php
/**
 * @version		$Id: memcache.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Memcache cache storage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorageMemcache extends JCacheStorage
{
	/**
	 * Resource for the current memcached connection.
	 * @var resource
	 */
	var $_db;

	/**
	 * Use compression?
	 * @var int
	 */
	var $_compress = null;

	/**
	 * Use persistent connections
	 * @var boolean
	 */
	var $_persistent = false;

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param array $options optional parameters
	 */
	function __construct($options = array())
	{
		if (!$this->test()) {
			return JError::raiseError(404, "The memcache extension is not available");
		}
		parent::__construct($options);

		$params = &JCacheStorageMemcache::getConfig();
		$this->_compress	= (isset($params['compression'])) ? $params['compression'] : 0;
		$this->_db = &JCacheStorageMemcache::getConnection();

		// Get the site hash
		$this->_hash = $params['hash'];
	}

	/**
	 * return memcache connection object
	 *
	 * @static
	 * @access private
	 * @return object memcache connection object
	 */
	function &getConnection() {
		static $db = null;
		if (is_null($db)) {
			$params = &JCacheStorageMemcache::getConfig();
			$persistent	= (isset($params['persistent'])) ? $params['persistent'] : false;
			// This will be an array of loveliness
			$servers	= (isset($params['servers'])) ? $params['servers'] : array();

			// Create the memcache connection
			$db = new Memcache;
			foreach($servers AS $server) {
				$db->addServer($server['host'], $server['port'], $persistent);
			}
		}
		return $db;
	}

	/**
	 * Return memcache related configuration
	 *
	 * @static
	 * @access private
	 * @return array options
	 */
	function &getConfig() {
		static $params = null;
		if (is_null($params)) {
			$config = &JFactory::getConfig();
			$params = $config->getValue('config.memcache_settings');
			if (!is_array($params)) {
				$params = unserialize(stripslashes($params));
			}

			if (!$params) {
				$params = array();
			}
			$params['hash'] = $config->getValue('config.secret');
		}
		return $params;
	}

	/**
	 * Get cached data from memcache by id and group
	 *
	 * @access	public
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	function get($id, $group, $checkTime)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return $this->_db->get($cache_id);
	}

	/**
	 * Store the data to memcache by id and group
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return $this->_db->set($cache_id, $data, $this->_compress, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return $this->_db->delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @access	public
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function clean($group, $mode)
	{
		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function gc()
	{
		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function test()
	{
		return (extension_loaded('memcache') && class_exists('Memcache'));
	}

	/**
	 * Get a cache_id string from an id/group pair
	 *
	 * @access	private
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	string	The cache_id string
	 * @since	1.5
	 */
	function _getCacheId($id, $group)
	{
		$name	= md5($this->_application.'-'.$id.'-'.$this->_hash.'-'.$this->_language);
		return 'cache_'.$group.'-'.$name;
	}
}
