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
 
// Grab the support libraries
jimport('joomla.registry.storageengine');
jimport('joomla.registry.storageformat');

class JRegistry {
	// Object!
	// Holds the class used to read/write data
	var $r_storageengine		= null;
	// Holds the registry object
	var $r_registryobject 		= null;

	function JRegistry($engine) {
		$this->r_storageengine = $engine;
	}

	// Get the configuration setting
	function getValue($regpath,$uid=0) {
		global $my;
		if($uid == 0) {
			$uid = $my->id;
		}
		$parts = explode('.',$regpath);
		if(count($parts) > 2) {
			return($this->r_storageengine->getConfig($parts[0],$parts[1],$parts[2],$uid));
		}
	}

	function getDefaultValue($regpath) {
		$parts = explode('.',$regpath);
		if(count($parts) > 2) {
			return($this->r_storageengine->getDefaultConfig($parts[0],$parts[1],$parts[2]));
		}
	}

	function setValue($regpath,$value,$uid=0) {
		$parts = explode('.',$regpath);
		if(count($parts) > 2) {
			return($this->r_storageengine->setConfig($parts[0],$parts[1],$parts[2],$value));
		}
	}

	// Set the configuration setting
	function setDefaultValue($regpath,$value) {
		$parts = explode('.',$regpath);
		if(count($parts) > 2) {
			return($this->r_storageengine->setDefaultConfig($parts[0],$parts[1],$parts[2],$value));
		}
	}

}

