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

/**
 * File cache storage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorageFile extends JCacheStorage
{
	
	private $_root;
	
	/**
	* Constructor
	*
	* @param array $options optional parameters
	*/
	public function __construct($options = array())
	{
		parent::__construct($options);
		$this->_root	= $options['cachebase'];

	}

	/**
	 * Get cached data from a file by id and group
	 *
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	public function get($id, $group, $checkTime=true)
	{	
		// @Todo: make reads use joomla.filesystem.file
		$data = false;

		$path = $this->_getFilePath($id, $group);
		
		if ($checkTime == false || ($checkTime == true && $this->_checkExpire($id, $group) === true)) {
		
		if (file_exists($path)) {
			$data = file_get_contents($path);
			if ($data) {
				// Remove the initial die() statement
				$data	= preg_replace('/^.*\n/', '', $data);
			}
		}

		return $data;
		
		} else {
			return false;
		}
	}
	
	
	 /**
	 * Get all cached data
	 *
	 * @return	array data
	 * @since	1.6
	 */
	
	public function getAll()
	{	
		parent::getAll();
		
		$path=$this->_root;
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($path);
		$data = array();

		foreach ($folders as $folder) {
			$files = array();
			$files = JFolder::files($path.DS.$folder);
			$item = new JCacheStorageHelper();

			foreach ($files as $file) {
				$item->updateSize(filesize($path.DS.$folder.DS.$file)/1024,$folder);
			}
			$data[$folder] = $item;
		}

		return $data;
	}
	
	/**
	 * Store the data to a file by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function store($id, $group, $data)
	{   
		// @Todo: make writes use joomla.filesystem.file ...will have to fix that too for locking
		$written	= false;
		$path		= $this->_getFilePath($id, $group);
		$die		= '<?php die("Access Denied"); ?>'."\n";

		// Prepend a die string

		$data		= $die.$data;

		$fp = @fopen($path, "wb");
		if ($fp) {
			if ($this->_locking) {
				@flock($fp, LOCK_EX);
			}
			$len = strlen($data);
			@fwrite($fp, $data, $len);
			if ($this->_locking) {
				@flock($fp, LOCK_UN);
			}
			@fclose($fp);
			$written = true;
		}
		// Data integrity check
		if ($written && ($data == file_get_contents($path))) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove a cached data file by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function remove($id, $group)
	{	
		jimport('joomla.filesystem.file');
		$path = $this->_getFilePath($id, $group);
		if (!@unlink($path)) {
			return false;
		}
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
		jimport('joomla.filesystem.folder');

		$return = true;
		$folder	= $group;

		if (trim($folder) == '') {
			$mode = 'notgroup';
		}

		switch ($mode)
		{
			case 'notgroup':
				$folders = JFolder::folders($this->_root);
				for ($i=0,$n=count($folders);$i<$n;$i++)
				{
					if ($folders[$i] != $folder) {
						$return |= JFolder::delete($this->_root.DS.$folders[$i]);
					}
				}
				break;
			case 'group':
			default:
				if (is_dir($this->_root.DS.$folder)) {
					$return = JFolder::delete($this->_root.DS.$folder);
				}
				break;
		}
		return $return;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public function gc()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$result = true;
		// files older than lifeTime get deleted from cache
		$files = JFolder::files($this->_root, '', true, true);
		foreach($files As $file) {
			$time = @filemtime($path);
			if (($time + $this->_lifetime) < $this->_now || empty($time)) {
				$result |= JFile::delete($file);
			}
		}
		return $result;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		$conf	= &JFactory::getConfig();
		return is_writable($conf->get('cache_path',JPATH_ROOT.DS.'cache'));
	}

	/**
	 * Check to make sure cache is still valid, if not, delete it.
	 *
	 * @param string  $id		Cache key to expire.
	 * @param string  $group	The cache data group.
	 */
	function _checkExpire($id, $group)
	{	
		jimport('joomla.filesystem.file');
		$path = $this->_getFilePath($id, $group);

		// check prune period
		if (file_exists($path)) {
			$time = @filemtime($path);
			if (($time + $this->_lifetime) < $this->_now || empty($time)) {
				JFile::delete($path);
				return false;
			}
			return true;
		} 
		return false;
	}

	/**
	 * Get a cache file path from an id/group pair
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	string	The cache file path
	 * @since	1.5
	 */
	private function _getFilePath($id, $group)
	{	
		$name	= $this->_getCacheId($id, $group);
		$dir	= $this->_root.DS.$group;

		// If the folder doesn't exist try to create it
		if (!is_dir($dir)) {

			// Make sure the index file is there
			$indexFile = $dir.'/index.html';
			@ mkdir($dir) && file_put_contents($indexFile, '<html><body bgcolor="#FFFFFF"></body></html>');
		}

		// Make sure the folder exists
		if (!is_dir($dir)) {
			return false;
		}
		return $dir.DS.$name;
	}
}
