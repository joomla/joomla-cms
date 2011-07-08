<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cache lite storage handler
 *
 * @package     Joomla.Platform
 * @subpackage  Cache
 * @since       11.1
 * 
 * @see http://pear.php.net/package/Cache_Lite/
 */
class JCacheStorageCachelite extends JCacheStorage
{
	/**
	 * 
	 * 
	 * @var    object
	 * @since  11.1
	 */
	protected static $CacheLiteInstance = null;

	/**
	 * 
	 * 
	 * @var
	 * @since   11.1
	 */
	protected $_root;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @return  JCacheStorageCachelite
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->_root	= $options['cachebase'];

		$cloptions = array(
			'cacheDir' 					=> $this->_root . '/',
			'lifeTime' 					=> $this->_lifetime,
			'fileLocking'   			=> $this->_locking,
			'automaticCleaningFactor'	=> isset($options['autoclean']) ? $options['autoclean'] : 200,
			'fileNameProtection'		=> false,
			'hashedDirectoryLevel'		=> 0,
			'caching' 					=> $options['caching']
		);

		if (self::$CacheLiteInstance === null) {
			$this->initCache($cloptions);
		}
	}

	/**
	 * Instantiates the appropriate CacheLite object.
	 * Only initializes the engine if it does not already exist.
	 * Note this is a protected method
	 *
	 * @param   array  $options  optional parameters
	 *
	 * @return  object
	 *
	 * @since   11.1
	 */
	protected function initCache($cloptions)
	{
		require_once 'Cache/Lite.php';

		self::$CacheLiteInstance = new Cache_Lite($cloptions);

		return self::$CacheLiteInstance;
	}

	/**
	 * Get cached data from a file by id and group
	 *
	 * @param   string   $id         The cache data id.
	 * @param   string   $group      The cache data group.
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold.
	 *
	 * @return  mixed  Boolean false on failure or a cached data string.
	 *
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		$data = false;
		self::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
		$this->_getCacheId($id, $group);
		$data = self::$CacheLiteInstance->get($this->rawname, $group);

		return $data;
	}


	/**
	 * Get all cached data
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		parent::getAll();

		$path = $this->_root;
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($path);
		$data = array();

		foreach ($folders as $folder)
		{
			$files = JFolder::files($path . '/' . $folder);
			$item = new JCacheStorageHelper($folder);

			foreach ($files as $file) {
				$item->updateSize(filesize($path . '/' . $folder . '/' . $file)/1024);
			}

			$data[$folder] = $item;
		}

		return $data;
	}

	/**
	 * Store the data to a file by id and group
	 *
	 * @param   string  $id     The cache data id.
	 * @param   string  $group  The cache data group.
	 * @param   string  $data   The data to store in cache.
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		$dir = $this->_root . '/' . $group;

		// If the folder doesn't exist try to create it
		if (!is_dir($dir)) {
			// Make sure the index file is there
			$indexFile = $dir.'/index.html';
			@mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
		}

		// Make sure the folder exists
		if (!is_dir($dir)) {
			return false;
		}

		self::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
		$this->_getCacheId($id, $group);
		$success = self::$CacheLiteInstance->save($data, $this->rawname, $group);

		if ($success == true) {
			return $success;
		}
		else {
			return false;
		}
	}

	/**
	 * Remove a cached data file by id and group
	 *
	 * @param   string   $id     The cache data id
	 * @param   string   $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function remove($id, $group)
	{
		self::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
		$this->_getCacheId($id, $group);
		$success = self::$CacheLiteInstance->remove($this->rawname, $group);

		if ($success == true) {
			return $success;
		}
		else {
			return false;
		}
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * @param   string  $group  The cache data group.
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup].
	 *                             group mode    : cleans all cache in the group
	 *                             notgroup mode : cleans all cache not in the group
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		jimport('joomla.filesystem.folder');

		if (trim($group) == '') {
			$clmode = 'notgroup';
		}

		if ($mode == null) {
			$clmode = 'group';
		}

		switch ($mode)
		{
			case 'notgroup':
				$clmode = 'notingroup';
				$success = self::$CacheLiteInstance->clean($group, $clmode);
				break;

			case 'group':
				if (is_dir($this->_root . '/' . $group)) {
					$clmode = $group;
					self::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
					$success = self::$CacheLiteInstance->clean($group, $clmode);
 					$return = JFolder::delete($this->_root . '/' . $group);
 				}
				else {
					$success = true;
				}

				break;

			default:
				if (is_dir($this->_root . '/' . $group)) {
					$clmode = $group;
					self::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
					$success = self::$CacheLiteInstance->clean($group, $clmode);
				}
				else {
					$success = true;
				}

				break;
		}

		if ($success == true)  {
			return $success;
		}
		else {
			return false;
		}
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		$result = true;
		self::$CacheLiteInstance->setOption('automaticCleaningFactor', 1);
		self::$CacheLiteInstance->setOption('hashedDirectoryLevel', 1);
		$test 		= self::$CacheLiteInstance;
		$success1 	= self::$CacheLiteInstance->_cleanDir($this->_root . '/', false, 'old');

		if (!($dh = opendir($this->_root . '/'))) {
			return false;
		}

		while ($file = readdir($dh))
		{
			if (($file != '.') && ($file != '..') && ($file != '.svn')) {
				$file2 = $this->_root . '/' . $file;

				if (is_dir($file2)) {
					$result = ($result and (self::$CacheLiteInstance->_cleanDir($file2 . '/', false, 'old')));
				}
			}
		}

		$success = ($success1 && $result);

		return $success;
	}
	
	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public static function test()
	{
			@include_once 'Cache/Lite.php';

			if (class_exists('Cache_Lite')) {
				return true;
			}
			else {
				return false;
			}
	}
}