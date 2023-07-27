<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Proxy;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Array read-only proxy class
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrayReadOnlyProxy extends ArrayProxy implements ReadOnlyProxyInterface
{
    /**
     * Implementation of ArrayAccess interface
     *
     * @param  mixed   $offset The key to set
     * @param  mixed   $value  The value to set
     *
     * @return void
     *
     * @throws \RuntimeException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException(sprintf('Trying to modify read-only element, by key "%s"', $offset));
    }
}
