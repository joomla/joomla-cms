<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Controller;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheController;

/**
 * Joomla Cache output type object
 *
 * @since  1.7.0
 */
class OutputController extends CacheController
{
	/**
	 * Cache data ID
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $_id;

	/**
	 * Cache data group
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $_group;

	/**
	 * Get stored cached data by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed  Boolean false on no result, cached object otherwise
	 *
	 * @since   1.7.0
	 */
	public function get($id, $group = null)
	{
		$data = $this->cache->get($id, $group);

		if ($data === false)
		{
			$locktest = $this->cache->lock($id, $group);

			// If locklooped is true try to get the cached data again; it could exist now.
			if ($locktest->locked === true && $locktest->locklooped === true)
			{
				$data = $this->cache->get($id, $group);
			}

			if ($locktest->locked === true)
			{
				$this->cache->unlock($id, $group);
			}
		}

		// Check again because we might get it from second attempt
		if ($data !== false)
		{
			// Trim to fix unserialize errors
			$data = unserialize(trim($data));
		}

		return $data;
	}

	/**
	 * Store data to cache by ID and group
	 *
	 * @param   mixed    $data        The data to store
	 * @param   string   $id          The cache data ID
	 * @param   string   $group       The cache data group
	 * @param   boolean  $wrkarounds  True to use wrkarounds
	 *
	 * @return  boolean  True if cache stored
	 *
	 * @since   1.7.0
	 */
	public function store($data, $id, $group = null, $wrkarounds = true)
	{
		$locktest = $this->cache->lock($id, $group);

		if ($locktest->locked === false && $locktest->locklooped === true)
		{
			// We can not store data because another process is in the middle of saving
			return false;
		}

		$result = $this->cache->store(serialize($data), $id, $group);

		if ($locktest->locked === true)
		{
			$this->cache->unlock($id, $group);
		}

		return $result;
	}
}
