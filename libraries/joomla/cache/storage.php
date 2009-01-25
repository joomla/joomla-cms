<?php
/**
* @version		$Id:storage.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Cache
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Abstract cache storage handler
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
abstract class JCacheStorage extends JClass
{
	protected $_application = null;
	protected $_language = null;
	protected $_locking = null;
	protected $_lifetime = null;
	protected $_now = null;

	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	protected function __construct( $options = array() )
	{
		$this->setOptions($options);
	}

	protected function setOptions($options = array()) {
		$this->_application	= (isset($options['application'])) ? $options['application'] : null;
		$this->_language	= (isset($options['language'])) ? $options['language'] : 'en-GB';
		$this->_locking		= (isset($options['locking'])) ? $options['locking'] : true;
		$this->_lifetime	= (isset($options['lifetime'])) ? $options['lifetime'] : null;
		$this->_now		= (isset($options['now'])) ? $options['now'] : time();
	}

	/**
	 * Returns a reference to a cache storage hanlder object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @param	string	$handler	The cache storage handler to instantiate
	 * @return	object	A JCacheStorageHandler object
	 * @since	1.5
	 */
	public static function &getInstance($handler = 'file', $options = array())
	{
		static $instances = array();
		static $now = null;
		if(is_null($now)) {
			$now = time();
		}
		if(!isset($instances[$handler])) {
			$options['now'] = $now;
			$handler = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $handler));
			$class   = 'JCacheStorage'.ucfirst($handler);
			if(!class_exists($class))
			{
				$path = dirname(__FILE__).DS.'storage'.DS.$handler.'.php';
				if (file_exists($path) ) {
					require_once($path);
				} else {
					return JError::raiseWarning(500, 'Unable to load Cache Storage: '.$handler);
				}
			}
			$instances[$handler] = new $class($options);
		}
		$return = clone($instances[$handler]);
		$return->setOptions($options);
		return $return;
	}

	/**
	 * Get cached data by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	public function get($id, $group, $checkTime = true) {
		return false;
	}

	/**
	 * Store the data to cache by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public abstract function store($id, $group, $data);

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public abstract function remove($id, $group);

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public abstract function clean($group, $mode);

	/**
	 * Garbage collect expired cache data
	 *
	 * @abstract
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	public abstract function gc();

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return false;
	}
}
