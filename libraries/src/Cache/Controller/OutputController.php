<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Controller;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Log\Log;

/**
 * Joomla Cache output type object
 *
 * @since  11.1
 */
class OutputController extends CacheController
{
	/**
	 * Cache data ID
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_id;

	/**
	 * Cache data group
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_group;

	/**
	 * Object to test locked state
	 *
	 * @var    \stdClass
	 * @since  11.1
	 * @deprecated  4.0
	 */
	protected $_locktest = null;

	/**
	 * Start the cache
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function start($id, $group = null)
	{
		Log::add(
			__METHOD__ . '() is deprecated.',
			Log::WARNING,
			'deprecated'
		);

		// If we have data in cache use that.
		$data = $this->cache->get($id, $group);

		$this->_locktest             = new \stdClass;
		$this->_locktest->locked     = null;
		$this->_locktest->locklooped = null;

		if ($data === false)
		{
			$this->_locktest = $this->cache->lock($id, $group);

			if ($this->_locktest->locked == true && $this->_locktest->locklooped == true)
			{
				$data = $this->cache->get($id, $group);
			}
		}

		if ($data !== false)
		{
			$data = unserialize(trim($data));
			echo $data;

			if ($this->_locktest->locked == true)
			{
				$this->cache->unlock($id, $group);
			}

			return true;
		}

		// Nothing in cache... let's start the output buffer and start collecting data for next time.
		if ($this->_locktest->locked == false)
		{
			$this->_locktest = $this->cache->lock($id, $group);
		}

		ob_start();
		ob_implicit_flush(false);

		// Set id and group placeholders
		$this->_id = $id;
		$this->_group = $group;

		return false;
	}

	/**
	 * Stop the cache buffer and store the cached data
	 *
	 * @return  boolean  True if the cache data was stored
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function end()
	{
		Log::add(
			__METHOD__ . '() is deprecated.',
			Log::WARNING,
			'deprecated'
		);

		// Get data from output buffer and echo it
		$data = ob_get_clean();
		echo $data;

		// Get the ID and group and reset the placeholders
		$id           = $this->_id;
		$group        = $this->_group;
		$this->_id    = null;
		$this->_group = null;

		// Get the storage handler and store the cached data
		$ret = $this->cache->store(serialize($data), $id, $group);

		if ($this->_locktest->locked == true)
		{
			$this->cache->unlock($id, $group);
		}

		return $ret;
	}

	/**
	 * Get stored cached data by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed  Boolean false on no result, cached object otherwise
	 *
	 * @since   11.1
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
	 * @since   11.1
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
