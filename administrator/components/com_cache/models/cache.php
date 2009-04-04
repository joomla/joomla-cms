<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Model Class used to hold Cache data
 *
 * @package		Joomla.Administrator
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
	protected $_data = null;

	/**
	 * Group total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _populateState()
	{
		$app =& JFactory::getApplication();
		
		$clientId = JRequest::getInt('client', 0);
		$this->setState('clientId', $clientId == 1 ? 1 : 0);
		
		$client	=& JApplicationHelper::getClientInfo($clientId);
		$this->setState('client', $client);
		
		$this->setState('path', $client->path.DS.'cache');
		
		$context	= 'com_cache.cache.';
		
		$start = $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int');
		$limit = $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit', 20), 'int');
		
		$this->setState('list.start', $start);
		$this->setState('list.limit', $limit);
	}

	/**
	 * Parse $path for cache file groups. Any files identifided as cache are logged
	 * in a group and stored in $this->items.
	 *
	 * @return	array
	 * @access	protected
	 */
	protected function _parse()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path = $this->getState('path');
		$folders = JFolder::folders($path);
		$data = array();

		foreach ($folders as $folder)
		{
			$files = array();
			$files = JFolder::files($path);
			$item = new CacheItem($folder);

			foreach ($files as $file)
			{
				$item->updateSize(filesize($path.DS.$folder.DS.$file)/1024);
			}
			$data[] = $item;
		}
		
		return $data;
	}

	/**
	 * Method to get cache data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_data = $this->_parse();
		}

		return $this->_data;
	}
	
	/**
	 * Method to get client data
	 *
	 * @access public
	 * @return array
	 */
	public function getClient()
	{
		return $this->getState('client');
	}	

	/**
	 * Get the number of current Cache Groups
	 *
	 * @access public
	 * @return int
	 */
	public function getTotal()
	{
		if (empty($this->_total))
		{
			$this->_total = count($this->getData());
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the cache
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('list.start'), $this->getState('list.limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Clean out a cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param String $group
	 */
	public function clean($group = '')
	{
		$cache =& JFactory::getCache('', 'callback', 'file');
		$cache->clean($group);
	}

	public function cleanlist($array)
	{
		foreach ($array as $group) {
			$this->clean($group);
		}
	}
	
	public function purge()
	{
		$cache =& JFactory::getCache('');
		return $cache->gc();	
	}
}

 /**
  * This Class is used by CacheData to store group cache data.
  *
  * @package		Joomla.Administrator
  * @subpackage	Cache
  * @since		1.5
  */
class CacheItem
{
	public $group 	= '';
	public $size 	= 0;
	public $count 	= 0;

	public function __construct($group)
	{
		$this->group = $group;
	}

	public function updateSize($size)
	{
		$this->size = number_format($this->size + $size, 2);
		$this->count++;
	}
}
