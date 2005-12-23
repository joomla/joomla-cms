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
	 * Returns a reference to the global JRegistry object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $registry = &JRegistry::getInstance($engine);</pre>
	 *
	 * @static
	 * @param string $engine The storage engine to use
	 * @param string $format The storage format to use
	 * @param string $namespace Registry namespace [Optional: defaults to 'joomla']
	 * @return object  The JRegistry object.
	 * @since 1.1
	 */
	function & getInstance($engine, $format, $namespace='joomla', $identifier='', $namespaceState=true) {
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$id = md5($engine.$format.$namespace.$identifier);

		if (empty ($instances[$id])) {

			// First load the engine and format
			jimport('joomla.registry.storageengine.'.$engine);
			jimport('joomla.registry.storageformat.'.$format);

			// Next instantiate the format
			$name = 'JRegistry'.$format.'Format';
			$r_format =& new $name($namespace, $namespaceState);

			// Next instantiate the engine
			$name = 'JRegistry'.$engine.'Engine';
			$r_engine =& new $name($r_format, $namespace, $identifier);

			$instances[$id] =& new JRegistry($r_engine);
		}

		return $instances[$id];
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

	/**
	 * Set storage format
	 * @param object The new storage format
	 */
	function setStorageFormat( &$format ) {
		$this->r_storageengine->setStorageFormat( $format );
	}

	/**
	 * Get the storage format
	 * @return object The current storage format
	 */
	function &getStorageFormat() {
		return $this->r_storageengine->getStorageFormat();
	}
}

