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
 * Array read-only proxy class.
 * The class provides read-only feature for Array, including its children.
 *
 * @since  5.0.0
 */
class ArrayReadOnlyProxy extends ArrayProxy implements ReadOnlyProxyInterface
{
    /**
     * Implementation of ArrayAccess interface
     *
     * @param mixed $offset The key to get
     *
     * @return mixed
     *
     * @since  5.0.0
     */
    public function offsetGet(mixed $offset): mixed
    {
        $value = $this->data[$offset] ?? null;

        // Ensure that the child also is a read-only
        if (\is_scalar($value) || $value === null) {
            return $value;
        }

        if (\is_array($value)) {
            $value = new static($value);
        } elseif (\is_object($value)) {
            $value = new ObjectReadOnlyProxy($value);
        }

        return $value;
    }

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
     * @since  5.0.0
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException(\sprintf('ArrayReadOnlyProxy: trying to modify read-only element, by key "%s"', $offset));
    }
}
