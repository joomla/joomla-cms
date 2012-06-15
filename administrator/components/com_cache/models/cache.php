<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Cache Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @since		1.6
 */
class CacheModelCache extends JModelList
{
	/**
	 * An Array of CacheItems indexed by cache group ID
	 *
	 * @var Array
	 */
	protected $_data = array();

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
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		$clientId = $this->getUserStateFromRequest($this->context.'.filter.client_id', 'filter_client_id', 0, 'int');
		$this->setState('clientId', $clientId == 1 ? 1 : 0);

		$client	= JApplicationHelper::getClientInfo($clientId);
		$this->setState('client', $client);

		parent::populateState('group', 'asc');
	}


	/**
	 * Method to get cache data
	 *
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->_data)) {
		    $cache 	= $this->getCache();
			$data 	= $cache->getAll();

			if ($data != false) {
				$this->_data = $data;
				$this->_total = count($data);

				if ($this->_total) {
					// Apply custom ordering
					$ordering 	= $this->getState('list.ordering');
					$direction 	= ($this->getState('list.direction') == 'asc') ? 1 : -1;

					jimport('joomla.utilities.arrayhelper');
					$this->_data = JArrayHelper::sortObjects($data, $ordering, $direction);

					// Apply custom pagination
					if ($this->_total > $this->getState('list.limit') && $this->getState('list.limit')) {
						$this->_data = array_slice($this->_data, $this->getState('list.start'), $this->getState('list.limit'));
					}
				}
			} else {
				$this->_data = array();
			}
		}
		return $this->_data;
	}



	/**
	 * Method to get cache instance
	 *
	 * @return object
	 */
	public function getCache()
	{
		$conf = JFactory::getConfig();

		$options = array(
			'defaultgroup'	=> '',
			'storage' 		=> $conf->get('cache_handler', ''),
			'caching'		=> true,
			'cachebase'		=> ($this->getState('clientId') == 1) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
		);

		$cache = JCache::getInstance('', $options);

		return $cache;
	}

	/**
	 * Method to get client data
	 *
	 * @return array
	 */
	public function getClient()
	{
		return $this->getState('client');
	}

	/**
	 * Get the number of current Cache Groups
	 *
	 * @return int
	 */
	public function getTotal()
	{
		if (empty($this->_total)) {
			$this->_total = count($this->getData());
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the cache
	 *
	 * @return integer
	 */
	public function getPagination()
	{
		if (empty($this->_pagination)) {
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
		$cache = $this->getCache();
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
		$cache = JFactory::getCache('');
		return $cache->gc();
	}
}
