<?php
/**
 * @version		$Id: eaccelerator.php 7074 2007-03-31 15:37:23Z jinx $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Memcache cache storage handler
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @author		Mitch Pirtle
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
	function __construct( $options = array() )
	{
		if (!$this->test()) {
            return JError::raiseError(404, "The memcache extension isn't available");
        }

		parent::__construct($options);

		$config =& JFactory::getConfig();
		$params	= unserialize(stripslashes($config->getValue('config.memcache_settings')));
		if (!$params) {
			$params = array();
		}

		$this->_compress	= (isset($params['compression'])) ? $params['compression'] : 0;
		$this->_persistent	= (isset($params['persistent'])) ? $params['persistent'] : false;

		// This will be an array of loveliness
		$this->_servers	= (isset($params['servers'])) ? $params['servers'] : array();

		// Create the memcache connection
		$this->_db = new Memcache;
		for ($i=0, $n=count($this->_servers); $i < $n; $i++)
		{
			$server = $this->_servers[$i];
			$this->_db->addServer($server['host'], $server['port'], $this->_persistent);
		}

		// Get the site hash
		$this->_hash = $config->getValue('config.secret');
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
		$this->_setExpire($cache_id);
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
		$this->_db->set($cache_id.'_expire', time(), 0, 0);
		return $this->_db->set($cache_id, $data, $this->_compress, 0);
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
		$this->_db->delete($cache_id.'_expire');
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
	 * Set expire time on each call since memcache sets it on cache creation.
	 *
	 * @access private
	 *
	 * @param string  $key   Cache key to expire.
	 * @param integer $lifetime  Lifetime of the data in seconds.
	 */
	function _setExpire($key)
	{
		$lifetime	= $this->_lifetime;
		$expire		= $this->_db->get($key.'_expire');

		// set prune period
		if ($expire + $lifetime < time()) {
			$this->_db->delete($key);
			$this->_db->delete($key.'_expire');
		} else {
			$this->_db->replace($key.'_expire', time());
		}
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