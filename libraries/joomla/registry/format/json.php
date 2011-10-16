<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Registry
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * JSON format handler for JRegistry.
 *
 * @package     Joomla.Platform
 * @subpackage  Registry
 * @since       11.1
 */
class JRegistryFormatJSON extends JRegistryFormat
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  JSON formatted string.
	 *
	 * @since   11.1
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
	 * @param   string  $data     JSON formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object   Data object.
	 *
	 * @since   11.1
	 */
	public function stringToObject($data, $options = array('processSections' => false))
	{
		// Fix legacy API.
		if (is_bool($options))
		{
			$options = array('processSections' => $options);

			// Deprecation warning.
			jimport('joomla.log.log');

			JLog::add('JRegistryFormatJSON::stringToObject() second argument should not be a boolean.', JLog::WARNING, 'deprecated');
		}

		$data = trim($data);
		if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}'))
		{
			$ini = JRegistryFormat::getInstance('INI');
			$obj = $ini->stringToObject($data, $options);
		}
		else
		{
			$obj = json_decode($data);
		}
		return $obj;
	}
}
