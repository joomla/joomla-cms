<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Class to cache a hash (used for ACL caching)
 * 
 * Class Based Heavily on Hashed_Cache_Lite by: Mike Benoit <ipso@snappymail.ca>
 * phpGACL - Generic Access Control List - Hashed Directory Caching.
 * 
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheHash extends JCache
{
	/**
	 * Constructor
	 *
	 * @access protected
	 * @param array $options options
	 */
	function _construct(&$options) {
		 parent::_construct($options);
	}

	/**
     * Enable/disbale caching
     *
     * @access public
     * @param boolean $enable If true enable caching.
     */
	function setCaching($enable)  {
		$this->_caching = $enable;
	}

	/**
	 * Make a file name (with path)
	 *
	 * @param string $id cache id
	 * @param string $group name of the group
	 * @access private
	 */
	function _setFileName($id, $group)
	{
		// CRC32 with SUBSTR is still faster then MD5.
		$encoded_id = substr(crc32($id),1);
		// $encoded_id = md5($id);
		
		// Generate just the directory, so it can be created.
		// Groups will have their own top level directory, for quick/easy purging of an entire group.
		$dir = $this->_cacheDir.$group.DS.substr($encoded_id,0,3);
		$this->_create_dir_structure($dir);
		
		$this->_file = $dir.DS.$encoded_id;
	}
	
	/**
	 * Create full directory structure, Ripped straight from the Smarty Template
	 * engine. Version:     2.3.0 Copyright:   2001,2002 ispi of Lincoln, Inc.
	 *
	 * @param string $dir Full directory.
	 * @access private
	 */
	function _create_dir_structure($dir)
	{
		if (!@file_exists($dir)) {
			$dir_parts = preg_split('![\/]+!', $dir, -1, PREG_SPLIT_NO_EMPTY);
			$new_dir = ($dir{0} == DS) ? DS : '';
			foreach ($dir_parts as $dir_part) {
				$new_dir .= $dir_part;
				if (!file_exists($new_dir) && !mkdir($new_dir, 0771)) {
					JError::raiseError( -3, 'JCacheHash::_create_dir_structure : problem creating directory \"$dir\" !');   
					return false;
				}
				$new_dir .= DS;
			}
		}
	}
	
	function _remove_dir_structure($dir,$remove_dir = false)
	{
		if (in_array(substr($dir,-1),array(DS,'/','\\'))) {
			$dir = substr($dir,0,-1);
		}
		
		if (!($dh = opendir($dir))) {
			JError::raiseError( -4, 'JCacheHash::_remove_dir_structure: Unable to open cache directory !');
			return false;
		}
		
		while ($file = readdir($dh)) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$file = $dir . DS . $file;
			if (is_dir($file)) {
				$this->_remove_dir_structure($file,true);
				continue;
			}
			if (is_file($file)) {
				if (!@unlink($file)) {
					closedir($dh);
					JError::raiseError( -3, 'JCacheHash::_remove_dir_structure: Unable to remove cache !');
					return false;
				}
				continue;
			}
		}
		
		closedir($dh);
		
		if ($remove_dir) {
			clearstatcache();
			if (!@rmdir($dir)) {
				JError::raiseError( -4, 'JCacheHash::_remove_dir_structure: Unable to remove cache directory !');
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Clean the cache
	 * if no group is specified all cache files will be destroyed else only
	 * cache files of the specified group will be destroyed
	 *
	 * @param string $group name of the cache group
	 * @return boolean true if no problem
	 * @access public
	 */
	function clean($group = false)
	{
		if ($group) {
			$motif = $this->_cacheDir.$group.'/';
			
			if ($this->_memoryCaching) {
				foreach ($this->_memoryCachingArray as $key => $value) {
					if (strpos($key, $motif, 0)) {
						unset($this->_memoryCachingArray[$key]);
					}
				}
				$this->_memoryCachingCounter = count($this->_memoryCachingArray);
				if ($this->_onlyMemoryCaching) {
					return true;
				}
			}
			
			return $this->_remove_dir_structure($motif);
		}
		
		if ($this->_memoryCaching) {
			$this->_memoryCachingArray   = array();
			$this->_memoryCachingCounter = 0;
			if ($this->_onlyMemoryCaching) {
				return true;
			}
		}
		
		if (!($dh = opendir($this->_cacheDir))) {
			JError::raiseError( -4, 'JCacheHash::clean: Unable to open cache directory !');
			return false;
		}
		
		while ($file = readdir($dh)) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$file = $this->_cacheDir . $file;
			if (is_dir($file) && !$this->_remove_dir_structure($file,true)) {
				return false;
			}
		}
		
		return true;
	}
}
?>