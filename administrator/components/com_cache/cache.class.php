<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Class used to hold Cache data
 *
 * @package		Joomla
 * @subpackage	Cache
 * @since		1.5
 */
class CacheData extends JObject
{
	/**
	 * An Array of CacheItems indexed by cache group ID
	 *
	 * @access protected
	 * @var Array
	 */
	var $_items = null;

	/**
	 * The cache path
	 *
	 * @access protected
	 * @var String
	 */
	var $_path = null;

	/**
	 * Class constructor
	 *
	 * @access protected
	 */
	function __construct($path)
	{
		$this->_path = $path;

		$this->_parse();
	}

	/**
	 * Parse $path for cache file groups. Any files identifided as cache are logged
	 * in a group and stored in $this->items.
	 *
	 * @access private
	 * @param String $path
	 */
	function _parse( )
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$folders = JFolder::folders($this->_path);

		foreach($folders as $folder)
		{
			$files = array();
			$files = JFolder::files($this->_path.DS.$folder);
			$this->_items[$folder] = new CacheItem( $folder );

			foreach($files as $file)
			{
				$this->_items[$folder]->updateSize( filesize( $this->_path.DS.$folder.DS.$file )/ 1024 );
			}
		}
	}

	/**
	 * Get the number of current Cache Groups
	 *
	 * @access public
	 * @return int
	 */
	function getGroupCount() {
		return count($this->_items);
	}

	/**
	 * Retrun an Array containing a sub set of the total
	 * number of Cache Groups as defined by the params.
	 *
	 * @access public
	 * @param Int $start
	 * @param Int $limit
	 * @return Array
	 */
	function getRows( $start, $limit )
	{
		$i=0;
		if(count($this->_items) == 0) return null;

		foreach($this->_items as $item) {
			if($i >= $start && $i < $start+$limit)
				$rows[] = $item;
			$i++;
		}
		return $rows;
	}

	/**
	 * Clean out a cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param String $group
	 */
	function cleanCache( $group='' )
	{
		$cache =& JFactory::getCache();
		$cache->clean( $group );
	}

	function cleanCacheList( $array )
	{
		foreach ($array as $group) {
			$this->cleanCache( $group );
		}
	}
}

 /**
 * This Class is used by CacheData to store group cache data.
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Cache
 * @since		1.5
 */
class CacheItem
{
	var $group 	= "";
	var $size 	= 0;
	var $count 	= 0;

	function CacheItem ( $group ) {
		$this->group = $group;
	}

	function updateSize( $size )
	{
		$this->size = number_format($this->size + $size, 2);
		$this->count++;
	}
}