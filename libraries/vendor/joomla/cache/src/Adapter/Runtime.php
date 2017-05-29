<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Adapter;

use Joomla\Cache\AbstractCacheItemPool;
use Joomla\Cache\Item\Item;
use Psr\Cache\CacheItemInterface;

/**
 * Runtime memory cache driver.
 *
 * @since  1.0
 */
class Runtime extends AbstractCacheItemPool
{
	/**
	 * Database of cached items, we use ArrayObject so it can be easily passed by reference
	 *
	 * @var    \ArrayObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   mixed  $options  An options array, or an object that implements \ArrayAccess
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->db = new \ArrayObject;
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return  boolean  True if the pool was successfully cleared. False if there was an error.
	 *
	 * @since   1.0
	 */
	public function clear()
	{
		// Replace the db with a new blank array
		$clearData = $this->db->exchangeArray(array());
		unset($clearData);

		return true;
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 *
	 * @param   string  $key  The key for which to return the corresponding Cache Item.
	 *
	 * @return  CacheItemInterface  The corresponding Cache Item.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($key)
	{
		$item = new Item($key);

		if ($this->hasItem($key))
		{
			$item->set($this->db[$key]);
		}

		return $item;
	}

	/**
	 * Removes the item from the pool.
	 *
	 * @param   string  $key  The key to delete.
	 *
	 * @return  boolean  True if the item was successfully removed. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deleteItem($key)
	{
		if ($this->hasItem($key))
		{
			$newCache = array_diff_key($this->db->getArrayCopy(), array($key => $key));
			$this->db->exchangeArray($newCache);
		}

		return true;
	}

	/**
	 * Persists a cache item immediately.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  boolean  True if the item was successfully persisted. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save(CacheItemInterface $item)
	{
		$this->db[$item->getKey()] = $item->get();

		return true;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param   string  $key  The key for which to check existence.
	 *
	 * @return  boolean  True if item exists in the cache, false otherwise.
	 *
	 * @since   1.0
	 */
	public function hasItem($key)
	{
		return array_key_exists($key, $this->db);
	}

	/**
	 * Test to see if the CacheItemPoolInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return true;
	}
}
