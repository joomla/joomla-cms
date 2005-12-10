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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

 
// Grab the support libraries
jimport('joomla.registry.storageengine');
jimport('joomla.registry.storageformat');

/**
 * The Joomla! Registry class
 * @package Joomla
 * @since 1.1
 */
class JRegistry {
	/** @var object Holds the class used to read/write data */
	var $r_storageengine		= null;
	/** @var object Holds the registry object */
	var $r_registryobject 		= null;

	/**
	 * Constructor
	 * @param object Storage Engine to use
	 */
	function JRegistry( &$engine ) {
		$this->r_storageengine = $engine;
	}

	/**
	 * Get the configuration setting (will fall back to default)
	 * @param string Registry path (e.g. joomla.content.showauthor)
	 * @param int    User Id
	 * @return mixed Value of entry
	 */
	function getValue( $regpath, $uid=0 ) {
		global $my;
		if ($uid == 0) {
			$uid = $my->id;
		}
		$parts = explode( '.', $regpath );
		if (count( $parts ) > 2) {
			return $this->r_storageengine->getConfig( $parts[0], $parts[1], $parts[2], $uid );
		}
	}

	/**
	 * Get the default configuration setting
	 * @param string Registry path (e.g. joomla.content.showauthor)	 
	 * @return mixed Value of entry
	 */
	function getDefaultValue( $regpath ) {
		$parts = explode( '.', $regpath );
		if (count( $parts ) > 2) {
			return $this->r_storageengine->getDefaultConfig( $parts[0], $parts[1], $parts[2] );
		}
	}

	/**
	 * Set the user configuration setting
	 * @param string Registry Path (e.g. joomla.content.showauthor)	 
	 * @param mixed  Value of entry
	 * @param int    User id
	 */
	function setValue( $regpath, $value, $uid=0 ) {
		$parts = explode( '.', $regpath );
		if(count( $parts ) > 2) {
			return $this->r_storageengine->setConfig( $parts[0], $parts[1], $parts[2], $value );
		}
	}

	/**
	 * Set the configuration setting
	 * @param string Registry Path (e.g. joomla.content.showauthor)	 
	 * @param mixed  Value of entry	
	 */
	function setDefaultValue( $regpath, $value ) {
		$parts = explode( '.', $regpath );
		if(count( $parts ) > 2) {
			return $this->r_storageengine->setDefaultConfig( $parts[0], $parts[1], $parts[2], $value );
		}
	}

	/**
	 * Set the storage engine
	 * @param object The new storage engine
	 */	
	function setStorageEngine( &$engine ) {
		$this->r_storageengine = $engine;
	}
	
	/**
	 * Get the storage engine
	 * @return object The current storage engine
	 */
	function &getStorageEngine() {
		return $this->r_storageengine;	
	}
}

