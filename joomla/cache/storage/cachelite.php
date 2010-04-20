<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;



/**
 * Cache litestorage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.6
 */
class JCacheStorageCachelite extends JCacheStorage
{

	private static $CacheLiteInstance = null;
	private $_root;

	/**
	 * Constructor
	 *
	 * @param array $options optional parameters
	 * @since	1.6
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		
		$this->_root	= $options['cachebase'];

		$cloptions = array(
			'cacheDir' 					=> $this->_root.DS,
			'lifeTime' 					=> $this->_lifetime,
			'fileLocking'   			=> $this->_locking,
			'automaticCleaningFactor'	=> isset($options['autoclean']) ? $options['autoclean'] : 200,
			'fileNameProtection'		=> false,
			'hashedDirectoryLevel'		=> 0,
			'caching' 					=> $options['caching']
		);

		if (self::$CacheLiteInstance === null) $this->initCache($cloptions);
	}

	/**
	 * Instantiates the appropriate CacheLite object.
	 * Only initializes the engine if it does not already exist.
	 * Note this is a private method
	 * @param array $options optional parameters
	 * @since		1.6
	 */
	private function initCache($cloptions) {

		require_once('Cache/Lite.php');
		self::$CacheLiteInstance = new Cache_Lite($cloptions);
		return self::$CacheLiteInstance;
	}


	/**
	 * Get cached data from a file by id and group
	 *
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.6
	 */
	public function get($id, $group, $checkTime)
	{
		$data = false;
		self::$CacheLiteInstance->setOption('cacheDir', $this->_root.DS.$group.DS);
		$this->_getCacheId($id, $group);
		$data = self::$CacheLiteInstance->get($this->rawname,$group);
		return $data;
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
	 * @since	1.6
	 */
	public function store($id, $group, $data)
	{
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
		self::$CacheLiteInstance->setOption('cacheDir', $this->_root.DS.$group.DS);
		$this->_getCacheId($id, $group);
		$sucess = self::$CacheLiteInstance->save($data,$this->rawname,$group);
		if($sucess == true) return $sucess; else return false;

	}

	/**
	 * Remove a cached data file by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.6
	 */
	public function remove($id, $group)
	{
		self::$CacheLiteInstance->setOption('cacheDir', $this->_root.DS.$group.DS);
		$this->_getCacheId($id, $group);
		$sucess = self::$CacheLiteInstance->remove($this->rawname,$group);
		if($sucess == true) return $sucess; else return false;
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
	 * @since	1.6
	 */
	public function clean($group, $mode=null)
	{
		jimport('joomla.filesystem.folder');

		if (trim($group) == '') {
			$clmode = 'notgroup';
		}
		if ($mode == null) $clmode = 'group';

		switch ($mode)
		{
			case 'notgroup':
				$clmode = 'notingroup';
				$sucess = self::$CacheLiteInstance->clean($group,$clmode);
				break;
			case 'group':
				$clmode = $group;
				self::$CacheLiteInstance->setOption('cacheDir', $this->_root.DS.$group.DS);
				$sucess = self::$CacheLiteInstance->clean($group,$clmode);
				if (is_dir($this->_root.DS.$group)) {
					$return = JFolder::delete($this->_root.DS.$group);
				}
				break;
			default:
				$clmode = $group;
				self::$CacheLiteInstance->setOption('cacheDir', $this->_root.DS.$group.DS);
				$sucess = self::$CacheLiteInstance->clean($group,$clmode);
				break;
		}

		if($sucess == true) return $sucess; else return false;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return boolean  True on success, false otherwise.
	 * @since	1.6
	 */
	public function gc()
	{	$result = true;
		self::$CacheLiteInstance->setOption('automaticCleaningFactor', 1);
		self::$CacheLiteInstance->setOption('hashedDirectoryLevel', 1);
		$test = self::$CacheLiteInstance;
		$sucess1 = self::$CacheLiteInstance->_cleanDir($this->_root.DS,false, 'old');
	    if (!($dh = opendir($this->_root.DS))) {
            return false; 
        }
		    while ($file = readdir($dh)) {
            if (($file != '.') && ($file != '..') && ($file != '.svn')) {
            	$file2 = $this->_root.DS.$file;
                    if (is_dir($file2)) {
                        $result = ($result and (self::$CacheLiteInstance->_cleanDir($file2.DS, false, 'old')));
                    }
                
            }
		    }
        $sucess = $sucess1 and $result;
		return $sucess;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 * @since	1.6
	 */
	public static function test()
	{	
		if(file_exists('Cache/Lite.php'))
		{
			include_once('Cache/Lite.php');
			if (class_exists('Cache_Lite'))
			{
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

}
