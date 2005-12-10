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
 * XML Format for JRegistry
 * @package Joomla
 * @since 1.1
 */
class JRegistryXMLFormat extends JRegistryStorageFormat {
	// Load the Default XML Configuration from the database
	function &stringToObject( $data, $namespace='' ) {
		// Parse Configuration
		$Configuration = new mosParameters( $data );		
		$success = $Configuration->parseXML( $data, true );
		return ($success);
		if (!$success) {
			$success = $Configuration->parseXML( "", true ); // Should work 100% of the time!
			if (!success) {
				// This should never ever occur. If it does, theres a serious error.
				die( "The impossible just occured!" );
			}
		}
	}

	function objectToString( &$data ) {
		$retval = "<?xml version=\"1.0\" ?>\n<config>\n";
		foreach (get_object_vars( $data ) as $namespace=>$groups) {						
			if (!$this->r_namespacestate) {
				if ($namespace != $this->r_namespace) {
					//echo $this->r_namespace;
					//echo "Breaking because namespace doesn't match, $namespace " . $this->r_namespacestate . " " . $this->r_namespace;
					break;
				}
			}
			$retval .= "<namespace value=\"$namespace\">\n";
			foreach (get_object_vars( $groups ) as $key=>$item) {				
				$retval .= "\t<group value=\"$key\">\n";
				foreach (get_object_vars( $item ) as $subkey=>$value) {
					$retval .= "\t\t<entry name=\"$subkey\">$value</entry>\n";
				}
				$retval .= "\t</group>\n";
			}
			$retval .= "</namespace>\n";
		}
		$retval .= '</config>';	
		return $retval;		
	}
	
	function getFormatName() {
		return 'XML';
	}	

}
?>