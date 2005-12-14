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
 * File Storage Engine for JRegistry
 * @package Joomla
 * @since 1.1
 */
class JRegistryFileEngine extends JRegistryStorageEngine {	
	/* Note:
	 * This storage engine doesn't support user configuration
	 * As such these functions fall back to the defaults
	 */

	/**
	 * Constructor
	 * @param object Storage Format
	 * @param string default namespace
	 * @param string storage identifier
	 */
	function JRegistryFileEngine( $format, $namespace, $identifier='' ) {
		$this->r_storageformat = $format;
		$this->r_defaultnamespace = $namespace;
		$this->r_storageidentifier = $identifier;		
		
		// Load and parse the file for caching
		if (is_file( $identifier )) {
			$tmpFile = fopen( $identifier, "r" );			
			$contents = fread( $tmpFile, filesize( $identifier ) );			
			$this->r_configuration = $format->stringToObject( $contents );			
			fclose( $tmpFile );
		} else {
			die($identifier . " isn't a file!");
		}
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
		if (!isset( $this->r_configuration->$namespace )) {
			$this->r_configuration->$namespace = new stdClass();
		}
		if (!isset( $this->r_configuration->$namespace->$group )) {
			$this->r_configuration->$namespace->$group = new stdClass();
		}
		$this->r_configuration->$namespace->$group->$name = $value;

		if (is_file( $this->r_storageidentifier )) {
			unlink( $this->r_storageidentifier );
		}
		$tmpFile = fopen( $this->r_storageidentifier, 'w+');
		fwrite( $tmpFile, $this->r_storageformat->objectToString( $this->r_configuration ) );
		fclose( $tmpFile );
		return true;		
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
		if (!isset( $this->r_configuration->$namespace )) {
			return false;
		}
		if (!isset( $this->r_configuration->$namespace->$group )) {
			return false;
		}
		return $this->r_configuration->$namespace->$group->$name;
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
		return JRegistryFileEngine::setDefaultconfig( $namespace, $group, $name, $value );
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
		return JRegistryFileEngine::getDefaultconfig( $namespace, $group, $name );
	}
	
	
	/**
	 * Get the friendly name of this format
	 * @return string friendly name
	 * @abstract
	 */
	function getStorageName() {
		return "File";
	}	
}