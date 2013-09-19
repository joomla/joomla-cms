<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cache Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       3.2
 */
class CacheModelCache extends JModelCmslist
{
	/**
	 * An Array of CacheItems indexed by cache group ID
	 *
	 * @var Array
	 * @since  3.2
	 */
	protected $data = array();

	/**
	 * Group total
	 *
	 * @var integer
	 * @since  3.2
	 */
	protected $total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 * @since  3.2
	 */
	protected $pagination = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   3.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$clientId = $this->getUserStateFromRequest($this->context.'.filter.client_id', 'filter_client_id', 0, 'int');
		$this->state->set('clientId', $clientId == 1 ? 1 : 0);

		$client	= JApplicationHelper::getClientInfo($clientId);
		$this->state->set('client', $client);

		parent::populateState('group', 'asc');
	}

	/**
	 * Method to get cache data
	 *
	 * @return array
	 *
	 * @since  3.2
	 */
	public function getItems()
	{
		if (empty($this->data))
		{
			$cache = $this->getCache();
			$data  = $cache->getAll();

			if ($data != false)
			{
				$this->data = $data;
				$this->total = count($data);

				if ($this->total)
				{
					// Apply custom ordering
					$ordering 	= $this->state->get('list.ordering');
					$direction 	= ($this->state->get('list.direction') == 'asc') ? 1 : -1;

					jimport('joomla.utilities.arrayhelper');
					$this->data = JArrayHelper::sortObjects($data, $ordering, $direction);

					// Apply custom pagination
					if ($this->total > $this->state->get('list.limit') && $this->getState('list.limit'))
					{
						$this->data = array_slice($this->data, $this->state->get('list.start'), $this->state->get('list.limit'));
					}
				}
			}
			else
			{
				$this->data = array();
			}
		}

		return $this->data;
	}

	/**
	 * Method to get cache instance
	 *
	 * @return object
	 *
	 * @since  3.2
	 */
	public function getCache()
	{
		$conf = JFactory::getConfig();

		$options = array(
			'defaultgroup'	=> '',
			'storage' 		=> $conf->get('cache_handler', ''),
			'caching'		=> true,
			'cachebase'		=> ($this->state->get('clientId') == 1) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
		);

		$cache = JCache::getInstance('', $options);

		return $cache;
	}

	/**
	 * Method to get client data
	 *
	 * @return array
	 *
	 * @since  3.2
	 */
	public function getClient()
	{
		return $this->state->get('client');
	}

	/**
	 * Get the number of current Cache Groups
	 *
	 * @return  integer
	 *
	 * @since  3.2
	 */
	public function getTotal()
	{
		if (empty($this->total))
		{
			$this->total = count($this->getItems());
		}

		return $this->total;
	}

	/**
	 * Method to get a pagination object for the cache
	 *
	 * @return  integer
	 *
	 * @since  3.2
	 */
	public function getPagination()
	{
		if (empty($this->pagination))
		{
			$this->pagination = new JPagination($this->getTotal(), $this->state->get('list.start'), $this->state->get('list.limit'));
		}

		return $this->pagination;
	}

	/**
	 * Clean out a cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param  string  $group  The name of the group
	 *
	 * @since  3.2
	 */
	public function clean($group = '')
	{
		$cache = $this->getCache();
		$cache->clean($group);
	}

	/**
	 * Clean out a list of cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param  array  $array  The array of groups to clean
	 * @param  array  $options  Options from the controller (optional).
	 *
	 * @since  3.2
	 */
	public function cleanlist($array, $option = null)
	{
		if ($option[0] == 'purge')
		{
			return $this->purge();
		}
		foreach ($array as $group)
		{
			$this->clean($group);
		}

		return true;
	}

	/**
	 * Garbage collect all expired cache files found
	 *
	 * @param  array  $array  The array of groups to clean
	 *
	 * @since  3.2
	 */
	public function purge()
	{
		$cache = JFactory::getCache('');

		return $cache->gc();
	}
}
