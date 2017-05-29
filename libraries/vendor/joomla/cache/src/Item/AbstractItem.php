<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Item;

use Psr\Cache\CacheItemInterface;

/**
 * Abstract cache item instance for the Joomla Framework.
 *
 * @since  1.0
 */
abstract class AbstractItem implements HasExpirationDateInterface, CacheItemInterface
{
}
