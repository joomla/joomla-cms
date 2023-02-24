<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A simple state holder class. This class acts for transition from CMSObject to Registry
 * and should not be used directly. Instead of, use the Registry class.
 *
 * @since  __DEPLOY_VERSION__
 *
 * @deprecated  7.0 Use the Registry directly
 */
class State extends Registry
{
     /**
     * Constructor
     *
     * @param  mixed  $data  The data to bind to the new Registry object.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        // To speed up things
        $this->separator = null;
    }

   /**
     * Returns an associative array of object properties.
     *
     * @return  array  The data array
     *
     * @since   __DEPLOY_VERSION__
     *
     * @deprecated  7.0 Use toArray instead
     */
    public function getProperties()
    {
        return $this->toArray();
    }

    /**
     * Proxy for internal data access for the given name.
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
    public function __get($name)
    {
        @trigger_error(sprintf('Direct property access will not be supported in 7.0 in %s::%s.', __METHOD__, __CLASS__), E_USER_DEPRECATED);

        return $this->get($name);
    }

    /**
     * Proxy for internal data storage for the given name and value.
     *
     * @param   string  $name   The name of the element
     * @param   string  $value  The value
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0
     *
     */
    public function __set($name, $value)
    {
        @trigger_error(sprintf('Direct property access will not be supported in 7.0 in %s::%s.', __METHOD__, __CLASS__), E_USER_DEPRECATED);

        return $this->set($name, $value);
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
    public function __isset($name)
    {
        @trigger_error(sprintf('Direct property access will not be supported in 7.0 in %s::%s.', __METHOD__, __CLASS__), E_USER_DEPRECATED);

        return $this->exists($name);
    }
}
