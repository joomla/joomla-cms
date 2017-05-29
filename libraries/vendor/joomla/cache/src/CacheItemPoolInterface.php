<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache;

use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;

/**
 * Interface defining Joomla! PSR-6 compatible CacheItemPoolInterface implementations
 *
 * @since  __DEPLOY_VERSION__
 */
interface CacheItemPoolInterface extends PsrCacheItemPoolInterface
{
	/**
	 * Test to see if the CacheItemPoolInterface is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported();
}
