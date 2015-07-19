<?php
/**
 * Part of the Joomla Framework Utilities Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Utilities;

use Joomla\String\String;

/**
 * ArrayHelper is an array utility class for doing all sorts of odds and ends with arrays.
 *
 * @since  1.0
 */
final class ArrayHelper
{
	/**
	 * Private constructor to prevent instantiation of this class
	 */
	private function __construct()
	{
	}

	/**
	 * Function to convert array to integer values
	 *
	 * @param   array  $array    The source array to convert
	 * @param   mixed  $default  A default value (int|array) to assign if $array is not an array
	 *
	 * @return  array The converted array
	 *
	 * @since   1.0
	 */
	public static function toInteger($array, $default = null)
	{
		if (is_array($array))
		{
			$array = array_map('intval', $array);
		}
		else
		{
			if ($default === null)
			{
				$array = array();
			}
			elseif (is_array($default))
			{
				$array = self::toInteger($default, null);
			}
			else
			{
				$array = array((int) $default);
			}
		}

		return $array;
	}

	/**
	 * Utility function to map an array to a stdClass object.
	 *
	 * @param   array    $array      The array to map.
	 * @param   string   $class      Name of the class to create
	 * @param   boolean  $recursive  Convert also any array inside the main array
	 *
	 * @return  object   The object mapped from the given array
	 *
	 * @since   1.0
	 */
	public static function toObject(array $array, $class = 'stdClass', $recursive = true)
	{
		$obj = new $class;

		foreach ($array as $k => $v)
		{
			if ($recursive && is_array($v))
			{
				$obj->$k = self::toObject($v, $class);
			}
			else
			{
				$obj->$k = $v;
			}
		}

		return $obj;
	}

	/**
	 * Utility function to map an array to a string.
	 *
	 * @param   array    $array         The array to map.
	 * @param   string   $inner_glue    The glue (optional, defaults to '=') between the key and the value.
	 * @param   string   $outer_glue    The glue (optional, defaults to ' ') between array elements.
	 * @param   boolean  $keepOuterKey  True if final key should be kept.
	 *
	 * @return  string   The string mapped from the given array
	 *
	 * @since   1.0
	 */
	public static function toString(array $array, $inner_glue = '=', $outer_glue = ' ', $keepOuterKey = false)
	{
		$output = array();

		foreach ($array as $key => $item)
		{
			if (is_array($item))
			{
				if ($keepOuterKey)
				{
					$output[] = $key;
				}

				// This is value is an array, go and do it again!
				$output[] = self::toString($item, $inner_glue, $outer_glue, $keepOuterKey);
			}
			else
			{
				$output[] = $key . $inner_glue . '"' . $item . '"';
			}
		}

		return implode($outer_glue, $output);
	}

	/**
	 * Utility function to map an object to an array
	 *
	 * @param   object   $p_obj    The source object
	 * @param   boolean  $recurse  True to recurse through multi-level objects
	 * @param   string   $regex    An optional regular expression to match on field names
	 *
	 * @return  array    The array mapped from the given object
	 *
	 * @since   1.0
	 */
	public static function fromObject($p_obj, $recurse = true, $regex = null)
	{
		if (is_object($p_obj))
		{
			return self::arrayFromObject($p_obj, $recurse, $regex);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Utility function to map an object or array to an array
	 *
	 * @param   mixed    $item     The source object or array
	 * @param   boolean  $recurse  True to recurse through multi-level objects
	 * @param   string   $regex    An optional regular expression to match on field names
	 *
	 * @return  array  The array mapped from the given object
	 *
	 * @since   1.0
	 */
	private static function arrayFromObject($item, $recurse, $regex)
	{
		if (is_object($item))
		{
			$result = array();

			foreach (get_object_vars($item) as $k => $v)
			{
				if (!$regex || preg_match($regex, $k))
				{
					if ($recurse)
					{
						$result[$k] = self::arrayFromObject($v, $recurse, $regex);
					}
					else
					{
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
				$result[$k] = self::arrayFromObject($v, $recurse, $regex);
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
	 * @param   array   $array  The source array
	 * @param   string  $index  The index of the column or name of object property
	 *
	 * @return  array  Column of values from the source array
	 *
	 * @since   1.0
	 */
	public static function getColumn(array $array, $index)
	{
		$result = array();

		foreach ($array as $item)
		{
			if (is_array($item) && isset($item[$index]))
			{
				$result[] = $item[$index];
			}
			elseif (is_object($item) && isset($item->$index))
			{
				$result[] = $item->$index;
			}
		}

		return $result;
	}

	/**
	 * Utility function to return a value from a named array or a specified default
	 *
	 * @param   array|\ArrayAccess  $array    A named array or object that implements ArrayAccess
	 * @param   string              $name     The key to search for
	 * @param   mixed               $default  The default value to give if no key found
	 * @param   string              $type     Return type for the variable (INT, FLOAT, STRING, WORD, BOOLEAN, ARRAY)
	 *
	 * @return  mixed  The value from the source array
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.0
	 */
	public static function getValue($array, $name, $default = null, $type = '')
	{
		if (!is_array($array) && !($array instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException('The object must be an array or a object that implements ArrayAccess');
		}

		$result = null;

		if (isset($array[$name]))
		{
			$result = $array[$name];
		}

		// Handle the default case
		if (is_null($result))
		{
			$result = $default;
		}

		// Handle the type constraint
		switch (strtoupper($type))
		{
			case 'INT':
			case 'INTEGER':
				// Only use the first integer value
				@preg_match('/-?[0-9]+/', $result, $matches);
				$result = @(int) $matches[0];
				break;

			case 'FLOAT':
			case 'DOUBLE':
				// Only use the first floating point value
				@preg_match('/-?[0-9]+(\.[0-9]+)?/', $result, $matches);
				$result = @(float) $matches[0];
				break;

			case 'BOOL':
			case 'BOOLEAN':
				$result = (bool) $result;
				break;

			case 'ARRAY':
				if (!is_array($result))
				{
					$result = array($result);
				}
				break;

			case 'STRING':
				$result = (string) $result;
				break;

			case 'WORD':
				$result = (string) preg_replace('#\W#', '', $result);
				break;

			case 'NONE':
			default:
				// No casting necessary
				break;
		}

		return $result;
	}

	/**
	 * Takes an associative array of arrays and inverts the array keys to values using the array values as keys.
	 *
	 * Example:
	 * $input = array(
	 *     'New' => array('1000', '1500', '1750'),
	 *     'Used' => array('3000', '4000', '5000', '6000')
	 * );
	 * $output = ArrayHelper::invert($input);
	 *
	 * Output would be equal to:
	 * $output = array(
	 *     '1000' => 'New',
	 *     '1500' => 'New',
	 *     '1750' => 'New',
	 *     '3000' => 'Used',
	 *     '4000' => 'Used',
	 *     '5000' => 'Used',
	 *     '6000' => 'Used'
	 * );
	 *
	 * @param   array  $array  The source array.
	 *
	 * @return  array  The inverted array.
	 *
	 * @since   1.0
	 */
	public static function invert(array $array)
	{
		$return = array();

		foreach ($array as $base => $values)
		{
			if (!is_array($values))
			{
				continue;
			}

			foreach ($values as $key)
			{
				// If the key isn't scalar then ignore it.
				if (is_scalar($key))
				{
					$return[$key] = $base;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @param   array  $array  An array to test.
	 *
	 * @return  boolean  True if the array is an associative array.
	 *
	 * @since   1.0
	 */
	public static function isAssociative($array)
	{
		if (is_array($array))
		{
			foreach (array_keys($array) as $k => $v)
			{
				if ($k !== $v)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Pivots an array to create a reverse lookup of an array of scalars, arrays or objects.
	 *
	 * @param   array   $source  The source array.
	 * @param   string  $key     Where the elements of the source array are objects or arrays, the key to pivot on.
	 *
	 * @return  array  An array of arrays pivoted either on the value of the keys, or an individual key of an object or array.
	 *
	 * @since   1.0
	 */
	public static function pivot(array $source, $key = null)
	{
		$result  = array();
		$counter = array();

		foreach ($source as $index => $value)
		{
			// Determine the name of the pivot key, and its value.
			if (is_array($value))
			{
				// If the key does not exist, ignore it.
				if (!isset($value[$key]))
				{
					continue;
				}

				$resultKey   = $value[$key];
				$resultValue = $source[$index];
			}
			elseif (is_object($value))
			{
				// If the key does not exist, ignore it.
				if (!isset($value->$key))
				{
					continue;
				}

				$resultKey   = $value->$key;
				$resultValue = $source[$index];
			}
			else
			{
				// Just a scalar value.
				$resultKey   = $value;
				$resultValue = $index;
			}

			// The counter tracks how many times a key has been used.
			if (empty($counter[$resultKey]))
			{
				// The first time around we just assign the value to the key.
				$result[$resultKey] = $resultValue;
				$counter[$resultKey] = 1;
			}
			elseif ($counter[$resultKey] == 1)
			{
				// If there is a second time, we convert the value into an array.
				$result[$resultKey] = array(
					$result[$resultKey],
					$resultValue,
				);
				$counter[$resultKey]++;
			}
			else
			{
				// After the second time, no need to track any more. Just append to the existing array.
				$result[$resultKey][] = $resultValue;
			}
		}

		unset($counter);

		return $result;
	}

	/**
	 * Utility function to sort an array of objects on a given field
	 *
	 * @param   array  $a              An array of objects
	 * @param   mixed  $k              The key (string) or a array of key to sort on
	 * @param   mixed  $direction      Direction (integer) or an array of direction to sort in [1 = Ascending] [-1 = Descending]
	 * @param   mixed  $caseSensitive  Boolean or array of booleans to let sort occur case sensitive or insensitive
	 * @param   mixed  $locale         Boolean or array of booleans to let sort occur using the locale language or not
	 *
	 * @return  array  The sorted array of objects
	 *
	 * @since   1.0
	 */
	public static function sortObjects(array $a, $k, $direction = 1, $caseSensitive = true, $locale = false)
	{
		if (!is_array($locale) || !is_array($locale[0]))
		{
			$locale = array($locale);
		}

		$sortCase      = (array) $caseSensitive;
		$sortDirection = (array) $direction;
		$key           = (array) $k;
		$sortLocale    = $locale;

		usort(
			$a, function($a, $b) use($sortCase, $sortDirection, $key, $sortLocale)
			{
				for ($i = 0, $count = count($key); $i < $count; $i++)
				{
					if (isset($sortDirection[$i]))
					{
						$direction = $sortDirection[$i];
					}

					if (isset($sortCase[$i]))
					{
						$caseSensitive = $sortCase[$i];
					}

					if (isset($sortLocale[$i]))
					{
						$locale = $sortLocale[$i];
					}

					$va = $a->{$key[$i]};
					$vb = $b->{$key[$i]};

					if ((is_bool($va) || is_numeric($va)) && (is_bool($vb) || is_numeric($vb)))
					{
						$cmp = $va - $vb;
					}
					elseif ($caseSensitive)
					{
						$cmp = String::strcmp($va, $vb, $locale);
					}
					else
					{
						$cmp = String::strcasecmp($va, $vb, $locale);
					}

					if ($cmp > 0)
					{
						return $direction;
					}

					if ($cmp < 0)
					{
						return -$direction;
					}
				}

				return 0;
			}
		);

		return $a;
	}

	/**
	 * Multidimensional array safe unique test
	 *
	 * @param   array  $array  The array to make unique.
	 *
	 * @return  array
	 *
	 * @see     http://php.net/manual/en/function.array-unique.php
	 * @since   1.0
	 */
	public static function arrayUnique(array $array)
	{
		$array = array_map('serialize', $array);
		$array = array_unique($array);
		$array = array_map('unserialize', $array);

		return $array;
	}

	/**
	 * An improved array_search that allows for partial matching
	 * of strings values in associative arrays.
	 *
	 * @param   string   $needle         The text to search for within the array.
	 * @param   array    $haystack       Associative array to search in to find $needle.
	 * @param   boolean  $caseSensitive  True to search case sensitive, false otherwise.
	 *
	 * @return  mixed    Returns the matching array $key if found, otherwise false.
	 *
	 * @since   1.0
	 */
	public static function arraySearch($needle, array $haystack, $caseSensitive = true)
	{
		foreach ($haystack as $key => $value)
		{
			$searchFunc = ($caseSensitive) ? 'strpos' : 'stripos';

			if ($searchFunc($value, $needle) === 0)
			{
				return $key;
			}
		}

		return false;
	}

	/**
	 * Method to recursively convert data to a one dimension array.
	 *
	 * @param   array|object  $array      The array or object to convert.
	 * @param   string        $separator  The key separator.
	 * @param   string        $prefix     Last level key prefix.
	 *
	 * @return  array
	 *
	 * @since   1.3.0
	 */
	public static function flatten($array, $separator = '.', $prefix = '')
	{
		if ($array instanceof \Traversable)
		{
			$array = iterator_to_array($array);
		}
		elseif (is_object($array))
		{
			$array = get_object_vars($array);
		}

		foreach ($array as $k => $v)
		{
			$key = $prefix ? $prefix . $separator . $k : $k;

			if (is_object($v) || is_array($v))
			{
				$array = array_merge($array, static::flatten($v, $separator, $key));
			}
			else
			{
				$array[$key] = $v;
			}
		}

		return $array;
	}
}
