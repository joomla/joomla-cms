<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * JArrayHelper is an array utility class for doing all sorts of odds and ends with arrays.
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
abstract class JArrayHelper
{
	/**
	 * Sort direction, should be +1 or -1.
	 *
	 * @var int
	 */
	static protected $_sortDirection;

	/**
	 * Sort key.
	 *
	 * @var string
	 */
	static protected $_sortKey;

	/**
	 * Add slashes transform for toString().
	 *
	 * @param string Input string.
	 * @param string Quote character(s).
	 * @return string Input string with PHP escapes and quote escaped
	 */
	protected static function _addSlashes($str, $quotes) {
		return addcslashes($str, "\0\n\r\t\\" . $quotes);
	}

	/**
	 * Null transform function for toString.
	 *
	 * @param string Input string.
	 * @return string Same as input.
	 */
	protected static function _nullTransform($str) {
		return $str;
	}

	/**
	 * Internal callback function for sorting an array of objects on a key
	 *
	 * @param   array   An array of objects
	 * @param   array   An array of objects
	 * @return  int     Comparison status
	 * @since   1.5
	 * @see     JArrayHelper::sortObjects()
	 */
	protected static function _sortObjects(&$a, &$b)
	{
		$key = self::$_sortKey;
		if ($a->$key > $b->$key) {
			return self::$_sortDirection;
		} elseif ($a->$key < $b->$key) {
			return -1 * self::$_sortDirection;
		}
		return 0;
	}

	/**
	 * Utility function to map an object to an array
	 *
	 * @param   object  The source object
	 * @param   boolean True to recurve through multi-level objects
	 * @param   string  An optional regular expression to match on field names
	 * @return  array   The array mapped from the given object
	 * @since   1.5
	 */
	public static function fromObject($source, $recurse = true, $regex = null)
	{
		$result = null;
		if (is_object($source))
		{
			$result = array();
			foreach (get_object_vars($source) as $key => $val)
			{
				if ($regex)
				{
					if (!preg_match($regex, $key))
					{
						continue;
					}
				}
				if (is_object($val) && $recurse)
				{
					$result[$key] = JArrayHelper::fromObject($val, $recurse, $regex);
				} else {
					$result[$key] = $val;
				}
			}
		}
		return $result;
	}

	/**
	 * Extracts a column from an array of arrays or objects
	 *
	 * @param   array   The source array
	 * @param   string  The index of the column or name of object property
	 * @return  array   Column of values from the source array
	 * @since   1.5
	 */
	public static function getColumn(&$data, $index)
	{
		$result = array();

		if (is_array($data))
		{
			$n = count($data);
			foreach ($data as &$item)
			{
				if (is_array($item) && isset($item[$index])) {
					$result[] = $item[$index];
				} elseif (is_object($item) && isset($item-> $index)) {
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
	 * @param   array   A named array.
	 * @param   string  The key to search for.
	 * @param   mixed   The default value to give if no key found.
	 * @param   string  Return type for the variable (INT, FLOAT, STRING, WORD,
	 * BOOLEAN, ARRAY).
	 * @return  mixed   The value from the source array.
	 * @since   1.5
	 */
	public static function getValue(&$data, $name, $default = null, $type = '')
	{
		// Initialize variables
		$result = null;

		if (isset ($data[$name])) {
			$result = $data[$name];
		}

		// Handle the default case
		if (is_null($result)) {
			$result = $default;
		}

		// Handle the type constraint
		switch (strtoupper($type))
		{
			case 'INT' :
			case 'INTEGER' :
				// Only use the first integer value
				@preg_match('/-?[0-9]+/', $result, $matches);
				$result = @(int) $matches[0];
				break;

			case 'FLOAT' :
			case 'DOUBLE' :
				// Only use the first floating point value
				@preg_match('/-?[0-9]+(\.[0-9]+)?/', $result, $matches);
				$result = @(float) $matches[0];
				break;

			case 'BOOL' :
			case 'BOOLEAN' :
				$result = (bool) $result;
				break;

			case 'ARRAY' :
				if (!is_array($result)) {
					$result = array($result);
				}
				break;

			case 'STRING' :
				$result = (string) $result;
				break;

			case 'WORD' :
				$result = (string) preg_replace( '#\W#', '', $result );
				break;

			case 'NONE' :
			default :
				// No casting necessary
				break;
		}
		return $result;
	}

	/**
	 * Utility function to sort an array of objects on a given field
	 *
	 * @param   array   An array of objects to be sorted. Passed by reference.
	 * @param   string  The key to sort on
	 * @param   int     Direction to sort in [1 = Ascending] [-1 = Descending]
	 * @return  array   The sorted array of objects
	 * @since   1.5
	 */
	public static function sortObjects(&$data, $key, $direction = 1)
	{
		self::$_sortKey = $key;
		self::$_sortDirection = $direction;
		usort($data, array('JArrayHelper', '_sortObjects'));

		return $data;
	}

	/**
	 * Function to convert array to integer values
	 *
	 * @static
	 * @param	array	The source array to convert
	 * @param	mixed	A default value (int|array) to assign if the data is not
	 * an array.
	 * @since	1.5
	 */
	public static function toInteger(&$data, $default = null)
	{
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = (int) $val;
			}
		} else {
			if ($default === null) {
				$data = array();
			} elseif (is_array($default)) {
				JArrayHelper::toInteger($default, null);
				$data = $default;
			} else {
				$data = array((int) $default);
			}
		}
	}

	/**
	 * Utility function to map an array to a JStdClass object.
	 *
	 * @param	array	The array to map.
	 * @param	string	Name of the class to create
	 * @return	object	The object mapped from the given array
	 * @since	1.5
	 */
	public static function toObject(&$data, $class = 'JStdClass')
	{
		$obj = null;
		if (is_array($data))
		{
			$obj = new $class();
			foreach ($data as $k => $v)
			{
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
	 * Convert array to a string, recurses into sub-arrays.
	 *
	 * @internal Anyone have a clue as to why the array is optional???
	 * @param array Optional. The array to be converted.
	 * @param string|array Optional. If a string, the "inner glue" (see options
	 * below). If an array, a set of options:
	 * <ul><li>
	 * </li><li>innerGlue String. String to delimit the array key and the value.
	 * </li><li>keepOuterKey Boolean. When nesting arrays, setting this true
	 * will cause the key of the sub-array to appear in the output.
	 * </li><li>nestClose string. The string used to mark the end of a nested
	 * array. Defaults to empty string.
	 * </li><li>nestMode boolean. If disabled, nest glue is ignored and the
	 * value of keepOuterKey is used.
	 * </li><li>nestOpen string. The string used to mark the beginning of a
	 * nested array. Defaults to empty string.
	 * </li><li>quoteChar string. Character used to quote the array values.
	 * Defaults to double-quote.
	 * </li><li>transform string. Transform function to apply to scalar array
	 * values. Valid values are: none, slash (escape with backslashes), special
	 * (htmlspecialchars, UTF-8), entities (htmlentities, UTF-8), and callback.
	 * The default is special.
	 * </li><li>transformFunction string|array. Callback function used if the
	 * transform option is callback.
	 * </li></ul>
	 * @param string The "outer glue", which separates array entries.
	 * @param boolean Optional. See option definitions above.
	 * @return string Formatted array contents.
	 */
	public static function toString(
		$data = null, $innerGlue = '=', $outerGlue = ' ', $keepOuterKey = false
	) {
		static $optionDefaults;

		if (empty($optionDefaults)) {
			$optionDefaults = array(
				'innerGlue' => '=',
				'keepOuterKey' => false,
				'nestClose' => '',
				'nestMode' => false,
				'nestOpen' => '',
				'outerGlue' => ' ',
				'quoteChar' => '"',
				'transform' => '',
				'transformFunction' => '',
			);
		}
		if (is_array($innerGlue)) {
			$options = array_merge($optionDefaults, $innerGlue);
		} else {
			$options = $optionDefaults;
			$options['innerGlue'] = $innerGlue;
			$options['outerGlue'] = $outerGlue;
			$options['keepOuterKey'] = $keepOuterKey;
		}
		if (! $options['nestMode']) {
			$options['nestClose'] = '';
			$options['nestOpen'] = '';
		}
		$output = array();

		if (is_array($data))
		{
			/*
			 * Determine the appropriate transform callback function and
			 * arguments.
			 */
			$transArgs = array(0 => '');
			switch ($options['transform']) {
				case 'callback': {
					$transFn = $options['transformFunction'];
				}
				break;

				case 'entities': {
					$transFn = 'htmlentities';
					$transArgs[1] = $options['quoteChar'] == '\'' ? ENT_QUOTES : ENT_COMPAT;
					$transArgs[2] = 'UTF-8';
				}
				break;

				case 'none': {
					$transFn = array(__CLASS__, '_nullTransform');
				}
				break;

				case 'slashes': {
					$transFn = array(__CLASS__, '_addSlashes');
					$transArgs[1] = $options['quoteChar'];
				}
				break;

				case 'special':
				default: {
					$transFn = 'htmlspecialchars';
					$transArgs[1] = $options['quoteChar'] == '\'' ? ENT_QUOTES : ENT_COMPAT;
					$transArgs[2] = 'UTF-8';
				}
				break;

			}
			/*
			 * Convert each element in the array.
			 */
			foreach ($data as $key => $item)
			{
				if (is_array($item))
				{
					// This is value is an array, recurse and do it again!
					if ($options['nestMode']) {
						$output[] = $key . $options['innerGlue']
							. $options['nestOpen'] . self::toString($item, $options)
							 . $options['nestClose'];
					} else {
						if ($options['keepOuterKey']) {
							$output[] = $key;
						}
						$output[] = self::toString($item, $options);
					}
				} else {
					$transArgs[0] = $item;
					$output[] = $key . $options['innerGlue'] . $options['quoteChar']
						. call_user_func_array($transFn, $transArgs) . $options['quoteChar'];
				}
			}
		}

		return implode($options['outerGlue'], $output);
	}

}
