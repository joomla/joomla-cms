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
class JRegistryDatabaseEngine extends JRegistryStorageEngine {

	function JRegistryDatabaseEngine($format,$namespace, $identifier='#__registry') {
		$this->r_storageformat = $format;
		$this->r_defaultnamespace = $namespace;
		$this->r_storageidentifier = $identifier;		
	}

	// Reset an existing config (e.g. delete)
	function resetConfig($namespace,$currentid) {
		global $database, $my;
		if(!$currentid) { return false; }
		$query = "DELETE FROM #__registry WHERE uid = $currentid AND namespace = '$namespace'";
		$database->setQuery($query);
		$database->Query();
		return true;
	}

	// Create an empty config
	function createEmptyConfig($namespace,$currentid) {
		global $database;
		JRegistryDatabaseEngine::resetConfig($namespace,$currentid);
		$query = "INSERT INTO #__registry VALUES ('','$namespace','','$currentid','')";
		$database->setQuery($query);
		if(!$database->Query()) {
			//die("Error creating empty configuration! (".$database->getQuery().")");
			return false;
		}
		return true;
	}

	function configExists($namespace) {
		global $database;
		$query = "SELECT * FROM #__registry WHERE namespace = '$namespace'";
		$database->setQuery($query);
		$database->Query();
		if(!$database->getNumRows()) {
			return false;
		}
		return true;
	}

	// Test Current Details
	function testUserConfig($namespace,$currentid=0) {
		global $database, $my;
		if(!$currentid) {
			$currentid = $my->id;
		}
		if($currentid != 0) {
			$query = "SELECT datafield FROM #__registry WHERE uid = $currentid AND namespace = '$namespace'";
			$database->setQuery($query);
			$database->Query();
			echo $database->getErrorMsg();
			$resultant = $database->loadRow();
			if($resultant[0] == "") {
				JRegistryDatabaseEngine::resetConfig($namespace,$currentid);
				JRegistryDatabaseEngine::createEmptyConfig($namespace,$currentid);
			}
		}
	}

	// Test Current Details
	function testDefaultConfig($namespace) {
		global $database;
		$query = "SELECT datafield FROM #__registry WHERE uid = 0 AND namespace = '$namespace'";
		$database->setQuery($query);
		$database->Query();
		echo $database->getErrorMsg();
		$resultant = $database->loadRow();
		if($resultant[0] == "") {
			JRegistryDatabaseEngine::resetConfig($namespace,0);
			JRegistryDatabaseEngine::createEmptyConfig($namespace,0);
		}
	}

	// Load the Default Configuration from the database
	/*
	 * @param string namespace override
	 * @returns string configuration 'file'
	 */
	function loadDefaultConfiguration($namespace='') {
		global $database;
		if(!$namespace) {
			$namespace = $this->r_defaultnamespace;
		}
		// Pull configuration out of the database
		$query = "SELECT datafield FROM #__registry WHERE uid = 0 AND namespace='$namespace'";
		$database->setQuery($query);
		$database->Query($query);

		if(!$database->getNumRows()) {
			return '';
		}

		// Only want (or need) the first row. Any surplus rows shouldn't be there
		$resultant = $database->loadRow();
		return $resultant[0];
	}

	// Load the user's Configuration from the database
	function loadUserConfiguration($currentid, &$config, $namespace) {
		global $database;
		if(!$currentid) { return false; }
		// Pull configuration out of the database
		$query = "SELECT datafield FROM  #__registry WHERE uid = $currentid AND namespace = '$namespace'";
		$database->setQuery($query);
		$database->Query();
		// Only want (or need) the first row). Any surplus rows shouldn't be there
		$resultant = $database->loadRow();
		return $resultant[0];
	}

	function getConfig($namespace, $group,$name,$currentid=0) {
		global $my,$database;
		$settingInUserConfig = false;
		$setting = false;
		if(!$currentid) {
			$currentid = $my->id;
		}


		if(!JRegistryDatabaseEngine::configExists($namespace)) {
			return null;
		}

		// Parse
		JRegistryDatabaseEngine::loadUserConfiguration($currentid, $Configuration, $namespace);
		$userConfiguration = $r_storageformat->stringToObject($Configuration);
		//$r_storageengine->load

		if(array_key_exists($group, get_object_vars($userConfiguration))) {
			if(array_key_exists($name, get_object_vars($userConfiguration->$group))) {
				$setting = $userConfiguration->$group->$name;
			} else {
				$setting = JRegistryDatabaseEngine::getDefaultConfig($namespace, $component,$name);
			}
		} else {
			$setting = JRegistryDatabaseEngine::getDefaultConfig($namespace, $component,$name);
		}

		return $setting;
	}	
	
	function getDefaultConfig($namespace, $group, $name) {
		global $my, $database;
		return 'Hi';
	}

	// Set the configuration setting
	function setConfig($namespace, $component,$name,$value) {
		global $my, $database;
		$currentid = $my->id;
		if($currentid == 0) {
			// Bail out
			return false;
		}
		$newConfigSet = false;

		// Parse Configuration
		JRegistry::loadUserConfiguration($currentid, $userConfiguration, $namespace,true);

		if(isset($userConfiguration->$component)) {
			if(isset($userConfiguration->$component->$name)) {
				$userConfiguration->$component->$name = $value;
			} else {
				$userConfiguration->$component->$name = $value;
			}
		} else {
			$userConfiguration->$component = new stdClass();
			$userConfiguration->$component->$name = $value;
		}
		JRegistry::testUserConfig($namespace);
		$iniFile = JRegistry::objectToINI($userConfiguration);
		$query = "UPDATE #__registry SET data = '$iniFile' WHERE user_id = $currentid AND namespace = '$namespace'";
		$database->setQuery($query);
		$database->Query();
		$newConfigSet = true;
		return $newConfigSet;
	}

	// Set the default configuration setting (for adminland)
	function setDefaultConfig($namespace, $group,$name,$value) {
		global $database;
		$currentid = 0;
		$newConfigSet = false;

		$this->r_storageformat->r_namespacestate = true;						
		$data = JRegistryDatabaseEngine::loadDefaultConfiguration($namespace);
		$userConfiguration = $this->r_storageformat->stringToObject($data,$namespace);
		echo "<pre>Configuration:\n";
		print_r($userConfiguration);
		echo '</pre><br>for:<br><pre>';
		print_r($data);
		echo '</pre><hr>';
		if(!isset($userConfiguration->$namespace)) {
			$userConfiguration->$namespace = new stdClass();
		}
		if(!isset($userConfiguration->$namespace->$group)) {
			$userConfiguration->$namespace->$group = new stdClass();
		}
		$userConfiguration->$namespace->$group->$name = $value;

		JRegistryDatabaseEngine::testDefaultConfig($namespace);
		$iniFile = $this->r_storageformat->objectToString($userConfiguration);

		echo "<pre>INI:\n";
		echo $iniFile;
		echo '</pre><hr>';

		
		$query = "UPDATE #__registry SET datafield = '$iniFile' WHERE uid = 0 AND namespace = '$namespace'";
		$database->setQuery($query);
		$database->Query() or die($database->getErrorMsg());
		$newConfigSet = true;
		return $newConfigSet;
	}

}
?>