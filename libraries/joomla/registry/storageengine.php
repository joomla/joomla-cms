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
class JRegistryStorageEngine {
	// Holds the class used to convert objects
	var $r_storageformat 		= null;
	// Storage Identifier
	var $r_storageidentifier   	= '';
	// Default Namespace
	var $r_defaultnamespace		= '';

	function JRegistryStorageEngine($format) {
		$this->setStorageFormat($format);

	}

	function setDefaultConfig($namespace,$group,$name,$value) {
		return false;
	}

	function getDefaultConfig($namespace,$group,$name) {
		return false;
	}

	function setConfig($namespace,$group,$name,$value,$id) {
		return false;
	}

	function getConfig($namespace,$group,$name,$id) {
		return false;
	}

	function setDefaultNamespace($namespace) {
		$this->r_defaultnamespace = $namespace;
	}

	function getDefaultNamespace($namespace) {
		return $this->r_defaultnamespace;
	}

	function setStorageFormat(&$format) {
		$this->r_storageformat = $format;
	}

	function &getStorageFormat() {
		return $this->r_storageformat;
	}

	function &setStorageFormat(&$format) {
		$this->r_storageformat = $format;
	}

	function getStorageMethod() {
		return "Null";
	}

	function store($location='') {
		return false;
	}

	function load() {
		return false;
	}
}
?>