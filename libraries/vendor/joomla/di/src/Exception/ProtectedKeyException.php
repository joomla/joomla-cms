<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Attempt to set the value of a protected key, which already is set
 *
 * @since  1.5.0
 */
class ProtectedKeyException extends \OutOfBoundsException implements ContainerExceptionInterface
{
}
