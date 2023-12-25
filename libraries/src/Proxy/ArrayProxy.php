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
 * Array proxy class
 *
 * @since  5.0.0
 */
class ArrayProxy implements ProxyInterface, \Countable, \ArrayAccess, \Iterator
{
    /**
     * Data source
     *
     * @var array
     *
     * @since  5.0.0
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @param  array  $data  The array for Proxy access
     *
     * @since  5.0.0
     */
    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * Implementation of ArrayAccess interface
     *
     * @param  mixed   $offset  The key to check
     *
     * @return boolean
     *
     * @since  5.0.0
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->data);
    }

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
        return $this->data[$offset] ?? null;
    }

    /**
     * Implementation of ArrayAccess interface
     *
     * @param  mixed   $offset The key to set
     * @param  mixed   $value  The value to set
     *
     * @return void
     *
     * @since  5.0.0
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Implementation of ArrayAccess interface
     *
     * @param  mixed   $offset  The key to remove
     *
     * @return void
     *
     * @since  5.0.0
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Implementation of Countable interface
     *
     * @return int
     *
     * @since  5.0.0
     */
    public function count(): int
    {
        return \count($this->data);
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
        $key = key($this->data);

        return $this->offsetGet($key);
    }

    /**
     * Implementation of Iterator interface
     *
     * @return mixed
     *
     * @since  5.0.0
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * Implementation of Iterator interface
     *
     * @return void
     *
     * @since  5.0.0
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Implementation of Iterator interface
     *
     * @return void
     *
     * @since  5.0.0
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Implementation of Iterator interface
     *
     * @return boolean
     *
     * @since  5.0.0
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }
}
