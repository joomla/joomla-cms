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


/**
 * Joomla! Registry Storage Format Abstract Class
 * 
 * The Format takes objects and turns them into a string representation for storage
 * It also does the reverse and turns it into an object
 * 
 * @package 	Joomla.Framework
 * @subpackage 	Registry
 * @abstract
 * @since 1.1
 */
class JRegistryStorageFormat {
	/** @var boolean Determines if a namespace should be included in the output/input */
	var $r_namespacestate 	= true;
	/** @var boolean The default namespace */
	var $r_namespace 	= '';

	/**
	 * Constructor
	 * @param string default namespace
	 * @param boolean set the namespace output option
	 */
	function JRegistryStorageFormat( $namespace='', $namespacestate=true ) {
		$this->setNamespace( $namespace );
		$this->setNamespaceState( $namespacestate );
	}

	/**
	 * Converts an object to a string for storage
	 * @param object The object representation of the data
	 * @returns string The string representation of the data
	 * @abstract
	 */
	function objectToString( &$data ) {
		return false;
	}

	/**
	 * Converts a String to an Object
	 * @param string The string representation of the data
	 * @returns object The object representation of the data
	 * @abstract
	 */
	function &stringToObject( $data ) {
		return false;
	}

	/**
	 * Set the namespace state
	 * @param boolean new namespace state
	 */
	function setNamespaceState( $namespace_state ) {
		$this->r_namespacestate = $namespace_state;
	}

	/**
	 * Get the namespace state
	 * @return boolean current namespace state
	 */
	function getNamespaceState() {
		return $this->r_namespacestate;
	}

	/**
	 * Set the default namespace
	 * @param string new default namespace
	 */
	function setNamespace( $namespace ) {
		$this->r_namespace = $namespace;
	}

	/**
	 * Get the default namespace
	 * @return string the current default namespace
	 */
	function getNamespace() {
		return $this->r_namespace;
	}

	/**
	 * Get the friendly name of this format
	 * @return string friendly name
	 * @abstract
	 */
	function getFormatName() {
		return "Undefined.";
	}

}
?>