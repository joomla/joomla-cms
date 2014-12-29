<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('FOF_INCLUDED') or die;

/**
 * Temporary class for backwards compatibility. You should not be using this
 * in your code. It is currently present to handle the validation error stack
 * for FOFTable::check() and will be removed in an upcoming version.
 *
 * This class is based on JObject as found in Joomla! 3.2.1
 *
 * @deprecated  2.1
 * @codeCoverageIgnore
 */
class FOFUtilsObject
{
    /**
     * An array of error messages or Exception objects.
     *
     * @var    array
     */
    protected $_errors = array();

    /**
     * Class constructor, overridden in descendant classes.
     *
     * @param   mixed  $properties  Either and associative array or another
     *                              object to set the initial properties of the object.
     */
    public function __construct($properties = null)
    {
        if ($properties !== null)
        {
            $this->setProperties($properties);
        }
    }

    /**
     * Magic method to convert the object to a string gracefully.
     *
     * @return  string  The classname.
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * Sets a default value if not alreay assigned
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed
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
     */
    public function get($property, $default = null)
    {
        if (isset($this->$property))
        {
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
     */
    public function getProperties($public = true)
    {
        $vars = get_object_vars($this);
        if ($public)
        {
            foreach ($vars as $key => $value)
            {
                if ('_' == substr($key, 0, 1))
                {
                    unset($vars[$key]);
                }
            }
        }

        return $vars;
    }

    /**
     * Get the most recent error message.
     *
     * @param   integer  $i         Option error index.
     * @param   boolean  $toString  Indicates if JError objects should return their error message.
     *
     * @return  string   Error message
     */
    public function getError($i = null, $toString = true)
    {
        // Find the error
        if ($i === null)
        {
            // Default, return the last message
            $error = end($this->_errors);
        }
        elseif (!array_key_exists($i, $this->_errors))
        {
            // If $i has been specified but does not exist, return false
            return false;
        }
        else
        {
            $error = $this->_errors[$i];
        }

        // Check if only the string is requested
        if ($error instanceof Exception && $toString)
        {
            return (string) $error;
        }

        return $error;
    }

    /**
     * Return all errors, if any.
     *
     * @return  array  Array of error messages or JErrors.
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $value     The value of the property to set.
     *
     * @return  mixed  Previous value of the property.
     */
    public function set($property, $value = null)
    {
        $previous = isset($this->$property) ? $this->$property : null;
        $this->$property = $value;
        return $previous;
    }

    /**
     * Set the object properties based on a named array/hash.
     *
     * @param   mixed  $properties  Either an associative array or another object.
     *
     * @return  boolean
     */
    public function setProperties($properties)
    {
        if (is_array($properties) || is_object($properties))
        {
            foreach ((array) $properties as $k => $v)
            {
                // Use the set function which might be overridden.
                $this->set($k, $v);
            }
            return true;
        }

        return false;
    }

    /**
     * Add an error message.
     *
     * @param   string  $error  Error message.
     *
     * @return  void
     */
    public function setError($error)
    {
        array_push($this->_errors, $error);
    }
}
