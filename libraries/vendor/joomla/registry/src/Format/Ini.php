<?php
/**
 * Part of the Joomla Framework Registry Package
 *
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Format;

use Joomla\Registry\AbstractRegistryFormat;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * INI format handler for Registry.
 *
 * @since  1.0
 */
class Ini extends AbstractRegistryFormat
{
	/**
	 * Default options array
	 *
	 * @var    array
	 * @since  1.3.0
	 */
	protected static $options = array(
		'supportArrayValues' => false,
		'parseBooleanWords'  => false,
		'processSections'    => false,
	);

	/**
	 * A cache used by stringToObject.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected static $cache = array();

	/**
	 * Converts an object into an INI formatted string
	 * - Unfortunately, there is no way to have ini values nested further than two
	 * levels deep.  Therefore we will only go through the first two levels of
	 * the object.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  INI formatted string.
	 *
	 * @since   1.0
	 */
	public function objectToString($object, $options = array())
	{
		$options            = array_merge(self::$options, $options);
		$supportArrayValues = $options['supportArrayValues'];

		$local  = array();
		$global = array();

		$variables = get_object_vars($object);

		$last = \count($variables);

		// Assume that the first element is in section
		$inSection = true;

		// Iterate over the object to set the properties.
		foreach ($variables as $key => $value)
		{
			// If the value is an object then we need to put it in a local section.
			if (\is_object($value))
			{
				// Add an empty line if previous string wasn't in a section
				if (!$inSection)
				{
					$local[] = '';
				}

				// Add the section line.
				$local[] = '[' . $key . ']';

				// Add the properties for this section.
				foreach (get_object_vars($value) as $k => $v)
				{
					if (\is_array($v) && $supportArrayValues)
					{
						$assoc = ArrayHelper::isAssociative($v);

						foreach ($v as $arrayKey => $item)
						{
							$arrayKey = $assoc ? $arrayKey : '';
							$local[]  = $k . '[' . $arrayKey . ']=' . $this->getValueAsIni($item);
						}
					}
					else
					{
						$local[] = $k . '=' . $this->getValueAsIni($v);
					}
				}

				// Add empty line after section if it is not the last one
				if (--$last !== 0)
				{
					$local[] = '';
				}
			}
			elseif (\is_array($value) && $supportArrayValues)
			{
				$assoc = ArrayHelper::isAssociative($value);

				foreach ($value as $arrayKey => $item)
				{
					$arrayKey = $assoc ? $arrayKey : '';
					$global[] = $key . '[' . $arrayKey . ']=' . $this->getValueAsIni($item);
				}
			}
			else
			{
				// Not in a section so add the property to the global array.
				$global[]  = $key . '=' . $this->getValueAsIni($value);
				$inSection = false;
			}
		}

		return implode("\n", array_merge($global, $local));
	}

	/**
	 * Parse an INI formatted string and convert it into an object.
	 *
	 * @param   string  $data     INI formatted string to convert.
	 * @param   array   $options  An array of options used by the formatter, or a boolean setting to process sections.
	 *
	 * @return  object   Data object.
	 *
	 * @since   1.0
	 */
	public function stringToObject($data, array $options = array())
	{
		$options = array_merge(self::$options, $options);

		// Check the memory cache for already processed strings.
		$hash = md5($data . ':' . (int) $options['processSections']);

		if (isset(self::$cache[$hash]))
		{
			return self::$cache[$hash];
		}

		// If no lines present just return the object.
		if (empty($data))
		{
			return new stdClass;
		}

		$obj     = new stdClass;
		$section = false;
		$array   = false;
		$lines   = explode("\n", $data);

		// Process the lines.
		foreach ($lines as $line)
		{
			// Trim any unnecessary whitespace.
			$line = trim($line);

			// Ignore empty lines and comments.
			if (empty($line) || ($line[0] === ';'))
			{
				continue;
			}

			if ($options['processSections'])
			{
				$length = \strlen($line);

				// If we are processing sections and the line is a section add the object and continue.
				if ($line[0] === '[' && ($line[$length - 1] === ']'))
				{
					$section       = substr($line, 1, $length - 2);
					$obj->$section = new stdClass;

					continue;
				}
			}
			elseif ($line[0] === '[')
			{
				continue;
			}

			// Check that an equal sign exists and is not the first character of the line.
			if (!strpos($line, '='))
			{
				// Maybe throw exception?
				continue;
			}

			// Get the key and value for the line.
			list($key, $value) = explode('=', $line, 2);

			// If we have an array item
			if (substr($key, -1) === ']' && ($openBrace = strpos($key, '[', 1)) !== false)
			{
				if ($options['supportArrayValues'])
				{
					$array    = true;
					$arrayKey = substr($key, $openBrace + 1, -1);

					// If we have a multi-dimensional array or malformed key
					if (strpos($arrayKey, '[') !== false || strpos($arrayKey, ']') !== false)
					{
						// Maybe throw exception?
						continue;
					}

					$key = substr($key, 0, $openBrace);
				}
				else
				{
					continue;
				}
			}

			// Validate the key.
			if (preg_match('/[^A-Z0-9_]/i', $key))
			{
				// Maybe throw exception?
				continue;
			}

			// If the value is quoted then we assume it is a string.
			$length = \strlen($value);

			if ($length && ($value[0] === '"') && ($value[$length - 1] === '"'))
			{
				// Strip the quotes and Convert the new line characters.
				$value = stripcslashes(substr($value, 1, $length - 2));
				$value = str_replace('\n', "\n", $value);
			}
			else
			{
				// If the value is not quoted, we assume it is not a string.

				// If the value is 'false' assume boolean false.
				if ($value === 'false')
				{
					$value = false;
				}
				elseif ($value === 'true')
				{
					// If the value is 'true' assume boolean true.
					$value = true;
				}
				elseif ($options['parseBooleanWords'] && \in_array(strtolower($value), array('yes', 'no'), true))
				{
					// If the value is 'yes' or 'no' and option is enabled assume appropriate boolean
					$value = (strtolower($value) === 'yes');
				}
				elseif (is_numeric($value))
				{
					// If the value is numeric than it is either a float or int.
					// If there is a period then we assume a float.
					if (strpos($value, '.') !== false)
					{
						$value = (float) $value;
					}
					else
					{
						$value = (int) $value;
					}
				}
			}

			// If a section is set add the key/value to the section, otherwise top level.
			if ($section)
			{
				if ($array)
				{
					if (!isset($obj->$section->$key))
					{
						$obj->$section->$key = array();
					}

					if (!empty($arrayKey))
					{
						$obj->$section->{$key}[$arrayKey] = $value;
					}
					else
					{
						$obj->$section->{$key}[] = $value;
					}
				}
				else
				{
					$obj->$section->$key = $value;
				}
			}
			else
			{
				if ($array)
				{
					if (!isset($obj->$key))
					{
						$obj->$key = array();
					}

					if (!empty($arrayKey))
					{
						$obj->{$key}[$arrayKey] = $value;
					}
					else
					{
						$obj->{$key}[] = $value;
					}
				}
				else
				{
					$obj->$key = $value;
				}
			}

			$array = false;
		}

		// Cache the string to save cpu cycles -- thus the world :)
		self::$cache[$hash] = clone $obj;

		return $obj;
	}

	/**
	 * Method to get a value in an INI format.
	 *
	 * @param   mixed  $value  The value to convert to INI format.
	 *
	 * @return  string  The value in INI format.
	 *
	 * @since   1.0
	 */
	protected function getValueAsIni($value)
	{
		$string = '';

		switch (\gettype($value))
		{
			case 'integer':
			case 'double':
				$string = $value;

				break;

			case 'boolean':
				$string = $value ? 'true' : 'false';

				break;

			case 'string':
				// Sanitize any CRLF characters..
				$string = '"' . str_replace(array("\r\n", "\n"), '\\n', $value) . '"';

				break;
		}

		return $string;
	}
}
