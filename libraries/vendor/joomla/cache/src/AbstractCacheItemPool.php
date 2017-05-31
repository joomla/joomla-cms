<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache;

use Joomla\Cache\Exception\InvalidArgumentException;
use Joomla\Cache\Item\HasExpirationDateInterface;
use Psr\Cache\CacheItemInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Joomla! Caching Class
 *
 * @since  1.0
 */
abstract class AbstractCacheItemPool implements CacheItemPoolInterface, CacheInterface
{
	/**
	 * The options for the cache object.
	 *
	 * @var    array|\ArrayAccess
	 * @since  1.0
	 */
	protected $options;

	/**
	 * The deferred items to store
	 *
	 * @var    Item\Item[]
	 * @since  1.0
	 */
	private $deferred = [];

	/**
	 * Constructor.
	 *
	 * @param   array|\ArrayAccess  $options  An options array, or an object that implements \ArrayAccess
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function __construct($options = [])
	{
		if (!($options instanceof \ArrayAccess || is_array($options)))
		{
			throw new InvalidArgumentException(sprintf('%s requires an options array or an object that implements \\ArrayAccess', get_class($this)));
		}

		$this->options = $options;
	}

	/**
	 * Returns a traversable set of cache items.
	 *
	 * @param   string[]  $keys  An indexed array of keys of items to retrieve.
	 *
	 * @return  array  A traversable collection of Cache Items keyed by the cache keys of each item.
	 *                 A Cache item will be returned for each key, even if that key is not found.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItems(array $keys = [])
	{
		$result = [];

		foreach ($keys as $key)
		{
			$result[$key] = $this->getItem($key);
		}

		return $result;
	}

	/**
	 * Get an option from the Cache instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   1.0
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * Removes multiple items from the pool.
	 *
	 * @param   array  $keys  An array of keys that should be removed from the pool.
	 *
	 * @return  boolean  True if the items were successfully removed. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deleteItems(array $keys)
	{
		foreach ($keys as $key)
		{
			if (!$this->deleteItem($key))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Set an option for the Cache instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Sets a cache item to be persisted later.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  boolean  False if the item could not be queued or if a commit was attempted and failed. True otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function saveDeferred(CacheItemInterface $item)
	{
		$this->deferred[$item->getKey()] = $item;

		return true;
	}

	/**
	 * Persists any deferred cache items.
	 *
	 * @return  boolean  True if all not-yet-saved items were successfully saved or there were none. False otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function commit()
	{
		$result = true;

		foreach ($this->deferred as $key => $deferred)
		{
			$saveResult = $this->save($deferred);

			if (true === $saveResult)
			{
				unset($this->deferred[$key]);
			}

			$result = $result && $saveResult;
		}

		return $result;
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param   string  $key      The unique key of this item in the cache.
	 * @param   mixed   $default  Default value to return if the key does not exist.
	 *
	 * @return  mixed  The value of the item from the cache, or $default in case of cache miss.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($key, $default = null)
	{
		$item = $this->getItem($key);

		if (!$item->isHit())
		{
			return $default;
		}

		return $item->get();
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param   string                 $key    The key of the item to store.
	 * @param   mixed                  $value  The value of the item to store, must be serializable.
	 * @param   null|int|DateInterval  $ttl    Optional. The TTL value of this item. If no value is sent and
	 *                                         the driver supports TTL then the library may set a default value
	 *                                         for it or let the driver take care of that.
	 *
	 * @return  boolean True on success and false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set($key, $value, $ttl = null)
	{
		$item = $this->getItem($key);
		$item->set($value);
		$item->expiresAfter($ttl);

		return $this->save($item);
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param   string  $key  The unique cache key of the item to delete.
	 *
	 * @return  boolean  True if the item was successfully removed. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete($key)
	{
		return $this->deleteItem($key);
	}

	/**
	 * Obtains multiple cache items by their unique keys.
	 *
	 * @param   iterable  $keys     A list of keys that can obtained in a single operation.
	 * @param   mixed     $default  Default value to return for keys that do not exist.
	 *
	 * @return  iterable  A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	public function getMultiple($keys, $default = null)
	{
		if (!is_array($keys))
		{
			if (!($keys instanceof \Traversable))
			{
				throw new InvalidArgumentException('$keys is neither an array nor Traversable');
			}

			$keys = iterator_to_array($keys, false);
		}

		$items = $this->getItems($keys);

		return $this->generateValues($default, $items);
	}

	/**
	 * Persists a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @param   iterable               $values  A list of key => value pairs for a multiple-set operation.
	 * @param   null|int|DateInterval  $ttl     Optional. The TTL value of this item. If no value is sent and
	 *                                          the driver supports TTL then the library may set a default value
	 *                                          for it or let the driver take care of that.
	 *
	 * @return  boolean  True on success and false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	public function setMultiple($values, $ttl = null)
	{
		if (!is_array($values))
		{
			if (!$values instanceof \Traversable)
			{
				throw new InvalidArgumentException('$values is neither an array nor Traversable');
			}
		}

		$keys        = [];
		$arrayValues = [];

		foreach ($values as $key => $value)
		{
			if (is_int($key))
			{
				$key = (string) $key;
			}

			$keys[]            = $key;
			$arrayValues[$key] = $value;
		}

		$items       = $this->getItems($keys);
		$itemSuccess = true;

		/** @var $item CacheItemInterface */
		foreach ($items as $key => $item)
		{
			$item->set($arrayValues[$key]);
			$item->expiresAfter($ttl);

			$itemSuccess = $itemSuccess && $this->saveDeferred($item);
		}

		return $itemSuccess && $this->commit();
	}

	/**
	 * Deletes multiple cache items in a single operation.
	 *
	 * @param   iterable  $keys  A list of string-based keys to be deleted.
	 *
	 * @return  boolean  True if the items were successfully removed. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	public function deleteMultiple($keys)
	{
		if (!is_array($keys))
		{
			if (!$keys instanceof \Traversable)
			{
				throw new InvalidArgumentException('$keys is neither an array nor Traversable');
			}

			$keys = iterator_to_array($keys, false);
		}

		return $this->deleteItems($keys);
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * NOTE: It is recommended that has() is only to be used for cache warming type purposes
	 * and not to be used within your live applications operations for get/set, as this method
	 * is subject to a race condition where your has() will return true and immediately after,
	 * another script can remove it making the state of your app out of date.
	 *
	 * @param   string  $key  The cache item key.
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	public function has($key)
	{
		return $this->hasItem($key);
	}

	/**
	 * Converts a DateTime object from the cache item to the expiry time in seconds from the present
	 *
	 * @param   HasExpirationDateInterface  $item  The cache item
	 *
	 * @return  integer  The time in seconds until expiry
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function convertItemExpiryToSeconds(HasExpirationDateInterface $item)
	{
		$itemExpiry   = $item->getExpiration();
		$itemTimezone = $itemExpiry->getTimezone();
		$now          = new \DateTime('now', $itemTimezone);
		$interval     = $now->diff($itemExpiry);

		return (int) $interval->format('%i') * 60;
	}

	/**
	 * Generate the values for the PSR-16 getMultiple method
	 *
	 * @param   mixed  $default  Default value to return for keys that do not exist.
	 * @param   array  $items    The items to process
	 *
	 * @return  \Generator
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function generateValues($default, $items)
	{
		/** @var $item CacheItemInterface */
		foreach ($items as $key => $item)
		{
			if (!$item->isHit())
			{
				yield $key => $default;
			}
			else
			{
				yield $key => $item->get();
			}
		}
	}
}
