<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which contains the legacy methods that formerly were inherited from \Joomla\CMS\Object\CMSObject to set and
 * get properties of the current class.
 *
 * @since       4.3.0
 *
 * @deprecated  4.3.0 will be removed in 6.0
 *              Will be removed without replacement
 *              Create proper setter functions for the individual properties or use a \Joomla\Registry\Registry
 */
trait LegacyPropertyManagementTrait
{
    /**
     * Sets a default value if not already assigned
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed
     *
     * @since   1.7.0
     *
     * @deprecated 4.3.0 will be removed in 6.0
     *             Defining dynamic properties should not be used anymore
     */
    public function def($property, $default = null)
    {
        $value = $this->get($property, $default);

        return $this->set($property, $value);
    }

    /**
     * Returns a property of the object or the default value if the property is not set.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed    The value of the property.
     *
     * @since   1.7.0
     *
     * @see     CMSObject::getProperties()
     *
     * @deprecated 4.3.0 will be removed in 6.0
     *             Create a proper getter function for the property
     */
    public function get($property, $default = null)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }

        return $default;
    }

    /**
     * Returns an associative array of object properties.
     *
     * @param   boolean  $public  If true, returns only the public properties.
     *
     * @return  array
     *
     * @since   1.7.0
     *
     * @see     CMSObject::get()
     *
     * @deprecated 4.3.0 will be removed in 6.0
     *             Create a proper getter function for the property
     */
    public function getProperties($public = true)
    {
        $vars = get_object_vars($this);

        if ($public) {
            foreach ($vars as $key => $value) {
                if ('_' == substr($key, 0, 1)) {
                    unset($vars[$key]);
                }
            }
        }

        return $vars;
    }

    /**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $value     The value of the property to set.
     *
     * @return  mixed  Previous value of the property.
     *
     * @since   1.7.0
     *
     * @deprecated 4.3.0 will be removed in 6.0
     *             Create a proper setter function for the property
     */
    public function set($property, $value = null)
    {
        $previous        = $this->$property ?? null;
        $this->$property = $value;

        return $previous;
    }

    /**
     * Set the object properties based on a named array/hash.
     *
     * @param   mixed  $properties  Either an associative array or another object.
     *
     * @return  boolean
     *
     * @since   1.7.0
     *
     * @see     CMSObject::set()
     *
     * @deprecated 4.3.0 will be removed in 6.0
     *             Create a proper setter function for the property
     */
    public function setProperties($properties)
    {
        if (\is_array($properties) || \is_object($properties)) {
            foreach ((array) $properties as $k => $v) {
                // Use the set function which might be overridden.
                $this->set($k, $v);
            }

            return true;
        }

        return false;
    }
}
