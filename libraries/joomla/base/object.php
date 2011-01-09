<?php
/**
 * @version		$Id:observer.php 6961 2007-03-15 16:06:53Z tcp $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Base object class.
 *
 * This class allows for simple but smart objects with get and set methods
 * and an internal error handler.
 *
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
class JObject
{
	/**
	 * An array of errors
	 *
	 * @var		array of error messages or JExceptions objects.
	 * @since	1.5
	 */
	protected $_errors = array();

	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param	mixed $properties	Either and associative array or another object to set the initial properties of the object.
	 * @since	1.5
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
	 * @return	string	The classname.
	 * @since	1.6
	 */
	public function __toString()
	{
		return get_class($this);
	}

	/**
	 * Sets a default value if not alreay assigned
	 *
	 * @param	string $property	The name of the property.
	 * @param	mixed  $default		The default value.
	 * @since	1.6
	 */
	public function def($property, $default=null)
	{
		$value = $this->get($property, $default);
		return $this->set($property, $value);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param	string $property	The name of the property.
	 * @param	mixed  $default		The default value.
	 *
	 * @return	mixed	The value of the property.
	 * @see		getProperties()
	 * @since	1.5
	 */
	public function get($property, $default=null)
	{
		if (isset($this->$property)) {
			return $this->$property;
		}
		return $default;
	}

	/**
	 * Returns an associative array of object properties.
	 *
	 * @param	boolean $public	If true, returns only the public properties.
	 *
	 * @return	array
	 * @see		get()
	 * @since	1.5
	 */
	public function getProperties($public = true)
	{
		$vars  = get_object_vars($this);
		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1)) {
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}

	/**
	 * Get the most recent error message.
	 *
	 * @param	integer	$i			Option error index.
	 * @param	boolean	$toString	Indicates if JError objects should return their error message.
	 * @return	string	Error message
	 * @since	1.5
	 */
	public function getError($i = null, $toString = true)
	{
		// Find the error
		if ($i === null)
		{
			// Default, return the last message
			$error = end($this->_errors);
		}
		else if (!array_key_exists($i, $this->_errors))
		{
			// If $i has been specified but does not exist, return false
			return false;
		}
		else {
			$error	= $this->_errors[$i];
		}

		// Check if only the string is requested
		if (JError::isError($error) && $toString) {
			return (string)$error;
		}

		return $error;
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return	array	Array of error messages or JErrors.
	 * @since	1.5
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param	string $property	The name of the property.
	 * @param	mixed  $value		The value of the property to set.
	 *
	 * @return	mixed	Previous value of the property.
	 * @since	1.5
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
	 * @param	mixed $properties	Either and associative array or another object.
	 * @return	boolean
	 * @see		set()
	 * @since	1.5
	 */
	public function setProperties($properties)
	{
		if (is_array($properties) || is_object($properties))
		{
			foreach ((array) $properties as $k => $v)
			{
				// Use the set function which might be overriden.
				$this->set($k, $v);
			}
			return true;
		}

		return false;
	}

	/**
	 * Add an error message.
	 *
	 * @param	string $error	Error message.
	 * @since	1.0
	 */
	public function setError($error)
	{
		array_push($this->_errors, $error);
	}

	/**
	 * @deprecated 1.6 - Jun 24, 2009
	 * @see __toString()
	 */
	function toString()
	{
		return __toString();
	}
}
