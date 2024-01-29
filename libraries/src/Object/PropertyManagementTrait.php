<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which contains the methods that formerly were inherited from \Joomla\CMS\Object\CMSObject to set and
 * get properties of the current class.
 *
 * @since       6.0.0
 *
 */
trait PropertyManagementTrait
{
    /**
     * Data array to save former object properties
     *
     * @var array $data
     */
    private $data = [];

    /**
     * Sets a default value if not already assigned
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed
     *
     * @since   1.7.0
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
     */
    public function get($property, $default = null)
    {
        if (isset($this->data->$property)) {
            return $this->data->$property;
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
     */
    public function getProperties($public = true)
    {
        return array_keys($data);
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
     */
    public function set($property, $value = null)
    {
        $previous              = $this->data->$property ?? null;
        $this->data->$property = $value;

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

    /**
     * Magic setter for direct property access
     *
     * @param $property
     * @param $value
     *
     * @return  mixed  Previous value of the property.
     *
     * @since   6.0.0
     */
    public function __set($property, $value)
    {
        return $this->set($property, $value);
    }

    /**
     * Magic getter for direct property access
     *
     * @param $property
     *
     * @return  mixed    The value of the property.
     *
     * @since   6.0.0
     */
    public function __get($property)
    {
        return $this->get($property);
    }
}
