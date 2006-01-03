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
 * Joomla! Registry Storage Engine Abstract Class
 * 
 * Storage Engines are responsible for handling basic I/O operations (reading and writing a string)
 * 
 * @package 	Joomla.Framework
 * @subpackage 	Registry
 * @abstract
 * @since 1.1
 
 */
class JRegistryStorageEngine {
	/** @var object Holds the class used to convert objects */
	var $r_storageformat 		= null;
	/** @var string Storage Identifier, roughly equivalent to 'filename' */
	var $r_storageidentifier   	= '';
	/** @var string Default Namespace */
	var $r_defaultnamespace		= '';
	/** @var object Configuration Cache */
	var $r_configuration	= null;

	/**
	 * Constructor
	 * @param object The format
	 * @param string Default Namespace
	 * @param string Storage Identifier
	 */
	function JRegistryStorageEngine( $format, $namespace, $identifier='' ) {
		$this->setStorageFormat( $format);
		$this->r_defaultnamespace = $namespace;
		$this->r_storageidentifier = $identifier;

	}

	/**
	 * Sets the default configuration (uid = 0);
	 * @param string namespace of setting (e.g. joomla)
	 * @param string group of setting (e.g. content)
	 * @param string name of setting (e.g. showauthor)
	 * @param mixed setting value
	 * @abstract
	 */
	function setDefaultConfig( $namespace, $group, $name, $value ) {
		return false;
	}

	/**
	 * Returns the default configuration (uid = 0);
	 * @param string namespace of setting (e.g. joomla)
	 * @param string group of setting (e.g. content)
	 * @param string name of setting (e.g. showauthor)
	 * @return mixed setting value
	 * @abstract
	 */
	function getDefaultConfig( $namespace, $group, $name ) {
		return false;
	}

	/**
	 * Sets a users configuration
	 * @param string namespace of setting (e.g. joomla)
	 * @param string group of setting (e.g. content)
	 * @param string name of setting (e.g. showauthor)
	 * @param mixed  setting value
	 * @param int	 user id
	 * @abstract
	 */
	function setConfig( $namespace, $group, $name, $value, $id=0 ) {
		return false;
	}

	/**
	 * Gets a users configuration
	 * @param string namespace of setting (e.g. joomla)
	 * @param string group of setting (e.g. content)
	 * @param string name of setting (e.g. showauthor)
	 * @param int	 user id
	 * @abstract
	 */
	function getConfig( $namespace, $group, $name, $id=0 ) {
		return false;
	}

	/**
	 * Sets the default namespace, useful for some storage engines
	 * @param string default namespace
	 */
	function setDefaultNamespace( $namespace ) {
		$this->r_defaultnamespace = $namespace;
	}

	/**
	 * Gets the default namespace
	 * @return string default namespace
	 */
	function getDefaultNamespace( $namespace ) {
		return $this->r_defaultnamespace;
	}

	/**
	 * Set the Storage format object
	 * @param object The storage format
	 */
	function setStorageFormat( &$format ) {
		$this->r_storageformat = $format;
	}

	/**
	 * Get the Storage Format object
	 * @return object The current storage format
	 */
	function &getStorageFormat() {
		return $this->r_storageformat;
	}

	/**
	 * Get the friendly name of the storage engine
	 * @return string friendly name
	 * @abstract
	 */
	function getStorageMethod() {
		return "Null";
	}
}
?>