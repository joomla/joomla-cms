<?php
/**
 * Installer Dependency Checking
 * Builds a tree to determine the dependency
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
 
/**
 * Dependency Checker
 * @since 1.6
 */
class JDependency {
	
	
	function getCurrentPlatform() {
		$version = new JVersion();
		$filter =& JFilterInput::getInstance();
		$name = strtolower($filter->clean($version->PRODUCT, 'cmd'));
		return Array('name'=>$name, 'version'=>$version->getShortVersion());
	}
}