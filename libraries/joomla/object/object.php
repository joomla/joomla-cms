<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Object
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Object Class
 *
 * This class allows for simple but smart objects with get and set methods
 * and an internal error handler.
 *
 * @since  11.1
 */
class JObject
{
	/** Return protected and public properties. */
	const RETURN_ALL = false;

	/** Return public properties only. These always include properties created on the fly. */
	const RETURN_PUBLIC = true;

	/**
	 * An array of error messages or Exception objects.
	 *
	 * @var         array
	 * @since       11.1
	 * @see         JError
	 * @deprecated  12.3  JError has been deprecated
	 */
	protected $errors = array();

	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param   mixed  $properties  Either and associative array or another
	 *                              object to set the initial properties of the object.
	 *
	 * @since   11.1
	 */
	public function __construct($properties = null)
	{
		if (!empty($properties))
		{
			$this->setProperties($properties);
		}
	}

	/**
	 * Magic method to convert the object to a string gracefully.
	 * Derived classes should provide their own __toString() implementation.
	 *
	 * @return  string  The class name.
	 *
	 * @since      11.1
	 */
	public function __toString()
	{
		return get_class($this);
	}

	/**
	 * Magic getter method to convert access to underscored properties to their replacement without the underscore
	 *
	 * CAVEAT: Don't rely on this existing in future versions. It is here only to get rid of the underscores
	 *         without breaking BC.
	 *
	 * @see     http://stackoverflow.com/questions/13421661/getting-indirect-modification-of-overloaded-property-has-no-effect-notice#answer-19749730
	 *
	 * @param   string  $property  The property to be retrieved
	 *
	 * @return  mixed   The value of the property
	 *
	 * @since   3.4
	 */
	public function &__get($property)
	{
		$property = $this->deUnderscore($property);

		if (!property_exists($this, $property))
		{
			$this->$property = null;
		}

		return $this->$property;
	}

	/**
	 * Magic setter method to convert access to underscored properties to their replacement without the underscore
	 *
	 * CAVEAT: Don't rely on this existing in future versions. It is here only to get rid of the underscores
	 *         without breaking BC.
	 *
	 * @param   string  $property  The property to be retrieved
	 * @param   mixed   $value     The value of the property
	 *
	 * @return  mixed   The value of the property
	 *
	 * @since   3.4
	 */
	public function __set($property, $value)
	{
		$property = $this->deUnderscore($property);

		return $this->$property = $value;
	}

	/**
	 * Magic isset method to convert access to underscored properties to their replacement without the underscore
	 *
	 * CAVEAT: Don't rely on this existing in future versions. It is here only to get rid of the underscores
	 *         without breaking BC.
	 *
	 * @param   string  $property  The property to be checked
	 *
	 * @return  bool    Whether or not the property exists
	 *
	 * @since   3.4
	 */
	public function __isset($property)
	{
		$property = $this->deUnderscore($property);

		return (property_exists($this, $property) && isset($this->$property));
	}

	/**
	 * Magic unset method to convert access to underscored properties to their replacement without the underscore
	 *
	 * CAVEAT: Don't rely on this existing in future versions. It is here only to get rid of the underscores
	 *         without breaking BC.
	 *
	 * @param   string  $property  The property to be unset
	 *
	 * @return  bool    void
	 *
	 * @since   3.4
	 */
	public function __unset($property)
	{
		$property = $this->deUnderscore($property);

		unset($this->$property);
	}

	/**
	 * Sets a default value if not already assigned
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed
	 *
	 * @since   11.1
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
	 * @return  mixed   The value of the property.
	 *
	 * @since   11.1
	 *
	 * @see     JObject::getProperties()
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
	 * @param   boolean  $scope  If set to JObject::RETURN_PUBLIC (default),
	 *                           getProperties() returns only the public properties.
	 *                           If set to JObject::RETURN_ALL, also private and
	 *                           protected properties are included.
	 *
	 * @return  array  The property values indexed by property names
	 *
	 * @since   11.1
	 *
	 * @see     JObject::get()
	 */
	public function getProperties($scope = self::RETURN_PUBLIC)
	{
		if ($scope)
		{
			$ref = new ReflectionObject($this);
			$vars = array();
			foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
			{
				if ($property->isStatic())
				{
					continue;
				}
				$name = $property->getName();
				$vars[$name] = $this->$name;
			}
		}
		else
		{
			$vars = get_object_vars($this);
		}

		return $vars;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed   Previous value of the property.
	 *
	 * @since   11.1
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
	 *
	 * @since   11.1
	 *
	 * @see     JObject::set()
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
	 * @param   string|JError|Exception|mixed  $error  Error message/object/code
	 *
	 * @return  void
	 *
	 * @since      11.1
	 * @see        JError
	 * @deprecated 12.3  JError has been deprecated
	 */
	public function setError($error)
	{
		$this->errors[] = $error;
	}

	/**
	 * Get the most recent error message.
	 *
	 * @param   integer  $i         Option error index.
	 * @param   boolean  $toString  Indicates if JError objects should return their error message.
	 *
	 * @return  string|JError|Exception|mixed  Error message/object/code
	 *
	 * @since      11.1
	 * @see        JError
	 * @deprecated 12.3  JError has been deprecated
	 */
	public function getError($i = null, $toString = true)
	{
		if ($i === null)
		{
			$error = end($this->errors);
		}
		elseif (!array_key_exists($i, $this->errors))
		{
			return false;
		}
		else
		{
			$error = $this->errors[$i];
		}

		if ($toString)
		{
			$error = (string) $error;
		}

		return $error;
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return  array  Array of error messages or JErrors.
	 *
	 * @since      11.1
	 * @see        JError
	 * @deprecated 12.3  JError has been deprecated
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Remove a leading underscore
	 *
	 * @param   string  $property  The name of a property
	 *
	 * @return  string  The unchanged name of the property (if it exists) or the name without a leading underscore
	 */
	private function deUnderscore($property)
	{
		if (is_string($property) && !empty($property) && $property[0] == '_' && !property_exists($this, $property))
		{
			JLog::add('Property names starting with an underscore are deprecated. Use their counterpart without the underscore instead.', JLog::INFO, 'deprecated');
			$property = substr($property, 1);
		}

		return $property;
	}
}
