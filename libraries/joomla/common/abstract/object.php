<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Object class, allowing __construct in PHP4.
 *
 * @abstract
 * @author	Johan Janssens <johan.janssens@joomla.org>
 * @package	Joomla.Framework
 * @since	1.5
 */

class JObject
{
	/**
	 * A hack to support __construct() on PHP 4
	 * Hint: descendant classes have no PHP4 class_name() constructors,
	 * so this constructor gets called first and calls the top-layer __construct()
	 * which (if present) should call parent::__construct()
	 *
	 * @return Object
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
	 */
	function __construct() {}

	/**
	* @param string The name of the property
	* @param mixed The value of the property to set
	*/
	function set( $property, $value=null ) {
		$this->$property = $value;
	}

	/**
	* @param string The name of the property
	* @param mixed  The default value
	* @return mixed The value of the property
	*/
	function get($property, $default=null)
	{
		if(isset($this->$property)) {
			return $this->$property;
		}
		return $default;
	}

	/**
	 * Bind an associative array
	 *
	 * Only existing properties of object are filled. When undefined in hash, properties wont be deleted
	 *
	 * @param mixed Either and associative array or another object
	 * @param string A list of attributes to ignore
	 * @param string
	 * @param boolean
	 */
	function bind( $from, $ignore='', $prefix='' )
	{
		$fromArray = is_array( $from );
		$fromObject = is_object( $from );

		if ($fromArray || $fromObject)
		{
			foreach (get_object_vars($this) as $k => $v)
			{
				// only bind to public variables
				if( substr( $k, 0, 1 ) != '_' )
				{
					// internal attributes of an object are ignored
					if (strpos( $ignore, $k) === false)
					{
						if ($prefix)
						{
							$ak = $prefix . $k;
						}
						else
						{
							$ak = $k;
						}

						if ($fromArray && isset( $from[$ak] ))
						{
							$this->$k = $from[$ak];
						}
						else if ($fromObject && isset( $from->$ak ))
						{
							$this->$k = $from->$ak;
						}
					}
				}
			}
		}
		else
		{
			return (false);
		}

		return true;
	}

	/**
	 * Returns an array of public properties
	 *
	 * @return array
	 */
	function getPublicProperties()
	{
		static $cache = null;

		if (is_null( $cache )) {
			$cache = array();
			foreach (get_class_vars( get_class( $this ) ) as $key=>$val) {
				if (substr( $key, 0, 1 ) != '_') {
					$cache[] = $key;
				}
			}
		}
		return $cache;
	}

	/**
	 * Object-to-string conversion.
	 * Each class can override it as necessary.
	 *
	 * @return string This name of this class
	 */
	function toString()
	{
		return get_class($this);
	}
}

?>