<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Storage;

use Joomla\CMS\Cache\CacheStorage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * File cache storage handler
 *
 * @since  1.7.0
 * @note   For performance reasons this class does not use the Filesystem package's API
 */
class FileStorage extends CacheStorage
{
    /**
     * Root path
     *
     * @var    string
     * @since  1.7.0
     */
    protected $_root;

    /**
     * Locked resources
     *
     * @var    array
     * @since  3.7.0
     *
     */
    protected $_locked_files = [];

    /**
     * Constructor
     *
     * @param   array  $options  Optional parameters
     *
     * @since   1.7.0
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->_root = $options['cachebase'];

        // Workaround for php 5.3
        $locked_files = &$this->_locked_files;

        // Remove empty locked files at script shutdown.
        $clearAtShutdown = function () use (&$locked_files) {
            foreach ($locked_files as $path => $handle) {
                if (\is_resource($handle)) {
                    @flock($handle, LOCK_UN);
                    @fclose($handle);
                }

                // Delete only the existing file if it is empty.
                if (@filesize($path) === 0) {
                    File::invalidateFileCache($path);
                    @unlink($path);
                }

                unset($locked_files[$path]);
            }
        };

        register_shutdown_function($clearAtShutdown);
    }

    /**
     * Check if the cache contains data stored by ID and group
     *
     * @param   string  $id     The cache data ID
     * @param   string  $group  The cache data group
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    public function contains($id, $group)
    {
        return $this->_checkExpire($id, $group);
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
     * @since   1.7.0
     */
    public function get($id, $group, $checkTime = true)
    {
        $path  = $this->_getFilePath($id, $group);
        $close = false;

        if ($checkTime == false || ($checkTime == true && $this->_checkExpire($id, $group) === true)) {
            if (file_exists($path)) {
                if (isset($this->_locked_files[$path])) {
                    $_fileopen = $this->_locked_files[$path];
                } else {
                    $_fileopen = @fopen($path, 'rb');

                    // There is no lock, we have to close file after store data
                    $close = true;
                }

                if ($_fileopen) {
                    // On Windows system we can not use file_get_contents on the file locked by yourself
                    $data = stream_get_contents($_fileopen);

                    if ($close) {
                        @fclose($_fileopen);
                    }

                    if ($data !== false) {
                        // Remove the initial die() statement
                        return str_replace('<?php die("Access Denied"); ?>#x#', '', $data);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get all cached data
     *
     * @return  mixed  Boolean false on failure or a cached data object
     *
     * @since   1.7.0
     */
    public function getAll()
    {
        $path    = $this->_root;
        $folders = $this->_folders($path);
        $data    = [];

        foreach ($folders as $folder) {
            $files = $this->_filesInFolder($path . '/' . $folder);
            $item  = new CacheStorageHelper($folder);

            foreach ($files as $file) {
                // Do not include index.html with the Number of Files
                if ($file === 'index.html') {
                    continue;
                }

                $item->updateSize(filesize($path . '/' . $folder . '/' . $file));
            }

            $data[$folder] = $item;
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
     * @since   1.7.0
     */
    public function store($id, $group, $data)
    {
        $path  = $this->_getFilePath($id, $group);
        $close = false;

        // Prepend a die string
        $data = '<?php die("Access Denied"); ?>#x#' . $data;

        if (isset($this->_locked_files[$path])) {
            $_fileopen = $this->_locked_files[$path];

            // Because lock method uses flag c+b we have to truncate it manually
            @ftruncate($_fileopen, 0);
        } else {
            $_fileopen = @fopen($path, 'wb');

            // There is no lock, we have to close file after store data
            $close = true;
        }

        if ($_fileopen) {
            $length = \strlen($data);
            $result = @fwrite($_fileopen, $data, $length);

            if ($close) {
                @fclose($_fileopen);
            }

            return $result === $length;
        }

        return false;
    }

    /**
     * Remove a cached data entry by ID and group
     *
     * @param   string  $id     The cache data ID
     * @param   string  $group  The cache data group
     *
     * @return  boolean
     *
     * @since   1.7.0
     */
    public function remove($id, $group)
    {
        $path = $this->_getFilePath($id, $group);

        File::invalidateFileCache($path);
        if (!@unlink($path)) {
            return false;
        }

        return true;
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
     * @since   1.7.0
     */
    public function clean($group, $mode = null)
    {
        $return = true;
        $folder = $group;

        if (trim($folder) == '') {
            $mode = 'notgroup';
        }

        switch ($mode) {
            case 'notgroup':
                $folders = $this->_folders($this->_root);

                for ($i = 0, $n = \count($folders); $i < $n; $i++) {
                    if ($folders[$i] != $folder) {
                        $return |= $this->_deleteFolder($this->_root . '/' . $folders[$i]);
                    }
                }

                break;

            case 'group':
            default:
                if (is_dir($this->_root . '/' . $folder)) {
                    $return = $this->_deleteFolder($this->_root . '/' . $folder);
                }

                break;
        }

        return (bool) $return;
    }

    /**
     * Garbage collect expired cache data
     *
     * @return  boolean
     *
     * @since   1.7.0
     */
    public function gc()
    {
        $result = true;

        // Files older than lifeTime get deleted from cache
        $files = $this->_filesInFolder($this->_root, '', true, true, ['.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html']);

        foreach ($files as $file) {
            $time = @filemtime($file);

            if (($time + $this->_lifetime) < $this->_now || empty($time)) {
                File::invalidateFileCache($file);
                $result |= @unlink($file);
            }
        }

        return (bool) $result;
    }

    /**
     * Lock cached item
     *
     * @param   string   $id        The cache data ID
     * @param   string   $group     The cache data group
     * @param   integer  $locktime  Cached item max lock time
     *
     * @return  mixed  Boolean false if locking failed or an object containing properties lock and locklooped
     *
     * @since   1.7.0
     */
    public function lock($id, $group, $locktime)
    {
        $returning             = new \stdClass();
        $returning->locklooped = false;

        $looptime  = $locktime * 10;
        $path      = $this->_getFilePath($id, $group);
        $_fileopen = @fopen($path, 'c+b');

        if (!$_fileopen) {
            $returning->locked = false;

            return $returning;
        }

        $data_lock = (bool) @flock($_fileopen, LOCK_EX | LOCK_NB);

        if ($data_lock === false) {
            $lock_counter = 0;

            // Loop until you find that the lock has been released.
            // That implies that data get from other thread has finished
            while ($data_lock === false) {
                if ($lock_counter > $looptime) {
                    break;
                }

                usleep(100);
                $data_lock = (bool) @flock($_fileopen, LOCK_EX | LOCK_NB);
                $lock_counter++;
            }

            $returning->locklooped = true;
        }

        if ($data_lock === true) {
            // Remember resource, flock release lock if you unset/close resource
            $this->_locked_files[$path] = $_fileopen;
        }

        $returning->locked = $data_lock;

        return $returning;
    }

    /**
     * Unlock cached item
     *
     * @param   string  $id     The cache data ID
     * @param   string  $group  The cache data group
     *
     * @return  boolean
     *
     * @since   1.7.0
     */
    public function unlock($id, $group = null)
    {
        $path = $this->_getFilePath($id, $group);

        if (isset($this->_locked_files[$path])) {
            $ret = (bool) @flock($this->_locked_files[$path], LOCK_UN);
            @fclose($this->_locked_files[$path]);
            unset($this->_locked_files[$path]);

            return $ret;
        }

        return true;
    }

    /**
     * Check if a cache object has expired
     *
     * Using @ error suppressor here because between if we did a file_exists() and then filemsize() there will
     * be a little time space when another process can delete the file and then you get PHP Warning
     *
     * @param   string  $id     Cache ID to check
     * @param   string  $group  The cache data group
     *
     * @return  boolean  True if the cache ID is valid
     *
     * @since   1.7.0
     */
    protected function _checkExpire($id, $group)
    {
        $path = $this->_getFilePath($id, $group);

        // Check prune period
        if (file_exists($path)) {
            $time = @filemtime($path);

            if (($time + $this->_lifetime) < $this->_now || empty($time)) {
                File::invalidateFileCache($path);
                @unlink($path);

                return false;
            }

            // If, right now, the file does not exist then return false
            if (@filesize($path) == 0) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get a cache file path from an ID/group pair
     *
     * @param   string  $id     The cache data ID
     * @param   string  $group  The cache data group
     *
     * @return  boolean|string  The path to the data object or boolean false if the cache directory does not exist
     *
     * @since   1.7.0
     */
    protected function _getFilePath($id, $group)
    {
        $name = $this->_getCacheId($id, $group);
        $dir  = $this->_root . '/' . $group;

        // If the folder doesn't exist try to create it
        if (!is_dir($dir)) {
            // Make sure the index file is there
            $indexFile = $dir . '/index.html';
            @mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
        }

        // Make sure the folder exists
        if (!is_dir($dir)) {
            return false;
        }

        return $dir . '/' . $name . '.php';
    }

    /**
     * Quickly delete a folder of files
     *
     * @param   string  $path  The path to the folder to delete.
     *
     * @return  boolean
     *
     * @since   1.7.0
     */
    protected function _deleteFolder($path)
    {
        // Sanity check
        if (!$path || !is_dir($path) || empty($this->_root)) {
            // Bad programmer! Bad, bad programmer!
            Log::add(__METHOD__ . ' ' . Text::_('JLIB_FILESYSTEM_ERROR_DELETE_BASE_DIRECTORY'), Log::WARNING, 'jerror');

            return false;
        }

        $path = $this->_cleanPath($path);

        // Check to make sure path is inside cache folder, we do not want to delete Joomla root!
        $pos = strpos($path, $this->_cleanPath($this->_root));

        if ($pos === false || $pos > 0) {
            Log::add(__METHOD__ . ' ' . Text::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', __METHOD__, $path), Log::WARNING, 'jerror');

            return false;
        }

        // Remove all the files in folder if they exist; disable all filtering
        $files = $this->_filesInFolder($path, '.', false, true, [], []);

        if (!empty($files) && !\is_array($files)) {
            File::invalidateFileCache($files);
            if (@unlink($files) !== true) {
                return false;
            }
        } elseif (!empty($files) && \is_array($files)) {
            foreach ($files as $file) {
                $file = $this->_cleanPath($file);

                // In case of restricted permissions we delete it one way or the other as long as the owner is either the webserver or the ftp
                File::invalidateFileCache($file);

                if (@unlink($file) !== true) {
                    Log::add(__METHOD__ . ' ' . Text::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', basename($file)), Log::WARNING, 'jerror');

                    return false;
                }
            }
        }

        // Remove sub-folders of folder; disable all filtering
        $folders = $this->_folders($path, '.', false, true, [], []);

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

        // In case of restricted permissions we zap it one way or the other as long as the owner is either the webserver or the ftp
        if (@rmdir($path)) {
            return true;
        }

        Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_FOLDER_DELETE', $path), Log::WARNING, 'jerror');

        return false;
    }

    /**
     * Function to strip additional / or \ in a path name
     *
     * @param   string  $path  The path to clean
     * @param   string  $ds    Directory separator (optional)
     *
     * @return  string  The cleaned path
     *
     * @since   1.7.0
     */
    protected function _cleanPath($path, $ds = DIRECTORY_SEPARATOR)
    {
        $path = trim($path);

        if (empty($path)) {
            return $this->_root;
        }

        // Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
        $path = preg_replace('#[/\\\\]+#', $ds, $path);

        return $path;
    }

    /**
     * Utility function to quickly read the files in a folder.
     *
     * @param   string   $path           The path of the folder to read.
     * @param   string   $filter         A filter for file names.
     * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
     * @param   boolean  $fullpath       True to return the full path to the file.
     * @param   array    $exclude        Array with names of files which should not be shown in the result.
     * @param   array    $excludefilter  Array of folder names to exclude
     *
     * @return  array  Files in the given folder.
     *
     * @since   1.7.0
     */
    protected function _filesInFolder(
        $path,
        $filter = '.',
        $recurse = false,
        $fullpath = false,
        $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'],
        $excludefilter = ['^\..*', '.*~']
    ) {
        $arr = [];

        // Check to make sure the path valid and clean
        $path = $this->_cleanPath($path);

        // Is the path a folder?
        if (!is_dir($path)) {
            Log::add(__METHOD__ . ' ' . Text::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', __METHOD__, $path), Log::WARNING, 'jerror');

            return false;
        }

        // Read the source directory.
        if (!($handle = @opendir($path))) {
            return $arr;
        }

        if (\count($excludefilter)) {
            $excludefilter = '/(' . implode('|', $excludefilter) . ')/';
        } else {
            $excludefilter = '';
        }

        while (($file = readdir($handle)) !== false) {
            if (($file != '.') && ($file != '..') && (!\in_array($file, $exclude)) && (!$excludefilter || !preg_match($excludefilter, $file))) {
                $dir   = $path . '/' . $file;
                $isDir = is_dir($dir);

                if ($isDir) {
                    if ($recurse) {
                        if (\is_int($recurse)) {
                            $arr2 = $this->_filesInFolder($dir, $filter, $recurse - 1, $fullpath);
                        } else {
                            $arr2 = $this->_filesInFolder($dir, $filter, $recurse, $fullpath);
                        }

                        $arr = array_merge($arr, $arr2);
                    }
                } else {
                    if (preg_match("/$filter/", $file)) {
                        if ($fullpath) {
                            $arr[] = $path . '/' . $file;
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
     * @param   string   $path           The path of the folder to read.
     * @param   string   $filter         A filter for folder names.
     * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
     * @param   boolean  $fullpath       True to return the full path to the folders.
     * @param   array    $exclude        Array with names of folders which should not be shown in the result.
     * @param   array    $excludefilter  Array with regular expressions matching folders which should not be shown in the result.
     *
     * @return  array  Folders in the given folder.
     *
     * @since   1.7.0
     */
    protected function _folders(
        $path,
        $filter = '.',
        $recurse = false,
        $fullpath = false,
        $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'],
        $excludefilter = ['^\..*']
    ) {
        $arr = [];

        // Check to make sure the path valid and clean
        $path = $this->_cleanPath($path);

        // Is the path a folder?
        if (!is_dir($path)) {
            Log::add(__METHOD__ . ' ' . Text::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', __METHOD__, $path), Log::WARNING, 'jerror');

            return false;
        }

        // Read the source directory
        if (!($handle = @opendir($path))) {
            return $arr;
        }

        if (\count($excludefilter)) {
            $excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
        } else {
            $excludefilter_string = '';
        }

        while (($file = readdir($handle)) !== false) {
            if (
                ($file != '.') && ($file != '..')
                && (!\in_array($file, $exclude))
                && (empty($excludefilter_string) || !preg_match($excludefilter_string, $file))
            ) {
                $dir   = $path . '/' . $file;
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
                        if (\is_int($recurse)) {
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
