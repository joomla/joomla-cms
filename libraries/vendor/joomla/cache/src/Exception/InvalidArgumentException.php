<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Exception;

use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException as PsrSimpleInvalidArgumentExceptionInterface;

/**
 * Joomla! Caching Class Invalid Argument Exception
 *
 * @since  1.0
 */
class InvalidArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentExceptionInterface, PsrSimpleInvalidArgumentExceptionInterface
{
}
