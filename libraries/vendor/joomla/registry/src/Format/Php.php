<?php
/**
 * Part of the Joomla Framework Registry Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Format;

use Joomla\Registry\AbstractRegistryFormat;

/**
 * PHP class format handler for Registry
 *
 * @since  1.0
 */
class Php extends AbstractRegistryFormat
{
	/**
	 * Converts an object into a php class string.
	 * - NOTE: Only one depth level is supported.
	 *
	 * @param   object  $object  Data Source Object
	 * @param   array   $params  Parameters used by the formatter
	 *
	 * @return  string  Config class formatted string
	 *
	 * @since   1.0
	 */
	public function objectToString($object, $params = array())
	{
		// A class must be provided
		$class = !empty($params['class']) ? $params['class'] : 'Registry';

		// Build the object variables string
		$vars = '';

		foreach (get_object_vars($object) as $k => $v)
		{
			if (is_scalar($v))
			{
				$vars .= "\tpublic $" . $k . " = '" . addcslashes($v, '\\\'') . "';\n";
			}
			elseif (is_array($v) || is_object($v))
			{
				$vars .= "\tpublic $" . $k . " = " . $this->getArrayString((array) $v) . ";\n";
			}
		}

		$str = "<?php\n";

		// If supplied, add a namespace to the class object
		if (isset($params['namespace']) && $params['namespace'] != '')
		{
			$str .= "namespace " . $params['namespace'] . ";\n\n";
		}

		$str .= "class " . $class . " {\n";
		$str .= $vars;
		$str .= "}";

		// Use the closing tag if it not set to false in parameters.
		if (!isset($params['closingtag']) || $params['closingtag'] !== false)
		{
			$str .= "\n?>";
		}

		return $str;
	}

	/**
	 * Parse a PHP class formatted string and convert it into an object.
	 *
	 * @param   string  $data     PHP Class formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object   Data object.
	 *
	 * @since   1.0
	 */
	public function stringToObject($data, array $options = array())
	{
		return true;
	}

	/**
	 * Method to get an array as an exported string.
	 *
	 * @param   array  $a  The array to get as a string.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	protected function getArrayString($a)
	{
		$s = 'array(';
		$i = 0;

		foreach ($a as $k => $v)
		{
			$s .= ($i) ? ', ' : '';
			$s .= '"' . $k . '" => ';

			if (is_array($v) || is_object($v))
			{
				$s .= $this->getArrayString((array) $v);
			}
			else
			{
				$s .= '"' . addslashes($v) . '"';
			}

			$i++;
		}

		$s .= ')';

		return $s;
	}
}
