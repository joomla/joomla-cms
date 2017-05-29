<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Item;

/**
 * CacheItemInterface containing an expiration time.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HasExpirationDateInterface
{
	/**
	 * Returns the expiration time of a not-yet-expired cache item.
	 *
	 * If this cache item is a Cache Miss, this method MAY return the time at which the item expired or the current time if that is not available.
	 *
	 * @return  \DateTimeInterface  The timestamp at which this cache item will expire.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getExpiration();
}
