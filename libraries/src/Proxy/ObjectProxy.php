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
class ObjectProxy implements ProxyInterface, \IteratorAggregate
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
