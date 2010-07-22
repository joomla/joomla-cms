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
	/**
	 * @since	1.6
	 */
	private $_root;

	/**
	 * Constructor
	 *
	 * @param	array	$options optional parameters
	 * @since	1.5
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		$this->_root	= $options['cachebase'];
	}

	// NOTE: raw php calls are up to 100 times faster than JFile or JFolder

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
		$data = false;

		$path = $this->_getFilePath($id, $group);

		if ($checkTime == false || ($checkTime == true && $this->_checkExpire($id, $group) === true)) {
			if (file_exists($path)) {
				$data = file_get_contents($path);
				if ($data) {
					// Remove the initial die() statement
					$data = str_replace('<?php die("Access Denied"); ?>#x#', '', $data);
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

		$path		= $this->_root;
		$folders 	= $this->_folders($path);
		$data 		= array();

		foreach ($folders as $folder) {
			$files 	= array();
			$files 	= $this->_filesInFolder($path.DS.$folder);
			$item 	= new JCacheStorageHelper($folder);

			foreach ($files as $file) {
				$item->updateSize(filesize($path.DS.$folder.DS.$file) / 1024);
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
		$written	= false;
		$path		= $this->_getFilePath($id, $group);
		$die		= '<?php die("Access Denied"); ?>#x#';

		// Prepend a die string
		$data		= $die.$data;

		$_fileopen = @fopen($path, "wb");

		if ($_fileopen) {
			$len = strlen($data);
			@fwrite($_fileopen, $data, $len);
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
		$return = true;
		$folder	= $group;

		if (trim($folder) == '') {
			$mode = 'notgroup';
		}

		switch ($mode) {
			case 'notgroup':
				$folders = $this->_folders($this->_root);
				for ($i=0, $n=count($folders); $i<$n; $i++) {
					if ($folders[$i] != $folder) {
						$return |= $this->_deleteFolder($this->_root.DS.$folders[$i]);
					}
				}
				break;
			case 'group':
			default:
				if (is_dir($this->_root.DS.$folder)) {
					$return = $this->_deleteFolder($this->_root.DS.$folder);
				}
				break;
		}
		return $return;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public function gc()
	{
		$result = true;
		// files older than lifeTime get deleted from cache
		$files = $this->_filesInFolder($this->_root, '', true, true);
		foreach($files As $file) {
			$time = @filemtime($file);
			if (($time + $this->_lifetime) < $this->_now || empty($time)) {
				$result |= @unlink($file);
			}
		}
		return $result;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public static function test()
	{
		$conf = JFactory::getConfig();
		return is_writable($conf->get('cache_path', JPATH_ROOT.DS.'cache'));
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
		$returning = new stdClass();
		$returning->locklooped = false;

		$looptime 	= $locktime * 10;
		$path		= $this->_getFilePath($id, $group);

		$_fileopen = @fopen($path, "r+b");

		if ($_fileopen) {
				$data_lock = @flock($_fileopen, LOCK_EX);
		} else {
			$data_lock = false;
		}

		if ($data_lock === false) {

			$lock_counter = 0;

			// loop until you find that the lock has been released.  that implies that data get from other thread has finished
			while ($data_lock === false) {

				if ($lock_counter > $looptime) {
					$returning->locked 		= false;
					$returning->locklooped 	= true;
					break;
				}

				usleep(100);
				$data_lock =  @flock($_fileopen, LOCK_EX);
				$lock_counter++;
			}

		}
		$returning->locked = $data_lock;

		return $returning;
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
		$path = $this->_getFilePath($id, $group);

		$_fileopen = @fopen($path, "r+b");

		if ($_fileopen) {
				$ret = @flock($_fileopen, LOCK_UN);
				@fclose($_fileopen);
		}

		return $ret;
	}



	/**
	 * Check to make sure cache is still valid, if not, delete it.
	 *
	 * @param	string	$id		Cache key to expire.
	 * @param	string	$group	The cache data group.
	 * @since	1.6
	 */
	private function _checkExpire($id, $group)
	{
		$path = $this->_getFilePath($id, $group);

		// check prune period
		if (file_exists($path)) {
			$time = @filemtime($path);
			if (($time + $this->_lifetime) < $this->_now || empty($time)) {
				@unlink($path);
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
		return $dir.DS.$name.'.php';
	}

	/**
	 * Quickly delete a folder of files
	 *
	 * @param string The path to the folder to delete.
	 * @return boolean True on success.
	 * @since 1.6
	 */
	private function _deleteFolder($path)
	{
	// Sanity check
		if (!$path || !is_dir($path) || empty($this->_root)) {
			// Bad programmer! Bad Bad programmer!
			JError::raiseWarning(500, 'JCacheStorageFile::_deleteFolder ' . JText::_('JLIB_FILESYSTEM_ERROR_DELETE_BASE_DIRECTORY'));
			return false;
		}

		$path = $this->_cleanPath($path);

		// Check to make sure path is inside cache folder, we do not want to delete Joomla root!
		$pos = strpos($path, $this->_cleanPath($this->_root));

		if ($pos === false || $pos > 0) {
			JError::raiseWarning(500, 'JCacheStorageFile::_deleteFolder' . JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', $path));
			return false;
		}


		// Remove all the files in folder if they exist; disable all filtering
		$files = $this->_filesInFolder($path, '.', false, true, array(), array());

		if (!empty($files) && !is_array($files)) {
			if (@unlink($files) !== true) {
				return false;
			}
		} else if (!empty($files) && is_array($files)) {

			foreach ($files as $file)
			{
				$file = $this->_cleanPath($file);

				// In case of restricted permissions we zap it one way or the other
				// as long as the owner is either the webserver or the ftp
				if (@unlink($file)) {
					// Do nothing
				} else {
					$filename = basename($file);
					JError::raiseWarning('SOME_ERROR_CODE', 'JCacheStorageFile::_deleteFolder' . JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename));
					return false;
				}
			}
		}


		// Remove sub-folders of folder; disable all filtering
		$folders = $this->_folders($path, '.', false, true, array(), array());

		foreach ($folders as $folder) {
			if (is_link($folder)) {
				// Don't descend into linked directories, just delete the link.
				if (@unlink($folder) !== true) {
					return false;
				}
			} elseif ($this->_deleteFolder($folder) !== true) {
				return false;
			}
		}


		// In case of restricted permissions we zap it one way or the other
		// as long as the owner is either the webserver or the ftp
		if (@rmdir($path)) {
			$ret = true;
		} else {
			JError::raiseWarning('SOME_ERROR_CODE', 'JCacheStorageFile::_deleteFolder' . JText::sprintf('JLIB_FILESYSTEM_ERROR_FOLDER_DELETE', $path));
			$ret = false;
		}
		return $ret;
	}


	/**
	 * Function to strip additional / or \ in a path name
	 *
	 * @param	string	The path to clean
	 * @param	string	Directory separator (optional)
	 * @return	string	The cleaned path
	 * @since	1.6
	 */
	private function _cleanPath($path, $ds = DS)
	{
		$path = trim($path);

		if (empty($path)) {
			$path = $this->_root;
		} else {
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}


	/**
	 * Utility function to quickly read the files in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for file names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the file.
	 * @param	array	Array with names of files which should not be shown in
	 * the result.
	 * @return	array	Files in the given folder.
	 * @since 1.6
	 */
	private function _filesInFolder($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX'), $excludefilter = array('^\..*','.*~'))
	{
		// Initialise variables.
		$arr = array();

		// Check to make sure the path valid and clean
		$path = $this->_cleanPath($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JCacheStorageFile::_filesInFolder' . JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', $path));
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		if (count($excludefilter)) {
			$excludefilter = '/('. implode('|', $excludefilter) .')/';
		} else {
			$excludefilter = '';
		}
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude)) && (!$excludefilter || !preg_match($excludefilter, $file))) {
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					if ($recurse) {
						if (is_integer($recurse)) {
							$arr2 = $this->_filesInFolder($dir, $filter, $recurse - 1, $fullpath);
						} else {
							$arr2 = $this->_filesInFolder($dir, $filter, $recurse, $fullpath);
						}

						$arr = array_merge($arr, $arr2);
					}
				} else {
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $path . DS . $file;
						} else {
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir($handle);

		return $arr;
	}

/**
	 * Utility function to read the folders in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for folder names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the folders.
	 * @param	array	Array with names of folders which should not be shown in
	 * the result.
	 * @param	array	Array with regular expressions matching folders which
	 * should not be shown in the result.
	 * @return	array	Folders in the given folder.
	 * @since 1.6
	 */
	private function _folders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX'), $excludefilter = array('^\..*'))
	{
		// Initialise variables.
		$arr = array();

		// Check to make sure the path valid and clean
		$path = $this->_cleanPath($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JCacheStorageFile::_folders' . JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', $path));
			return false;
		}

		// read the source directory
		$handle = opendir($path);

		if (count($excludefilter)) {
			$excludefilter_string = '/('. implode('|', $excludefilter) .')/';
		} else {
			$excludefilter_string = '';
		}
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude)) && (empty($excludefilter_string) || !preg_match($excludefilter_string, $file))) {
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					// Removes filtered directories
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $dir;
						} else {
							$arr[] = $file;
						}
					}
					if ($recurse) {
						if (is_integer($recurse)) {
						$arr2 = $this->_folders($dir, $filter, $recurse - 1, $fullpath, $exclude, $excludefilter);
						} else {
						$arr2 = $this->_folders($dir, $filter, $recurse, $fullpath, $exclude, $excludefilter);
						}

						$arr = array_merge($arr, $arr2);
					}
				}
			}
		}
		closedir($handle);

		return $arr;
	}
}