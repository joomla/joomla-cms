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
 * No container is available.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContainerNotFoundException extends \RuntimeException implements ContainerExceptionInterface
{
}
