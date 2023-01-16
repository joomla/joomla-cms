<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A simple state holder class.
 *
 * @since  __DEPLOY_VERSION__
 */
class State
{
    /**
     * The data array.
     *
     * @var    array
     *
     * @since  __DEPLOY_VERSION__
     */
    private $data = [];

    /**
     * Returns a value of the state or the default value if the key is not available.
     *
     * @param   string  $key  The name of the key
     * @param   mixed   $default   The default value
     *
     * @return  mixed   The value of the key
     *
     * @since   __DEPLOY_VERSION__
     */
    public function get($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Modifies a value of the internal data storage for the given key.
     *
     * @param   string  $key    The name of the key.
     * @param   mixed   $value  The value of the key to set.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function set($key, $value = null)
    {
        $this->data[$key] = $value;
    }

    /**
     * Returns an associative array of object properties.
     *
     * @return  array  The data array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getProperties()
    {
        return $this->data;
    }

    /**
     * Proxy for internal data access for the given key.
     *
     * @param   string  $name  The name of the element
     *
     * @return  mixed  The value of the element if set, null otherwise
     *
     * @since   __DEPLOY_VERSION__
     *
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0
     *
     */
    public function __get($key)
    {
        @trigger_error(sprintf('Direct property access will not be supported in 7.0 in %s::%s.', __METHOD__, __CLASS__), E_USER_DEPRECATED);

        return $this->get($key);
    }

    /**
     * Proxy for internal data check for a variable with the given key.
     *
     * @param   string  $name  The name of the element
     *
     * @return  bool    Returns if the internal data storage contains a key with the given
     *
     * @since   __DEPLOY_VERSION__
     *
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0
     *
     */
    public function __isset($key)
    {
        @trigger_error(sprintf('Direct property access will not be supported in 7.0 in %s::%s.', __METHOD__, __CLASS__), E_USER_DEPRECATED);

        return array_key_exists($key, $this->data);
    }
}
