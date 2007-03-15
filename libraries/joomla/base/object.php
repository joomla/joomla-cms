<?php
/**
 * @version		$Id: object.php 6616 2007-02-14 00:22:01Z chrisdavenport $
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Object class, allowing __construct in PHP4.
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
class JObject
{
	/**
	 * A hack to support __construct() on PHP 4
	 * Hint: descendant classes have no PHP4 class_name() constructors,
	 * so this constructor gets called first and calls the top-layer __construct()
	 * which (if present) should call parent::__construct()
	 *
	 * @access	public
	 * @return	Object
	 * @since	1.5
	 */
	function JObject()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}

	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @access	protected
	 * @since	1.5
	 */
	function __construct() {}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @access	public
	 * @param	string $property The name of the property
	 * @param	mixed  $value The value of the property to set
	 * @return	mixed Previous value of the property
	 * @since	1.5
 	 */
	function set( $property, $value=null ) {
		$previous = isset($this->property) ? $this->property : null;
		$this->$property = $value;
		return $previous;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @access	public
	 * @param	string $property The name of the property
	 * @param	mixed  $default The default value
	 * @return	mixed The value of the property
	 * @see		get(), getPublicProperties()
	 * @since	1.5
 	 */
	function get($property, $default=null)
	{
		if(isset($this->$property)) {
			return $this->$property;
		}
		return $default;
	}

	/**
	 * Returns an array of public properties
	 *
	 * @access	public
	 * @param	boolean $assoc If true, returns an associative key=>value array
	 * @return	array
	 * @see		get(), toString()
	 * @since	1.5
 	 */
	function getPublicProperties( $assoc = false )
	{
		$vars = array(array(),array());
		foreach (get_object_vars( $this ) as $key => $val)
		{
			if (substr( $key, 0, 1 ) != '_')
			{
				$vars[0][] = $key;
				$vars[1][$key] = $val;
			}
		}
		return $vars[$assoc ? 1 : 0];
	}

	/**
	 * Object-to-string conversion.
	 * Each class can override it as necessary.
	 *
	 * @access	public
	 * @return	string This name of this class
	 * @since	1.5
 	 */
	function toString()
	{
		return get_class($this);
	}
}
