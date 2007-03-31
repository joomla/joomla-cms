<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * INI format handler for JRegistry
 *
 * @author 		Samuel Moffatt <pasamio@gmail.com>
 * @package 	Joomla.Framework
 * @subpackage		Registry
 * @since		1.5
 */
class JRegistryFormatINI extends JRegistryFormat {

	/**
	 * Converts an object into an INI formatted string
	 * 	-	Unfortunately, there is no way to have ini values nested further than two
	 * 		levels deep.  Therefore we will only go through the first two levels of
	 * 		the object.
	 *
	 * @access public
	 * @param object $object Data Source Object
	 * @param array  $param  Parameters used by the formatter
	 * @return string INI Formatted String
	 */
	function objectToString( &$object, $params ) {

		// Initialize variables
		$retval = '';
		$prepend = '';

		// First handle groups (or first level key/value pairs)
		foreach (get_object_vars( $object ) as $key => $level1) {

			if (is_object($level1)) {
				// This field is an object, so we treat it as a section
				$retval .= "[".$key."]\n";
				foreach (get_object_vars( $level1 ) as $key => $level2) {
					if (!is_object($level2) && !is_array($level2)) {
						$retval .= $key."=".$level2."\n";
					}
				}
				$retval .= "\n";
			} else {
				$prepend .= $key."=".$level1."\n";
			}
		}
		return $prepend."\n".$retval;
	}

	/**
	 * Parse an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 *
	 * @access public
	 * @param mixed The INI string or array of lines
	 * @param boolean add an associative index for each section [in brackets]
	 * @return object Data Object
	 */
	function &stringToObject( $data, $process_sections = false )
	{
		if (is_string($data)) {
			$lines = explode("\n", $data);
		} else {
			if (is_array($data)) {
				$lines = $data;
			} else {
				$lines = array ();
			}
		}
		$obj = new stdClass();

		$sec_name = '';
		$unparsed = 0;
		if (!$lines) {
			return $obj;
		}
		foreach ($lines as $line) {
			// ignore comments
			if ($line && $line{0} == ';') {
				continue;
			}
			$line = trim($line);

			if ($line == '') {
				continue;
			}
			$lineLen = strlen($line);
			if ($line && $line{0} == '[' && $line{$lineLen-1} == ']') {
				$sec_name = substr($line, 1, $lineLen - 2);
				if ($process_sections) {
					$obj-> $sec_name = new stdClass();
				}
			} else {
				if ($pos = strpos($line, '=')) {
					$property = trim(substr($line, 0, $pos));

					// property is assumed to be ascii
					if ($property && $property{0} == '"') {
						$propLen = strlen( $property );
						if ($property{$propLen-1} == '"') {
							$property = stripcslashes(substr($property, 1, $propLen - 2));
						}
					}
					// AJE: 2006-11-06 Fixes problem where you want leading spaces
					// for some parameters, eg, class suffix
					// $value = trim(substr($line, $pos +1));
					$value = substr($line, $pos +1);
					if ($value == 'false') {
						$value = false;
					}
					else if ($value == 'true') {
						$value = true;
					}
					else if ($value && $value{0} == '"') {
						$valueLen = strlen( $value );
						if ($value{$valueLen-1} == '"') {
							$value = stripcslashes(substr($value, 1, $valueLen - 2));
						}
					}

					if ($process_sections) {
						$value = str_replace('\n', "\n", $value);
						if ($sec_name != '') {
							$obj->$sec_name->$property = $value;
						} else {
							$obj->$property = $value;
						}
					} else {
						$obj->$property = str_replace('\n', "\n", $value);
					}
				} else {
					if ($line && $line{0} == ';') {
						continue;
					}
					if ($process_sections) {
						$property = '__invalid'.$unparsed ++.'__';
						if ($process_sections) {
							if ($sec_name != '') {
								$obj->$sec_name->$property = trim($line);
							} else {
								$obj->$property = trim($line);
							}
						} else {
							$obj->$property = trim($line);
						}
					}
				}
			}
		}
		return $obj;
	}
}