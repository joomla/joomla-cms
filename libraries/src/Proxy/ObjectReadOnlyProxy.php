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
 * Object read-only proxy class.
 * The class provides read-only feature for Object, including its children.
 *
 * @since  5.0.0
 */
class ObjectReadOnlyProxy extends ObjectProxy implements ReadOnlyProxyInterface
{
    /**
     * Implementing reading from object
     *
     * @param mixed $key  The key name to read
     *
     * @return mixed
     *
     * @since  5.0.0
     */
    public function __get($key): mixed
    {
        $value = $this->data->$key ?? null;

        // Ensure that the child also is a read-only
        if (\is_scalar($value) || $value === null) {
            return $value;
        }

        if (\is_object($value)) {
            return new static($value);
        }

        if (\is_array($value)) {
            return new ArrayReadOnlyProxy($value);
        }

        return $value;
    }

    /**
     * Implementing writing to object
     *
     * @param mixed $key    The key name to write
     * @param mixed $value  The value to write
     *
     * @return void
     *
     * @throws \RuntimeException
     *
     * @since  5.0.0
     */
    public function __set($key, $value): void
    {
        throw new \RuntimeException(\sprintf('ObjectReadOnlyProxy: trying to modify read-only element, by key "%s"', $key));
    }

    /**
     * Implementation of Iterator interface
     *
     * @return mixed
     *
     * @since  5.0.0
     */
    public function current(): mixed
    {
        $value = $this->iterator->current();

        // Ensure that the child also is a read-only
        if (\is_scalar($value) || $value === null) {
            return $value;
        }

        if (\is_object($value)) {
            return new static($value);
        }

        if (\is_array($value)) {
            return new ArrayReadOnlyProxy($value);
        }

        return $value;
    }
}
