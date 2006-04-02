<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
 
/**
 * INI format handler for JRegistry
 * 
 * @author 		Samuel Moffatt <pasamio@gmail.com>
 * @package 	Joomla.Framework
 * @subpackage 	Registry
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
				$retval .= "[$key]\n";
				foreach (get_object_vars( $level1 ) as $key => $level2) {
					if (!is_object($level2) && !is_array($level2)) {
						$retval .= "$key=$level2\n";
					}
				}
				$retval .= "\n";
			} else {
				$prepend .= "$key=$level1\n";
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
	function stringToObject( $data, $process_sections = false, $asArray = false ) 
	{
		if (is_string($data)) {
			$lines = explode("\n", $data);
		} else
			if (is_array($data)) {
				$lines = $data;
			} else {
				$lines = array ();
			}
		$obj = $asArray ? array () : new stdClass();

		$sec_name = '';
		$unparsed = 0;
		if (!$lines) {
			return $obj;
		}
		foreach ($lines as $line) {
			// ignore comments
			if ($line && $line[0] == ';') {
				continue;
			}
			$line = trim($line);

			if ($line == '') {
				continue;
			}
			if ($line && $line[0] == '[' && $line[strlen($line) - 1] == ']') {
				$sec_name = substr($line, 1, strlen($line) - 2);
				if ($process_sections) {
					if ($asArray) {
						$obj[$sec_name] = array ();
					} else {
						$obj-> $sec_name = new stdClass();
					}
				}
			} else {
				if ($pos = strpos($line, '=')) {
					$property = trim(substr($line, 0, $pos));

					// property is assumed to be ascii
					if (substr($property, 0, 1) == '"' && substr($property, -1) == '"') {
						$property = stripcslashes(substr($property, 1, count($property) - 2));
					}
					$value = trim(substr($line, $pos +1));
					if ($value == 'false') {
						$value = false;
					}
					if ($value == 'true') {
						$value = true;
					}
					if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
						$value = stripcslashes(substr($value, 1, strlen($value) - 2));
					}

					if ($process_sections) {
						$value = str_replace('\n', "\n", $value);
						if ($sec_name != '') {
							if ($asArray) {
								$obj[$sec_name][$property] = $value;
							} else {
								$obj-> $sec_name-> $property = $value;
							}
						} else {
							if ($asArray) {
								$obj[$property] = $value;
							} else {
								$obj-> $property = $value;
							}
						}
					} else {
						$value = str_replace('\n', "\n", $value);
						if ($asArray) {
							$obj[$property] = $value;
						} else {
							$obj-> $property = $value;
						}
					}
				} else {
					if ($line && trim($line[0]) == ';') {
						continue;
					}
					if ($process_sections) {
						$property = '__invalid'.$unparsed ++.'__';
						if ($process_sections) {
							if ($sec_name != '') {
								if ($asArray) {
									$obj[$sec_name][$property] = trim($line);
								} else {
									$obj-> $sec_name-> $property = trim($line);
								}
							} else {
								if ($asArray) {
									$obj[$property] = trim($line);
								} else {
									$obj-> $property = trim($line);
								}
							}
						} else {
							if ($asArray) {
								$obj[$property] = trim($line);
							} else {
								$obj-> $property = trim($line);
							}
						}
					}
				}
			}
		}
		return $obj;
	}
}
?>