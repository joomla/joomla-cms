<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform Object Class
 *
 * This class allows for simple but smart objects with get and set methods
 * and an internal error handler.
 *
 * @since       1.7.0
 * @deprecated  4.0.0  Use \stdClass or \Joomla\Registry\Registry instead.
 */
class CMSObject
{
    use LegacyErrorHandlingTrait;

    /**
     * Class constructor, overridden in descendant classes.
     *
     * @param   mixed  $properties  Either and associative array or another
     *                              object to set the initial properties of the object.
     *
     * @since   1.7.0
     */
    public function __construct($properties = null)
    {
        if ($properties !== null) {
            $this->setProperties($properties);
        }
    }

    /**
     * Magic method to convert the object to a string gracefully.
     *
     * @return  string  The classname.
     *
     * @since   1.7.0
     * @deprecated 3.1.4  Classes should provide their own __toString() implementation.
     */
    public function __toString()
    {
        return \get_class($this);
    }

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
     */
    public function set($property, $value = null)
    {
        $previous = $this->$property ?? null;
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
