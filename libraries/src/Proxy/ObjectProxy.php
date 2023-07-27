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
 * Object proxy class
 *
 * @since  __DEPLOY_VERSION__
 */
class ObjectProxy implements ProxyInterface, \Iterator
{
    /**
     * Data source
     *
     * @var object
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $data;

    /**
     * An iterator instance
     *
     * @var \ArrayIterator
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $iterator;

    /**
     * Class constructor
     *
     * @param  object  $data  The object for Proxy access
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(object $data)
    {
        $this->data = $data;
    }

    /**
     * Implementing reading from object
     *
     * @param mixed $key  The key name to read
     *
     * @return mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __get($key): mixed
    {
        return $this->data->$key ?? null;
    }

    /**
     * Implementing writing to object
     *
     * @param mixed $key    The key name to write
     * @param mixed $value  The value to write
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __set($key, $value): void
    {
        $this->data->$key = $value;
    }

    /**
     * Implementation of Iterator interface
     *
     * @return mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function current(): mixed
    {
        return $this->iterator->current();
    }

    /**
     * Implementation of Iterator interface
     *
     * @return mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function key(): mixed
    {
        return $this->iterator->key();
    }

    /**
     * Implementation of Iterator interface
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function next(): void
    {
        $this->iterator->next();
    }

    /**
     * Implementation of Iterator interface
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function rewind(): void
    {
        $this->iterator = new \ArrayIterator($this->data);
    }

    /**
     * Implementation of Iterator interface
     *
     * @return boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }
}
