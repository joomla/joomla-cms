<?php
/**
 * Part of the Joomla Framework Registry Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Format;

use Joomla\Registry\Factory;
use Joomla\Registry\FormatInterface;

/**
 * JSON format handler for Registry.
 *
 * @since  1.0
 */
class Json implements FormatInterface
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  JSON formatted string.
	 *
	 * @since   1.0
	 */
	public function objectToString($object, array $options = [])
	{
		$bitmask = isset($options['bitmask']) ? $options['bitmask'] : 0;
		$depth = isset($options['depth']) ? $options['depth'] : 512;

		return json_encode($object, $bitmask, $depth);
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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function stringToObject($data, array $options = ['processSections' => false])
	{
		$data = trim($data);

		if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}'))
		{
			return Factory::getFormat('Ini')->stringToObject($data, $options);
		}

		$decoded = json_decode($data);

		// Check for an error decoding the data
		if ($decoded === null)
		{
			throw new \RuntimeException(sprintf('Error decoding JSON data: %s', json_last_error_msg()));
		}

		return (object) $decoded;
	}
}
