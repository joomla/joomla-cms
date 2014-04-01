<?php
/**
 * Part of the Joomla Framework Registry Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry;

/**
 * Abstract Format for Registry
 *
 * @since  1.0
 */
abstract class AbstractRegistryFormat
{
	/**
	 * @var    array  Format instances container.
	 * @since  1.0
	 */
	protected static $instances = array();

	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $type  The format to load
	 *
	 * @return  AbstractRegistryFormat  Registry format handler
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public static function getInstance($type)
	{
		// Sanitize format type.
		$type = strtolower(preg_replace('/[^A-Z0-9_]/i', '', $type));

		// Only instantiate the object if it doesn't already exist.
		if (!isset(self::$instances[$type]))
		{
			$class = '\\Joomla\\Registry\\Format\\' . ucfirst($type);

			if (!class_exists($class))
			{
				throw new \InvalidArgumentException('Unable to load format class.', 500);
			}

			self::$instances[$type] = new $class;
		}

		return self::$instances[$type];
	}

	/**
	 * Converts an object into a formatted string.
	 *
	 * @param   object  $object   Data Source Object.
	 * @param   array   $options  An array of options for the formatter.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   1.0
	 */
	abstract public function objectToString($object, $options = null);

	/**
	 * Converts a formatted string into an object.
	 *
	 * @param   string  $data     Formatted string
	 * @param   array   $options  An array of options for the formatter.
	 *
	 * @return  object  Data Object
	 *
	 * @since   1.0
	 */
	abstract public function stringToObject($data, array $options = array());
}
