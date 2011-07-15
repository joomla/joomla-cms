<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JArrayHelper is an array utility class for doing all sorts of odds and ends with arrays.
 *
 * @package     Joomla.Platform
 * @subpackage  Utilities
 * @since       11.1
 */
class JArrayHelper
{
	/**
	 * Function to convert array to integer values
	 *
	 * @param   array    $array    The source array to convert
	 * @param   mixed    $default   A default value (int|array) to assign if $array is not an array
	 *
	 * @since   11.1
	 */
	public static function toInteger(&$array, $default = null)
	{
		if (is_array($array)) {
			foreach ($array as $i => $v) {
				$array[$i] = (int) $v;
			}
		} else {
			if ($default === null) {
				$array = array();
			} elseif (is_array($default)) {
				JArrayHelper::toInteger($default, null);
				$array = $default;
			} else {
				$array = array((int) $default);
			}
		}
	}

	/**
	 * Utility function to map an array to a stdClass object.
	 *
	 * @param   array    $array		The array to map.
	 * @param   string   $class		Name of the class to create
	 *
	 * @return  object   The object mapped from the given array
	 * @since   11.1
	 */
	public static function toObject(&$array, $class = 'stdClass')
	{
		$obj = null;
		if (is_array($array)) {
			$obj = new $class;
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					$obj->$k = JArrayHelper::toObject($v, $class);
				} else {
					$obj->$k = $v;
				}
			}
		}
		return $obj;
	}
	/**
	 * Utility function to map an array to a string.
	 *
	 * @param   array    $array         The array to map.
	 * @param   string   $inner_glue
	 * @param   string   $outer_glue
	 * @param   boolean  $keepOuterKey  True if final key should be kept.
	 *
	 * @return  string   The string mapped from the given array
	 * @since   11.1
	 */

	public static function toString($array = null, $inner_glue = '=', $outer_glue = ' ', $keepOuterKey = false)
	{
		$output = array();

		if (is_array($array)) {
			foreach ($array as $key => $item) {
				if (is_array ($item)) {
					if ($keepOuterKey) {
						$output[] = $key;
					}
					// This is value is an array, go and do it again!
					$output[] = JArrayHelper::toString($item, $inner_glue, $outer_glue, $keepOuterKey);
				} else {
					$output[] = $key.$inner_glue.'"'.$item.'"';
				}
			}
		}

		return implode($outer_glue, $output);
	}

	/**
	 * Utility function to map an object to an array
	 *
	 * @param   object   The source object
	 * @param   boolean  True to recurve through multi-level objects
	 * @param   string   An optional regular expression to match on field names
	 *
	 * @return  array    The array mapped from the given object
	 * @since   11.1
	 */
	public static function fromObject($p_obj, $recurse = true, $regex = null)
	{
		if (is_object($p_obj)) {
			return self::_fromObject($p_obj, $recurse, $regex);
		}
		else {
			return null;
		}
	}

	/**
	 * Utility function to map an object or array to an array
	 *
	 * @param   mixed     The source object or array
	 * @param   boolean   True to recurve through multi-level objects
	 * @param   string    An optional regular expression to match on field names
	 *
	 * @return  array     The array mapped from the given object
	 * @since   11.1
	 */
	protected static function _fromObject($item, $recurse, $regex)
	{
		if (is_object($item))
		{
			$result = array();
			foreach (get_object_vars($item) as $k => $v)
			{
				if (!$regex || preg_match($regex, $k))
				{
					if ($recurse) {
						$result[$k] = self::_fromObject($v, $recurse, $regex);
					}
					else {
						$result[$k] = $v;
					}
				}
			}
		}
		elseif (is_array($item))
		{
			$result = array();
			foreach ($item as $k => $v)
			{
				if ($recurse) {
					$result[$k] = self::_fromObject($v, $recurse, $regex);
				}
				else {
					$result[$k] = $v;
				}
			}
		}
		else
		{
			$result = $item;
		}
		return $result;
	}

	/**
	 * Extracts a column from an array of arrays or objects
	 *
	 * @param   array    $array  The source array
	 * @param   string   $index  The index of the column or name of object property
	 *
	 * @return  array    Column of values from the source array
	 * @since   11.1
	 */
	public static function getColumn(&$array, $index)
	{
		$result = array ();

		if (is_array($array)) {
			$n = count($array);

			for ($i = 0; $i < $n; $i++) {
				$item = & $array[$i];

				if (is_array($item) && isset ($item[$index])) {
					$result[] = $item[$index];
				} elseif (is_object($item) && isset ($item-> $index)) {
					$result[] = $item-> $index;
				}
				// else ignore the entry
			}
		}
		return $result;
	}

	/**
	 * Utility function to return a value from a named array or a specified default
	 *
	 * @param   array    $array    A named array
	 * @param   string   $name     The key to search for
	 * @param   mixed    $default  The default value to give if no key found
	 * @param   string   $type     Return type for the variable (INT, FLOAT, STRING, WORD, BOOLEAN, ARRAY)
	 *
	 * @return  mixed    The value from the source array
	 * @since   11.1
	 */
	public static function getValue(&$array, $name, $default=null, $type='')
	{
		// Initialise variables.
		$result = null;

		if (isset ($array[$name])) {
			$result = $array[$name];
		}

		// Handle the default case
		if (is_null($result)) {
			$result = $default;
		}

		// Handle the type constraint
		switch (strtoupper($type)) {
			case 'INT' :
			case 'INTEGER' :
				// Only use the first integer value
				@ preg_match('/-?[0-9]+/', $result, $matches);
				$result = @ (int) $matches[0];
				break;

			case 'FLOAT' :
			case 'DOUBLE' :
				// Only use the first floating point value
				@ preg_match('/-?[0-9]+(\.[0-9]+)?/', $result, $matches);
				$result = @ (float) $matches[0];
				break;

			case 'BOOL' :
			case 'BOOLEAN' :
				$result = (bool) $result;
				break;

			case 'ARRAY' :
				if (!is_array($result)) {
					$result = array ($result);
				}
				break;

			case 'STRING' :
				$result = (string) $result;
				break;

			case 'WORD' :
				$result = (string) preg_replace('#\W#', '', $result);
				break;

			case 'NONE' :
			default :
				// No casting necessary
				break;
		}
		return $result;
	}

	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @param   array    An array to test.
	 *
	 * @return  boolean  True if the array is an associative array.
	 * @since   11.1
	 */
	public static function isAssociative($array)
	{
		if (is_array($array)) {
			foreach (array_keys($array) as $k => $v) {
				if ($k !== $v) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Utility function to sort an array of objects on a given field
	 *
	 * @param   array       $arr            An array of objects
	 * @param   mixed       $k              The key (string) or a array of key to sort on
	 * @param   mixed       $direction      Direction (integer) or an array of direction to sort in [1 = Ascending] [-1 = Descending]
	 * @param   mixed       $casesensitive  Boolean or array of booleans to let sort occur case sensitive or insensitive
	 * @param   mixed       $locale         Boolean or array of booleans to let sort occur using the locale language or not
	 *
	 * @return  array       The sorted array of objects
	 * @since   11.1
	 */
	public static function sortObjects(&$a, $k, $direction=1, $casesensitive = true, $locale = false)
	{
		if (!is_array($locale) or !is_array($locale[0])) {
			$locale = array($locale);
		}

		$GLOBALS['JAH_so'] = array(
			'key'			=> (array)$k,
			'direction'		=> (array)$direction,
			'casesensitive'	=> (array)$casesensitive,
			'locale'		=> $locale,
		);
		usort($a, array( __CLASS__ , '_sortObjects'));
		unset($GLOBALS['JAH_so']);

		return $a;
	}

	/**
	 * Callback function for sorting an array of objects on a key
	 *
	 * @param   array    $a  An array of objects
	 * @param   array    $b  An array of objects
	 *
	 * @return  integer  Comparison status
	 * @since   11.1
	 * @see     JArrayHelper::sortObjects()
	 */
	protected static function _sortObjects(&$a, &$b)
	{
		$params = $GLOBALS['JAH_so'];

		for ($i = 0, $count = count($params['key']); $i < $count; $i++)
		{
			if (isset($params['direction'][$i])) {
				$direction = $params['direction'][$i];
			}

			if (isset($params['casesensitive'][$i])) {
				$casesensitive = $params['casesensitive'][$i];
			}

			if (isset($params['locale'][$i])) {
				$locale = $params['locale'][$i];
			}

			$va = $a->$params['key'][$i];
			$vb = $b->$params['key'][$i];

			if ((is_bool($va) or is_numeric($va)) and (is_bool($vb) or is_numeric($vb))) {
				$cmp = $va - $vb;
			}
			elseif ($casesensitive) {
				$cmp = JString::strcmp($va, $vb, $locale);
			}
			else {
				$cmp = JString::strcasecmp($va, $vb, $locale);
			}

			if ($cmp > 0) {

				return $direction;
			}

			if ($cmp < 0) {

				return - $direction;
			}
		}

		return 0;
	}

	/**
	 * Multidimensional array safe unique test
	 * Borrowed from PHP.net
	 * @see http://au2.php.net/manual/en/function.array-unique.php
	 */
	public static function arrayUnique($myArray)
	{
		if (!is_array($myArray)) {
			return $myArray;
		}

		foreach ($myArray as &$myvalue){
			$myvalue=serialize($myvalue);
		}

		$myArray=array_unique($myArray);

		foreach ($myArray as &$myvalue){
			$myvalue=unserialize($myvalue);
		}

		return $myArray;
	}
}
