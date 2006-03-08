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
 * PHP class format handler for JRegistry
 * 
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage 	Registry
 * @since		1.1
 */
class JRegistryFormatPHP extends JRegistryFormat {

	/**
	 * Converts an object into a php class string.
	 * 	- NOTE: Only one depth level is supported.
	 * 
	 * @access public
	 * @param object $object Data Source Object
	 * @param array  $param  Parameters used by the formatter
	 * @return string Config class formatted string
	 * @since 1.1
	 */
	function objectToString( &$object, $params ) {
		
		// Build the object variables string
		$vars = '';
		foreach (get_object_vars( $object ) as $k => $v) {
			$vars .= "\tvar $". $k . " = '" . addslashes($v) . "';\n";
		}

		$str = "<?php\nclass ".$params['class']." {\n";
		$str .= $vars;
		$str .= "}\n?>";

		return $str;	
	}

	/**
	 * Placeholder method
	 * 
	 * @access public
	 * @return boolean True
	 * @since 1.1
	 */
	function stringToObject() {
		return true;
	}
}
?>