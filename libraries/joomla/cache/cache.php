<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

//Register the session storage class with the loader
JLoader::register('JCacheStorage', dirname(__FILE__).'/storage.php');

/**
 * Joomla Cache base object.
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
abstract class JCache extends JObject
{
	/**
	 * Storage Handler
	 * @var		object
	 */
	protected $_handler;

	/**
	 * Cache Options
	 * @var		array
	 */
	protected $_options;

	/**
	 * Constructor
	 *
	 * @param	array	An array of options (cachebase|caching|defaultgroup|language|storage).
	 */
	public function __construct($options)
	{
		// Initialise default options.
		$this->_options = array(
			'cachebase'		=> JPATH_ROOT.'/cache',
			'caching'		=> true,
			'defaultgroup'	=> 'default',
			'language'		=> 'en-GB',
			'storage'		=> 'file'
		);

		// Overwrite default options with given options
		if (isset($options['cachebase'])) {
			$this->_options['cachebase'] = $options['cachebase'];
		}

		if (isset($options['caching'])) {
			$this->_options['caching'] =  $options['caching'];
		}

		if (isset($options['defaultgroup'])) {
			$this->_options['defaultgroup'] = $options['defaultgroup'];
		}

		if (isset($options['language'])) {
			$this->_options['language'] = $options['language'];
		}

		if (isset($options['storage'])) {
			$this->_options['storage'] = $options['storage'];
		}

		// Fix to detect if template positions are enabled...
		if (JRequest::getCMD('tpl',0)) {
			$this->_options['caching'] = false;
		}
	}

	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param	string	The cache object type to instantiate
	 * @return	object	A JCache object
	 * @since	1.5
	 */
	public static function getInstance($type = 'output', $options = array())
	{
		$type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));

		$class = 'JCache'.ucfirst($type);

		if (!class_exists($class)) {
			$path = dirname(__FILE__).'/handler/'.$type.'.php';

			if (file_exists($path)) {
				require_once $path;
			} else {
				JError::raiseError(500, 'Unable to load Cache Handler: '.$type);
			}
		}

		return new $class($options);
	}

	/**
	 * Get the storage handlers
	 *
	 * @return	array	An array of available storage handlers
	 */
	public function getStores()
	{
		jimport('joomla.filesystem.folder');
		$handlers = JFolder::files(dirname(__FILE__).DS.'storage', '.php$');

		$names = array();
		foreach($handlers as $handler) {
			$name = substr($handler, 0, strrpos($handler, '.'));
			$class = 'JCacheStorage'.$name;

			if (!class_exists($class)) {
				require_once dirname(__FILE__).DS.'storage'.DS.$name.'.php';
			}

			if (call_user_func_array(array(trim($class), 'test'), array())) {
				$names[] = $name;
			}
		}

		return $names;
	}

	/**
	 * Set caching enabled state
	 *
	 * @param	boolean	True to enable caching
	 * @return	void
	 * @since	1.5
	 */
	public function setCaching($enabled)
	{
		$this->_options['caching'] = $enabled;
	}

	/**
	 * Set cache lifetime
	 *
	 * @param	int		Cache lifetime
	 * @return	void
	 * @since	1.5
	 */
	public function setLifeTime($lt)
	{
		$this->_options['lifetime'] = $lt;
	}

	/**
	 * Get cached data by id and group
	 *
	 * @param	string	The cache data id
	 * @param	string	The cache data group
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	public function get($id, $group=null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage handler
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['caching']) {
			return $handler->get($id, $group, (isset($this->_options['checkTime']))? $this->_options['checkTime'] : true);
		}
		return false;
	}

	/**
	 * Store the cached data by id and group
	 *
	 * @param	string	The cache data id
	 * @param	string	The cache data group
	 * @param	mixed	The data to store
	 * @return	boolean	True if cache stored
	 * @since	1.5
	 */
	public function store($data, $id, $group=null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage handler and store the cached data
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['caching']) {
			return $handler->store($id, $group, $data);
		}
		return false;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param	string	The cache data id
	 * @param	string	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function remove($id, $group=null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage handler
		$handler = &$this->_getStorage();
		if (!JError::isError($handler)) {
			return $handler->remove($id, $group);
		}
		return false;
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @param	string	The cache data group
	 * @param	string	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function clean($group=null, $mode='group')
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage handler
		$handler = &$this->_getStorage();
		if (!JError::isError($handler)) {
			return $handler->clean($group, $mode);
		}
		return false;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public function gc()
	{
		// Get the storage handler
		$handler = &$this->_getStorage();
		if (!JError::isError($handler)) {
			return $handler->gc();
		}
		return false;
	}

	/**
	 * Get the cache storage handler
	 *
	 * @return	object	A JCacheStorage object
	 * @since	1.5
	 */
	protected function _getStorage()
	{
		if (is_a($this->_handler, 'JCacheStorage')) {
			return $this->_handler;
		}

		$this->_handler = &JCacheStorage::getInstance($this->_options['storage'], $this->_options);
		return $this->_handler;
	}
}