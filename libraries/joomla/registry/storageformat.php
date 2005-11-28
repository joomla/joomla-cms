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
class JRegistryStorageFormat {
	/**
	 * Parent Class for Storage Format
	 * The Format takes objects and turns them into a string representation for storage
	 * It also does the reverse and turns it into an object
	 */

	// Determines if a namespace should be included in the output/input
	var $r_namespacestate 	= true;
	// The namespace
	var $r_namespace 	= '';

	function JRegistryStorageFormat($namespace='',$namespacestate=true) {
		$this->setNamespace($namespace);
		$this->setNamespaceState($namespacestate);
	}

	/**
	 * @description
	 * @param object The object representation of the data
	 * @returns string The string representation of the data
	 */
	function objectToString(&$data) {
		return false;
	}

	/**
	 * @description Converts a String to an Object
	 * @param string The string representation of the data
	 * @returns object The object representation of the data
	 */
	function &stringToObject($data) {
		return false;
	}

	function setNamespaceState($namespace_state) {
		$this->r_namespacestate = $namespacestate;
	}

	function getNamespaceState() {
		return $this->r_namespacestate;
	}

	function setNamespace($namespace) {
		$this->r_namespace = $namespace;
	}

	function getNamespace() {
		return $this->r_namespace;
	}

	function getFormatName() {
		return "Undefined.";
	}

}
?>