<?php
/**
 * @version		$Id: $
 * @package		Joomla
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
 * Model Class used to hold Cache data
 *
 * @package		Joomla
 * @subpackage	Cache
 * @since		1.6
 */
class CacheModelCache extends JModel
{
	/**
	 * An Array of CacheItems indexed by cache group ID
	 *
	 * @access protected
	 * @var Array
	 */
	var $_data = null;

	/**
	 * The cache path
	 *
	 * @access protected
	 * @var String
	 */
	var $_path = null;

	/**
	 * Group total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Class constructor
	 *
	 * @access protected
	 */
	function __construct( $path )
	{
		parent::__construct();

		global $mainframe, $option;

		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'));
		$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0 );

		$this->setPath($path);
	}

	/**
	 * Method to set contacts item path
	 *
	 * @access public
	 * @param string
	 */
	function setPath($path)
	{
		$this->_path = $path;
		$this->_data = null;
	}

	/**
	 * Method to get contacts item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_parse();
		}

		return $this->_data;
	}

	/**
	 * Parse $path for cache file groups. Any files identifided as cache are logged
	 * in a group and stored in $this->items.
	 *
	 * @access	private
	 */
	function _parse()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$folders = JFolder::folders($this->_path);

		foreach ($folders as $folder)
		{
			$files = array();
			$files = JFolder::files($this->_path.DS.$folder);
			$item = new CacheItem( $folder );

			foreach ($files as $file)
			{
				$item->updateSize( filesize( $this->_path.DS.$folder.DS.$file )/ 1024 );
			}
			$this->_data[] = $item;
		}
	}

	/**
	 * Get the number of current Cache Groups
	 *
	 * @access public
	 * @return int
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$this->getData();
			$this->_total = count($this->_data);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the cache
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Clean out a cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param String $group
	 */
	function clean( $group='' )
	{
		$cache =& JFactory::getCache();
		$cache->clean( $group );
	}

	function cleanlist( $array )
	{
		foreach ($array as $group) {
			$this->clean( $group );
		}
	}
}

 /**
  * This Class is used by CacheData to store group cache data.
  *
  * @package		Joomla
  * @subpackage	Cache
  * @since		1.5
  */
class CacheItem
{
	var $group 	= "";
	var $size 	= 0;
	var $count 	= 0;

	function CacheItem ( $group )
	{
		$this->group = $group;
	}

	function updateSize( $size )
	{
		$this->size = number_format( $this->size + $size, 2 );
		$this->count++;
	}
}