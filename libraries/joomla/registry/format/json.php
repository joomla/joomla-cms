<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Registry
 */

defined('JPATH_PLATFORM') or die;

/**
 * JSON format handler for JRegistry.
 *
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @since		1.6
 */
class JRegistryFormatJSON extends JRegistryFormat
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param	object	Data source object.
	 * @param	array	Options used by the formatter.
	 * @return	string	JSON formatted string.
	 * @since	1.6
	 */
	public function objectToString($object, $options = array())
	{
		return json_encode($object);
	}

	/**
	 * Parse a JSON formatted string and convert it into an object.
	 *
	 * If the string is not in JSON format, this method will attempt to parse it as INI format.
	 *
	 * @param	string	JSON formatted string to convert.
	 * @param	array	Options used by the formatter.
	 * @return	object	Data object.
	 */
	public function stringToObject($data, $process_sections = false)
	{
		$data = trim($data);
		if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}')) {
			$ini = JRegistryFormat::getInstance('INI');
			$obj = $ini->stringToObject($data, $process_sections);
		} else {
			$obj = json_decode($data);
		}
		return $obj;
	}
}
