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
 * @since  __DEPLOY_VERSION__
 */
class ArrayProxy implements ProxyInterface, \Countable, \ArrayAccess, \IteratorAggregate
{
    /**
     * Data source
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @param  array  $data  The array for Proxy access
     *
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    public function count(): int
    {
        return \count($this->data);
    }

    /**
     * Implementation of IteratorAggregate interface
     *
     * @return \Traversable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }
}
