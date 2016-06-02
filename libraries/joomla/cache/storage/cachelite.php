<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cache lite storage handler
 *
 * @see    http://pear.php.net/package/Cache_Lite/
 * @since  11.1
 */
class JCacheStorageCachelite extends JCacheStorage
{
	/**
	 * Singleton Cache_Lite instance
	 *
	 * @var    Cache_Lite
	 * @since  11.1
	 */
	protected static $CacheLiteInstance = null;

	/**
	 * Root path
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_root;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->_root = $options['cachebase'];

		$cloptions = array(
			'cacheDir'                => $this->_root . '/',
			'lifeTime'                => $this->_lifetime,
			'fileLocking'             => $this->_locking,
			'automaticCleaningFactor' => isset($options['autoclean']) ? $options['autoclean'] : 200,
			'fileNameProtection'      => false,
			'hashedDirectoryLevel'    => 0,
			'caching'                 => $options['caching']
		);

		if (static::$CacheLiteInstance === null)
		{
			$this->initCache($cloptions);
		}
	}

	/**
	 * Instantiates the Cache_Lite object. Only initializes the engine if it does not already exist.
	 *
	 * @param   array  $cloptions  optional parameters
	 *
	 * @return  Cache_Lite
	 *
	 * @since   11.1
	 */
	protected function initCache($cloptions)
	{
		if (!class_exists('Cache_Lite'))
		{
			require_once 'Cache/Lite.php';
		}

		static::$CacheLiteInstance = new Cache_Lite($cloptions);

		return static::$CacheLiteInstance;
	}

	/**
	 * Get cached data by ID and group
	 *
	 * @param   string   $id         The cache data ID
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		static::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');

		// This call is needed to ensure $this->rawname is set
		$this->_getCacheId($id, $group);

		return static::$CacheLiteInstance->get($this->rawname, $group);
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		$path    = $this->_root;
		$folders = new DirectoryIterator($path);
		$data    = array();

		foreach ($folders as $folder)
		{
			if (!$folder->isDir() || $folder->isDot())
			{
				continue;
			}

			$foldername = $folder->getFilename();

			$files = new DirectoryIterator($path . '/' . $foldername);
			$item  = new JCacheStorageHelper($foldername);

			foreach ($files as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				$filename = $file->getFilename();

				$item->updateSize(filesize($path . '/' . $foldername . '/' . $filename) / 1024);
			}

			$data[$foldername] = $item;
		}

		return $data;
	}

	/**
	 * Store the data to cache by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		$dir = $this->_root . '/' . $group;

		// If the folder doesn't exist try to create it
		if (!is_dir($dir))
		{
			// Make sure the index file is there
			$indexFile = $dir . '/index.html';
			@mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
		}

		// Make sure the folder exists
		if (!is_dir($dir))
		{
			return false;
		}

		static::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');

		// This call is needed to ensure $this->rawname is set
		$this->_getCacheId($id, $group);

		return static::$CacheLiteInstance->save($data, $this->rawname, $group);
	}

	/**
	 * Remove a cached data entry by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function remove($id, $group)
	{
		static::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');

		// This call is needed to ensure $this->rawname is set
		$this->_getCacheId($id, $group);

		return static::$CacheLiteInstance->remove($this->rawname, $group);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode    : cleans all cache in the group
	 * notgroup mode : cleans all cache not in the group
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		switch ($mode)
		{
			case 'notgroup':
				$clmode  = 'notingroup';
				$success = static::$CacheLiteInstance->clean($group, $clmode);
				break;

			case 'group':
				if (is_dir($this->_root . '/' . $group))
				{
					$clmode = $group;
					static::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
					$success = static::$CacheLiteInstance->clean($group, $clmode);

					// Remove sub-folders of folder; disable all filtering
					$folders = JFolder::folders($this->_root . '/' . $group, '.', false, true, array(), array());

					foreach ($folders as $folder)
					{
						if (is_link($folder))
						{
							if (JFile::delete($folder) !== true)
							{
								return false;
							}
						}
						elseif (JFolder::delete($folder) !== true)
						{
							return false;
						}
					}
				}
				else
				{
					$success = true;
				}

				break;

			default:
				if (is_dir($this->_root . '/' . $group))
				{
					$clmode = $group;
					static::$CacheLiteInstance->setOption('cacheDir', $this->_root . '/' . $group . '/');
					$success = static::$CacheLiteInstance->clean($group, $clmode);
				}
				else
				{
					$success = true;
				}

				break;
		}

		return $success;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		$result = true;
		static::$CacheLiteInstance->setOption('automaticCleaningFactor', 1);
		static::$CacheLiteInstance->setOption('hashedDirectoryLevel', 1);
		$success1 = static::$CacheLiteInstance->_cleanDir($this->_root . '/', false, 'old');

		if (!($dh = opendir($this->_root . '/')))
		{
			return false;
		}

		while ($file = readdir($dh))
		{
			if (($file != '.') && ($file != '..') && ($file != '.svn'))
			{
				$file2 = $this->_root . '/' . $file;

				if (is_dir($file2))
				{
					$result = ($result && (static::$CacheLiteInstance->_cleanDir($file2 . '/', false, 'old')));
				}
			}
		}

		$success = ($success1 && $result);

		return $success;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		@include_once 'Cache/Lite.php';

		return class_exists('Cache_Lite');
	}
}
