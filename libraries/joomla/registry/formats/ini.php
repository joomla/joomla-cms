<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
 * @since 1.1
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
	 * @return string INI Formatted String
	 */
	function objectToString( &$object ) {
		
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
	 * Converts an INI formatted string into an object
	 * 
	 * @access public
	 * @param string  INI Formatted String
	 * @return object Data Object
	 */
	function &stringToObject( $data ) {
		// Use the JParameters class to parse the INI filr		
		$ini =& new JParameters( $data );
		
		return $ini->parse( $data, true );
	}
}
?>