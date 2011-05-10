<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Registry
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Abstract Format for JRegistry
 *
 * @package     Joomla.Platform
 * @subpackage  Registry
 * @since       11.1
 */
abstract class JRegistryFormat
{
	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string   The format to load
	 * @return  object   Registry format handler
	 * @throws	JException
	 * @since   11.1
	 */
	public static function getInstance($type)
	{
		// Initialize static variable.
		static $instances;
		if (!isset ($instances)) {
			$instances = array ();
		}

		// Sanitize format type.
		$type = strtolower(preg_replace('/[^A-Z0-9_]/i', '', $type));

		// Only instantiate the object if it doesn't already exist.
		if (!isset($instances[$type])) {
			// Only load the file the class does not exist.
			$class = 'JRegistryFormat'.$type;
			if (!class_exists($class)) {
				$path = dirname(__FILE__).'/format/'.$type.'.php';
				if (is_file($path)) {
					require_once $path;
				} else {
					throw new JException(JText::_('JLIB_REGISTRY_EXCEPTION_LOAD_FORMAT_CLASS'), 500, E_ERROR);
				}
			}

			$instances[$type] = new $class();
		}
		return $instances[$type];
	}

	/**
	 * Converts an object into a formatted string.
	 *
	 * @param   object   Data Source Object.
	 * @param   array    An array of options for the formatter.
	 * @return  string   Formatted string.
	 * @since   11.1
	 */
	abstract public function objectToString($object, $options = null);

	/**
	 * Converts a formatted string into an object.
	 *
	 * @param   string   Formatted string
	 * @param   array    An array of options for the formatter.
	 * @return  object   Data Object
	 * @since   11.1
	 */
	abstract public function stringToObject($data, $options = null);
}