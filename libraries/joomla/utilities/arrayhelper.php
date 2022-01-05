<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * JArrayHelper is an array utility class for doing all sorts of odds and ends with arrays.
 *
 * @since       1.7.0
 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper instead
 */
abstract class JArrayHelper
{
	/**
	 * Option to perform case-sensitive sorts.
	 *
	 * @var    mixed  Boolean or array of booleans.
	 * @since  1.7.3
	 */
	protected static $sortCase;

	/**
	 * Option to set the sort direction.
	 *
	 * @var    mixed  Integer or array of integers.
	 * @since  1.7.3
	 */
	protected static $sortDirection;

	/**
	 * Option to set the object key to sort on.
	 *
	 * @var    string
	 * @since  1.7.3
	 */
	protected static $sortKey;

	/**
	 * Option to perform a language aware sort.
	 *
	 * @var    mixed  Boolean or array of booleans.
	 * @since  1.7.3
	 */
	protected static $sortLocale;

	/**
	 * Function to convert array to integer values
	 *
	 * @param   array  &$array   The source array to convert
	 * @param   mixed  $default  A default value (int|array) to assign if $array is not an array
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::toInteger instead
	 */
	public static function toInteger(&$array, $default = null)
	{
		$array = ArrayHelper::toInteger($array, $default);
	}

	/**
	 * Utility function to map an array to a stdClass object.
	 *
	 * @param   array    &$array     The array to map.
	 * @param   string   $class      Name of the class to create
	 * @param   boolean  $recursive  Convert also any array inside the main array
	 *
	 * @return  object   The object mapped from the given array
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::toObject instead
	 */
	public static function toObject(&$array, $class = 'stdClass', $recursive = true)
	{
		$obj = null;

		if (is_array($array))
		{
			$obj = ArrayHelper::toObject($array, $class, $recursive);
		}
		else
		{
			JLog::add('This method is typehinted to be an array in \Joomla\Utilities\ArrayHelper::toObject.', JLog::WARNING, 'deprecated');
		}

		return $obj;
	}

	/**
	 * Utility function to map an array to a string.
	 *
	 * @param   array    $array         The array to map.
	 * @param   string   $innerGlue     The glue (optional, defaults to '=') between the key and the value.
	 * @param   string   $outerGlue     The glue (optional, defaults to ' ') between array elements.
	 * @param   boolean  $keepOuterKey  True if final key should be kept.
	 *
	 * @return  string   The string mapped from the given array
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::toString instead
	 */
	public static function toString($array = null, $innerGlue = '=', $outerGlue = ' ', $keepOuterKey = false)
	{
		$output = array();

		if (is_array($array))
		{
			$output[] = ArrayHelper::toString($array, $innerGlue, $outerGlue, $keepOuterKey);
		}
		else
		{
			JLog::add('This method is typehinted to be an array in \Joomla\Utilities\ArrayHelper::toString.', JLog::WARNING, 'deprecated');
		}

		return implode($outerGlue, $output);
	}

	/**
	 * Utility function to map an object to an array
	 *
	 * @param   object   $object   The source object
	 * @param   boolean  $recurse  True to recurse through multi-level objects
	 * @param   string   $regex    An optional regular expression to match on field names
	 *
	 * @return  array    The array mapped from the given object
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::fromObject instead
	 */
	public static function fromObject($object, $recurse = true, $regex = null)
	{
		if (is_object($object))
		{
			return self::_fromObject($object, $recurse, $regex);
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
	 * @since   1.7.0
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
					if ($recurse)
					{
						$result[$k] = self::_fromObject($v, $recurse, $regex);
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
				$result[$k] = self::_fromObject($v, $recurse, $regex);
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
	 * @param   array   &$array  The source array
	 * @param   string  $index   The index of the column or name of object property
	 *
	 * @return  array  Column of values from the source array
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::getColumn instead
	 */
	public static function getColumn(&$array, $index)
	{
		$result = array();

		if (is_array($array))
		{
			$result = ArrayHelper::getColumn($array, $index);
		}
		else
		{
			JLog::add('This method is typehinted to be an array in \Joomla\Utilities\ArrayHelper::getColumn.', JLog::WARNING, 'deprecated');
		}

		return $result;
	}

	/**
	 * Utility function to return a value from a named array or a specified default
	 *
	 * @param   array   &$array   A named array
	 * @param   string  $name     The key to search for
	 * @param   mixed   $default  The default value to give if no key found
	 * @param   string  $type     Return type for the variable (INT, FLOAT, STRING, WORD, BOOLEAN, ARRAY)
	 *
	 * @return  mixed  The value from the source array
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::getValue instead
	 */
	public static function getValue(&$array, $name, $default = null, $type = '')
	{
		// Previously we didn't typehint an array. So force any object to be an array
		return ArrayHelper::getValue((array) $array, $name, $default, $type);
	}

	/**
	 * Takes an associative array of arrays and inverts the array keys to values using the array values as keys.
	 *
	 * Example:
	 * $input = array(
	 *     'New' => array('1000', '1500', '1750'),
	 *     'Used' => array('3000', '4000', '5000', '6000')
	 * );
	 * $output = JArrayHelper::invert($input);
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
	 * @since   3.1.4
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::invert instead
	 */
	public static function invert($array)
	{
		return ArrayHelper::invert($array);
	}

	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @param   array  $array  An array to test.
	 *
	 * @return  boolean  True if the array is an associative array.
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::isAssociative instead
	 */
	public static function isAssociative($array)
	{
		return ArrayHelper::isAssociative($array);
	}

	/**
	 * Pivots an array to create a reverse lookup of an array of scalars, arrays or objects.
	 *
	 * @param   array   $source  The source array.
	 * @param   string  $key     Where the elements of the source array are objects or arrays, the key to pivot on.
	 *
	 * @return  array  An array of arrays pivoted either on the value of the keys, or an individual key of an object or array.
	 *
	 * @since   1.7.3
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::pivot instead
	 */
	public static function pivot($source, $key = null)
	{
		$result = array();

		if (is_array($source))
		{
			$result = ArrayHelper::pivot($source, $key);
		}
		else
		{
			JLog::add('This method is typehinted to be an array in \Joomla\Utilities\ArrayHelper::pivot.', JLog::WARNING, 'deprecated');
		}

		return $result;
	}

	/**
	 * Utility function to sort an array of objects on a given field
	 *
	 * @param   array  &$a             An array of objects
	 * @param   mixed  $k              The key (string) or an array of keys to sort on
	 * @param   mixed  $direction      Direction (integer) or an array of direction to sort in [1 = Ascending] [-1 = Descending]
	 * @param   mixed  $caseSensitive  Boolean or array of booleans to let sort occur case sensitive or insensitive
	 * @param   mixed  $locale         Boolean or array of booleans to let sort occur using the locale language or not
	 *
	 * @return  array  The sorted array of objects
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::sortObjects instead
	 */
	public static function sortObjects(&$a, $k, $direction = 1, $caseSensitive = true, $locale = false)
	{
		if (!is_array($locale) || !is_array($locale[0]))
		{
			$locale = array($locale);
		}

		self::$sortCase = (array) $caseSensitive;
		self::$sortDirection = (array) $direction;
		self::$sortKey = (array) $k;
		self::$sortLocale = $locale;

		usort($a, array(__CLASS__, '_sortObjects'));

		self::$sortCase = null;
		self::$sortDirection = null;
		self::$sortKey = null;
		self::$sortLocale = null;

		return $a;
	}

	/**
	 * Callback function for sorting an array of objects on a key
	 *
	 * @param   array  $a  An array of objects
	 * @param   array  $b  An array of objects
	 *
	 * @return  integer  Comparison status
	 *
	 * @see     JArrayHelper::sortObjects()
	 * @since   1.7.0
	 */
	protected static function _sortObjects($a, $b)
	{
		$key = self::$sortKey;

		for ($i = 0, $count = count($key); $i < $count; $i++)
		{
			if (isset(self::$sortDirection[$i]))
			{
				$direction = self::$sortDirection[$i];
			}

			if (isset(self::$sortCase[$i]))
			{
				$caseSensitive = self::$sortCase[$i];
			}

			if (isset(self::$sortLocale[$i]))
			{
				$locale = self::$sortLocale[$i];
			}

			$va = $a->{$key[$i]};
			$vb = $b->{$key[$i]};

			if ((is_bool($va) || is_numeric($va)) && (is_bool($vb) || is_numeric($vb)))
			{
				$cmp = $va - $vb;
			}
			elseif ($caseSensitive)
			{
				$cmp = StringHelper::strcmp($va, $vb, $locale);
			}
			else
			{
				$cmp = StringHelper::strcasecmp($va, $vb, $locale);
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

	/**
	 * Multidimensional array safe unique test
	 *
	 * @param   array  $myArray  The array to make unique.
	 *
	 * @return  array
	 *
	 * @link    https://www.php.net/manual/en/function.array-unique.php
	 * @since   1.7.0
	 * @deprecated  4.0 Use Joomla\Utilities\ArrayHelper::arrayUnique instead
	 */
	public static function arrayUnique($myArray)
	{
		return is_array($myArray) ? ArrayHelper::arrayUnique($myArray) : $myArray;
	}
}
