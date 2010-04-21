<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

//Register the storage class with the loader
JLoader::register('JCacheStorage', dirname(__FILE__).DS.'storage.php');

//Register the controller class with the loader
JLoader::register('JCacheController', dirname(__FILE__).DS.'controller.php');


/**
 * Joomla! Cache base object
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */

// ALMOST EVERYTHING MUST BE PUBLIC HERE TO ALLOW OVERLOADING!

class JCache extends JObject
{

	public $_handler;
	private static $_storage;
	public $_options;


	/**
	 * Constructor
	 *
	 * @param	array	$options	options
	 */
	public function __construct($options)
	{
		$conf = &JFactory::getConfig();

		$caching = $conf->get('caching', 1);

		$this->_options = array(
			'cachebase'		=> $conf->get('cache_path',JPATH_ROOT.DS.'cache'),
			'lifetime'		=> $conf->get('cachetime') * 60,	// minutes to seconds
			'language'		=> $conf->get('language','en-GB'),
			'storage'		=> $conf->get('cache_handler', 'file'),
			'defaultgroup'=>'default',
			'locking'=>true,
			'locktime'=>15,
			'checkTime' => true,
			'caching'	=> $caching == 1 ? true : false
		);

		// Overwrite default options with given options
		foreach ($options AS $option=>$value) {
			if (isset($options[$option])) {
				$this->_options[$option] = $options[$option];
			}
		}

		// Fix to detect if template positions are enabled...
		//@todo remove, moved to safeuri parameters, no need to disable cache
		/*if (JRequest::getCMD('tpl',0)) {
		$this->_options['caching'] = false;
		}*/
	}

	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param	string	$type	The cache object type to instantiate
	 * @return	object	A JCache object
	 * @since	1.5
	 */
	public static function getInstance($type = 'output', $options = array())
	{
		return JCacheController::getInstance($type, $options);
	}

	/**
	 * Get the storage handlers
	 *
	 * @return array An array of available storage handlers
	 */
	public static function getStores()
	{
		jimport('joomla.filesystem.folder');
		$handlers = JFolder::files(dirname(__FILE__).DS.'storage', '.php');

		$names = array();
		foreach($handlers as $handler)
		{
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
	 * @param	boolean	$enabled	True to enable caching
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
	 * @access	public
	 * @param	int	$lt	Cache lifetime
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
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	public function get($id, $group=null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['caching']) {
			return $handler->get($id, $group, $this->_options['checkTime']);
		}
		return false;
	}

	/**
	 * Get a list of all cached data
	 *
	 * @return	mixed	Boolean false on failure or an object with a list of cache groups and data
	 * @since	1.6
	 */
	public function getAll()
	{
		// Get the storage
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['caching']) {
			return $handler->getAll();
		}
		return false;
	}

	/**
	 * Store the cached data by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	mixed	$data	The data to store
	 * @return	boolean	True if cache stored
	 * @since	1.5
	 */
	public function store($data, $id, $group=null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage and store the cached data
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['caching']) {
			$handler->_lifetime = $this->_options['lifetime'];
			return $handler->store($id, $group, $data);
		}
		return false;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function remove($id, $group=null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage
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
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
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
	 * @return boolean  True on success, false otherwise.
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
	 * Set lock flag on cached item
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @since	1.6
	 * @return boolean  True on success, false otherwise.
	 */
	public function lock($id,$group=null,$locktime=null)
	{
		$returning = new stdClass();
		$returning->locklooped = false;
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the default locktime
		$locktime = ($locktime) ? $locktime : $this->_options['locktime'];

		//allow storage handlers to perform locking on their own
		// NOTE drivers with lock need also unlock or unlocking will fail because of false $id
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['locking'] == true && $this->_options['caching'] == true) {
			$locked = $handler->lock($id,$group,$locktime);
			if ($locked !== false) return $locked;
		}

		// fallback
		$curentlifetime = $this->_options['lifetime'];
		// set lifetime to locktime for storing in children
		$this->_options['lifetime'] = $locktime;

		//$lock = $this->store(1, $id.'_lock', $group);

		$looptime = $locktime * 10;
		$id2 = $id.'_lock';

		if ($this->_options['locking'] == true && $this->_options['caching'] == true ) {
			$data_lock = $this->get($id2,$group);

		} else {
			$data_lock = false;
			$returning->locked = false;
			}

			if ( $data_lock !== false ) {

				$lock_counter = 0;

				// loop until you find that the lock has been released.  that implies that data get from other thread has finished
				while ( $data_lock !== false ) {

					if ( $lock_counter > $looptime) {
						$returning->locked = false;
						$returning->locklooped = true;
						break;
					}

					usleep(100);
					$data_lock = $this->get($id2,$group);
					$lock_counter++;
				}

			}


			if ($this->_options['locking'] == true && $this->_options['caching'] == true ) {
				$returning->locked = $this->store(1,$id2,$group);
			}

		// revert lifetime to previuos one
		$this->_options['lifetime'] = $curentlifetime;

		//return $lock;
		return $returning;
	}

	/**
	 * Unset lock flag on cached item
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @since	1.6
	 * @return boolean  True on success, false otherwise.
	 */
	public function unlock($id,$group=null)
	{
		$unlock = false;
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		//allow handlers to perform unlocking on their own
		$handler = &$this->_getStorage();
		if (!JError::isError($handler) && $this->_options['caching']) {
			$unlocked = $handler->unlock($id,$group);
			if ($unlocked !== false) return $unlocked;
		}

		// fallback
		if ($this->_options['caching']) {
			$unlock = $this->remove($id.'_lock',$group);
		}

		return $unlock;
	}

	/**
	 * Get the cache storage handler
	 *
	 * @return object A JCacheStorage object
	 * @since	1.5
	 */
	private function _getStorage()
	{
		// this is often used so cache this during execution in static variable
		if (self::$_storage) {
			return self::$_storage;
		}

		if (is_a($this->_handler, 'JCacheStorage')) {
			return $this->_handler;
		}

		$this->_handler = &JCacheStorage::getInstance($this->_options['storage'], $this->_options);
		self::$_storage = $this->_handler;
		return $this->_handler;
	}

	/**
	 * Perform workarounds on retrieved cached data
	 *
	 * @param	string	$data		Cached data
	 * @return	string	$body		Body of cached data
	 * @since	1.6
	 */
	public static function getWorkarounds($data) {

		// Initialise variables.
		$app 		= &JFactory::getApplication();
		$document	= &JFactory::getDocument();
		$body = null;

		// Get the document head out of the cache.
		$document->setHeadData((isset($data['head'])) ? $data['head'] : array());

		// If the pathway buffer is set in the cache data, get it.
		if (isset($data['pathway']) && is_array($data['pathway']))
		{
			// Push the pathway data into the pathway object.
			$pathway = &$app->getPathWay();
			$pathway->setPathway($data['pathway']);
		}

		// @todo chech if the following is needed, seems like it should be in page cache
		// If a module buffer is set in the cache data, get it.
		if (isset($data['module']) && is_array($data['module']))
		{
			// Iterate through the module positions and push them into the document buffer.
			foreach ($data['module'] as $name => $contents) {
				$document->setBuffer($contents, 'module', $name);
			}
		}

		if (isset($data['body'])) {
			// the following code searches for a token in the cached page and replaces it with the
			// proper token.
			$token	= JUtility::getToken();
			$search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="'.$token.'" value="1" />';
			$data['body'] = preg_replace($search, $replacement, $data['body']);
			$body = $data['body'];
		}

		// Get the document body out of the cache.
		return $body;
	}

	/**
	 * Create workarounded data to be cached
	 *
	 * @param	string	$data		Cached data
	 * @return	string	$cached		Data to be cached
	 * @since	1.6
	 */

	public static function setWorkarounds($data) {

		// Initialise variables.
		$app = &JFactory::getApplication();
		$document	= &JFactory::getDocument();

		// Get the modules buffer before component execution.
		$buffer1 = $document->getBuffer();

		// Make sure the module buffer is an array.
		if (!isset($buffer1['module']) || !is_array($buffer1['module'])) {
			$buffer1['module'] = array();
		}

		// View body data
		$cached['body'] = $data;

		// Document head data
		$cached['head'] = $document->getHeadData();

		// Pathway data
		$pathway			= &$app->getPathWay();
		if (isset($pathway)) {$cached['pathway'] = $pathway->getPathway();}

		// @todo chech if the following is needed, seems like it should be in page cache
		// Get the module buffer after component execution.
		$buffer2 = $document->getBuffer();

		// Make sure the module buffer is an array.
		if (!isset($buffer2['module']) || !is_array($buffer2['module'])) {
			$buffer2['module'] = array();
		}

		// Compare the second module buffer against the first buffer.
		$cached['module'] = array_diff_assoc($buffer2['module'], $buffer1['module']);

		return $cached;
	}

	/**
	 * Create safe id for cached data from url parameters set by plugins and framework
	 *
	 * @return	string	md5 encoded cacheid
	 * @since	1.6
	 */

	public static function makeId() {

		$app = & JFactory::getApplication();
		// get url parameters set by plugins
		$registeredurlparams = $app->get('registeredurlparams');

		if (empty($registeredurlparams)) {
			/*$registeredurlparams=new stdClass();
			$registeredurlparams->Itemid='INT';
			$registeredurlparams->catid='INT';
			$registeredurlparams->id='INT';**/

			return md5(serialize(JRequest::getURI()));   // provided for backwards compatibility - THIS IS NOT SAFE!!!!
		}
		// framework defaults
		$registeredurlparams->protocol='WORD';
		$registeredurlparams->option='WORD';
		$registeredurlparams->view='WORD';
		$registeredurlparams->layout='WORD';
		$registeredurlparams->tpl='CMD';
		$registeredurlparams->id='INT';

		$safeuriaddon=new stdClass();

		foreach ($registeredurlparams AS $key => $value) {
			$safeuriaddon->$key = JRequest::getVar($key, null,'default',$value);

		}

		return md5(serialize($safeuriaddon));
	}

	/**
	 * Add a directory where JCache should search for handlers. You may
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