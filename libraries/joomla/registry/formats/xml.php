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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
 
// XML Support Library
jimport('domit.xml_domit_include');
 
/**
 * XML Format for JRegistry
 * @package Joomla
 * @since 1.1
 */
class JRegistryXMLFormat extends JRegistryStorageFormat {

	// Load the Default XML Configuration from the database
	function &stringToObject( $data, $namespace='' ) {
		// Parse Configuration
		$Configuration =& new DOMIT_Document();		
		$success = $Configuration->parseXML( $data, true );
		if (!$success) {
			return false;
		}

		// Check that the top level node is correct				
		if ($Configuration->documentElement->nodeName != 'config') {
// 			// Should only happen if tampering occurs with the file.
			return false;
		}
		
		// Check to see if child nodes exist or if empty config
		if (!$Configuration->documentElement->hasChildNodes()) {
			return false;
		}
		
		// Create a temporary object
		$tmpConfig = new stdClass();
		
		$namespaces =& $Configuration->documentElement->childNodes;
		$namespaceCount =& $Configuration->documentElement->childCount;
		
		
		// Go through each 'namespace'
		for ($i = 0; $i < $namespaceCount; $i++) {
			// Check to see if its a namespace with children				
			if ($namespaces[$i]->nodeName == "namespace" && $namespaces[$i]->hasChildNodes() && ($currentNamespace = $namespaces[$i]->getAttribute( "name" )) != '') {				
				$tmpConfig->$currentNamespace = new stdClass();
				$groupCount =& $namespaces[$i]->childCount;
				$groups =& $namespaces[$i]->childNodes;
				for ($k = 0; $k < $groupCount; $k++) {
					if ($groups[$k]->nodeName == "group" && $groups[$k]->hasChildNodes() && ($currentGroup = $groups[$k]->getAttribute( "name" )) != '') {						
						$tmpConfig->$currentNamespace->$currentGroup = new stdClass();					
						$entryCount =& $groups[$k]->childCount . "\n";
						$entries =& $groups[$k]->childNodes . "\n";						
						
						for ($j = 0; $j < $entryCount; $j++) {
							if ($entries[$j]->nodeName == "entry" && ($currentName = $entries[$j]->getAttribute( "name" )) != '') {
								$tmpConfig->$currentNamespace->$currentGroup->$currentName = $entries[$j]->getText();							
							}
						} 
					}
				}
			}
		}
		return $tmpConfig;
		

	}

	function objectToString( &$data ) {
		$retval = "<?xml version=\"1.0\" ?>\n<config>\n";
		foreach (get_object_vars( $data ) as $namespace=>$groups) {						
			if (!$this->r_namespacestate) {
				if ($namespace != $this->r_namespace) {
					break;
				}
			}
			$retval .= "<namespace name=\"$namespace\">\n";
			foreach (get_object_vars( $groups ) as $key=>$item) {				
				$retval .= "\t<group name=\"$key\">\n";
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