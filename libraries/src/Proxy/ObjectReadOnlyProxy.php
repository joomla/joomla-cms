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
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    public function __get($key): mixed
    {
        $value = $this->data->$key ?? null;

        // Ensure that the child also is a read-only
        if (\is_object($value)) {
            $value = new static($value);
        } elseif (\is_array($value)) {
            $value = new ArrayReadOnlyProxy($value);
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
     * @since  __DEPLOY_VERSION__
     */
    public function __set($key, $value): void
    {
        throw new \RuntimeException(sprintf('ObjectReadOnlyProxy: trying to modify read-only element, by key "%s"', $key));
    }
}
