<?php
/**
 * @version $Id: tree.php 881 2005-11-05 06:03:09Z Jinx $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// ensure this file is being included by a parent file
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 */
class JRegistryXMLFormat {
	// Load the Default XML Configuration from the database
	function stringToObject(&$Configuration, $namespace) {				
		// Parse Configuration
		$success = $Configuration->parseXML($Configuration_xml, true);	
		if(!$success) {
			$success = $Configuration->parseXML("<?xml version=\"1.0\" ?><mosconfig></mosconfig>",true); // Should work 100% of the time!
			if(!success) {
				// This should never ever occur. If it does, theres a serious error.
				die("The impossible just occured!");
			}
		}
	}	
	
	function objectToString(&$target) {
	
	}

}
?>