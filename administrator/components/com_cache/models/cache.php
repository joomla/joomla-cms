<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Cache Model
 *
 * @since  1.6
 */
class CacheModelCache extends JModelList
{
	/**
	 * An Array of CacheItems indexed by cache group ID
	 *
	 * @var  array
	 */
	protected $_data = array();

	/**
	 * Group total
	 *
	 * @var  integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var  JPagination
	 */
	protected $_pagination = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Field for ordering.
	 * @param   string  $direction  Direction of ordering.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$clientId = $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', 0, 'int');
		$this->setState('clientId', $clientId == 1 ? 1 : 0);

		$client = JApplicationHelper::getClientInfo($clientId);
		$this->setState('client', $client);

		parent::populateState('group', 'asc');
	}

	/**
	 * Method to get cache data
	 *
	 * @return  array
	 */
	public function getData()
	{
		if (empty($this->_data))
		{
			$cache = $this->getCache();
			$data = $cache->getAll();

			if ($data != false)
			{
				$this->_data = $data;
				$this->_total = count($data);

				if ($this->_total)
				{
					// Apply custom ordering.
					$ordering = $this->getState('list.ordering');
					$direction = ($this->getState('list.direction') == 'asc') ? 1 : (-1);

					$this->_data = ArrayHelper::sortObjects($data, $ordering, $direction);

					// Apply custom pagination.
					if ($this->_total > $this->getState('list.limit') && $this->getState('list.limit'))
					{
						$this->_data = array_slice($this->_data, $this->getState('list.start'), $this->getState('list.limit'));
					}
				}
			}
			else
			{
				$this->_data = array();
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get cache instance.
	 *
	 * @return  JCacheController
	 */
	public function getCache()
	{
		$conf = JFactory::getConfig();

		$options = array(
			'defaultgroup' => '',
			'storage'      => $conf->get('cache_handler', ''),
			'caching'      => true,
			'cachebase'    => ($this->getState('clientId') == 1) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
		);

		return JCache::getInstance('', $options);
	}

	/**
	 * Method to get client data.
	 *
	 * @return  array
	 */
	public function getClient()
	{
		return $this->getState('client');
	}

	/**
	 * Get the number of current Cache Groups.
	 *
	 * @return  integer
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
	 * Method to get a pagination object for the cache.
	 *
	 * @return  JPagination
	 */
	public function getPagination()
	{
		if (empty($this->_pagination))
		{
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('list.start'), $this->getState('list.limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Clean out a cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param   string  $group  Cache group name.
	 *
	 * @return  void
	 */
	public function clean($group = '')
	{
		$this->getCache()->clean($group);
	}

	/**
	 * Purge an array of cache groups.
	 *
	 * @param   array  $array  Array of cache group names.
	 *
	 * @return  void
	 */
	public function cleanlist($array)
	{
		foreach ($array as $group)
		{
			$this->clean($group);
		}
	}

	/**
	 * Purge all cache items.
	 *
	 * @return  boolean  True if successful; false otherwise.
	 */
	public function purge()
	{
		return JFactory::getCache('')->gc();
	}
}
