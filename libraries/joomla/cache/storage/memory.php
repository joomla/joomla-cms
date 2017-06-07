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
 * Native PHP in-memory cache storage handler.
 *
 * @since  __DEPLOY_VERSION__
 */
class JCacheStorageMemory extends JCacheStorage
{
	/**
	 * Cache buffers.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $buffers = array();

	/**
	 * Maximum number of buffers permitted.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	private $maxBuffers = 0;

	/**
	 * A simple integer that stands in for a clock to indicate how old an item is.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	private $clock = 0;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Optional parameters
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->maxBuffers = isset($options['maxBuffers']) ? $options['maxBuffers'] : 100;
	}

	/**
	 * Get cached data by ID and group.
	 *
	 * @param   string   $id         The cache data ID
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($id, $group, $checkTime = true)
	{
		if (!isset($this->buffers[$group][$id]))
		{
			return false;
		}

		// Update the clock to show when the entry was last used.
		$this->buffers[$group][$id]['clock'] = $this->clock++;

		return unserialize($this->buffers[$group][$id]['value']);
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAll()
	{
		return $this->buffers;
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function store($id, $group, $data)
	{
		// If we have enough room, store the data.
		if (!isset($this->buffers[$group]) || count($this->buffers[$group]) < $this->maxBuffers)
		{
			$this->buffers[$group][$id] = array(
				'clock' => $this->clock++,
				'value' => serialize($data),
				);

			return true;
		}

		$lru = 0;
		$min = $this->clock;

		// Find the least recently-used item.
		foreach ($this->buffers[$group] as $key => $entry)
		{
			if ($entry['clock'] < $min)
			{
				$lru = $key;
				$min = $entry['clock'];
			}
		}

		// Remove the least-recently used item.
		unset($this->buffers[$group][$lru]);

		// Insert the new item.
		$this->buffers[$group][$id] = array(
			'clock' => $this->clock++,
			'value' => serialize($data),
			);

		return true;
	}

	/**
	 * Remove a cached data entry by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove($id, $group)
	{
		if (!isset($this->buffers[$group][$id]))
		{
			return false;
		}

		unset($this->buffers[$group][$id]);

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
	 * @since   __DEPLOY_VERSION__
	 */
	public function clean($group, $mode = null)
	{
		// Clear the specified group.
		if ($mode == 'group')
		{
			unset($this->buffers[$group]);

			return true;
		}

		// Not a valid mode.
		if ($mode != 'notgroup')
		{
			return false;
		}

		// Clear all buffers except the one specified.
		foreach ($this->buffers as $key => $buffer)
		{
			if ($key != $group)
			{
				unset($this->buffers[$key]);
			}
		}

		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc()
	{
		return true;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Lock cached item
	 *
	 * @param   string   $id        The cache data ID
	 * @param   string   $group     The cache data group
	 * @param   integer  $locktime  Cached item max lock time
	 *
	 * @return  mixed  Boolean false if locking failed or an object containing properties locked and locklooped
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function lock($id, $group, $locktime)
	{
		$returning = new stdClass;
		$returning->locklooped = false;
		$returning->locked = false;

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
	 * @since   __DEPLOY_VERSION__
	 */
	public function unlock($id, $group = null)
	{
		return true;
	}
}
