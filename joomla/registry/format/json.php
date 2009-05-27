<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JSON format handler for JRegistry
 *
 * @package 	Joomla.Framework
 * @subpackage	Registry
 * @since		1.6
 */
class JRegistryFormatJSON extends JRegistryFormat
{
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
	public function objectToString(&$object, $params)
	{
		$string = json_encode($object);
		return $string;
	}

	/**
	 * Parse an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 *
	 * @access public
	 * @param mixed The INI string or array of lines
	 * @param boolean add an associative index for each section [in brackets]
	 * @return object Data Object
	 */
	public function stringToObject($data, $process_sections = false)
	{
		$data = trim($data);
		if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}')) {
			$ini = & JRegistryFormat::getInstance('INI');
			$obj = $ini->stringToObject($data, $process_sections);
		}
		else {
			$obj = json_decode($data);
		}
		return $obj;
	}
}
