<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

/**
 * Object class, directly instantiatable
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
	 * @var		array of error messages or JExceptions objects
	 * @access	protected
	 * @since	1.0
	 */
	protected $_errors = array();

	/**
	 * Provides interception of default php error handling logic for objects.  Enforceing class definitions
	 *
	 * @access	public
	 * @throw Jexception
	 * @since	1.6
 	 */
	public function __call($func, $args) {
		throw new JException('Attempted to call non-existant method on object',0, E_ERROR, array($func, $args), true);
	}
	
	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @access	public
	 * @param	string $property The name of the property
	 * @param	mixed  $default The default value
	 * @return	mixed The value of the property
	 * @see		getProperties()
	 * @since	1.5
 	 */
	public function get($property, $default=null)
	{
		if(isset($this->$property)) {
			return $this->$property;
		}
		return $default;
	}
	
	/**
	 * Returns an associative array of object properties
	 *
	 * @access	public
	 * @param	boolean $public If true, returns only the public properties
	 * @return	array
	 * @see		get()
	 * @since	1.5
 	 */
	public function getProperties( $public = true )
	{
		$vars  = get_object_vars($this);
		if($public)
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
	 * Get the most recent error message
	 *
	 * @param	integer	$i Option error index
	 * @param	boolean	$toString Indicates if JError objects should return their error message
	 * @return	string	Error message
	 * @access	public
	 * @since	1.5
	 */
	public function getError($i = null, $toString = true )
	{
		// Find the error
		if ( $i === null) {
			// Default, return the last message
			$error = end($this->_errors);
		}
		else
		if ( ! array_key_exists($i, $this->_errors) ) {
			// If $i has been specified but does not exist, return false
			return false;
		}
		else {
			$error	= $this->_errors[$i];
		}

		// Check if only the string is requested
		if ( JError::isError($error) && $toString ) {
			return $error->toString();
		}

		return $error;
	}

	/**
	 * Return all errors, if any
	 *
	 * @access	public
	 * @return	array	Array of error messages or JErrors
	 * @since	1.5
	 */
	public function getErrors()
	{
		return $this->_errors;
	}


	/**
	 * Modifies a property of the object, by default creating it if it does not already exist.
	 *
	 * @access	public
	 * @param	string $property The name of the property
	 * @param	mixed  $value The value of the property to set
	 * @param  bool $create Create the item if it doesn't exist already
	 * @return	mixed Previous value of the property
	 * @see		setProperties()
	 * @since	1.5
	 */
	public function set( $property, $value = null, $create = true )
	{
		$previous = isset($this->$property) ? $this->$property : null;
		if(!isset($this->$property) && !$create) return null;
		$this->$property = $value;
		return $previous;
	}

	/**
	* Set the object properties based on a named array/hash
	*
	* @access	protected
	* @param	$array  mixed Either and associative array or another object
	* @return	boolean
	* @see		set()
	* @since	1.5
	*/
	public function setProperties( $properties )
	{
		if(!is_array($properties)) {
			if(!empty($properties)) {
				$properties = (array) $properties; //cast to an array
			} else return false;
		}
		if (is_array($properties))
		{
			foreach ($properties as $k => $v) {
				$this->set($k, $v); // use the set function which might be overriden
			}

			return true;
		}

		return false;
	}

	/**
	 * Add an error message
	 *
	 * @param	string $error Error message
	 * @access	public
	 * @since	1.0
	 */
	public function setError($error)
	{
		array_push($this->_errors, $error);
	}

	/**
	 * Object-to-string conversion.
	 * Each class can override it as necessary.
	 *
	 * @access	public
	 * @return	string This name of this class
	 * @since	1.5
 	 */
	public function toString()
	{
		return get_class($this);
	}

}
