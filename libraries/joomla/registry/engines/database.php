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
 * Database Storage Engine for JRegistry
 * 
 * @package 	Joomla.Framework
 * @subpackage 	Registry
 * @since 1.1
 */
class JRegistryDatabaseEngine extends JRegistryStorageEngine {

	/**
	 * Constructor
	 * @param object Storage Format
	 * @param string default namespace
	 * @param string storage identifier
	 */
	function JRegistryDatabaseEngine( $format, $namespace, $identifier='#__registry' ) {
		$this->r_storageformat = $format;
		$this->r_defaultnamespace = $namespace;
		$this->r_storageidentifier = $identifier;
	}

	/**
	 * Reset an existing config (e.g. delete)
	 * @param string namespace to delete
	 * @param int    user id to delete
	 * @return boolean
	 */
	function resetConfig( $namespace, $currentid ) {
		global $database, $my;
		if (!$currentid) { return false; }
		$query = "DELETE FROM #__registry WHERE uid = $currentid AND namespace = '$namespace'";
		$database->setQuery( $query );
		$database->Query();
		return true;
	}

	/**
	 * Create an empty config (mirrors resetConfig)
	 * @param string namespace to create
	 * @param int    user id to create
	 * @return boolean
	 */
	function createEmptyConfig( $namespace, $currentid ) {
		global $database;
		JRegistryDatabaseEngine::resetConfig( $namespace, $currentid );
		$query = "INSERT INTO #__registry VALUES ('','$namespace','','$currentid','')";
		$database->setQuery( $query );
		if (!$database->Query()) {
			return false;
		}
		return true;
	}

	/**
	 * Test to see if a configuration exists for a namespace
	 * @param string namespace to test
	 * @return boolean
	 */
	function configExists( $namespace ) {
		global $database;
		$query = "SELECT * FROM #__registry WHERE namespace = '$namespace'";
		$database->setQuery( $query );
		$database->Query();
		if (!$database->getNumRows()) {
			return false;
		}
		return true;
	}

	/**
	 * Test Current Details (create if missing, user specific)
	 * @param string namespace
	 * @param int    user id
	 */
	function testUserConfig ( $namespace, $currentid=0 ) {
		global $database, $my;
		if (!$currentid) {
			$currentid = $my->id;
		}
		if ($currentid != 0) {
			$query = "SELECT datafield FROM #__registry WHERE uid = $currentid AND namespace = '$namespace'";
			$database->setQuery( $query );
			$database->Query();
			$resultant = $database->loadRow();
			if ($resultant[0] == "") {
				JRegistryDatabaseEngine::resetConfig( $namespace, $currentid );
				JRegistryDatabaseEngine::createEmptyConfig( $namespace, $currentid );
			}
		}
	}

	/**
	 * Test Current Details (create if missing, default)
	 * @param string namespace
	 * @param int    user id
	 */
	function testDefaultConfig( $namespace ) {
		global $database;
		$query = "SELECT datafield FROM #__registry WHERE uid = 0 AND namespace = '$namespace'";
		$database->setQuery( $query );
		$database->Query();
		$resultant = $database->loadRow();
		if ($resultant[0] == "") {
			JRegistryDatabaseEngine::resetConfig( $namespace, 0 );
			JRegistryDatabaseEngine::createEmptyConfig( $namespace, 0 );
		}
	}

	/**
	 * Load the Default Configuration from the database
	 * @param string namespace override
	 * @returns string configuration 'file'
	 */
	function loadDefaultConfiguration( $namespace='' ) {
		global $database;
		if (!$namespace) {
			$namespace = $this->r_defaultnamespace;
		}
		// Pull configuration out of the database
		$query = "SELECT datafield FROM #__registry WHERE uid = 0 AND namespace='$namespace'";
		$database->setQuery( $query );
		$database->Query( $query );

		if (!$database->getNumRows()) {
			return '';
		}

		// Only want (or need) the first row. Any surplus rows shouldn't be there
		$resultant = $database->loadRow();
		return $resultant[0];
	}

	/**
	 * Load the user's Configuration from the database
	 * @param int Current User ID
	 * @param string namespace
	 */
	function loadUserConfiguration( $currentid, $namespace ) {
		global $database;
		if (!$currentid) { return false; }
		// Pull configuration out of the database
		$query = "SELECT datafield FROM  #__registry WHERE uid = $currentid AND namespace = '$namespace'";
		$database->setQuery( $query );
		$database->Query();
		// Only want (or need) the first row). Any surplus rows shouldn't be there
		$resultant = $database->loadRow();
		return $resultant[0];
	}

	/**
	 * Get Configuration
	 * @param string namespace
	 * @param string group
	 * @param string name
	 * @param int    user id
	 * @return mixed value
	 */
	function getConfig( $namespace, $group, $name, $currentid=0 ) {
		global $my,$database;
		$settingInUserConfig = false;
		$setting = false;
		if (!$currentid) {
			$currentid = $my->id;
		}


		if (!JRegistryDatabaseEngine::configExists( $namespace )) {
			return null;
		}


		$this->r_storageformat->r_namespacestate = true;
		$data = JRegistryDatabaseEngine::loadUserConfiguration( $currentid, $namespace );
		$userConfiguration = $this->r_storageformat->stringToObject( $data, $namespace );
		if (array_key_exists( $namespace, get_object_vars( $userConfiguration ) )) {
			if (array_key_exists( $group, get_object_vars( $userConfiguration->$namespace ) )) {
				if (array_key_exists( $name, get_object_vars( $userConfiguration->$namespace->$group ) )) {
					$setting = $userConfiguration->$namespace->$group->$name;
				} else {
					$setting = JRegistryDatabaseEngine::getDefaultConfig( $namespace, $group, $name );
				}
			} else {
				$setting = JRegistryDatabaseEngine::getDefaultConfig( $namespace, $group, $name );
			}
		} else {
			$setting = JRegistryDatabaseEngine::getDefaultConfig( $namespace, $group, $name );
		}

		return $setting;
	}

	/**
	 * Get Default Configuration
	 * @param string namespace
	 * @param string group
	 * @param string name
	 * @return mixed value
	 */
	function getDefaultConfig( $namespace, $group, $name ) {
		global $my, $database;
		$this->r_storageformat->r_namespacestate = true;
		$data = JRegistryDatabaseEngine::loadDefaultConfiguration( $namespace );
		$userConfiguration = $this->r_storageformat->stringToObject( $data, $namespace );
		if (!isset( $userConfiguration->$namespace )) {
			return false;
		}
		if (!isset( $userConfiguration->$namespace->$group )) {
			return false;
		}
		return $userConfiguration->$namespace->$group->$name;
	}

	/**
	 * Set the configuration setting
	 * @param string namespace
	 * @param string group
	 * @param string name
	 * @param mixed  value
	 * @param int    user id
	 */
	function setConfig( $namespace, $group, $name, $value, $currentid=0 ) {
		global $my, $database;

		if ($currentid == 0 && $my->id == 0) {
			// Bail out
			return false;
		}
		if ($currentid == 0) {
			$currentid = $my->id;
		}
		$newConfigSet = false;

		$this->r_storageformat->r_namespacestate = true;
		$data = JRegistryDatabaseEngine::loadUserConfiguration( $currentid, $namespace );
		$userConfiguration = $this->r_storageformat->stringToObject( $data, $namespace );

		if (!isset( $userConfiguration->$namespace )) {
			$userConfiguration->$namespace = new stdClass();
		}
		if (!isset( $userConfiguration->$namespace->$group )) {
			$userConfiguration->$namespace->$group = new stdClass();
		}
		$userConfiguration->$namespace->$group->$name = $value;

		JRegistryDatabaseEngine::testUserConfig( $namespace) ;
		$iniFile = $this->r_storageformat->objectToString( $userConfiguration );
		$this->r_configuration = $userConfiguration;
		$query = "UPDATE #__registry SET datafield = '$iniFile' WHERE uid = $currentid AND namespace = '$namespace'";
		$database->setQuery( $query );
		$database->Query();
		$newConfigSet = true;
		return $newConfigSet;
	}

	/**
	 * Set the default configuration setting (for adminland)
	 * @param string namespace
	 * @param string group
	 * @param string name
	 * @param mixed  value
	 */
	function setDefaultConfig( $namespace, $group, $name, $value ) {
		global $database;
		$currentid = 0;
		$newConfigSet = false;

		$this->r_storageformat->r_namespacestate = true;
		$data = JRegistryDatabaseEngine::loadDefaultConfiguration( $namespace );
		$userConfiguration = $this->r_storageformat->stringToObject( $data, $namespace );

		if (!isset( $userConfiguration->$namespace )) {
			$userConfiguration->$namespace = new stdClass();
		}
		if (!isset( $userConfiguration->$namespace->$group )) {
			$userConfiguration->$namespace->$group = new stdClass();
		}
		$userConfiguration->$namespace->$group->$name = $value;

		JRegistryDatabaseEngine::testDefaultConfig( $namespace );
		$iniFile = $this->r_storageformat->objectToString( $userConfiguration );

		$this->r_configuration = $userConfiguration;
		$query = "UPDATE #__registry SET datafield = '$iniFile' WHERE uid = 0 AND namespace = '$namespace'";
		$database->setQuery( $query );
		$database->Query();
		$newConfigSet = true;
		return $newConfigSet;
	}

}
?>