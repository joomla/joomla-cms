<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JArrayHelper is an array utility class for doing all sorts of odds and ends with arrays.
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JArrayHelper
{
	/**
	 * Function to convert array to integer values
	 *
	 * @static
	 * @param	array	$array		The source array to convert
	 * @param	mixed	$default	A default value (int|array) to assign if $array is not an array
	 * @since	1.5
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
	 * @static
	 * @param	array	$array		The array to map.
	 * @param	string	$calss		Name of the class to create
	 * @return	object	The object mapped from the given array
	 * @since	1.5
	 */
	public static function toObject(&$array, $class = 'stdClass')
	{
		$obj = null;
		if (is_array($array)) {
			$obj = new $class();
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
	 * @static
	 * @param	object	The source object
	 * @param	boolean	True to recurve through multi-level objects
	 * @param	string	An optional regular expression to match on field names
	 * @return	array	The array mapped from the given object
	 * @since	1.5
	 */
	public static function fromObject($p_obj, $recurse = true, $regex = null)
	{
		$result = null;
		if (is_object($p_obj)) {
			$result = array();

			foreach (get_object_vars($p_obj) as $k => $v) {
				if ($regex) {
					if (!preg_match($regex, $k)) {
						continue;
					}
				}

				if (is_object($v)) {
					if ($recurse) {
						$result[$k] = JArrayHelper::fromObject($v, $recurse, $regex);
					}
				} else {
					$result[$k] = $v;
				}
			}
		}
		return $result;
	}

	/**
	 * Extracts a column from an array of arrays or objects
	 *
	 * @static
	 * @param	array	$array	The source array
	 * @param	string	$index	The index of the column or name of object property
	 * @return	array	Column of values from the source array
	 * @since	1.5
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
	 * @static
	 * @param	array	$array		A named array
	 * @param	string	$name		The key to search for
	 * @param	mixed	$default	The default value to give if no key found
	 * @param	string	$type		Return type for the variable (INT, FLOAT, STRING, WORD, BOOLEAN, ARRAY)
	 * @return	mixed	The value from the source array
	 * @since	1.5
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
	 * @param	array		An array to test.
	 * @return	boolean		True if the array is an associative array.
	 * @since	1.6
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
	 * @static
	 * @param	array			$arr			An array of objects
	 * @param	string|array	$k				The key or a array of key to sort on
	 * @param	int|array		$direction		Direction or an array of direction to sort in [1 = Ascending] [-1 = Descending]
	 * @param	bool|array		$casesensitive	Let sort occur casesensitive or insensitive
	 * @param	bool|array		$locale			Let sort occur using the locale language or not
	 *
	 * @return	array							The sorted array of objects
	 * @since	1.5
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
	 * Private callback function for sorting an array of objects on a key
	 *
	 * @static
	 * @param	array	$a	An array of objects
	 * @param	array	$b	An array of objects
	 * @return	int		Comparison status
	 * @since	1.5
	 * @see		JArrayHelper::sortObjects()
	 */
	public static function _sortObjects(&$a, &$b)
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
}
