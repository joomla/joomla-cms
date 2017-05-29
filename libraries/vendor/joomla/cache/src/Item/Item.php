<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Item;

/**
 * Cache item instance for the Joomla Framework.
 *
 * @since  1.0
 */
class Item extends AbstractItem
{
	/**
	 * The time the object expires at
	 *
	 * @var    \DateTimeInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $expiration;

	/**
	 * The key for the cache item.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $key;

	/**
	 * The value of the cache item.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	private $value;

	/**
	 * Whether the cache item has been hit.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	private $hit = false;

	/**
	 * Class constructor.
	 *
	 * @param   string                           $key  The key for the cache item.
	 * @param   \DateTimeInterface|integer|null  $ttl  The expiry time for the cache item in seconds or as a datetime object
	 *
	 * @since   1.0
	 */
	public function __construct($key, $ttl = null)
	{
		$this->key = $key;

		if (is_int($ttl))
		{
			$this->expiresAfter($ttl);
		}
		elseif ($ttl instanceof \DateTimeInterface)
		{
			$this->expiresAt($ttl);
		}
		else
		{
			$this->expiresAfter(900);
		}
	}

	/**
	 * Confirms if the cache item exists in the cache.
	 *
	 * Note: This method MAY avoid retrieving the cached value for performance
	 * reasons, which could result in a race condition between exists() and get().
	 * To avoid that potential race condition use isHit() instead.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function exists()
	{
		return $this->isHit();
	}

	/**
	 * Returns the key for the current cache item.
	 *
	 * @return  string  The key string for this cache item.
	 *
	 * @since   1.0
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Retrieves the value of the item from the cache associated with this object's key.
	 *
	 * @return  mixed  The value corresponding to this cache item's key, or null if not found.
	 *
	 * @since   1.0
	 */
	public function get()
	{
		return $this->value;
	}

	/**
	 * Sets the value represented by this cache item.
	 *
	 * If the value is set, we are assuming that there was a valid hit on the cache for the given key.
	 *
	 * @param   mixed  $value  The serializable value to be stored.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function set($value)
	{
		$this->value = $value;
		$this->hit = true;

		return $this;
	}

	/**
	 * Confirms if the cache item lookup resulted in a cache hit.
	 *
	 * @return  boolean  True if the request resulted in a cache hit. False otherwise.
	 *
	 * @since   1.0
	 */
	public function isHit()
	{
		return $this->hit;
	}

	/**
	 * Sets the expiration time for this cache item.
	 *
	 * @param   \DateTimeInterface|null  $expiration  The point in time after which the item MUST be considered expired.
	 *                                                If null is passed explicitly, a default value MAY be used. If none is
	 *                                                set, the value should be stored permanently or for as long as the
	 *                                                implementation allows.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function expiresAt($expiration)
	{
		$this->expiration = $expiration;

		return $this;
	}

	/**
	 * Sets the expiration time for this cache item.
	 *
	 * @param   int|\DateInterval|null  $time  The period of time from the present after which the item MUST be considered
	 *                                         expired. An integer parameter is understood to be the time in seconds until
	 *                                         expiration.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function expiresAfter($time)
	{
		if (is_integer($time))
		{
			$this->expiration = new \DateTime('now +' . $time . ' seconds');
		}
		elseif ($time instanceof \DateInterval)
		{
			$this->expiration = new \DateTime('now');
			$this->expiration->add($time);
		}
		else
		{
			$this->expiration = new \DateTime('now + 900 seconds');
		}

		return $this;
	}

	/**
	 * Returns the expiration time of a not-yet-expired cache item.
	 *
	 * If this cache item is a Cache Miss, this method MAY return the time at which the item expired or the current time if that is not available.
	 *
	 * @return  \DateTimeInterface  The timestamp at which this cache item will expire.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getExpiration()
	{
		return $this->expiration;
	}
}
