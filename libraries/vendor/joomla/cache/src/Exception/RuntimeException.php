<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Exception;

use Psr\Cache\CacheException;

/**
 * Joomla! Caching Class Runtime Exception
 *
 * @since  1.0
 */
class RuntimeException extends \RuntimeException implements CacheException
{
}
