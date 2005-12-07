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
class JRegistryINIFormat extends JRegistryStorageFormat {

	function objectToString(&$data) {
		$retval = '';
		foreach(get_object_vars($data) as $namespace=>$groups) {
			if(!$this->r_namespacestate) {
				if($namespace != $this->r_namespacestate) {
					echo($this->r_namespace);
					echo "Breaking because namespace doesn't match, $namespace " . $this->r_namespacestate . " " . $this->r_namespace;
					break;
				}
			}
			foreach(get_object_vars($groups) as $key=>$item) {
				if(is_object($item)) {
					if($r_namespacestate) {
						$retval .= "[$namespace.$key]\n";
					} else {
						$retval .= "[$key]\n";
					}
					foreach(get_object_vars($item) as $subkey=>$value) {
						$retval .= "$subkey=$value\n";
					}
				} else {
					$retval .= "$key=$data\n";
				}
			}
		}
		return $retval;	
	}

	function &stringToObject($data,$namespace_override='') {
		
		$Configuration = new mosParameters($data);
		$configobject = $Configuration->parse($data, true);
		if($configObject = new stdClass()) {
			die("Blank configuration!");
		}
		$tmpnamespace = $this->r_namespace;
		if($namespace_override) {
			$tmpnamespace = $namespace_override;
		}
		$tmp = new stdClass();
		if(!$this->r_namespacestate) {
			foreach(get_object_vars($configobject) as $namespace=>$values) {
				$parts = explode('.',$namespace);
				$configobject->$parts[0]->$parts[1] = $values;
			}
		} else {

			$tmp->$tmpnamespace = new stdClass();
			foreach(get_object_vars($configobject) as $namespace=>$values) {
				$tmp->$tmpnamespace->$namespace = $values;
			}
		}	
		return $tmp;
	}

	function getFormatName() {
		return 'INI';
	}
}
?>